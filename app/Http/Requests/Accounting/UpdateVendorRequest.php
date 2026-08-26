<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $vendorId = $this->route('vendor') ?? $this->route('id');
        if (is_object($vendorId)) {
            $vendorId = $vendorId->id;
        }

        return [
            'code'               => ['nullable', 'string', 'max:30', Rule::unique('vendors', 'code')->ignore($vendorId)],
            'vendor_code'        => ['nullable', 'string', 'max:30', Rule::unique('vendors', 'code')->ignore($vendorId)],
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
