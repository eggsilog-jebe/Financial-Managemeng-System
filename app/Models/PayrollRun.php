<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class PayrollRun extends Model
{
    protected $fillable = [
        'payroll_run_number',
        'cutoff_start',
        'cutoff_end',
        'payout_date',
        'employee_count',
        'total_gross_pay',
        'total_sss_employee',
        'total_sss_employer',
        'total_philhealth_employee',
        'total_philhealth_employer',
        'total_pagibig_employee',
        'total_pagibig_employer',
        'total_withholding_tax_1601c',
        'total_statutory_deductions',
        'total_net_pay',
        'status',
    ];

    protected $casts = [
        'cutoff_start'                => 'date',
        'cutoff_end'                  => 'date',
        'payout_date'                 => 'date',
        'total_gross_pay'             => 'decimal:4',
        'total_sss_employee'          => 'decimal:4',
        'total_sss_employer'          => 'decimal:4',
        'total_philhealth_employee'   => 'decimal:4',
        'total_philhealth_employer'   => 'decimal:4',
        'total_pagibig_employee'      => 'decimal:4',
        'total_pagibig_employer'      => 'decimal:4',
        'total_withholding_tax_1601c' => 'decimal:4',
        'total_statutory_deductions'  => 'decimal:4',
        'total_net_pay'               => 'decimal:4',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function disbursementVoucher(): HasOne
    {
        return $this->hasOne(DisbursementVoucher::class);
    }
}
