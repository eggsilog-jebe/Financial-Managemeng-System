<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class IngestVendorBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_id'             => ['required', 'integer', 'exists:vendors,id'],
            'doctor_id'             => ['nullable', 'integer', 'exists:doctor_profiles,id'],
            'bill_date'             => ['required', 'date'],
            'due_date'              => ['required', 'date', 'after_or_equal:bill_date'],
            'po_number'             => ['required', 'string', 'max:50'],
            'grn_number'            => ['required', 'string', 'max:50'],
            'vendor_invoice_number' => ['required', 'string', 'max:50'],
            'po_amount'             => ['required', 'numeric', 'min:0'],
            'grn_amount'            => ['required', 'numeric', 'min:0'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.item_code'     => ['required', 'string', 'max:50'],
            'items.*.description'   => ['required', 'string', 'max:255'],
            'items.*.expense_type'  => ['required', 'string', 'in:GOODS_INVENTORY,SERVICES_MAINTENANCE,DOCTOR_PROFESSIONAL_FEE,CAPEX_EQUIPMENT,UTILITIES'],
            'items.*.quantity'      => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'    => ['required', 'numeric', 'min:0'],
            'items.*.atc_code'      => ['nullable', 'string', 'max:20'],
        ];
    }
}
