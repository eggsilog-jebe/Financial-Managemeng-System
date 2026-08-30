<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\Invoice;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReceivableAgingService
{
    /**
     * Compute comprehensive Accounts Receivable (AR) Aging Schedule across Patients and HMO Payors.
     */
    public function getReceivableAgingReport(
        ?string $asOfDate = null,
        ?string $payorType = null,
        ?string $search = null,
        ?string $admissionType = null
    ): array {
        $cutoffDate = $asOfDate ? Carbon::parse($asOfDate)->startOfDay() : now()->startOfDay();

        $invoicesQuery = Invoice::with(['patientAccount', 'hmoClaims', 'philhealthClaim', 'creditNotes', 'statutoryDiscounts'])
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'ISSUED'])
            ->where('invoice_date', '<=', $cutoffDate->toDateString());

        if ($search) {
            $invoicesQuery->where(function ($q) use ($search): void {
                $q->where('invoice_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('patientAccount', function ($pq) use ($search): void {
                      $pq->where('full_name', 'LIKE', "%{$search}%")
                         ->orWhere('patient_id_number', 'LIKE', "%{$search}%")
                         ->orWhere('hmo_provider', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('hmoClaims', function ($hq) use ($search): void {
                      $hq->where('hmo_provider', 'LIKE', "%{$search}%")
                         ->orWhere('loa_number', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('philhealthClaim', function ($phq) use ($search): void {
                      $phq->where('claim_series_number', 'LIKE', "%{$search}%")
                          ->orWhere('member_pin', 'LIKE', "%{$search}%")
                          ->orWhere('primary_case_rate_code', 'LIKE', "%{$search}%");
                  });

                if (stripos('philhealth', $search) !== false || stripos('phic', $search) !== false) {
                    $q->orWhereHas('philhealthClaim');
                }
            });
        }

        if ($admissionType && $admissionType !== 'ALL') {
            $invoicesQuery->whereHas('patientAccount', function ($pq) use ($admissionType): void {
                $pq->where('admission_type', $admissionType);
            });
        }

        $invoices = $invoicesQuery->get();

        $debtorGroups = [];
        $totalCurrent  = '0.0000';
        $total31To60   = '0.0000';
        $total61To90   = '0.0000';
        $total91To120  = '0.0000';
        $total120Plus  = '0.0000';
        $grandTotalAR  = '0.0000';

        foreach ($invoices as $inv) {
            $invDate = Carbon::parse($inv->invoice_date)->startOfDay();
            $days = (int) $invDate->diffInDays($cutoffDate);

            // 1. Patient Copay Portion
            $unpaidCopay = (string) $inv->patient_payable;
            if (bccomp($unpaidCopay, '0.0000', 4) > 0 && ($payorType === null || $payorType === 'ALL' || $payorType === 'PATIENT')) {
                $pId = 'PATIENT_' . $inv->patient_account_id;
                if (! isset($debtorGroups[$pId])) {
                    $debtorGroups[$pId] = [
                        'id'                 => $inv->patient_account_id,
                        'debtor_type'        => 'Patient Copay',
                        'debtor_code'        => $inv->patientAccount?->patient_id_number ?? 'N/A',
                        'debtor_name'        => $inv->patientAccount?->full_name ?? 'Unknown Patient',
                        'admission'          => $inv->patientAccount?->admission_type ?? 'Inpatient',
                        'statutory_category' => $inv->effective_discount_category ?? ($inv->patientAccount?->effective_discount_category ?? 'NONE'),
                        'hmo'                => $inv->patientAccount?->hmo_provider ?? 'Self-Pay',
                        'current'            => '0.0000',
                        'days_31_60'         => '0.0000',
                        'days_61_90'         => '0.0000',
                        'days_91_120'        => '0.0000',
                        'days_120_plus'      => '0.0000',
                        'total_due'          => '0.0000',
                        'invoices'           => [],
                    ];
                }

                $bucket = 'Current (<30d)';
                if ($days <= 30) {
                    $debtorGroups[$pId]['current'] = bcadd($debtorGroups[$pId]['current'], $unpaidCopay, 4);
                    $totalCurrent = bcadd($totalCurrent, $unpaidCopay, 4);
                } elseif ($days <= 60) {
                    $debtorGroups[$pId]['days_31_60'] = bcadd($debtorGroups[$pId]['days_31_60'], $unpaidCopay, 4);
                    $total31To60 = bcadd($total31To60, $unpaidCopay, 4);
                    $bucket = '31 - 60 Days';
                } elseif ($days <= 90) {
                    $debtorGroups[$pId]['days_61_90'] = bcadd($debtorGroups[$pId]['days_61_90'], $unpaidCopay, 4);
                    $total61To90 = bcadd($total61To90, $unpaidCopay, 4);
                    $bucket = '61 - 90 Days';
                } elseif ($days <= 120) {
                    $debtorGroups[$pId]['days_91_120'] = bcadd($debtorGroups[$pId]['days_91_120'], $unpaidCopay, 4);
                    $total91To120 = bcadd($total91To120, $unpaidCopay, 4);
                    $bucket = '91 - 120 Days';
                } else {
                    $debtorGroups[$pId]['days_120_plus'] = bcadd($debtorGroups[$pId]['days_120_plus'], $unpaidCopay, 4);
                    $total120Plus = bcadd($total120Plus, $unpaidCopay, 4);
                    $bucket = '120+ Days';
                }

                $debtorGroups[$pId]['total_due'] = bcadd($debtorGroups[$pId]['total_due'], $unpaidCopay, 4);
                $grandTotalAR = bcadd($grandTotalAR, $unpaidCopay, 4);

                $debtorGroups[$pId]['invoices'][] = [
                    'id'             => $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'invoice_date'   => $inv->invoice_date ? $inv->invoice_date->format('Y-m-d') : date('Y-m-d'),
                    'days_overdue'   => $days,
                    'aging_bucket'   => $bucket,
                    'amount_due'     => $unpaidCopay,
                    'claim_type'     => 'Patient Copay',
                    'status'         => $inv->status,
                ];
            }

            // 2. HMO Claim Portion
            if (($payorType === null || $payorType === 'ALL' || $payorType === 'HMO') && $inv->hmoClaims->isNotEmpty()) {
                foreach ($inv->hmoClaims as $hmoClaim) {
                    if (in_array($hmoClaim->status, ['SUBMITTED', 'PENDING', 'UNPAID', 'APPROVED'], true)) {
                        $hmoAmt = (string) bcsub((string) $hmoClaim->claimed_amount, (string) ($hmoClaim->settled_amount ?? '0.0000'), 4);
                        if (bccomp($hmoAmt, '0.0000', 4) > 0) {
                            $providerName = $hmoClaim->hmo_provider ?: 'Private HMO Provider';
                            $hKey = 'HMO_' . strtoupper(str_replace(' ', '_', $providerName));
                            if (! isset($debtorGroups[$hKey])) {
                                $debtorGroups[$hKey] = [
                                    'id'            => $hKey,
                                    'debtor_type'   => 'HMO Guarantee Claim',
                                    'debtor_code'   => 'HMO-' . strtoupper(substr(md5($providerName), 0, 6)),
                                    'debtor_name'   => $providerName,
                                    'admission'     => 'Corporate / Multi',
                                    'hmo'           => $providerName,
                                    'current'       => '0.0000',
                                    'days_31_60'    => '0.0000',
                                    'days_61_90'    => '0.0000',
                                    'days_91_120'   => '0.0000',
                                    'days_120_plus' => '0.0000',
                                    'total_due'     => '0.0000',
                                    'invoices'      => [],
                                ];
                            }

                            $bucket = 'Current (<30d)';
                            if ($days <= 30) {
                                $debtorGroups[$hKey]['current'] = bcadd($debtorGroups[$hKey]['current'], $hmoAmt, 4);
                                $totalCurrent = bcadd($totalCurrent, $hmoAmt, 4);
                            } elseif ($days <= 60) {
                                $debtorGroups[$hKey]['days_31_60'] = bcadd($debtorGroups[$hKey]['days_31_60'], $hmoAmt, 4);
                                $total31To60 = bcadd($total31To60, $hmoAmt, 4);
                                $bucket = '31 - 60 Days';
                            } elseif ($days <= 90) {
                                $debtorGroups[$hKey]['days_61_90'] = bcadd($debtorGroups[$hKey]['days_61_90'], $hmoAmt, 4);
                                $total61To90 = bcadd($total61To90, $hmoAmt, 4);
                                $bucket = '61 - 90 Days';
                            } elseif ($days <= 120) {
                                $debtorGroups[$hKey]['days_91_120'] = bcadd($debtorGroups[$hKey]['days_91_120'], $hmoAmt, 4);
                                $total91To120 = bcadd($total91To120, $hmoAmt, 4);
                                $bucket = '91 - 120 Days';
                            } else {
                                $debtorGroups[$hKey]['days_120_plus'] = bcadd($debtorGroups[$hKey]['days_120_plus'], $hmoAmt, 4);
                                $total120Plus = bcadd($total120Plus, $hmoAmt, 4);
                                $bucket = '120+ Days';
                            }

                            $debtorGroups[$hKey]['total_due'] = bcadd($debtorGroups[$hKey]['total_due'], $hmoAmt, 4);
                            $grandTotalAR = bcadd($grandTotalAR, $hmoAmt, 4);

                            $debtorGroups[$hKey]['invoices'][] = [
                                'id'             => $inv->id,
                                'invoice_number' => $inv->invoice_number,
                                'invoice_date'   => $inv->invoice_date ? $inv->invoice_date->format('Y-m-d') : date('Y-m-d'),
                                'days_overdue'   => $days,
                                'aging_bucket'   => $bucket,
                                'amount_due'     => $hmoAmt,
                                'claim_type'     => 'HMO Claim (' . $providerName . ')' . ($inv->patientAccount ? ' - ' . $inv->patientAccount->full_name : ''),
                                'status'         => $hmoClaim->status,
                            ];
                        }
                    }
                }
            }

            // 3. PhilHealth Claim Portion
            if (($payorType === null || $payorType === 'ALL' || $payorType === 'PHILHEALTH') && $inv->philhealthClaim) {
                $phic = $inv->philhealthClaim;
                if (in_array($phic->claim_status, ['TRANSMITTED', 'SUBMITTED', 'PENDING'], true)) {
                    $phicAmt = (string) $phic->total_case_rate_amount;
                    if (bccomp($phicAmt, '0.0000', 4) > 0) {
                        $phicKey = 'PHIC_CLAIMS';
                        if (! isset($debtorGroups[$phicKey])) {
                            $debtorGroups[$phicKey] = [
                                'id'            => 'PHIC_CLAIMS',
                                'debtor_type'   => 'PhilHealth Benefit Claims',
                                'debtor_code'   => 'PHIC-ACR',
                                'debtor_name'   => 'Philippine Health Insurance Corporation (PhilHealth)',
                                'admission'     => 'All Encounters',
                                'hmo'           => 'Government Insurer',
                                'current'       => '0.0000',
                                'days_31_60'    => '0.0000',
                                'days_61_90'    => '0.0000',
                                'days_91_120'   => '0.0000',
                                'days_120_plus' => '0.0000',
                                'total_due'     => '0.0000',
                                'invoices'      => [],
                            ];
                        }

                        $bucket = 'Current (<30d)';
                        if ($days <= 30) {
                            $debtorGroups[$phicKey]['current'] = bcadd($debtorGroups[$phicKey]['current'], $phicAmt, 4);
                            $totalCurrent = bcadd($totalCurrent, $phicAmt, 4);
                        } elseif ($days <= 60) {
                            $debtorGroups[$phicKey]['days_31_60'] = bcadd($debtorGroups[$phicKey]['days_31_60'], $phicAmt, 4);
                            $total31To60 = bcadd($total31To60, $phicAmt, 4);
                            $bucket = '31 - 60 Days';
                        } elseif ($days <= 90) {
                            $debtorGroups[$phicKey]['days_61_90'] = bcadd($debtorGroups[$phicKey]['days_61_90'], $phicAmt, 4);
                            $total61To90 = bcadd($total61To90, $phicAmt, 4);
                            $bucket = '61 - 90 Days';
                        } elseif ($days <= 120) {
                            $debtorGroups[$phicKey]['days_91_120'] = bcadd($debtorGroups[$phicKey]['days_91_120'], $phicAmt, 4);
                            $total91To120 = bcadd($total91To120, $phicAmt, 4);
                            $bucket = '91 - 120 Days';
                        } else {
                            $debtorGroups[$phicKey]['days_120_plus'] = bcadd($debtorGroups[$phicKey]['days_120_plus'], $phicAmt, 4);
                            $total120Plus = bcadd($total120Plus, $phicAmt, 4);
                            $bucket = '120+ Days';
                        }

                        $debtorGroups[$phicKey]['total_due'] = bcadd($debtorGroups[$phicKey]['total_due'], $phicAmt, 4);
                        $grandTotalAR = bcadd($grandTotalAR, $phicAmt, 4);

                        $debtorGroups[$phicKey]['invoices'][] = [
                            'id'             => $inv->id,
                            'invoice_number' => $inv->invoice_number,
                            'invoice_date'   => $inv->invoice_date ? $inv->invoice_date->format('Y-m-d') : date('Y-m-d'),
                            'days_overdue'   => $days,
                            'aging_bucket'   => $bucket,
                            'amount_due'     => $phicAmt,
                            'claim_type'     => 'PhilHealth ACR (' . ($phic->primary_case_rate_code ?? 'ACR') . ')' . ($inv->patientAccount ? ' - ' . $inv->patientAccount->full_name : ''),
                            'status'         => $phic->claim_status,
                        ];
                    }
                }
            }
        }

        return [
            'as_of_date'    => $cutoffDate->toDateString(),
            'debtors'       => array_values($debtorGroups),
            'total_current' => $totalCurrent,
            'total_31_60'   => $total31To60,
            'total_61_90'   => $total61To90,
            'total_91_120'  => $total91To120,
            'total_120_plus'=> $total120Plus,
            'grand_total'   => $grandTotalAR,
            'total_debtors' => count($debtorGroups),
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
