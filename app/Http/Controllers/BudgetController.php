<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\Accounting\BudgetAllocationData;
use App\DTOs\Accounting\BudgetReallocationData;
use App\Http\Requests\Budget\ReallocateBudgetRequest;
use App\Http\Requests\Budget\StoreBudgetAllocationRequest;
use App\Http\Requests\Budget\StoreEncumbranceRequest;
use App\Models\BudgetAllocation;
use App\Models\BudgetEncumbrance;
use App\Models\BudgetReallocation;
use App\Services\Accounting\BudgetMonitoringService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class BudgetController extends Controller
{
    public function __construct(
        private readonly BudgetMonitoringService $budgetService
    ) {}

    public function fiscalPlanning(Request $request): View
    {
        $fiscalYear = $request->input('fiscal_year', '2026');
        $metrics = $this->budgetService->getVarianceMetrics($fiscalYear);
        $budgets = $metrics['budgets'];

        $totalAllocated = $metrics['total_allocated'];
        $totalSpent = $metrics['total_spent'];
        $totalRemaining = $metrics['total_remaining'];

        $plans = $budgets->map(function (BudgetAllocation $b): array {
            return [
                'title'        => "{$b->department} Operating Plan",
                'sub'          => $b->category ?? 'Annual Departmental Allocation',
                'period'       => "Jan 01, {$b->fiscal_year} - Dec 31, {$b->fiscal_year}",
                'year'         => $b->fiscal_year,
                'revenue'      => '₱' . number_format((float) $b->allocated_amount, 2),
                'expense'      => '₱' . number_format((float) $b->spent_amount, 2),
                'margin'       => '₱' . number_format((float) $b->remaining_balance, 2),
                'status'       => $b->status,
                'status_badge' => $b->status === 'Approved' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning',
                'status_icon'  => $b->status === 'Approved' ? 'ph-check-circle' : 'ph-clock',
                'resolution'   => "BOT-RES-{$b->fiscal_year}-" . str_pad((string) $b->id, 3, '0', STR_PAD_LEFT),
            ];
        });

        return view('budget.fiscal-planning', compact(
            'plans',
            'budgets',
            'totalAllocated',
            'totalSpent',
            'totalRemaining',
        ));
    }

    public function budgetAllocation(Request $request): View
    {
        $fiscalYear = $request->input('fiscal_year', '2026');
        $metrics = $this->budgetService->getVarianceMetrics($fiscalYear);
        $budgets = $metrics['budgets'];

        $totalAllocated = $metrics['total_allocated'];
        $totalSpent = $metrics['total_spent'];
        $totalRemaining = $metrics['total_remaining'];
        $totalEncumbered = $metrics['total_encumbered'];

        $allocations = $budgets->map(function (BudgetAllocation $b): array {
            return [
                'id'         => $b->id,
                'cc'         => $b->department_code ?? ('CC-' . str_pad((string) $b->id, 3, '0', STR_PAD_LEFT)),
                'cat'        => $b->category ?? 'Medical & Clinical Supplies',
                'sub'        => $b->department,
                'initial'    => '₱' . number_format((float) $b->allocated_amount, 2),
                'encumbered' => '₱' . number_format((float) $b->active_encumbered_total, 2),
                'expended'   => '₱' . number_format((float) $b->spent_amount, 2),
                'available'  => '₱' . number_format((float) $b->available_unencumbered_balance, 2),
            ];
        });

        return view('budget.budget-allocation', compact(
            'allocations',
            'budgets',
            'totalAllocated',
            'totalSpent',
            'totalRemaining',
            'totalEncumbered'
        ));
    }

    public function departmentalBudgets(Request $request): View
    {
        $fiscalYear = $request->input('fiscal_year', '2026');
        $metrics = $this->budgetService->getVarianceMetrics($fiscalYear);
        $budgets = $metrics['budgets'];

        $departments = $budgets->map(function (BudgetAllocation $b): array {
            $burn = $b->burn_rate;
            return [
                'id'          => $b->id,
                'code'        => $b->department_code ?? ('CC-' . str_pad((string) $b->id, 3, '0', STR_PAD_LEFT)),
                'name'        => $b->department,
                'head'        => $b->department_head ?? 'Department Head',
                'cap'         => '₱' . number_format((float) $b->allocated_amount, 2),
                'spent'       => '₱' . number_format((float) $b->spent_amount, 2),
                'available'   => '₱' . number_format((float) $b->remaining_balance, 2),
                'burn'        => number_format($burn, 1) . '%',
                'burn_val'    => $burn,
                'burn_class'  => $burn > 85 ? 'bg-danger' : ($burn > 70 ? 'bg-warning' : 'bg-info'),
                'burn_status' => $burn > 85 ? 'critical' : ($burn > 70 ? 'high' : 'normal'),
            ];
        });

        return view('budget.departmental-budgets', compact('departments', 'budgets'));
    }

    public function varianceAnalysis(Request $request): View
    {
        $fiscalYear = $request->input('fiscal_year', '2026');
        $metrics = $this->budgetService->getVarianceMetrics($fiscalYear);
        $budgets = $metrics['budgets'];
        $totalVariance = $metrics['total_variance'];

        $variances = $budgets->map(function (BudgetAllocation $b): array {
            $varAmt = (float) bcsub((string) $b->allocated_amount, (string) $b->spent_amount, 4);
            $alloc = (float) $b->allocated_amount;
            $varPct = $alloc > 0 ? round(($varAmt / $alloc) * 100, 1) : 0.0;
            return [
                'id'           => $b->id,
                'item'         => "{$b->department} Operating Quota",
                'cc'           => $b->department_code ?? ('CC-' . str_pad((string) $b->id, 3, '0', STR_PAD_LEFT)),
                'budget'       => '₱' . number_format((float) $b->allocated_amount, 2),
                'actual'       => '₱' . number_format((float) $b->spent_amount, 2),
                'variance'     => ($varAmt >= 0 ? '+' : '') . '₱' . number_format($varAmt, 2),
                'pct'          => ($varPct >= 0 ? '+' : '') . number_format($varPct, 1) . '%',
                'pct_class'    => $varAmt >= 0 ? 'text-success' : 'text-danger',
                'status'       => $varAmt >= 0 ? 'Favorable Under-Run' : 'Critical Overrun',
                'status_badge' => $varAmt >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger',
                'status_icon'  => $varAmt >= 0 ? 'ph-check-circle' : 'ph-warning-circle',
                'type'         => $varAmt >= 0 ? 'favorable' : 'unfavorable',
            ];
        });

        return view('budget.variance-analysis', compact('variances', 'budgets', 'totalVariance'));
    }

    public function budgetReallocations(Request $request): View
    {
        $reallocations = BudgetReallocation::with(['sourceAllocation', 'destinationAllocation', 'approver'])
            ->latest('transfer_date')
            ->get();

        $reallocationsList = $reallocations->map(function (BudgetReallocation $r): array {
            return [
                'ref'          => $r->reference_number,
                'from'         => $r->source_department,
                'to'           => $r->destination_department,
                'amount'       => '₱' . number_format((float) $r->amount, 2),
                'reason'       => $r->reason,
                'status'       => $r->status,
                'status_badge' => $r->status === 'APPROVED' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning',
                'status_icon'  => $r->status === 'APPROVED' ? 'ph-check-circle' : 'ph-clock',
            ];
        });

        $budgets = BudgetAllocation::where('status', 'Approved')->orderBy('department')->get();

        return view('budget.budget-reallocations', [
            'reallocations'     => $reallocationsList,
            'rawReallocations'  => $reallocations,
            'budgets'           => $budgets,
        ]);
    }

    public function storeAllocation(StoreBudgetAllocationRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $dto = new BudgetAllocationData(
                department: $validated['department'],
                fiscalYear: $validated['fiscal_year'],
                allocatedAmount: (string) $validated['allocated_amount'],
                departmentCode: $validated['department_code'] ?? null,
                category: $validated['category'] ?? null,
                departmentHead: $validated['department_head'] ?? null,
                notes: $validated['notes'] ?? null,
            );

            $allocation = $this->budgetService->createAllocation($dto);

            return redirect()->back()->with('success', "Budget allocation of ₱" . number_format((float) $allocation->allocated_amount, 2) . " for [{$allocation->department}] created successfully.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reallocate(ReallocateBudgetRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $dto = new BudgetReallocationData(
                sourceAllocationId: (int) $validated['source_budget_allocation_id'],
                destinationAllocationId: (int) $validated['destination_budget_allocation_id'],
                amount: (string) $validated['amount'],
                transferDate: $validated['transfer_date'],
                reason: $validated['reason'],
                approvedBy: auth()->id() ?? 1,
            );

            $realloc = $this->budgetService->reallocateBudget($dto);

            return redirect()->back()->with('success', "Budget Reallocation [{$realloc->reference_number}] of ₱" . number_format((float) $realloc->amount, 2) . " from {$realloc->source_department} to {$realloc->destination_department} approved and executed successfully!");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function encumber(StoreEncumbranceRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $encumbrance = $this->budgetService->encumberBudget(
                budgetAllocationId: (int) $validated['budget_allocation_id'],
                referenceType: $validated['reference_type'],
                referenceNumber: $validated['reference_number'],
                amount: (string) $validated['amount'],
                notes: $validated['notes'] ?? null
            );

            return redirect()->back()->with('success', "Budget pre-committed for [{$encumbrance->reference_number}] (₱" . number_format((float) $encumbrance->encumbered_amount, 2) . ") successfully.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function releaseEncumbrance(int $id, Request $request): RedirectResponse
    {
        $reason = $request->input('reason', 'Cancelled procurement / voided request');

        try {
            $encumbrance = $this->budgetService->releaseEncumbrance($id, $reason);

            return redirect()->back()->with('success', "Budget Encumbrance [{$encumbrance->reference_number}] released and funds restored to department available capacity.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
