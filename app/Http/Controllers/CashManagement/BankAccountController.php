<?php

declare(strict_types=1);

namespace App\Http\Controllers\CashManagement;

use App\DTOs\Accounting\BankAccountData;
use App\Http\Controllers\Controller;
use App\Http\Requests\CashManagement\StoreBankAccountRequest;
use App\Http\Requests\CashManagement\UpdateBankAccountRequest;
use App\Models\Account;
use App\Models\BankAccount;
use App\Services\Accounting\BankAccountService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class BankAccountController extends Controller
{
    public function __construct(
        private readonly BankAccountService $bankAccountService
    ) {}

    public function index(Request $request): View
    {
        $query = BankAccount::with('glAccount')->orderBy('name');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('bank_name', 'like', $search)
                  ->orWhere('account_number', 'like', $search)
                  ->orWhere('gl_code', 'like', $search);
            });
        }

        $bankAccounts = $query->get();
        $totalBalance = $bankAccounts->where('status', 'Active')->sum('balance');
        $activeCount = $bankAccounts->where('status', 'Active')->count();
        $glAccounts = Account::where('category', 'ASSET')->orderBy('code')->get();

        $viewName = view()->exists('accounting.cash-management.accounts.index')
            ? 'accounting.cash-management.accounts.index'
            : 'cash.bank-accounts';

        return view($viewName, compact(
            'bankAccounts',
            'totalBalance',
            'activeCount',
            'glAccounts'
        ));
    }

    public function store(StoreBankAccountRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $dto = new BankAccountData(
                name: $validated['name'],
                bankName: $validated['bank_name'],
                accountNumber: $validated['account_number'],
                glCode: $validated['gl_code'] ?? '1020',
                glAccountId: ! empty($validated['gl_account_id']) ? (int) $validated['gl_account_id'] : null,
                purpose: $validated['purpose'],
                currency: $validated['currency'] ?? 'PHP',
                openingBalance: (string) $validated['opening_balance'],
                minimumBalance: ! empty($validated['minimum_balance']) ? (string) $validated['minimum_balance'] : '50000.0000',
                status: $validated['status'] ?? 'Active',
                isActive: $request->boolean('is_active', true),
            );

            $account = $this->bankAccountService->createBankAccount($dto);

            return redirect()->back()->with('success', "Bank Account [{$account->bank_name} - {$account->account_number}] created successfully.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update(UpdateBankAccountRequest $request, int $id): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $dto = new BankAccountData(
                name: $validated['name'],
                bankName: $validated['bank_name'],
                accountNumber: $validated['account_number'],
                glCode: $validated['gl_code'] ?? '1020',
                glAccountId: ! empty($validated['gl_account_id']) ? (int) $validated['gl_account_id'] : null,
                purpose: $validated['purpose'],
                currency: $validated['currency'] ?? 'PHP',
                openingBalance: '0.0000', // Unchanged during update
                minimumBalance: ! empty($validated['minimum_balance']) ? (string) $validated['minimum_balance'] : '50000.0000',
                status: $validated['status'] ?? 'Active',
                isActive: $request->boolean('is_active', true),
            );

            $account = $this->bankAccountService->updateBankAccount($id, $dto);

            return redirect()->back()->with('success', "Bank Account [{$account->name}] updated successfully.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function toggle(int $id): RedirectResponse
    {
        try {
            $account = $this->bankAccountService->toggleStatus($id);

            return redirect()->back()->with('success', "Bank Account [{$account->name}] status toggled to {$account->status}.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
