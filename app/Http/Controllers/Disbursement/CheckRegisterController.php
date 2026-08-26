<?php

declare(strict_types=1);

namespace App\Http\Controllers\Disbursement;

use App\DTOs\Accounting\CheckIssueData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\IssueCheckRequest;
use App\Models\BankAccount;
use App\Models\CheckRegister;
use App\Models\DisbursementVoucher;
use App\Services\Accounting\CheckRegisterService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class CheckRegisterController extends Controller
{
    public function __construct(
        private readonly CheckRegisterService $checkService,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = CheckRegister::with(['disbursementVoucher', 'bankAccount'])
            ->latest('check_date');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('check_number', 'LIKE', "%{$search}%")
                  ->orWhere('payee_name', 'LIKE', "%{$search}%")
                  ->orWhereHas('disbursementVoucher', fn ($vq) => $vq->where('voucher_number', 'LIKE', "%{$search}%"));
            });
        }

        $checks = $query->paginate(15)->withQueryString();

        $totalIssued = CheckRegister::count();
        $totalReleased = CheckRegister::whereIn('status', ['ISSUED', 'RELEASED'])->sum('amount');
        $totalCleared = CheckRegister::where('status', 'CLEARED')->sum('amount');
        $totalVoid = CheckRegister::where('status', 'VOID')->sum('amount');

        $approvedVouchers = DisbursementVoucher::where('payment_method', 'CHECK')
            ->whereIn('status', ['APPROVED', 'RELEASED'])
            ->whereDoesntHave('checkRegister')
            ->get();

        $bankAccounts = BankAccount::where('status', 'Active')->orderBy('bank_name')->get();

        return view('disbursement.check-register', compact(
            'checks',
            'totalIssued',
            'totalReleased',
            'totalCleared',
            'totalVoid',
            'approvedVouchers',
            'bankAccounts',
            'status',
            'search',
        ));
    }

    public function store(IssueCheckRequest $request): RedirectResponse
    {
        try {
            $dto = CheckIssueData::fromArray($request->validated());
            $check = $this->checkService->issueCheck($dto);

            return redirect()->back()->with('success', "Bank Check [{$check->check_number}] issued successfully to {$check->payee_name}.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function print(int|string $id): View
    {
        $check = CheckRegister::with(['disbursementVoucher', 'bankAccount'])->findOrFail((int) $id);

        return view('disbursement.check-print', compact('check'));
    }

    public function clear(int|string $id): RedirectResponse
    {
        try {
            $check = $this->checkService->clearCheck((int) $id);

            return redirect()->back()->with('success', "Check [{$check->check_number}] marked as CLEARED upon bank reconciliation.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
