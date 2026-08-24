<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class IngestClinicalBillablesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_account_id'                    => ['required', 'integer', 'exists:patient_accounts,id'],
            'invoice_date'                          => ['required', 'date'],
            'discount_type'                         => ['nullable', 'string', 'in:SENIOR_CITIZEN,PWD,EMPLOYEE,CHARITY'],
            'id_card_number'                        => ['nullable', 'string', 'max:50'],
            'philhealth_member_pin'                 => ['nullable', 'string', 'max:30'],
            'philhealth_primary_icd'                => ['nullable', 'string', 'max:20'],
            'philhealth_primary_case_code'          => ['nullable', 'string', 'max:30'],
            'philhealth_primary_case_rate_amount'   => ['nullable', 'numeric', 'min:0'],
            'philhealth_secondary_case_code'        => ['nullable', 'string', 'max:30'],
            'philhealth_secondary_case_rate_amount' => ['nullable', 'numeric', 'min:0'],
            'hmo_provider'                          => ['nullable', 'string', 'max:100'],
            'hmo_loa_number'                        => ['nullable', 'string', 'max:50'],
            'hmo_card_number'                       => ['nullable', 'string', 'max:50'],
            'hmo_approved_limit'                    => ['nullable', 'numeric', 'min:0'],
            'items'                                 => ['required', 'array', 'min:1'],
            'items.*.item_code'                     => ['required', 'string', 'max:50'],
            'items.*.description'                   => ['required', 'string', 'max:255'],
            'items.*.department'                    => ['required', 'string', 'max:50'],
            'items.*.revenue_category'              => ['required', 'string', 'max:50'],
            'items.*.quantity'                      => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'                    => ['required', 'numeric', 'min:0'],
            'items.*.is_vatable'                    => ['nullable', 'boolean'],
            'items.*.is_senior_pwd_eligible'        => ['nullable', 'boolean'],
        ];
    }
}
