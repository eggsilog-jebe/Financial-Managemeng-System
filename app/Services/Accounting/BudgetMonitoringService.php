<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\Accounting\BudgetAllocationData;
use App\DTOs\Accounting\BudgetEncumbranceData;
use App\DTOs\Accounting\BudgetReallocationData;
use App\Models\BudgetAllocation;
use App\Models\BudgetEncumbrance;
use App\Models\BudgetReallocation;
use DomainException;
use Illuminate\Support\Facades\DB;

final class BudgetMonitoringService
{
    public function __construct(
        private readonly CasAuditTrailService $auditTrailService
    ) {}

    /**
     * Create a new Departmental Budget Allocation with strict zero-spent initial balance.
     */
    public function createAllocation(BudgetAllocationData $dto): BudgetAllocation
    {
        return DB::transaction(function () use ($dto): BudgetAllocation {
            $amount = (string) $dto->allocatedAmount;
            if (bccomp($amount, '0.0000', 4) <= 0) {
                throw new DomainException("Budget allocation amount must be strictly greater than 0.00.");
            }

            // Uniqueness check per department & fiscal year
            $exists = BudgetAllocation::where('department', $dto->department)
                ->where('fiscal_year', $dto->fiscalYear)
                ->when($dto->category, fn ($q) => $q->where('category', $dto->category))
                ->exists();

            if ($exists) {
                throw new DomainException("A budget allocation already exists for department [{$dto->department}] in fiscal year [{$dto->fiscalYear}].");
            }

            $allocation = BudgetAllocation::create([
                'department'        => $dto->department,
                'department_code'   => $dto->departmentCode,
                'category'          => $dto->category,
                'department_head'   => $dto->departmentHead,
                'fiscal_year'       => $dto->fiscalYear,
                'allocated_amount'  => $amount,
                'spent_amount'      => '0.0000',
                'remaining_balance' => $amount,
                'status'            => $dto->status ?? 'Approved',
                'notes'             => $dto->notes,
            ]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $allocation,
                action: 'INSERT',
                oldValues: null,
                newValues: $allocation->toArray(),
                userId: auth()->id() ?? 1,
                userName: auth()->user()?->name ?? 'Budget Administrator',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $allocation;
        });
    }

    /**
     * Pre-commit funds for a Purchase Order or Request with concurrency protection,
     * asserting that the department has available unencumbered budget capacity.
     */
    public function encumberBudget(
        int $budgetAllocationId,
        string $referenceType,
        string $referenceNumber,
        string $amount,
        ?string $notes = null
    ): BudgetEncumbrance {
        return DB::transaction(function () use ($budgetAllocationId, $referenceType, $referenceNumber, $amount, $notes): BudgetEncumbrance {
            if (bccomp($amount, '0.0000', 4) <= 0) {
                throw new DomainException("Encumbrance amount must be strictly greater than 0.00.");
            }

            // Pessimistic row locking to eliminate concurrent over-commitment race conditions
            $budget = BudgetAllocation::where('id', $budgetAllocationId)->lockForUpdate()->firstOrFail();

            if ($budget->status !== 'Approved') {
                throw new DomainException("Cannot encumber budget for department [{$budget->department}]: Budget status is [{$budget->status}].");
            }

            // Compute Total Currently Encumbered
            $currentEncumbered = '0.0000';
            $committedEncumbrances = $budget->encumbrances()
                ->where('status', 'COMMITTED')
                ->lockForUpdate()
                ->get();

            foreach ($committedEncumbrances as $enc) {
                $currentEncumbered = bcadd($currentEncumbered, (string) $enc->encumbered_amount, 4);
            }

            $availableUnencumbered = bcsub((string) $budget->remaining_balance, $currentEncumbered, 4);

            if (bccomp($amount, $availableUnencumbered, 4) > 0) {
                throw new DomainException(
                    "Budget Encumbrance Overdraft: Requested ₱" . number_format((float) $amount, 2) . " exceeds available unencumbered budget [₱" . number_format((float) $availableUnencumbered, 2) . "] for department [{$budget->department}]."
                );
            }

            $encumbrance = BudgetEncumbrance::create([
                'budget_allocation_id' => $budget->id,
                'reference_type'       => $referenceType,
                'reference_number'     => $referenceNumber,
                'encumbered_amount'    => $amount,
                'liquidated_amount'    => '0.0000',
                'status'               => 'COMMITTED',
            ]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $encumbrance,
                action: 'INSERT',
                oldValues: null,
                newValues: $encumbrance->toArray(),
                userId: auth()->id() ?? 1,
                userName: auth()->user()?->name ?? 'Procurement Officer',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $encumbrance;
        });
    }

    /**
     * Liquidate encumbrance when vendor bill or voucher is finalized, updating spent balance
     * and enforcing idempotency to prevent double-liquidation.
     */
    public function liquidateEncumbrance(int $encumbranceId, string $actualSpentAmount): BudgetEncumbrance
    {
        return DB::transaction(function () use ($encumbranceId, $actualSpentAmount): BudgetEncumbrance {
            if (bccomp($actualSpentAmount, '0.0000', 4) <= 0) {
                throw new DomainException("Liquidation amount must be strictly greater than 0.00.");
            }

            $encumbrance = BudgetEncumbrance::where('id', $encumbranceId)->lockForUpdate()->firstOrFail();
            $budget = BudgetAllocation::where('id', $encumbrance->budget_allocation_id)->lockForUpdate()->firstOrFail();

            // Idempotency Guard: prevent double-charging the department budget
            if ($encumbrance->status !== 'COMMITTED') {
                throw new DomainException(
                    "Encumbrance [{$encumbrance->reference_number}] cannot be liquidated: Current status is already [{$encumbrance->status}]."
                );
            }

            $newSpent = bcadd((string) $budget->spent_amount, $actualSpentAmount, 4);
            $newRemaining = bcsub((string) $budget->allocated_amount, $newSpent, 4);

            // Overdraft Guard on actual expenditure
            if (bccomp($newRemaining, '0.0000', 4) < 0) {
                throw new DomainException(
                    "Budget Liquidation Overdraft: Actual expenditure of ₱" . number_format((float) $actualSpentAmount, 2) . " exceeds remaining budget of ₱" . number_format((float) $budget->remaining_balance, 2) . " for department [{$budget->department}]."
                );
            }

            $budget->update([
                'spent_amount'      => $newSpent,
                'remaining_balance' => $newRemaining,
            ]);

            $encumbrance->update([
                'liquidated_amount' => $actualSpentAmount,
                'status'            => 'LIQUIDATED',
            ]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $encumbrance,
                action: 'UPDATE',
                oldValues: ['status' => 'COMMITTED', 'liquidated_amount' => '0.0000'],
                newValues: $encumbrance->toArray(),
                userId: auth()->id() ?? 1,
                userName: auth()->user()?->name ?? 'Finance Auditor',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $encumbrance;
        });
    }

    /**
     * Cancel and release an encumbrance when a Purchase Order or Request is voided/rejected,
     * immediately restoring the available unencumbered capacity.
     */
    public function releaseEncumbrance(int $encumbranceId, string $reason): BudgetEncumbrance
    {
        return DB::transaction(function () use ($encumbranceId, $reason): BudgetEncumbrance {
            $encumbrance = BudgetEncumbrance::where('id', $encumbranceId)->lockForUpdate()->firstOrFail();

            if ($encumbrance->status !== 'COMMITTED') {
                throw new DomainException(
                    "Encumbrance [{$encumbrance->reference_number}] cannot be released: Current status is [{$encumbrance->status}]."
                );
            }

            $oldValues = $encumbrance->toArray();
            $encumbrance->update([
                'status' => 'RELEASED',
            ]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $encumbrance,
                action: 'UPDATE',
                oldValues: $oldValues,
                newValues: array_merge($encumbrance->toArray(), ['release_reason' => $reason]),
                userId: auth()->id() ?? 1,
                userName: auth()->user()?->name ?? 'Budget Controller',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $encumbrance;
        });
    }

    /**
     * Execute an atomic, deadlock-free inter-departmental budget reallocation.
     */
    public function reallocateBudget(BudgetReallocationData $dto): BudgetReallocation
    {
        return DB::transaction(function () use ($dto): BudgetReallocation {
            if ($dto->sourceAllocationId === $dto->destinationAllocationId) {
                throw new DomainException("Source and Destination budget allocations must be different.");
            }

            $amount = (string) $dto->amount;
            if (bccomp($amount, '0.0000', 4) <= 0) {
                throw new DomainException("Reallocation amount must be strictly greater than 0.00.");
            }

            // Acquire locks in deterministic ascending ID order to eliminate deadlocks
            $firstId = min($dto->sourceAllocationId, $dto->destinationAllocationId);
            $secondId = max($dto->sourceAllocationId, $dto->destinationAllocationId);

            $allocations = BudgetAllocation::whereIn('id', [$firstId, $secondId])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $source = $allocations->get($dto->sourceAllocationId);
            $dest = $allocations->get($dto->destinationAllocationId);

            if (! $source) {
                throw new DomainException("Source budget allocation [ID {$dto->sourceAllocationId}] not found.");
            }

            if (! $dest) {
                throw new DomainException("Destination budget allocation [ID {$dto->destinationAllocationId}] not found.");
            }

            if ($source->fiscal_year !== $dest->fiscal_year) {
                throw new DomainException("Cannot reallocate budget across different fiscal years [{$source->fiscal_year}] and [{$dest->fiscal_year}].");
            }

            // Validate source has sufficient unencumbered available funds
            $sourceActiveEnc = '0.0000';
            foreach ($source->encumbrances()->where('status', 'COMMITTED')->get() as $enc) {
                $sourceActiveEnc = bcadd($sourceActiveEnc, (string) $enc->encumbered_amount, 4);
            }
            $sourceAvailable = bcsub((string) $source->remaining_balance, $sourceActiveEnc, 4);

            if (bccomp($amount, $sourceAvailable, 4) > 0) {
                throw new DomainException(
                    "Insufficient unencumbered funds in source department [{$source->department}]. Available: ₱" . number_format((float) $sourceAvailable, 2) . ", Requested: ₱" . number_format((float) $amount, 2) . "."
                );
            }

            // Mutate Source Allocation
            $sourceNewAlloc = bcsub((string) $source->allocated_amount, $amount, 4);
            $sourceNewRem = bcsub((string) $source->remaining_balance, $amount, 4);
            $source->update([
                'allocated_amount'  => $sourceNewAlloc,
                'remaining_balance' => $sourceNewRem,
            ]);

            // Mutate Destination Allocation
            $destNewAlloc = bcadd((string) $dest->allocated_amount, $amount, 4);
            $destNewRem = bcadd((string) $dest->remaining_balance, $amount, 4);
            $dest->update([
                'allocated_amount'  => $destNewAlloc,
                'remaining_balance' => $destNewRem,
            ]);

            // Generate sequential reference
            $todayStr = date('Ymd', strtotime($dto->transferDate));
            $countToday = BudgetReallocation::whereDate('transfer_date', $dto->transferDate)->count() + 1;
            $refNumber = 'REAL-' . $todayStr . '-' . str_pad((string) $countToday, 4, '0', STR_PAD_LEFT);

            $reallocation = BudgetReallocation::create([
                'reference_number'                 => $refNumber,
                'source_budget_allocation_id'      => $source->id,
                'destination_budget_allocation_id' => $dest->id,
                'source_department'                => $source->department,
                'destination_department'           => $dest->department,
                'fiscal_year'                      => $source->fiscal_year,
                'amount'                           => $amount,
                'transfer_date'                    => $dto->transferDate,
                'reason'                           => $dto->reason,
                'status'                           => 'APPROVED',
                'approved_by'                      => $dto->approvedBy ?? auth()->id() ?? 1,
            ]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $reallocation,
                action: 'INSERT',
                oldValues: null,
                newValues: $reallocation->toArray(),
                userId: $dto->approvedBy ?? auth()->id() ?? 1,
                userName: auth()->user()?->name ?? 'Finance Director',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $reallocation->loadMissing(['sourceAllocation', 'destinationAllocation', 'approver']);
        });
    }

    /**
     * Compute comprehensive fiscal variance and burn rate metrics.
     */
    public function getVarianceMetrics(?string $fiscalYear = null): array
    {
        $query = BudgetAllocation::with(['encumbrances'])->orderBy('department');
        if ($fiscalYear) {
            $query->where('fiscal_year', $fiscalYear);
        }
        $budgets = $query->get();

        $variances = [];
        $totalAllocated = '0.0000';
        $totalSpent = '0.0000';
        $totalRemaining = '0.0000';
        $totalEncumbered = '0.0000';

        foreach ($budgets as $b) {
            $alloc = (string) $b->allocated_amount;
            $spent = (string) $b->spent_amount;
            $rem = (string) $b->remaining_balance;
            $enc = $b->active_encumbered_total;

            $totalAllocated = bcadd($totalAllocated, $alloc, 4);
            $totalSpent = bcadd($totalSpent, $spent, 4);
            $totalRemaining = bcadd($totalRemaining, $rem, 4);
            $totalEncumbered = bcadd($totalEncumbered, $enc, 4);

            $varAmt = bcsub($alloc, $spent, 4);
            $varPct = bccomp($alloc, '0.0000', 4) > 0
                ? (float) bcmul(bcdiv($varAmt, $alloc, 4), '100', 2)
                : 0.0;

            $isFavorable = (bccomp($varAmt, '0.0000', 4) >= 0);

            $variances[] = [
                'id'              => $b->id,
                'department'      => $b->department,
                'department_code' => $b->department_code ?? 'CC-' . str_pad((string) $b->id, 3, '0', STR_PAD_LEFT),
                'category'        => $b->category ?? 'Operating Budget',
                'allocated'       => $alloc,
                'spent'           => $spent,
                'remaining'       => $rem,
                'encumbered'      => $enc,
                'available'       => $b->available_unencumbered_balance,
                'variance_amount' => (float) $varAmt,
                'variance_pct'    => $varPct,
                'is_favorable'    => $isFavorable,
                'burn_rate'       => $b->burn_rate,
                'status'          => $b->status,
            ];
        }

        $totalVariance = bcsub($totalAllocated, $totalSpent, 4);

        return [
            'budgets'          => $budgets,
            'variances'        => $variances,
            'total_allocated'  => $totalAllocated,
            'total_spent'      => $totalSpent,
            'total_remaining'  => $totalRemaining,
            'total_encumbered' => $totalEncumbered,
            'total_variance'   => (float) $totalVariance,
        ];
    }
}
