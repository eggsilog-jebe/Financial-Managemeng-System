<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\Invoice;
use App\Models\PatientAccount;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class AccountsReceivableService
{
    /**
     * Compute Accounts Receivable Aging matrix across Patient, PhilHealth, and HMO receivables.
     * Buckets: Current (0-30 days), 31-60 days, 61-90 days, >90 days.
     */
    public function getAgingAnalysis(): array
    {
        $openInvoices = Invoice::with(['patientAccount', 'philhealthClaim', 'hmoClaims'])
            ->whereIn('status', ['UNPAID', 'PARTIAL'])
            ->get();

        $today = Carbon::today();

        $aging = [
            'current' => '0.0000', // 0-30 days
            'bucket_31_60' => '0.0000',
            'bucket_61_90' => '0.0000',
            'over_90' => '0.0000',
            'total_ar' => '0.0000',
            'patient_total' => '0.0000',
            'philhealth_total' => '0.0000',
            'hmo_total' => '0.0000',
            'accounts' => [],
        ];

        foreach ($openInvoices as $inv) {
            $daysOld = $today->diffInDays(Carbon::parse($inv->invoice_date));
            $balance = (string) $inv->patient_payable;

            $aging['total_ar'] = bcadd($aging['total_ar'], $balance, 4);
            $aging['patient_total'] = bcadd($aging['patient_total'], $balance, 4);

            if ($inv->philhealthClaim && $inv->philhealthClaim->claim_status !== 'PAID') {
                $phClaim = (string) $inv->philhealthClaim->total_case_rate_amount;
                $aging['philhealth_total'] = bcadd($aging['philhealth_total'], $phClaim, 4);
                $aging['total_ar'] = bcadd($aging['total_ar'], $phClaim, 4);
            }

            foreach ($inv->hmoClaims as $hmo) {
                if ($hmo->status !== 'SETTLED') {
                    $hmoBal = bcsub((string) $hmo->claimed_amount, (string) $hmo->settled_amount, 4);
                    $aging['hmo_total'] = bcadd($aging['hmo_total'], $hmoBal, 4);
                    $aging['total_ar'] = bcadd($aging['total_ar'], $hmoBal, 4);
                }
            }

            if ($daysOld <= 30) {
                $aging['current'] = bcadd($aging['current'], $balance, 4);
            } elseif ($daysOld <= 60) {
                $aging['bucket_31_60'] = bcadd($aging['bucket_31_60'], $balance, 4);
            } elseif ($daysOld <= 90) {
                $aging['bucket_61_90'] = bcadd($aging['bucket_61_90'], $balance, 4);
            } else {
                $aging['over_90'] = bcadd($aging['over_90'], $balance, 4);
            }
        }

        return $aging;
    }
}
