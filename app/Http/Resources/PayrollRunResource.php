<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PayrollRunResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                          => $this->id,
            'payroll_run_number'          => $this->payroll_run_number,
            'cutoff_start'                => $this->cutoff_start->toDateString(),
            'cutoff_end'                  => $this->cutoff_end->toDateString(),
            'payout_date'                 => $this->payout_date->toDateString(),
            'employee_count'              => $this->employee_count,
            'total_gross_pay'             => (float) $this->total_gross_pay,
            'total_sss_employee'          => (float) $this->total_sss_employee,
            'total_sss_employer'          => (float) $this->total_sss_employer,
            'total_philhealth_employee'   => (float) $this->total_philhealth_employee,
            'total_philhealth_employer'   => (float) $this->total_philhealth_employer,
            'total_pagibig_employee'      => (float) $this->total_pagibig_employee,
            'total_pagibig_employer'      => (float) $this->total_pagibig_employer,
            'total_withholding_tax_1601c' => (float) $this->total_withholding_tax_1601c,
            'total_net_pay'               => (float) $this->total_net_pay,
            'status'                      => $this->status,
            'items'                       => $this->whenLoaded('items'),
            'disbursement_voucher'        => $this->whenLoaded('disbursementVoucher'),
        ];
    }
}
