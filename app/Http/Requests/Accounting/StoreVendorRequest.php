<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'                => ['nullable', 'string', 'max:30', Rule::unique('vendors', 'code')],
            'vendor_code'         => ['nullable', 'string', 'max:30', Rule::unique('vendors', 'code')],
            'name'                => ['required', 'string', 'max:255'],
            'tin'                 => ['nullable', 'string', 'max:30'],
            'tax_type'            => ['nullable', 'string', 'in:VAT_REGISTERED,NON_VAT'],
            'default_ewt_rate'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'default_atc_code'    => ['nullable', 'string', 'max:20'],
            'contact_person'      => ['nullable', 'string', 'max:255'],
            'email'               => ['nullable', 'email', 'max:255'],
            'phone'               => ['nullable', 'string', 'max:50'],
            'registered_address'  => ['nullable', 'string', 'max:500'],
            'bank_name'           => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_account_name'   => ['nullable', 'string', 'max:255'],
            'payment_terms_days'  => ['nullable', 'integer', 'min:0', 'max:365'],
            'payment_terms'       => ['nullable', 'integer', 'min:0', 'max:365'],
            'is_active'           => ['nullable'],
            'status'              => ['nullable', 'string', 'in:Active,Inactive'],
        ];
    }
}
