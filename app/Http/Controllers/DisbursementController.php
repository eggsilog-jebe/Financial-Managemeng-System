<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PaymentRequest;
use Illuminate\Contracts\View\View;

final class DisbursementController extends Controller
{
    public function paymentRequests(): View
    {
        $requisitions    = PaymentRequest::latest()->get();
        $pendingAmount   = $requisitions->where('status', 'PENDING')->sum('amount');
        $approvedAmount  = $requisitions->where('status', 'APPROVED')->sum('amount');

        return view('disbursement.payment-requests', compact(
            'requisitions',
            'pendingAmount',
            'approvedAmount',
        ));
    }

    public function checkRegister(): View
    {
        $checks       = PaymentRequest::where('status', 'DISBURSED')->latest()->get();
        $totalReleased = $checks->sum('amount');

        return view('disbursement.check-register', compact('checks', 'totalReleased'));
    }

    public function eftTransfers(): View
    {
        $transfers    = PaymentRequest::where('status', 'APPROVED')->latest()->get();
        $totalAmount  = $transfers->sum('amount');

        return view('disbursement.eft-transfers', compact('transfers', 'totalAmount'));
    }

    public function disbursementApprovals(): View
    {
        $approvals = PaymentRequest::where('status', 'PENDING')->latest()->get();
        $totalForApproval = $approvals->sum('amount');

        return view('disbursement.disbursement-approvals', compact('approvals', 'totalForApproval'));
    }

    public function pettyCash(): View
    {
        $pettyRequests = PaymentRequest::where('status', 'DISBURSED')->latest()->get();
        $totalPetty    = $pettyRequests->sum('amount');

        return view('disbursement.petty-cash', compact('pettyRequests', 'totalPetty'));
    }
}
