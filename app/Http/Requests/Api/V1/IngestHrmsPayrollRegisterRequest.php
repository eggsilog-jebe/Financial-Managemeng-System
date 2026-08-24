<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

final class IngestHrmsPayrollRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cutoff_start'                  => ['required', 'date'],
            'cutoff_end'                    => ['required', 'date', 'after_or_equal:cutoff_start'],
            'payout_date'                   => ['required', 'date', 'after_or_equal:cutoff_end'],
            'disbursement_bank_account_id'  => ['required', 'integer', 'exists:bank_accounts,id'],
            'total_gross_pay'               => ['required', 'numeric', 'min:0.01'],
            'total_net_pay'                 => ['required', 'numeric', 'min:0.01'],
            'total_sss_employee'            => ['required', 'numeric', 'min:0'],
            'total_sss_employer'            => ['required', 'numeric', 'min:0'],
            'total_philhealth_employee'     => ['required', 'numeric', 'min:0'],
            'total_philhealth_employer'     => ['required', 'numeric', 'min:0'],
            'total_pagibig_employee'        => ['required', 'numeric', 'min:0'],
            'total_pagibig_employer'        => ['required', 'numeric', 'min:0'],
            'total_withholding_tax_1601c'   => ['required', 'numeric', 'min:0'],
            'employees'                     => ['nullable', 'array'],
            'employees.*.employee_id_number'=> ['required_with:employees', 'string', 'max:50'],
            'employees.*.employee_name'     => ['required_with:employees', 'string', 'max:255'],
            'employees.*.department'        => ['required_with:employees', 'string', 'max:50'],
            'employees.*.basic_salary'      => ['required_with:employees', 'numeric', 'min:0'],
        ];
    }
}
