<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\HmoClaim;
use App\Models\Invoice;
use App\Models\PatientAccount;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReceivableAgingService
{
    /**
     * Compute comprehensive Accounts Receivable (AR) Aging Schedule across Patients and HMO Payors.
     */
    public function getReceivableAgingReport(?string $asOfDate = null, ?string $debtorType = null): array
    {
        $cutoff = $asOfDate ? Carbon::parse($asOfDate)->endOfDay() : now()->endOfDay();

        $invoicesQuery = Invoice::with(['patientAccount', 'hmoClaims'])
            ->whereIn('status', ['UNPAID', 'PARTIAL'])
            ->where('invoice_date', '<=', $cutoff->toDateString());

        $invoices = $invoicesQuery->get();

        $patientGroups = [];
        $totalCurrent = '0.0000';
        $total1To30 = '0.0000';
        $total31To60 = '0.0000';
        $total61To90 = '0.0000';
        $total90Plus = '0.0000';
        $grandTotalAR = '0.0000';

        foreach ($invoices as $inv) {
            $unpaidCopay = bcsub((string) $inv->patient_payable, (string) $inv->paid_amount, 4);

            if (bccomp($unpaidCopay, '0.0000', 4) > 0 && ($debtorType === null || $debtorType === 'PATIENT')) {
                $invDate = Carbon::parse($inv->invoice_date);
                $days = (int) $invDate->diffInDays($cutoff);

                $cur = '0.0000';
                $d30 = '0.0000';
                $d60 = '0.0000';
                $d90 = '0.0000';
                $d90p = '0.0000';

                if ($days <= 30) {
                    $cur = $unpaidCopay;
                    $totalCurrent = bcadd($totalCurrent, $unpaidCopay, 4);
                } elseif ($days <= 60) {
                    $d30 = $unpaidCopay;
                    $total1To30 = bcadd($total1To30, $unpaidCopay, 4);
                } elseif ($days <= 90) {
                    $d60 = $unpaidCopay;
                    $total31To60 = bcadd($total31To60, $unpaidCopay, 4);
                } elseif ($days <= 120) {
                    $d90 = $unpaidCopay;
                    $total61To90 = bcadd($total61To90, $unpaidCopay, 4);
                } else {
                    $d90p = $unpaidCopay;
                    $total90Plus = bcadd($total90Plus, $unpaidCopay, 4);
                }

                $grandTotalAR = bcadd($grandTotalAR, $unpaidCopay, 4);

                $pId = $inv->patient_account_id;
                if (! isset($patientGroups[$pId])) {
                    $patientGroups[$pId] = [
                        'debtor_type'  => 'Patient Copay',
                        'debtor_code'  => $inv->patientAccount?->patient_id_number ?? 'N/A',
                        'debtor_name'  => $inv->patientAccount?->full_name ?? 'Unknown Patient',
                        'admission'    => $inv->patientAccount?->admission_type ?? 'Inpatient',
                        'hmo'          => $inv->patientAccount?->hmo_provider ?? 'Self-Pay',
                        'current'      => '0.0000',
                        'days_31_60'   => '0.0000',
                        'days_61_90'   => '0.0000',
                        'days_91_120'  => '0.0000',
                        'days_120_plus'=> '0.0000',
                        'total_due'    => '0.0000',
                        'invoices_count'=> 0,
                    ];
                }

                $patientGroups[$pId]['current'] = bcadd($patientGroups[$pId]['current'], $cur, 4);
                $patientGroups[$pId]['days_31_60'] = bcadd($patientGroups[$pId]['days_31_60'], $d30, 4);
                $patientGroups[$pId]['days_61_90'] = bcadd($patientGroups[$pId]['days_61_90'], $d60, 4);
                $patientGroups[$pId]['days_91_120'] = bcadd($patientGroups[$pId]['days_91_120'], $d90, 4);
                $patientGroups[$pId]['days_120_plus'] = bcadd($patientGroups[$pId]['days_120_plus'], $d90p, 4);
                $patientGroups[$pId]['total_due'] = bcadd($patientGroups[$pId]['total_due'], $unpaidCopay, 4);
                $patientGroups[$pId]['invoices_count']++;
            }
        }

        return [
            'as_of_date'    => $cutoff->toDateString(),
            'debtors'       => array_values($patientGroups),
            'total_current' => $totalCurrent,
            'total_31_60'   => $total1To30,
            'total_61_90'   => $total31To60,
            'total_91_120'  => $total61To90,
            'total_120_plus'=> $total90Plus,
            'grand_total'   => $grandTotalAR,
            'total_debtors' => count($patientGroups),
        ];
    }

    /**
     * Stream CSV export of AR Aging Schedule.
     */
    public function exportAgingCsv(?string $asOfDate = null): StreamedResponse
    {
        $report = $this->getReceivableAgingReport($asOfDate);
        $filename = 'AR_Aging_Schedule_' . ($asOfDate ?? date('Ymd')) . '_' . date('His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($report): void {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, ['St. Jude Metropolitan Medical Center']);
            fputcsv($handle, ['Accounts Receivable (AR) Aging Schedule']);
            fputcsv($handle, ["As-of Date: " . ($report['as_of_date'] ?? date('Y-m-d'))]);
            fputcsv($handle, ["Generated: " . date('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            // KPI Overview
            fputcsv($handle, ['AGING OVERVIEW']);
            fputcsv($handle, ['Current (< 30 Days)', number_format((float) $report['total_current'], 2, '.', '')]);
            fputcsv($handle, ['31 - 60 Days Overdue', number_format((float) $report['total_31_60'], 2, '.', '')]);
            fputcsv($handle, ['61 - 90 Days Overdue', number_format((float) $report['total_61_90'], 2, '.', '')]);
            fputcsv($handle, ['91 - 120 Days Overdue', number_format((float) $report['total_91_120'], 2, '.', '')]);
            fputcsv($handle, ['120+ Days Overdue', number_format((float) $report['total_120_plus'], 2, '.', '')]);
            fputcsv($handle, ['Grand Total Accounts Receivable', number_format((float) $report['grand_total'], 2, '.', '')]);
            fputcsv($handle, []);

            // Data Columns
            fputcsv($handle, [
                'Patient MRN / Code',
                'Debtor / Patient Name',
                'Admission Type',
                'HMO Provider',
                'Current (<30d) (PHP)',
                '31-60 Days (PHP)',
                '61-90 Days (PHP)',
                '91-120 Days (PHP)',
                '120+ Days (PHP)',
                'Total Balance Due (PHP)',
            ]);

            foreach ($report['debtors'] as $d) {
                fputcsv($handle, [
                    $d['debtor_code'],
                    $d['debtor_name'],
                    $d['admission'],
                    $d['hmo'],
                    number_format((float) $d['current'], 2, '.', ''),
                    number_format((float) $d['days_31_60'], 2, '.', ''),
                    number_format((float) $d['days_61_90'], 2, '.', ''),
                    number_format((float) $d['days_91_120'], 2, '.', ''),
                    number_format((float) $d['days_120_plus'], 2, '.', ''),
                    number_format((float) $d['total_due'], 2, '.', ''),
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, [
                'TOTALS',
                '',
                '',
                '',
                number_format((float) $report['total_current'], 2, '.', ''),
                number_format((float) $report['total_31_60'], 2, '.', ''),
                number_format((float) $report['total_61_90'], 2, '.', ''),
                number_format((float) $report['total_91_120'], 2, '.', ''),
                number_format((float) $report['total_120_plus'], 2, '.', ''),
                number_format((float) $report['grand_total'], 2, '.', ''),
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
