<?php

declare(strict_types=1);

namespace App\Http\Controllers\Collection;

use App\DTOs\Accounting\BankDepositCreateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Collection\ClearBankDepositRequest;
use App\Http\Requests\Collection\CreateBankDepositRequest;
use App\Models\BankAccount;
use App\Models\BankDeposit;
use App\Models\CashierShift;
use App\Services\Accounting\BankDepositService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class BankDepositController extends Controller
{
    public function __construct(
        private readonly BankDepositService $bankDepositService
    ) {}

    public function index(): View
    {
        $deposits = BankDeposit::with(['bankAccount', 'cashierShift.cashier'])
            ->latest('deposit_date')
            ->get();
        $totalDeposits = $deposits->sum('total_deposited');
        $bankAccounts = BankAccount::where('status', 'Active')->get();
        $closedShifts = CashierShift::where('status', 'CLOSED')->latest('closed_at')->get();

        $viewName = view()->exists('accounting.collection.bank-deposits.index')
            ? 'accounting.collection.bank-deposits.index'
            : 'collection.bank-deposits';

        return view($viewName, compact(
            'deposits',
            'totalDeposits',
            'bankAccounts',
            'closedShifts'
        ));
    }

    public function store(CreateBankDepositRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $dto = new BankDepositCreateData(
                bankAccountId: (int) $validated['bank_account_id'],
                cashierShiftId: ! empty($validated['cashier_shift_id']) ? (int) $validated['cashier_shift_id'] : null,
                depositDate: $validated['deposit_date'],
                cashAmount: (string) $validated['cash_amount'],
                checkAmount: ! empty($validated['check_amount']) ? (string) $validated['check_amount'] : '0.0000',
                bankRef: $validated['bank_reference_number'] ?? null,
                teller: $validated['validated_by_teller'] ?? null,
            );

            $deposit = $this->bankDepositService->createDeposit($dto);

            return redirect()->back()->with('success', "Deposit Slip [{$deposit->deposit_reference}] created successfully for ₱" . number_format((float) $deposit->total_deposited, 2));
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function clear(ClearBankDepositRequest $request, int $id): RedirectResponse
    {
        $validated = $request->validated();
        $validatedBy = auth()->id() ?? 1;

        try {
            $deposit = $this->bankDepositService->clearDeposit(
                depositId: $id,
                bankRef: $validated['bank_reference_number'],
                teller: $validated['validated_by_teller'] ?? null,
                validatedBy: $validatedBy
            );

            return redirect()->back()->with('success', "Deposit [{$deposit->deposit_reference}] validated and cleared! Bank balance updated and GL entry posted.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $reason = $request->input('reason', 'Deposit discrepancy identified.');

        try {
            $deposit = $this->bankDepositService->rejectDeposit($id, $reason);

            return redirect()->back()->with('success', "Deposit [{$deposit->deposit_reference}] marked as rejected.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
