<?php

declare(strict_types=1);

namespace App\Http\Requests\Collection;

use Illuminate\Foundation\Http\FormRequest;

final class CollectPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_id'              => ['required', 'exists:invoices,id'],
            'payment_method'          => ['required', 'string'],
            'amount'                  => ['required', 'numeric', 'gt:0'],
            'tendered_amount'         => ['nullable', 'numeric', 'min:0'],
            'gateway_provider'        => ['nullable', 'string', 'max:50'],
            'gateway_transaction_id'  => ['nullable', 'string', 'max:100'],
            'payor_name'              => ['nullable', 'string', 'max:255'],
            'payor_tin'               => ['nullable', 'string', 'max:30'],
            'notes'                   => ['nullable', 'string'],
            'cashier_shift_id'        => ['nullable', 'exists:cashier_shifts,id'],
        ];
    }
}
