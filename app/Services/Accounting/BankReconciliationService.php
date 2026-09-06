<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\Accounting\BankReconciliationData;
use App\Models\BankAccount;
use App\Models\BankDeposit;
use App\Models\BankReconciliation;
use App\Models\CheckRegister;
use DomainException;
use Illuminate\Support\Facades\DB;

final class BankReconciliationService
{
    public function __construct(
        private readonly CasAuditTrailService $auditTrailService
    ) {}

    /**
     * Compute reconciliation workspace items for a bank account & cutoff date.
     */
    public function getReconciliationWorkspace(int $bankAccountId, ?string $cutoffDate = null): array
    {
        $cutoff = $cutoffDate ?: date('Y-m-d');
        $bankAccount = BankAccount::with('reconciliations')->findOrFail($bankAccountId);

        // 1. Current GL Book Balance
        $bookBalance = (string) $bankAccount->balance;

        // 2. Uncleared Checks / Outstanding Disbursements
        $outstandingChecks = CheckRegister::with(['disbursementVoucher'])
            ->where('bank_account_id', $bankAccountId)
            ->whereIn('status', ['ISSUED', 'RELEASED', 'PRINTED'])
            ->whereDate('check_date', '<=', $cutoff)
            ->orderBy('check_date')
            ->get();

        $totalOutstandingChecks = '0.0000';
        foreach ($outstandingChecks as $chk) {
            $totalOutstandingChecks = bcadd($totalOutstandingChecks, (string) $chk->amount, 4);
        }

        // 3. Deposits in Transit (Uncleared Bank Deposits)
        $depositsInTransit = BankDeposit::with(['cashierShift'])
            ->where('bank_account_id', $bankAccountId)
            ->whereIn('status', ['PREPARED', 'IN_TRANSIT'])
            ->whereDate('deposit_date', '<=', $cutoff)
            ->orderBy('deposit_date')
            ->get();

        $totalDepositsInTransit = '0.0000';
        foreach ($depositsInTransit as $dep) {
            $totalDepositsInTransit = bcadd($totalDepositsInTransit, (string) $dep->total_deposited, 4);
        }

        // Past Reconciliation History
        $history = BankReconciliation::with(['reconciler'])
            ->where('bank_account_id', $bankAccountId)
            ->latest('statement_date')
            ->get();

        return [
            'bank_account'              => $bankAccount,
            'cutoff_date'               => $cutoff,
            'book_balance'              => $bookBalance,
            'outstanding_checks'        => $outstandingChecks,
            'total_outstanding_checks'  => $totalOutstandingChecks,
            'deposits_in_transit'       => $depositsInTransit,
            'total_deposits_in_transit' => $totalDepositsInTransit,
            'history'                   => $history,
        ];
    }

