<?php

declare(strict_types=1);

namespace App\Http\Controllers\GeneralLedger;

use App\DTOs\Accounting\AccountData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreAccountRequest;
use App\Http\Requests\Accounting\UpdateAccountRequest;
use App\Models\Account;
use App\Services\Accounting\ChartOfAccountsService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ChartOfAccountsController extends Controller
{
    public function __construct(
        private readonly ChartOfAccountsService $coaService,
    ) {}

    public function index(Request $request): View
    {
        $category = $request->query('category');
        $status = $request->query('status');
        $search = $request->query('q') ?? $request->query('search');

        $isActive = null;
        if ($status === 'active') {
            $isActive = true;
        } elseif ($status === 'inactive') {
            $isActive = false;
        }

        $accounts = $this->coaService->getAccountsList(
            category: $category,
            isActive: $isActive,
            search: $search,
        );

        // Summary totals per classification
        $allAccounts = Account::with(['journalEntryLines.journalEntry' => function ($q): void {
            $q->where('status', 'POSTED');
        }])->get();

        $assetTotal     = $allAccounts->where('category', 'ASSET')->sum(fn (Account $a): float => (float) $a->current_balance);
        $liabilityTotal = $allAccounts->where('category', 'LIABILITY')->sum(fn (Account $a): float => (float) $a->current_balance);
        $equityTotal    = $allAccounts->where('category', 'EQUITY')->sum(fn (Account $a): float => (float) $a->current_balance);
        $revenueTotal   = $allAccounts->where('category', 'REVENUE')->sum(fn (Account $a): float => (float) $a->current_balance);
        $expenseTotal   = $allAccounts->where('category', 'EXPENSE')->sum(fn (Account $a): float => (float) $a->current_balance);

        return view('general-ledger.chart-of-accounts', [
            'accounts'       => $accounts,
            'category'       => $category,
            'status'         => $status,
            'search'         => $search,
            'assetTotal'     => $assetTotal,
            'liabilityTotal' => $liabilityTotal,
            'equityTotal'    => $equityTotal,
            'revenueTotal'   => $revenueTotal,
            'expenseTotal'   => $expenseTotal,
        ]);
    }

    public function store(StoreAccountRequest $request): Response
    {
        $dto = AccountData::fromArray($request->validated());
        $account = $this->coaService->createAccount(
            dto: $dto,
            userId: $request->user()?->id,
            userName: $request->user()?->name,
            ipAddress: $request->ip(),
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => "Account [{$account->code} - {$account->name}] created successfully.",
                'account' => $account,
            ], 201);
        }

        return redirect()->route('gl.chart-of-accounts')
            ->with('success', "Account [{$account->code} - {$account->name}] created successfully.");
    }

    public function update(UpdateAccountRequest $request, int $id): Response
    {
        $account = Account::findOrFail($id);
        $dto = AccountData::fromArray($request->validated());

        try {
            $updated = $this->coaService->updateAccount(
                account: $account,
                dto: $dto,
                userId: $request->user()?->id,
                userName: $request->user()?->name,
                ipAddress: $request->ip(),
            );
        } catch (DomainException $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return redirect()->route('gl.chart-of-accounts')->with('error', $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => "Account [{$updated->code}] updated successfully.",
                'account' => $updated,
            ]);
        }

        return redirect()->route('gl.chart-of-accounts')
            ->with('success', "Account [{$updated->code} - {$updated->name}] updated successfully.");
    }

    public function toggleStatus(Request $request, int $id): Response
    {
        $account = Account::findOrFail($id);

        try {
            $updated = $this->coaService->toggleAccountStatus(
                account: $account,
                userId: $request->user()?->id,
                userName: $request->user()?->name,
                ipAddress: $request->ip(),
            );
        } catch (DomainException $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return redirect()->route('gl.chart-of-accounts')->with('error', $e->getMessage());
        }

        $statusText = $updated->is_active ? 'activated' : 'deactivated';

        if ($request->wantsJson()) {
            return response()->json([
                'message'   => "Account [{$updated->code}] successfully {$statusText}.",
                'is_active' => $updated->is_active,
            ]);
        }

        return redirect()->route('gl.chart-of-accounts')
            ->with('success', "Account [{$updated->code}] successfully {$statusText}.");
    }
}
