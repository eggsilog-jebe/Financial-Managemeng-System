<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PayrollItem extends Model
{
    protected $fillable = [
        'payroll_run_id',
        'employee_id_number',
        'employee_name',
        'department',
        'tin',
        'sss_number',
        'philhealth_number',
        'pagibig_number',
        'bank_account_number',
        'basic_salary',
        'overtime_pay',
        'allowances',
        'gross_pay',
        'sss_employee_share',
        'sss_employer_share',
        'philhealth_employee_share',
        'philhealth_employer_share',
        'pagibig_employee_share',
        'pagibig_employer_share',
        'withholding_tax',
        'net_pay',
    ];

    protected $casts = [
        'basic_salary'              => 'decimal:4',
        'overtime_pay'              => 'decimal:4',
        'allowances'                => 'decimal:4',
        'gross_pay'                 => 'decimal:4',
        'sss_employee_share'        => 'decimal:4',
        'sss_employer_share'        => 'decimal:4',
        'philhealth_employee_share' => 'decimal:4',
        'philhealth_employer_share' => 'decimal:4',
        'pagibig_employee_share'    => 'decimal:4',
        'pagibig_employer_share'    => 'decimal:4',
        'withholding_tax'           => 'decimal:4',
        'net_pay'                   => 'decimal:4',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }
}
