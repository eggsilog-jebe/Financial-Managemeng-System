<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accounting\Export;

use App\Http\Controllers\Controller;
use App\Models\Bir2307Certificate;
use App\Models\Payment;
use App\Services\Accounting\Exports\GeneralLedgerExportService;
use App\Services\Accounting\Exports\TrialBalanceExportService;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExportAndPrintController extends Controller
{
    /**
     * Display printable BIR-compliant Official Receipt.
     */
    public function printOfficialReceipt(int $paymentId): View
    {
        $payment = Payment::with(['patientAccount', 'invoice.invoiceItems', 'officialReceipt'])->findOrFail($paymentId);

        return view('accounting.print.official-receipt', [
            'payment' => $payment,
        ]);
    }

    /**
     * Display printable BIR Form 2307 Certificate.
     */
    public function printBir2307(int $certificateId): View
    {
        $cert = Bir2307Certificate::with(['purchaseBill.vendor', 'doctorProfile'])->findOrFail($certificateId);

        return view('accounting.print.bir-2307', [
            'cert' => $cert,
        ]);
    }

    /**
     * Download CSV export of Trial Balance.
     */
    public function downloadTrialBalanceCsv(TrialBalanceExportService $service): StreamedResponse
    {
        return $service->exportCsv();
    }

    /**
     * Download memory-safe CSV export of General Ledger Book for BIR CAS audits.
     */
    public function downloadGeneralLedgerCsv(GeneralLedgerExportService $service): StreamedResponse
    {
        return $service->exportCsv();
    }
}
