<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\Accounting\PettyCashExpenseData;
use App\DTOs\JournalEntryData;
use App\DTOs\JournalLineData;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\DisbursementVoucher;
use App\Models\PettyCashExpense;
use App\Models\PettyCashFund;
use DomainException;
use Illuminate\Support\Facades\DB;

final class PettyCashService
{
    public function __construct(
        private readonly JournalEntryService $journalEntryService,
        private readonly CasAuditTrailService $auditTrailService,
    ) {}

    /**
     * Record a petty cash expense slip.
     */
    public function recordExpense(PettyCashExpenseData $dto): PettyCashExpense
    {
        return DB::transaction(function () use ($dto): PettyCashExpense {
            $fund = PettyCashFund::findOrFail($dto->pettyCashFundId);

            if (bccomp((string) $fund->current_balance, (string) $dto->amount, 4) < 0) {
                throw new DomainException("Insufficient petty cash balance (₱{$fund->current_balance}) for expense amount (₱{$dto->amount}).");
            }

            $voucherNum = $dto->voucherNumber ?? ('PCV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))));

            $expense = PettyCashExpense::create([
                'petty_cash_fund_id' => $fund->id,
                'voucher_number'     => $voucherNum,
                'expense_date'       => $dto->expenseDate,
                'payee'              => $dto->payee,
                'department'         => $dto->department,
                'particulars'        => $dto->particulars,
                'amount'             => $dto->amount,
                'receipt_ref'        => $dto->receiptRef,
                'status'             => 'UNREPLENISHED',
            ]);

            // Decrement Petty Cash Fund Balance
            $newBalance = bcsub((string) $fund->current_balance, (string) $dto->amount, 4);
            $fund->update(['current_balance' => $newBalance]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $expense,
                action: 'INSERT',
                oldValues: null,
                newValues: $expense->toArray(),
                userId: auth()->id(),
                userName: auth()->user()?->name ?? 'Petty Cash Custodian',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $expense->loadMissing('pettyCashFund');
        });
    }

    /**
     * Replenish petty cash revolving fund.
     */
    public function replenishFund(int $fundId, int $bankAccountId, int $userId): DisbursementVoucher
    {
        return DB::transaction(function () use ($fundId, $bankAccountId, $userId): DisbursementVoucher {
            $fund = PettyCashFund::findOrFail($fundId);
            $bank = BankAccount::findOrFail($bankAccountId);

            $unreplenished = PettyCashExpense::where('petty_cash_fund_id', $fund->id)
                ->where('status', 'UNREPLENISHED')
                ->get();

            if ($unreplenished->isEmpty()) {
                throw new DomainException("No unreplenished petty cash expense slips found for fund [{$fund->fund_name}].");
            }

            $totalExpense = '0.0000';
            foreach ($unreplenished as $e) {
                $totalExpense = bcadd($totalExpense, (string) $e->amount, 4);
            }

            // 1. Create Reimbursement Disbursement Voucher
            $voucherNum = 'DV-PC-REPL-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
            $voucher = DisbursementVoucher::create([
                'voucher_number'       => $voucherNum,
                'bank_account_id'      => $bank->id,
                'prepared_by'          => $userId,
                'voucher_date'         => date('Y-m-d'),
                'payee_name'           => "Petty Cash Replenishment ({$fund->custodian_name})",
                'description'          => "Replenishment of {$unreplenished->count()} expense slips for {$fund->fund_name}",
                'gross_amount'         => $totalExpense,
                'withheld_tax_amount'  => '0.0000',
                'net_disbursed_amount' => $totalExpense,
                'payment_method'       => 'CHECK',
                'status'               => 'APPROVED',
                'approved_by'          => $userId,
            ]);

            // 2. Mark Expense Slips as Replenished
            foreach ($unreplenished as $e) {
                $e->update([
                    'status'                  => 'REPLENISHED',
                    'disbursement_voucher_id' => $voucher->id,
                ]);
            }

            // 3. Restore Petty Cash Fund Balance to Float Limit
            $fund->update(['current_balance' => $fund->float_limit]);

            // 4. Post Double-Entry Journal Entry: DR 5030 Operating Expenses, CR 1020 Cash in Bank
            $expenseAcc = Account::firstOrCreate(['code' => '5030'], ['name' => 'Administrative & Operating Expenses', 'category' => 'EXPENSE', 'normal_balance' => 'DEBIT']);
            $bankGlCode = $bank->gl_code ?: '1020';
            $bankAcc = Account::firstOrCreate(['code' => $bankGlCode], ['name' => "Cash in Bank - {$bank->bank_name}", 'category' => 'ASSET', 'normal_balance' => 'DEBIT']);

            $journalLines = [
                new JournalLineData(
                    accountId: $expenseAcc->id,
                    debit: $totalExpense,
                    credit: '0.0000',
                    memo: "Petty cash expenses replenishment on {$voucher->voucher_number}"
                ),
                new JournalLineData(
                    accountId: $bankAcc->id,
                    debit: '0.0000',
                    credit: $totalExpense,
                    memo: "Bank disbursement for petty cash replenishment"
                ),
            ];

            $entryData = new JournalEntryData(
                referenceNumber: 'JE-PC-' . $voucher->voucher_number,
                entryDate: date('Y-m-d'),
                description: "Petty cash replenishment for {$fund->fund_name}",
                type: 'GENERAL',
                postedBy: $userId,
                lines: $journalLines
            );

            $this->journalEntryService->createAndPostEntry($entryData);

            $this->auditTrailService->logFinancialEvent(
                auditable: $fund,
                action: 'UPDATE',
                oldValues: ['unreplenished_count' => $unreplenished->count()],
                newValues: ['replenished_amount' => $totalExpense, 'new_balance' => $fund->float_limit],
                userId: $userId,
                userName: auth()->user()?->name ?? 'Finance Approver',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $voucher;
        });
    }
}
