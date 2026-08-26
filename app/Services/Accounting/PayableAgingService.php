<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\PurchaseBill;
use App\Models\Vendor;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PayableAgingService
{
    /**
     * Compute comprehensive Payable Aging Schedule grouped by vendor with 30-day aging buckets.
     */
    public function getPayableAgingReport(?string $asOfDate = null, ?int $vendorId = null): array
    {
        $cutoff = $asOfDate ? Carbon::parse($asOfDate)->endOfDay() : now()->endOfDay();

        $billsQuery = PurchaseBill::with('vendor')
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE', 'APPROVED'])
            ->where('bill_date', '<=', $cutoff->toDateString());

        if ($vendorId) {
            $billsQuery->where('vendor_id', $vendorId);
        }

        $bills = $billsQuery->get();

        $vendors = Vendor::with('purchaseBills')->orderBy('name')->get();

        $vendorGroups = [];
        $totalCurrent = '0.0000';
        $total1To30 = '0.0000';
        $total31To60 = '0.0000';
        $total61To90 = '0.0000';
        $total90Plus = '0.0000';
        $grandTotalPayable = '0.0000';

        foreach ($bills as $bill) {
            $unpaid = bcsub((string) $bill->total_amount, (string) $bill->paid_amount, 4);
            if (bccomp($unpaid, '0.0000', 4) <= 0) {
                continue;
            }

            $dueDate = Carbon::parse($bill->due_date);
            $daysOverdue = (int) $dueDate->diffInDays($cutoff, false); // negative means not yet due

            $cur = '0.0000';
            $d30 = '0.0000';
            $d60 = '0.0000';
            $d90 = '0.0000';
            $d90p = '0.0000';

            if ($daysOverdue <= 0) {
                $cur = $unpaid;
                $totalCurrent = bcadd($totalCurrent, $unpaid, 4);
            } elseif ($daysOverdue <= 30) {
                $d30 = $unpaid;
                $total1To30 = bcadd($total1To30, $unpaid, 4);
            } elseif ($daysOverdue <= 60) {
                $d60 = $unpaid;
                $total31To60 = bcadd($total31To60, $unpaid, 4);
            } elseif ($daysOverdue <= 90) {
                $d90 = $unpaid;
                $total61To90 = bcadd($total61To90, $unpaid, 4);
            } else {
                $d90p = $unpaid;
                $total90Plus = bcadd($total90Plus, $unpaid, 4);
            }

            $grandTotalPayable = bcadd($grandTotalPayable, $unpaid, 4);

            $vId = $bill->vendor_id;
            if (! isset($vendorGroups[$vId])) {
                $vendorGroups[$vId] = [
                    'vendor_id'    => $vId,
                    'vendor_code'  => $bill->vendor?->code ?? 'N/A',
                    'vendor_name'  => $bill->vendor?->name ?? 'Unknown Vendor',
                    'tin'          => $bill->vendor?->tin ?? '-',
                    'terms'        => $bill->vendor?->payment_terms_days ?? 30,
                    'current'      => '0.0000',
                    'days_1_30'    => '0.0000',
                    'days_31_60'   => '0.0000',
                    'days_61_90'   => '0.0000',
                    'days_90_plus' => '0.0000',
                    'total_due'    => '0.0000',
                    'bills_count'  => 0,
                ];
            }

            $vendorGroups[$vId]['current'] = bcadd($vendorGroups[$vId]['current'], $cur, 4);
            $vendorGroups[$vId]['days_1_30'] = bcadd($vendorGroups[$vId]['days_1_30'], $d30, 4);
            $vendorGroups[$vId]['days_31_60'] = bcadd($vendorGroups[$vId]['days_31_60'], $d60, 4);
            $vendorGroups[$vId]['days_61_90'] = bcadd($vendorGroups[$vId]['days_61_90'], $d90, 4);
            $vendorGroups[$vId]['days_90_plus'] = bcadd($vendorGroups[$vId]['days_90_plus'], $d90p, 4);
            $vendorGroups[$vId]['total_due'] = bcadd($vendorGroups[$vId]['total_due'], $unpaid, 4);
            $vendorGroups[$vId]['bills_count']++;
        }

        return [
            'as_of_date'     => $cutoff->toDateString(),
            'vendors'        => array_values($vendorGroups),
            'total_current'  => $totalCurrent,
            'total_1_30'     => $total1To30,
            'total_31_60'    => $total31To60,
            'total_61_90'    => $total61To90,
            'total_90_plus'  => $total90Plus,
            'grand_total'    => $grandTotalPayable,
            'total_vendors'  => count($vendorGroups),
        ];
    }

    /**
     * Stream CSV export of the Payable Aging Schedule.
     */
    public function exportAgingCsv(?string $asOfDate = null): StreamedResponse
    {
        $report = $this->getPayableAgingReport($asOfDate);
        $filename = 'AP_Aging_Schedule_' . ($asOfDate ?? date('Ymd')) . '_' . date('His') . '.csv';

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
            fputcsv($handle, ['Accounts Payable (AP) Aging Schedule']);
            fputcsv($handle, ["As-of Date: " . ($report['as_of_date'] ?? date('Y-m-d'))]);
            fputcsv($handle, ["Generated: " . date('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            // Summary KPIs
            fputcsv($handle, ['AGING OVERVIEW']);
            fputcsv($handle, ['Current (Not Yet Due)', number_format((float) $report['total_current'], 2, '.', '')]);
            fputcsv($handle, ['1 - 30 Days Overdue', number_format((float) $report['total_1_30'], 2, '.', '')]);
            fputcsv($handle, ['31 - 60 Days Overdue', number_format((float) $report['total_31_60'], 2, '.', '')]);
            fputcsv($handle, ['61 - 90 Days Overdue', number_format((float) $report['total_61_90'], 2, '.', '')]);
            fputcsv($handle, ['90+ Days Overdue', number_format((float) $report['total_90_plus'], 2, '.', '')]);
            fputcsv($handle, ['Grand Total AP Payables', number_format((float) $report['grand_total'], 2, '.', '')]);
            fputcsv($handle, []);

            // Vendor Columns
            fputcsv($handle, [
                'Vendor Code',
                'Vendor Name',
                'TIN',
                'Payment Terms',
                'Current (PHP)',
                '1-30 Days (PHP)',
                '31-60 Days (PHP)',
                '61-90 Days (PHP)',
                '90+ Days (PHP)',
                'Total Balance Due (PHP)',
            ]);

            foreach ($report['vendors'] as $v) {
                fputcsv($handle, [
                    $v['vendor_code'],
                    $v['vendor_name'],
                    $v['tin'],
                    "Net {$v['terms']} Days",
                    number_format((float) $v['current'], 2, '.', ''),
                    number_format((float) $v['days_1_30'], 2, '.', ''),
                    number_format((float) $v['days_31_60'], 2, '.', ''),
                    number_format((float) $v['days_61_90'], 2, '.', ''),
                    number_format((float) $v['days_90_plus'], 2, '.', ''),
                    number_format((float) $v['total_due'], 2, '.', ''),
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, [
                'TOTALS',
                '',
                '',
                '',
                number_format((float) $report['total_current'], 2, '.', ''),
                number_format((float) $report['total_1_30'], 2, '.', ''),
                number_format((float) $report['total_31_60'], 2, '.', ''),
                number_format((float) $report['total_61_90'], 2, '.', ''),
                number_format((float) $report['total_90_plus'], 2, '.', ''),
                number_format((float) $report['grand_total'], 2, '.', ''),
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
