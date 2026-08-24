<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

final class IngestBdmsPatientBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id'             => ['required', 'integer', 'exists:patient_accounts,id'],
            'bdms_bill_number'       => ['nullable', 'string', 'max:50'],
            'invoice_date'           => ['required', 'date'],
            'gross_amount'           => ['required', 'numeric', 'min:0.01'],
            'philhealth_amount'      => ['nullable', 'numeric', 'min:0'],
            'hmo_amount'             => ['nullable', 'numeric', 'min:0'],
            'hmo_provider'           => ['nullable', 'string', 'max:100'],
            'discount_amount'        => ['nullable', 'numeric', 'min:0'],
            'discount_type'          => ['nullable', 'string', 'in:SENIOR_CITIZEN,PWD,CHARITY,EMPLOYEE'],
            'id_card_number'         => ['nullable', 'string', 'max:50'],
            'net_copay'              => ['required', 'numeric', 'min:0'],
            'charge_lines'           => ['required', 'array', 'min:1'],
            'charge_lines.*.item_code'         => ['required', 'string', 'max:50'],
            'charge_lines.*.description'       => ['required', 'string', 'max:255'],
            'charge_lines.*.department'        => ['required', 'string', 'max:50'],
            'charge_lines.*.revenue_category'  => ['required', 'string', 'max:50'],
            'charge_lines.*.quantity'          => ['required', 'numeric', 'min:0.01'],
            'charge_lines.*.unit_price'        => ['required', 'numeric', 'min:0'],
            'charge_lines.*.is_vatable'        => ['nullable', 'boolean'],
            'charge_lines.*.is_senior_eligible'=> ['nullable', 'boolean'],
        ];
    }
}
