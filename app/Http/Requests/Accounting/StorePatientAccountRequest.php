<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePatientAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_mrn'       => ['nullable', 'string', 'max:30', Rule::unique('patient_accounts', 'patient_id_number')],
            'patient_id_number' => ['nullable', 'string', 'max:30', Rule::unique('patient_accounts', 'patient_id_number')],
            'full_name'         => ['required', 'string', 'max:255'],
            'admission_type'    => ['required', 'string', 'in:Inpatient,Outpatient,Emergency'],
            'discount_category' => ['nullable', 'string', 'in:NONE,SENIOR_CITIZEN,PWD,EMPLOYEE_SUBSIDY,EMPLOYEE,CHARITY'],
            'id_card_number'    => ['nullable', 'string', 'max:50'],
            'hmo_provider'      => ['nullable', 'string', 'max:100'],
            'phone'             => ['nullable', 'string', 'max:50'],
            'email'             => ['nullable', 'email', 'max:100'],
            'address'           => ['nullable', 'string', 'max:255'],
            'status'            => ['nullable', 'string', 'in:Active,Inactive,Discharged'],
        ];
    }
}
