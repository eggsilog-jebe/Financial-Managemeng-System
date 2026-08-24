<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\JournalEntryData;
use App\DTOs\JournalLineData;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\BankDeposit;
use App\Models\CashierShift;
use DomainException;
use Illuminate\Support\Facades\DB;

final class CashDrawerAndDepositService
{
    public function __construct(
        private readonly JournalEntryService $journalEntryService
    ) {}

    /**
     * Open a new Cashier Shift and record opening float.
     */
    public function openShift(int $cashierId, string $terminalName = 'POS-MAIN-01', string $openingFloat = '5000.0000'): CashierShift
    {
        $existingOpen = CashierShift::where('cashier_id', $cashierId)
            ->where('status', 'OPEN')
            ->first();

        if ($existingOpen) {
            throw new DomainException("Cashier already has an active open shift [{$existingOpen->shift_code}].");
        }

        $shiftCode = 'SHIFT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));

        return CashierShift::create([
            'shift_code'         => $shiftCode,
            'cashier_id'         => $cashierId,
            'terminal_name'      => $terminalName,
            'opened_at'          => now(),
            'opening_cash_float' => $openingFloat,
            'expected_cash'      => $openingFloat,
            'actual_cash_counted'=> '0.0000',
            'cash_variance'      => '0.0000',
            'status'             => 'OPEN',
        ]);
    }

    /**
     * Close out cashier shift with cash drawer counting and variance audit.
     */
    public function closeShift(int $shiftId, string $actualCashCounted, ?string $notes = null): CashierShift
    {
        return DB::transaction(function () use ($shiftId, $actualCashCounted, $notes): CashierShift {
            $shift = CashierShift::findOrFail($shiftId);

            if ($shift->status !== 'OPEN') {
                throw new DomainException("Shift [{$shift->shift_code}] is already closed.");
            }

            $expectedCash = (string) $shift->expected_cash;
            $variance = bcsub($actualCashCounted, $expectedCash, 4); // positive = overage, negative = shortage

            $shift->update([
                'closed_at'           => now(),
                'actual_cash_counted' => $actualCashCounted,
                'cash_variance'       => $variance,
                'status'              => 'CLOSED',
                'notes'               => $notes,
            ]);

            return $shift;
        });
    }

    /**
     * Transfer drawer cash collections to Bank Operating Account (Armored Pickup / Vault Deposit).
     */
    public function depositToBank(
        int $bankAccountId,
        string $cashAmount,
        string $checkAmount = '0.0000',
        ?int $cashierShiftId = null,
        ?string $bankRef = null,
        ?string $teller = null
    ): BankDeposit {
        return DB::transaction(function () use ($bankAccountId, $cashAmount, $checkAmount, $cashierShiftId, $bankRef, $teller): BankDeposit {
            $bank = BankAccount::findOrFail($bankAccountId);
            $totalDeposited = bcadd($cashAmount, $checkAmount, 4);

            $depositRef = 'DEP-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            $deposit = BankDeposit::create([
                'deposit_reference'      => $depositRef,
                'bank_account_id'        => $bank->id,
                'cashier_shift_id'       => $cashierShiftId,
                'deposit_date'           => date('Y-m-d'),
                'cash_amount'            => $cashAmount,
                'check_amount'           => $checkAmount,
                'total_deposited'        => $totalDeposited,
                'bank_reference_number'  => $bankRef,
                'validated_by_teller'    => $teller,
                'status'                 => 'DEPOSITED',
            ]);

            // Increase bank account cash ledger balance
            $bank->increment('balance', (float) $totalDeposited);

            // General Ledger Entry: DR Cash in Bank, CR Undeposited Cash / Cashier Drawer
            $cashAtBankAcc = Account::firstOrCreate(
                ['code' => '1020'],
                ['name' => 'Cash in Bank - Operating', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']
            );

            $drawerCashAcc = Account::firstOrCreate(
                ['code' => '1010'],
                ['name' => 'Cash on Hand - Cashier Drawer', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']
            );

            $journalLines = [
                new JournalLineData(
                    accountId: $cashAtBankAcc->id,
                    debit: $totalDeposited,
                    credit: '0.0000',
                    memo: "Bank deposit {$deposit->deposit_reference} to {$bank->bank_name}"
                ),
                new JournalLineData(
                    accountId: $drawerCashAcc->id,
                    debit: '0.0000',
                    credit: $totalDeposited,
                    memo: "Transfer of cashier collections to bank {$deposit->deposit_reference}"
                ),
            ];

            $entryData = new JournalEntryData(
                referenceNumber: $deposit->deposit_reference,
                entryDate: date('Y-m-d'),
                description: "Bank deposit to {$bank->bank_name} - {$deposit->deposit_reference}",
                type: 'GENERAL',
                postedBy: auth()->id(),
                lines: $journalLines
            );

            $this->journalEntryService->createAndPostEntry($entryData);

            return $deposit->loadMissing('bankAccount');
        });
    }
}
