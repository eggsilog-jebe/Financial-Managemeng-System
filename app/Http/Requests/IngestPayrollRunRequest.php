<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class IngestPayrollRunRequest extends FormRequest
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
            'employees'                     => ['required', 'array', 'min:1'],
            'employees.*.employee_id_number'=> ['required', 'string', 'max:50'],
            'employees.*.employee_name'     => ['required', 'string', 'max:255'],
            'employees.*.department'        => ['required', 'string', 'max:50'],
            'employees.*.basic_salary'      => ['required', 'numeric', 'min:0'],
            'employees.*.overtime_pay'      => ['nullable', 'numeric', 'min:0'],
            'employees.*.allowances'        => ['nullable', 'numeric', 'min:0'],
            'employees.*.tin'               => ['nullable', 'string', 'max:30'],
            'employees.*.sss_number'        => ['nullable', 'string', 'max:30'],
            'employees.*.philhealth_number' => ['nullable', 'string', 'max:30'],
            'employees.*.pagibig_number'    => ['nullable', 'string', 'max:30'],
            'employees.*.bank_account_number'=> ['nullable', 'string', 'max:50'],
        ];
    }
}
