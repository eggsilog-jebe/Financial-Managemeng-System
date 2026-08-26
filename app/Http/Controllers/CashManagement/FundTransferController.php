<?php

declare(strict_types=1);

namespace App\Http\Controllers\CashManagement;

use App\DTOs\Accounting\FundTransferData;
use App\Http\Controllers\Controller;
use App\Http\Requests\CashManagement\ExecuteFundTransferRequest;
use App\Models\BankAccount;
use App\Models\FundTransfer;
use App\Services\Accounting\FundTransferService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class FundTransferController extends Controller
{
    public function __construct(
        private readonly FundTransferService $fundTransferService
    ) {}

    public function index(Request $request): View
    {
        $query = FundTransfer::with(['sourceBank', 'destinationBank', 'journalEntry', 'author'])
            ->latest('transfer_date');

        if ($request->filled('date_from')) {
            $query->whereDate('transfer_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transfer_date', '<=', $request->input('date_to'));
        }

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', $search)
                  ->orWhere('source_account', 'like', $search)
                  ->orWhere('destination_account', 'like', $search)
                  ->orWhere('memo', 'like', $search);
            });
        }

        $transfers = $query->paginate(20)->withQueryString();
        $totalTransferVolume = FundTransfer::sum('amount');
        $bankAccounts = BankAccount::where('status', 'Active')->where('is_active', true)->orderBy('name')->get();

        $viewName = view()->exists('accounting.cash-management.transfers.index')
            ? 'accounting.cash-management.transfers.index'
            : 'cash.fund-transfers';

        return view($viewName, compact(
            'transfers',
            'totalTransferVolume',
            'bankAccounts'
        ));
    }

    public function store(ExecuteFundTransferRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $createdBy = auth()->id() ?? 1;

        try {
            $dto = new FundTransferData(
                sourceBankAccountId: (int) $validated['source_bank_account_id'],
                destinationBankAccountId: (int) $validated['destination_bank_account_id'],
                amount: (string) $validated['amount'],
                transferDate: $validated['transfer_date'],
                transferMethod: $validated['transfer_method'] ?? 'INSTAPAY_PESONET',
                memo: $validated['memo'] ?? null,
                createdBy: $createdBy,
            );

            $transfer = $this->fundTransferService->executeTransfer($dto);

            return redirect()->back()->with('success', "Fund Transfer [{$transfer->reference_number}] of ₱" . number_format((float) $transfer->amount, 2) . " executed and General Ledger entry posted successfully!");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
