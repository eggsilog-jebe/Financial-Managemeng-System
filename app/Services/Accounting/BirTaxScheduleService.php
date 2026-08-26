<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\Bir2307Certificate;
use App\Models\OfficialReceipt;
use App\Models\PayrollRun;
use Illuminate\Support\Collection;

final class BirTaxScheduleService
{
    /**
     * Generate BIR Form 1601-EQ (Quarterly Remittance Return of Creditable Income Taxes Withheld (Expanded)) Summary.
     */
    public function getBir1601EQSummary(string $fromDate, string $toDate): array
    {
        $certs = Bir2307Certificate::whereBetween('period_from', [$fromDate, $toDate])->get();

        $byAtc = [];
        $totalTaxBase = '0.0000';
        $totalWithheld = '0.0000';

        foreach ($certs as $cert) {
            $atc = $cert->atc_code;
            if (! isset($byAtc[$atc])) {
                $byAtc[$atc] = [
                    'atc_code'      => $atc,
                    'description'   => $this->getAtcDescription($atc),
                    'tax_rate'      => (string) $cert->tax_rate,
                    'tax_base'      => '0.0000',
                    'tax_withheld'  => '0.0000',
                    'count'         => 0,
                ];
            }

            $byAtc[$atc]['tax_base'] = bcadd($byAtc[$atc]['tax_base'], (string) $cert->tax_base_amount, 4);
            $byAtc[$atc]['tax_withheld'] = bcadd($byAtc[$atc]['tax_withheld'], (string) $cert->tax_withheld, 4);
            $byAtc[$atc]['count']++;

            $totalTaxBase = bcadd($totalTaxBase, (string) $cert->tax_base_amount, 4);
            $totalWithheld = bcadd($totalWithheld, (string) $cert->tax_withheld, 4);
        }

        return [
            'period_from'    => $fromDate,
            'period_to'      => $toDate,
            'schedules'      => array_values($byAtc),
            'total_tax_base' => $totalTaxBase,
            'total_withheld' => $totalWithheld,
            'total_forms'    => $certs->count(),
        ];
    }

    /**
     * Generate BIR Form 1601-C (Monthly Remittance Return of Income Taxes Withheld on Compensation) Summary.
     */
    public function getBir1601CSummary(string $year, ?string $month = null): array
    {
        $query = PayrollRun::where('status', 'DISBURSED')
            ->whereYear('payout_date', $year);

        if ($month) {
            $query->whereMonth('payout_date', $month);
        }

        $runs = $query->get();

        $grossCompensation = '0.0000';
        $statutoryExemptions = '0.0000';
        $taxableCompensation = '0.0000';
        $taxWithheld = '0.0000';
        $totalEmployees = 0;

        foreach ($runs as $run) {
            $grossCompensation = bcadd($grossCompensation, (string) $run->total_gross_pay, 4);
            $statTotal = bcadd(bcadd((string) $run->total_sss_employee, (string) $run->total_philhealth_employee, 4), (string) $run->total_pagibig_employee, 4);
            $statutoryExemptions = bcadd($statutoryExemptions, $statTotal, 4);
            $taxWithheld = bcadd($taxWithheld, (string) $run->total_withholding_tax_1601c, 4);
            $totalEmployees += $run->employee_count;
        }

        $taxableCompensation = bcsub($grossCompensation, $statutoryExemptions, 4);

        return [
            'fiscal_year'          => $year,
            'month'                => $month ?? 'ALL',
            'employee_count'       => $totalEmployees,
            'gross_compensation'   => $grossCompensation,
            'statutory_exemptions' => $statutoryExemptions,
            'taxable_compensation' => $taxableCompensation,
            'tax_withheld'         => $taxWithheld,
            'payroll_runs_count'   => $runs->count(),
        ];
    }

    /**
     * Generate BIR Form 2550M/2550Q (Value-Added Tax Return) Summary.
     */
    public function getBirVatSummary(string $fromDate, string $toDate): array
    {
        $receipts = OfficialReceipt::where('status', 'VALID')
            ->whereBetween('or_date', [$fromDate, $toDate])
            ->get();

        $vatableSales = '0.0000';
        $vatExemptSales = '0.0000';
        $outputVat = '0.0000';
        $totalCollected = '0.0000';

        foreach ($receipts as $or) {
            $vatableSales = bcadd($vatableSales, (string) $or->vatable_sales, 4);
            $vatExemptSales = bcadd($vatExemptSales, (string) $or->vat_exempt_sales, 4);
            $outputVat = bcadd($outputVat, (string) $or->vat_amount, 4);
            $totalCollected = bcadd($totalCollected, (string) $or->total_amount_collected, 4);
        }

        return [
            'period_from'          => $fromDate,
            'period_to'            => $toDate,
            'vatable_sales'        => $vatableSales,
            'vat_exempt_sales'     => $vatExemptSales,
            'output_vat_12'        => $outputVat,
            'total_collections'    => $totalCollected,
            'receipts_count'       => $receipts->count(),
            'total_receipts_count' => $receipts->count(),
        ];
    }

    private function getAtcDescription(string $atc): string
    {
        return match ($atc) {
            'WI158' => 'Payments to suppliers of goods (1%)',
            'WI160' => 'Payments to suppliers of services (2%)',
            'WI010' => 'Medical Practitioners / Consultants (10%)',
            'WI020' => 'Medical Practitioners Gross > ₱3M (15%)',
            default => 'Expanded Withholding Tax General',
        };
    }
}
