<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\PatientAccount;
use App\Models\Payment;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class CustomerStatementService
{
    /**
     * Compute chronological running-balance Statement of Account for a patient.
     */
    public function generateStatement(int $patientAccountId, ?string $startDate = null, ?string $endDate = null): array
    {
        $patient = PatientAccount::findOrFail($patientAccountId);

        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::parse('2020-01-01')->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay();

        // 1. Calculate Beginning Balance (prior to startDate)
        $priorInvoicesList = Invoice::with('creditNotes')
            ->where('patient_account_id', $patient->id)
            ->where('invoice_date', '<', $start->toDateString())
            ->whereNotIn('status', ['VOID', 'CANCELLED'])
            ->get();

        $priorBilledCopay = '0.0000';
        foreach ($priorInvoicesList as $pInv) {
            $postedCreditsOnInv = (string) $pInv->creditNotes->whereIn('status', ['POSTED', 'APPLIED'])->sum('amount');
            $invInitialCopay = bcadd((string) $pInv->patient_payable, bcadd((string) ($pInv->paid_amount ?? '0.0000'), $postedCreditsOnInv, 4), 4);
            $priorBilledCopay = bcadd($priorBilledCopay, $invInitialCopay, 4);
        }

        $priorPayments = (string) Payment::whereHas('invoice', fn ($q) => $q->where('patient_account_id', $patient->id))
            ->whereDoesntHave('officialReceipt', fn ($q) => $q->where('status', 'CANCELLED'))
            ->where('payment_date', '<', $start->toDateString())
            ->sum('amount');

        $priorCredits = (string) CreditNote::where('patient_account_id', $patient->id)
            ->whereIn('status', ['POSTED', 'APPLIED'])
            ->where('issue_date', '<', $start->toDateString())
            ->sum('amount');

        $beginningBalance = bcsub(bcsub($priorBilledCopay, $priorPayments, 4), $priorCredits, 4);
        if (bccomp($beginningBalance, '0.0000', 4) < 0) {
            $beginningBalance = '0.0000';
        }

        // 2. Fetch Period Transactions (Excluding voided, cancelled, or reversed movements)
        $periodInvoices = Invoice::with('creditNotes')
            ->where('patient_account_id', $patient->id)
            ->whereBetween('invoice_date', [$start->toDateString(), $end->toDateString()])
            ->whereNotIn('status', ['VOID', 'CANCELLED'])
            ->get();

        $periodPayments = Payment::with(['invoice', 'officialReceipt'])
            ->whereHas('invoice', fn ($q) => $q->where('patient_account_id', $patient->id))
            ->whereDoesntHave('officialReceipt', fn ($q) => $q->where('status', 'CANCELLED'))
            ->whereBetween('payment_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $periodCredits = CreditNote::where('patient_account_id', $patient->id)
            ->whereIn('status', ['POSTED', 'APPLIED'])
            ->whereBetween('issue_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        // 3. Merge into chronological ledger movements
        $movements = [];

        foreach ($periodInvoices as $inv) {
            $postedCreditsOnInv = (string) $inv->creditNotes->whereIn('status', ['POSTED', 'APPLIED'])->sum('amount');
            $initialBilledCopay = bcadd((string) $inv->patient_payable, bcadd((string) ($inv->paid_amount ?? '0.0000'), $postedCreditsOnInv, 4), 4);

            $movements[] = [
                'date'        => $inv->invoice_date->format('Y-m-d'),
                'type'        => 'INVOICE',
                'reference'   => $inv->invoice_number,
                'description' => "Hospital Encounter Billing [{$inv->status}]",
                'debit'       => $initialBilledCopay,
                'credit'      => '0.0000',
            ];
        }

        foreach ($periodPayments as $pay) {
            $movements[] = [
                'date'        => $pay->payment_date ? $pay->payment_date->format('Y-m-d') : date('Y-m-d'),
                'type'        => 'PAYMENT',
                'reference'   => $pay->officialReceipt?->or_number ?? $pay->payment_reference ?? 'OR-COL',
                'description' => "Cashier Collection ({$pay->payment_method})",
                'debit'       => '0.0000',
                'credit'      => (string) $pay->amount,
            ];
        }

        foreach ($periodCredits as $cn) {
            $movements[] = [
                'date'        => $cn->issue_date ? $cn->issue_date->format('Y-m-d') : date('Y-m-d'),
                'type'        => 'CREDIT_NOTE',
                'reference'   => $cn->credit_note_number,
                'description' => "Billing Adjustment / Discount ({$cn->reason})",
                'debit'       => '0.0000',
                'credit'      => (string) $cn->amount,
            ];
        }

        // Sort by date ascending
        usort($movements, fn ($a, $b) => strcmp($a['date'], $b['date']));

        // Calculate running balance
        $running = $beginningBalance;
        $totalDebits = '0.0000';
        $totalCredits = '0.0000';

        foreach ($movements as &$m) {
            $totalDebits = bcadd($totalDebits, $m['debit'], 4);
            $totalCredits = bcadd($totalCredits, $m['credit'], 4);

            $running = bcadd($running, $m['debit'], 4);
            $running = bcsub($running, $m['credit'], 4);
            $m['balance'] = $running;
        }
        unset($m);

        $endingBalance = $running;

        return [
            'patient'           => $patient,
            'start_date'        => $startDate ?? $start->toDateString(),
            'end_date'          => $endDate ?? $end->toDateString(),
            'beginning_balance' => $beginningBalance,
            'total_debits'      => $totalDebits,
            'total_credits'     => $totalCredits,
            'ending_balance'    => $endingBalance,
            'movements'         => $movements,
        ];
    }

    /**
     * Stream CSV export of Statement of Account.
     */
    public function exportStatementCsv(int $patientAccountId, ?string $startDate = null, ?string $endDate = null): StreamedResponse
    {
        $statement = $this->generateStatement($patientAccountId, $startDate, $endDate);
        $patient = $statement['patient'];
        $filename = "SOA_{$patient->patient_id_number}_" . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($statement, $patient): void {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['St. Jude Metropolitan Medical Center']);
            fputcsv($handle, ['OFFICIAL STATEMENT OF ACCOUNT (SOA)']);
            fputcsv($handle, ['Patient MRN:', $patient->patient_id_number]);
            fputcsv($handle, ['Patient Name:', $patient->full_name]);
            fputcsv($handle, ['Admission Type:', $patient->admission_type]);
            fputcsv($handle, ['HMO Coverage:', $patient->hmo_provider ?? 'Self-Pay']);
            fputcsv($handle, ['Statement Period:', "{$statement['start_date']} to {$statement['end_date']}"]);
            fputcsv($handle, ['Generated At:', date('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            // Summary
            fputcsv($handle, ['STATEMENT FINANCIAL SUMMARY']);
            fputcsv($handle, ['Beginning Balance', number_format((float) $statement['beginning_balance'], 2, '.', '')]);
            fputcsv($handle, ['Total Invoiced Charges (Debits)', number_format((float) $statement['total_debits'], 2, '.', '')]);
            fputcsv($handle, ['Total Payments & Credit Adjustments (Credits)', number_format((float) $statement['total_credits'], 2, '.', '')]);
            fputcsv($handle, ['Ending Outstanding Balance', number_format((float) $statement['ending_balance'], 2, '.', '')]);
            fputcsv($handle, []);

            // Ledger Lines
            fputcsv($handle, ['Date', 'Type', 'Reference #', 'Particulars / Description', 'Charges / Debit (PHP)', 'Payments / Credit (PHP)', 'Running Balance (PHP)']);
            fputcsv($handle, [$statement['start_date'], 'BALANCE_FORWARD', '-', 'Beginning Balance Forwarded', '', '', number_format((float) $statement['beginning_balance'], 2, '.', '')]);

            foreach ($statement['movements'] as $m) {
                fputcsv($handle, [
                    $m['date'],
                    $m['type'],
                    $m['reference'],
                    $m['description'],
                    number_format((float) $m['debit'], 2, '.', ''),
                    number_format((float) $m['credit'], 2, '.', ''),
                    number_format((float) $m['balance'], 2, '.', ''),
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['TOTALS', '', '', '', number_format((float) $statement['total_debits'], 2, '.', ''), number_format((float) $statement['total_credits'], 2, '.', ''), number_format((float) $statement['ending_balance'], 2, '.', '')]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
