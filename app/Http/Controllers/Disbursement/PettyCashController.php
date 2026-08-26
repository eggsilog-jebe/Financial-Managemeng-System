<?php

declare(strict_types=1);

namespace App\Http\Controllers\Disbursement;

use App\DTOs\Accounting\PettyCashExpenseData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StorePettyCashExpenseRequest;
use App\Models\BankAccount;
use App\Models\PettyCashExpense;
use App\Models\PettyCashFund;
use App\Services\Accounting\PettyCashService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PettyCashController extends Controller
{
    public function __construct(
        private readonly PettyCashService $pettyCashService,
    ) {}

    public function index(Request $request): View
    {
        $funds = PettyCashFund::where('status', 'Active')->get();
        $selectedFundId = $request->query('fund_id');

        $fund = null;
        if ($selectedFundId) {
            $fund = $funds->firstWhere('id', (int) $selectedFundId);
        }

        if (! $fund && $funds->isNotEmpty()) {
            $fund = $funds->first();
        }

        $expenses = collect();
        $unreplenishedTotal = '0.0000';
        $replenishedTotal = '0.0000';

        if ($fund) {
            $expenses = PettyCashExpense::where('petty_cash_fund_id', $fund->id)
                ->latest('expense_date')
                ->paginate(15)
                ->withQueryString();

            $unreplenishedTotal = (string) PettyCashExpense::where('petty_cash_fund_id', $fund->id)
                ->where('status', 'UNREPLENISHED')
                ->sum('amount');

            $replenishedTotal = (string) PettyCashExpense::where('petty_cash_fund_id', $fund->id)
                ->where('status', 'REPLENISHED')
                ->sum('amount');
        }

        $bankAccounts = BankAccount::where('status', 'Active')->orderBy('bank_name')->get();

        return view('disbursement.petty-cash', compact(
            'funds',
            'fund',
            'expenses',
            'unreplenishedTotal',
            'replenishedTotal',
            'bankAccounts',
        ));
    }

    public function storeFund(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fund_name'      => ['required', 'string', 'max:255', 'unique:petty_cash_funds,fund_name'],
            'custodian_name' => ['required', 'string', 'max:255'],
            'float_limit'    => ['required', 'numeric', 'gt:0'],
            'gl_code'        => ['nullable', 'string', 'max:30'],
        ]);

        $fund = PettyCashFund::create([
            'fund_name'       => trim($validated['fund_name']),
            'custodian_name'  => trim($validated['custodian_name']),
            'float_limit'     => (string) $validated['float_limit'],
            'current_balance' => (string) $validated['float_limit'],
            'gl_code'         => $validated['gl_code'] ?? '1030',
            'status'          => 'Active',
        ]);

        return redirect()->route('disbursement.petty-cash', ['fund_id' => $fund->id])
            ->with('success', "Petty Cash Fund [{$fund->fund_name}] registered successfully with ₱" . number_format((float) $fund->float_limit, 2) . " float.");
    }

    public function storeExpense(StorePettyCashExpenseRequest $request): RedirectResponse
    {
        try {
            $dto = PettyCashExpenseData::fromArray($request->validated());
            $expense = $this->pettyCashService->recordExpense($dto);

            return redirect()->route('disbursement.petty-cash', ['fund_id' => $expense->petty_cash_fund_id])
                ->with('success', "Petty cash expense slip [{$expense->voucher_number}] (₱" . number_format((float) $expense->amount, 2) . ") recorded successfully.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function replenish(Request $request): RedirectResponse
    {
        try {
            $fundId = (int) $request->input('fund_id');
            $bankAccountId = (int) $request->input('bank_account_id');
            $userId = auth()->id() ?? 1;

            $voucher = $this->pettyCashService->replenishFund($fundId, $bankAccountId, $userId);

            return redirect()->route('disbursement.petty-cash', ['fund_id' => $fundId])
                ->with('success', "Petty Cash Fund replenished to ₱" . number_format((float) $voucher->gross_amount, 2) . " via Reimbursement Voucher [{$voucher->voucher_number}].");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
