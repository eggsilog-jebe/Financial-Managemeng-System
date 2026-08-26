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
            'code'               => ['nullable', 'string', 'max:30', Rule::unique('vendors', 'code')],
            'vendor_code'        => ['nullable', 'string', 'max:30', Rule::unique('vendors', 'code')],
            'name'               => ['required', 'string', 'max:255'],
            'tin'                => ['nullable', 'string', 'max:30'],
            'contact_person'     => ['nullable', 'string', 'max:255'],
            'email'              => ['nullable', 'email', 'max:255'],
            'phone'              => ['nullable', 'string', 'max:50'],
            'payment_terms_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'payment_terms'      => ['nullable', 'integer', 'min:0', 'max:365'],
            'is_active'          => ['nullable'],
            'status'             => ['nullable', 'string', 'in:Active,Inactive'],
        ];
    }
}
