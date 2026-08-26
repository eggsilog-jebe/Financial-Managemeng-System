<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\Accounting\BankDepositCreateData;
use App\DTOs\JournalEntryData;
use App\DTOs\JournalLineData;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\BankDeposit;
use App\Models\JournalEntry;
use App\Models\Payment;
use DomainException;
use Illuminate\Support\Facades\DB;

final class BankDepositService
{
    public function __construct(
        private readonly JournalEntryService $journalEntryService,
        private readonly CasAuditTrailService $auditTrailService,
    ) {}

    /**
     * Create bank deposit slip in PREPARED status.
     */
    public function createDeposit(BankDepositCreateData $dto): BankDeposit
    {
        return DB::transaction(function () use ($dto): BankDeposit {
            $bank = BankAccount::findOrFail($dto->bankAccountId);
            $totalDeposited = bcadd((string) $dto->cashAmount, (string) $dto->checkAmount, 4);

            $countToday = BankDeposit::whereDate('deposit_date', $dto->depositDate)->count() + 1;
            $depositRef = 'DEP-' . date('Ymd', strtotime($dto->depositDate)) . '-' . str_pad((string) $countToday, 4, '0', STR_PAD_LEFT);

            $deposit = BankDeposit::create([
                'deposit_reference'      => $depositRef,
                'bank_account_id'        => $bank->id,
                'cashier_shift_id'       => $dto->cashierShiftId,
                'deposit_date'           => $dto->depositDate,
                'cash_amount'            => $dto->cashAmount,
                'check_amount'           => $dto->checkAmount,
                'total_deposited'        => $totalDeposited,
                'bank_reference_number'  => $dto->bankRef,
                'validated_by_teller'    => $dto->teller,
                'status'                 => 'PREPARED',
            ]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $deposit,
                action: 'INSERT',
                oldValues: null,
                newValues: $deposit->toArray(),
                userId: auth()->id() ?? 1,
                userName: auth()->user()?->name ?? 'Treasury Vault Officer',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $deposit;
        });
    }

    /**
     * Validate and clear deposit slip, increment bank balance, and post balanced GL entry:
     * DR 1020 (Cash in Bank)
     * CR 1011 (Cashier Undeposited Collections)
     */
    public function clearDeposit(int $depositId, string $bankRef, ?string $teller, int $validatedBy): BankDeposit
    {
        return DB::transaction(function () use ($depositId, $bankRef, $teller, $validatedBy): BankDeposit {
            $deposit = BankDeposit::with(['bankAccount', 'cashierShift'])->findOrFail($depositId);

            if ($deposit->status === 'DEPOSITED' || $deposit->status === 'RECONCILED') {
                throw new DomainException("Deposit [{$deposit->deposit_reference}] is already cleared/deposited.");
            }

            $oldValues = $deposit->toArray();
            $totalAmount = (string) $deposit->total_deposited;
            $bank = $deposit->bankAccount;

            // 1. Update deposit record
            $deposit->update([
                'bank_reference_number' => $bankRef,
                'validated_by_teller'   => $teller ?? 'Bank Verified',
                'status'                => 'DEPOSITED',
            ]);

            // 2. Increment Bank Account Balance
            if ($bank) {
                $curBal = (string) $bank->balance;
                $newBal = bcadd($curBal, $totalAmount, 4);
                $bank->update([
                    'balance' => $newBal,
                ]);
            }

            // 3. Post Double-Entry Journal:
            // DR 1020 (Cash in Bank - Operational)
            // CR 1011 (Cashier Undeposited Collections)
            $bankGlAccount = null;
            if ($bank && $bank->gl_code) {
                $bankGlAccount = Account::where('code', $bank->gl_code)->first();
            }

            if (! $bankGlAccount) {
                $bankGlAccount = Account::firstOrCreate(
                    ['code' => $bank?->gl_code ?: '1020'],
                    ['name' => 'Cash in Bank - Operations', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']
                );
            }

            $undepositedGl = Account::firstOrCreate(
                ['code' => '1011'],
                ['name' => 'Cashier Undeposited Collections', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']
            );

            $lines = [
                new JournalLineData(
                    accountId: $bankGlAccount->id,
                    debit: (string) $totalAmount,
                    credit: '0.0000',
                    memo: "Bank deposit cleared [Ref: {$bankRef}] to {$bank->bank_name} ({$bank->account_number})"
                ),
                new JournalLineData(
                    accountId: $undepositedGl->id,
                    debit: '0.0000',
                    credit: (string) $totalAmount,
                    memo: "Clearance of undeposited cashier shift collections [{$deposit->deposit_reference}]"
                ),
            ];

            $this->journalEntryService->createAndPostEntry(new JournalEntryData(
                referenceNumber: 'JE-DEP-' . $deposit->deposit_reference,
                entryDate: date('Y-m-d'),
                description: "Bank Deposit Clearance [{$deposit->deposit_reference}] - Ref: {$bankRef} by User #{$validatedBy}",
                lines: $lines,
                type: 'GENERAL'
            ));

            $this->auditTrailService->logFinancialEvent(
                auditable: $deposit,
                action: 'UPDATE',
                oldValues: $oldValues,
                newValues: $deposit->toArray(),
                userId: $validatedBy,
                userName: auth()->user()?->name ?? 'Finance Approver',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $deposit;
        });
    }

    /**
     * Reject deposit slip.
     */
    public function rejectDeposit(int $depositId, string $reason): BankDeposit
    {
        $deposit = BankDeposit::findOrFail($depositId);

        if ($deposit->status === 'DEPOSITED' || $deposit->status === 'RECONCILED') {
            throw new DomainException("Cannot reject an already cleared deposit.");
        }

        $oldValues = $deposit->toArray();
        $deposit->update([
            'status'                => 'PREPARED',
            'bank_reference_number' => 'REJECTED: ' . $reason,
        ]);

        $this->auditTrailService->logFinancialEvent(
            auditable: $deposit,
            action: 'UPDATE',
            oldValues: $oldValues,
            newValues: $deposit->toArray(),
            userId: auth()->id() ?? 1,
            userName: auth()->user()?->name ?? 'Treasury Supervisor',
            ipAddress: request()?->ip() ?? '127.0.0.1',
        );

        return $deposit;
    }

    /**
     * Re-trigger GL posting for payment (Fail-safe mechanism).
     */
    public function retriggerPaymentJournal(int $paymentId): JournalEntry
    {
        $payment = Payment::with(['invoice', 'patientAccount', 'officialReceipt'])->findOrFail($paymentId);
        $amount = (string) $payment->amount;
        $orNumber = $payment->officialReceipt?->or_number ?? $payment->payment_reference;

        $undepositedCashAcc = Account::firstOrCreate(
            ['code' => '1011'],
            ['name' => 'Cashier Undeposited Collections', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']
        );

        $patientArAcc = Account::firstOrCreate(
            ['code' => '1120'],
            ['name' => 'Accounts Receivable - Patients', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']
        );

        $lines = [
            new JournalLineData(
                accountId: $undepositedCashAcc->id,
                debit: (string) $amount,
                credit: '0.0000',
                memo: "Digital gateway settlement [{$orNumber}] via {$payment->payment_method}"
            ),
            new JournalLineData(
                accountId: $patientArAcc->id,
                debit: '0.0000',
                credit: (string) $amount,
                memo: "Settlement for patient billing"
            ),
        ];

        return $this->journalEntryService->createAndPostEntry(new JournalEntryData(
            referenceNumber: 'JE-COL-SYNC-' . $payment->payment_reference . '-' . bin2hex(random_bytes(2)),
            entryDate: $payment->payment_date ? $payment->payment_date->format('Y-m-d') : date('Y-m-d'),
            description: "Re-Triggered GL Journalization for Payment [{$payment->payment_reference}]",
            lines: $lines,
            type: 'GENERAL'
        ));
    }
}
