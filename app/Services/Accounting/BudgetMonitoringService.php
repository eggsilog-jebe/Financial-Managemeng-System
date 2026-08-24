<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\BudgetAllocation;
use App\Models\BudgetEncumbrance;
use DomainException;
use Illuminate\Support\Facades\DB;

final class BudgetMonitoringService
{
    /**
     * Pre-commit funds for a Purchase Order or Request, asserting that the department has available unencumbered budget.
     */
    public function encumberBudget(
        int $budgetAllocationId,
        string $referenceType,
        string $referenceNumber,
        string $amount
    ): BudgetEncumbrance {
        return DB::transaction(function () use ($budgetAllocationId, $referenceType, $referenceNumber, $amount): BudgetEncumbrance {
            $budget = BudgetAllocation::findOrFail($budgetAllocationId);

            // Compute Total Currently Encumbered
            $currentEncumbered = (string) $budget->encumbrances()
                ->where('status', 'COMMITTED')
                ->sum('encumbered_amount');

            $availableUnencumbered = bcsub((string) $budget->remaining_balance, $currentEncumbered, 4);

            if (bccomp($amount, $availableUnencumbered, 4) > 0) {
                throw new DomainException(
                    "Budget Encumbrance Overdraft: Requested ₱{$amount} exceeds available unencumbered budget [₱{$availableUnencumbered}] for department [{$budget->department}]."
                );
            }

            return BudgetEncumbrance::create([
                'budget_allocation_id' => $budget->id,
                'reference_type'       => $referenceType,
                'reference_number'     => $referenceNumber,
                'encumbered_amount'    => $amount,
                'liquidated_amount'    => '0.0000',
                'status'               => 'COMMITTED',
            ]);
        });
    }

    /**
     * Liquidate encumbrance when vendor bill or voucher is finalized and update spent balance.
     */
    public function liquidateEncumbrance(int $encumbranceId, string $actualSpentAmount): BudgetEncumbrance
    {
        return DB::transaction(function () use ($encumbranceId, $actualSpentAmount): BudgetEncumbrance {
            $encumbrance = BudgetEncumbrance::with('budgetAllocation')->findOrFail($encumbranceId);
            $budget = $encumbrance->budgetAllocation;

            $newSpent = bcadd((string) $budget->spent_amount, $actualSpentAmount, 4);
            $newRemaining = bcsub((string) $budget->allocated_amount, $newSpent, 4);

            $budget->update([
                'spent_amount'      => $newSpent,
                'remaining_balance' => $newRemaining,
            ]);

            $encumbrance->update([
                'liquidated_amount' => $actualSpentAmount,
                'status'            => 'LIQUIDATED',
            ]);

            return $encumbrance;
        });
    }
}
