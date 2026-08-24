<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

final class IngestPsmVendorBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_id'             => ['required', 'integer', 'exists:vendors,id'],
            'po_number'             => ['required', 'string', 'max:50'],
            'grn_reference'         => ['required', 'string', 'max:50'],
            'vendor_invoice_number' => ['required', 'string', 'max:50'],
            'bill_date'             => ['required', 'date'],
            'due_date'              => ['required', 'date', 'after_or_equal:bill_date'],
            'invoice_amount'        => ['required', 'numeric', 'min:0.01'],
            'ewt_rate'              => ['nullable', 'numeric', 'min:0', 'max:0.20'], // 0.01 for goods, 0.02 for services, etc.
            'atc_code'              => ['nullable', 'string', 'max:20'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.item_code'     => ['required', 'string', 'max:50'],
            'items.*.description'   => ['required', 'string', 'max:255'],
            'items.*.expense_type'  => ['required', 'string', 'in:GOODS_INVENTORY,SERVICES_MAINTENANCE,DOCTOR_PROFESSIONAL_FEE,CAPEX_EQUIPMENT,UTILITIES'],
            'items.*.quantity'      => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'    => ['required', 'numeric', 'min:0'],
        ];
    }
}
