<?php

declare(strict_types=1);

namespace App\Http\Controllers\CashManagement;

use App\DTOs\Accounting\BankReconciliationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\CashManagement\PostBankReconciliationRequest;
use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Services\Accounting\BankReconciliationService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class BankReconciliationController extends Controller
{
    public function __construct(
        private readonly BankReconciliationService $reconciliationService
    ) {}

    public function index(Request $request): View
    {
        $bankAccounts = BankAccount::where('status', 'Active')->orderBy('name')->get();
        $selectedBankId = $request->filled('bank_account_id')
            ? (int) $request->input('bank_account_id')
            : ($bankAccounts->first()?->id ?? 1);

        $cutoffDate = $request->input('cutoff_date', date('Y-m-d'));
        $workspace = [];

        if ($selectedBankId && BankAccount::where('id', $selectedBankId)->exists()) {
            $workspace = $this->reconciliationService->getReconciliationWorkspace($selectedBankId, $cutoffDate);
        }

        $reconciliations = BankReconciliation::with(['bankAccount', 'reconciler'])
            ->latest('statement_date')
            ->get();
        $totalBookBalance = $bankAccounts->sum('balance');

        $viewName = view()->exists('accounting.cash-management.reconciliation.index')
            ? 'accounting.cash-management.reconciliation.index'
            : 'cash.bank-reconciliation';

        return view($viewName, compact(
            'bankAccounts',
            'selectedBankId',
            'cutoffDate',
            'workspace',
            'reconciliations',
            'totalBookBalance'
        ));
    }

    public function post(PostBankReconciliationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $reconciledBy = auth()->id() ?? 1;

        try {
            $dto = new BankReconciliationData(
                bankAccountId: (int) $validated['bank_account_id'],
                statementDate: $validated['statement_date'],
                cutoffDate: $validated['cutoff_date'],
                statementBalance: (string) $validated['statement_balance'],
                bookBalance: (string) $validated['book_balance'],
                clearedCheckIds: ! empty($validated['cleared_check_ids']) ? array_map('intval', $validated['cleared_check_ids']) : [],
                clearedDepositIds: ! empty($validated['cleared_deposit_ids']) ? array_map('intval', $validated['cleared_deposit_ids']) : [],
                notes: $validated['notes'] ?? null,
                reconciledBy: $reconciledBy,
            );

            $recon = $this->reconciliationService->postReconciliation($dto);

            return redirect()->back()->with('success', "Bank Statement for [{$recon->bankAccount?->name}] as of {$recon->statement_date->format('M d, Y')} successfully reconciled with Zero Variance!");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