    /**
     * Post Bank Reconciliation with Zero Variance validation.
     */
    public function postReconciliation(BankReconciliationData $dto): BankReconciliation
    {
        return DB::transaction(function () use ($dto): BankReconciliation {
            $bank = BankAccount::findOrFail($dto->bankAccountId);

            $stmtBal = (string) $dto->statementBalance;
            $bookBal = (string) $dto->bookBalance;

            // 1. Calculate cleared items total and update clearance statuses
            $clearedChecksTotal = '0.0000';
            if (! empty($dto->clearedCheckIds)) {
                $clearedChecks = CheckRegister::with('disbursementVoucher')
                    ->whereIn('id', $dto->clearedCheckIds)
                    ->where('bank_account_id', $bank->id)
                    ->get();
                foreach ($clearedChecks as $chk) {
                    $clearedChecksTotal = bcadd($clearedChecksTotal, (string) $chk->amount, 4);
                    $chk->update([
                        'status'     => 'CLEARED',
                        'cleared_at' => $dto->statementDate,
                    ]);
                    if ($chk->disbursement_voucher_id) {
                        $chk->disbursementVoucher?->update(['status' => 'CLEARED']);
                    }
                }
            }

            $clearedDepositsTotal = '0.0000';
            if (! empty($dto->clearedDepositIds)) {
                $clearedDeposits = BankDeposit::whereIn('id', $dto->clearedDepositIds)
                    ->where('bank_account_id', $bank->id)
                    ->get();
                foreach ($clearedDeposits as $dep) {
                    $clearedDepositsTotal = bcadd($clearedDepositsTotal, (string) $dep->total_deposited, 4);
                    $dep->update(['status' => 'RECONCILED']);
                }
            }

            // 2. Determine Timing Differences (Uncleared items as of cutoff date)
            $unclearedChecks = CheckRegister::where('bank_account_id', $bank->id)
                ->whereIn('status', ['ISSUED', 'RELEASED', 'PRINTED'])
                ->whereDate('check_date', '<=', $dto->cutoffDate)
                ->when(! empty($dto->clearedCheckIds), fn ($q) => $q->whereNotIn('id', $dto->clearedCheckIds))
                ->get();
            $totalOutstandingChecks = '0.0000';
            foreach ($unclearedChecks as $chk) {
                $totalOutstandingChecks = bcadd($totalOutstandingChecks, (string) $chk->amount, 4);
            }

            $unclearedDeposits = BankDeposit::where('bank_account_id', $bank->id)
                ->whereIn('status', ['PREPARED', 'IN_TRANSIT'])
                ->whereDate('deposit_date', '<=', $dto->cutoffDate)
                ->when(! empty($dto->clearedDepositIds), fn ($q) => $q->whereNotIn('id', $dto->clearedDepositIds))
                ->get();
            $totalDepositsInTransit = '0.0000';
            foreach ($unclearedDeposits as $dep) {
                $totalDepositsInTransit = bcadd($totalDepositsInTransit, (string) $dep->total_deposited, 4);
            }

            // 3. Two-Way Reconciliation (GAAP / IFRS Timing Adjustments):
            // Adjusted Bank Balance = Statement Balance + Deposits in Transit - Outstanding Checks
            $adjustedBankBalance = bcsub(
                bcadd($stmtBal, $totalDepositsInTransit, 4),
                $totalOutstandingChecks,
                4
            );

            // Variance = Adjusted Bank Balance - GL Book Balance
            $variance = bcsub($adjustedBankBalance, $bookBal, 4);

            if (bccomp($variance, '0.0000', 4) !== 0) {
                throw new DomainException(
                    "Bank reconciliation cannot be posted with an unresolved variance of ₱" . number_format((float) $variance, 2) . ". Adjusted Bank Balance (₱" . number_format((float) $adjustedBankBalance, 2) . " = Statement ₱" . number_format((float) $stmtBal, 2) . " + In-Transit Deposits ₱" . number_format((float) $totalDepositsInTransit, 2) . " - Outstanding Checks ₱" . number_format((float) $totalOutstandingChecks, 2) . ") must match GL Book Balance (₱" . number_format((float) $bookBal, 2) . ")."
                );
            }

            $reconciliation = BankReconciliation::create([
                'bank_account_id'       => $bank->id,
                'statement_date'        => $dto->statementDate,
                'cutoff_date'           => $dto->cutoffDate,
                'statement_balance'     => $stmtBal,
                'book_balance'          => $bookBal,
                'variance'              => '0.0000',
                'cleared_deposits'      => $clearedDepositsTotal,
                'cleared_disbursements' => $clearedChecksTotal,
                'status'                => 'Reconciled',
                'reconciled_by'         => $dto->reconciledBy,
                'notes'                 => $dto->notes,
            ]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $reconciliation,
                action: 'INSERT',
                oldValues: null,
                newValues: $reconciliation->toArray(),
                userId: $dto->reconciledBy,
                userName: auth()->user()?->name ?? 'Treasury Accountant',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $reconciliation;
        });
    }
}
