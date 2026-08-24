<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BudgetAllocation;
use Illuminate\Contracts\View\View;

final class BudgetController extends Controller
{
    public function fiscalPlanning(): View
    {
        $budgets          = BudgetAllocation::orderBy('department')->get();
        $totalAllocated   = $budgets->sum('allocated_amount');
        $totalSpent       = $budgets->sum('spent_amount');
        $totalRemaining   = $budgets->sum('remaining_balance');

        return view('budget.fiscal-planning', compact(
            'budgets',
            'totalAllocated',
            'totalSpent',
            'totalRemaining',
        ));
    }

    public function budgetAllocation(): View
    {
        $budgets        = BudgetAllocation::orderBy('department')->get();
        $totalAllocated = $budgets->sum('allocated_amount');
        $totalSpent     = $budgets->sum('spent_amount');

        return view('budget.budget-allocation', compact(
            'budgets',
            'totalAllocated',
            'totalSpent',
        ));
    }

    public function departmentalBudgets(): View
    {
        $budgets = BudgetAllocation::orderBy('department')->get();

        return view('budget.departmental-budgets', compact('budgets'));
    }

    public function varianceAnalysis(): View
    {
        $budgets = BudgetAllocation::orderBy('department')->get();
        $totalVariance = $budgets->sum(fn ($b) => (float) $b->allocated_amount - (float) $b->spent_amount);

        return view('budget.variance-analysis', compact('budgets', 'totalVariance'));
    }

    public function budgetReallocations(): View
    {
        $budgets = BudgetAllocation::orderBy('department')->get();

        return view('budget.budget-reallocations', compact('budgets'));
    }
}
