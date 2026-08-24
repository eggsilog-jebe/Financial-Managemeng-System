<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_account_id'      => ['required', 'integer', 'exists:patient_accounts,id'],
            'invoice_id'              => ['nullable', 'integer', 'exists:invoices,id'],
            'cashier_shift_id'        => ['nullable', 'integer', 'exists:cashier_shifts,id'],
            'amount'                  => ['required', 'numeric', 'min:0.01'],
            'payment_method'          => ['required', 'string', 'in:CASH,CREDIT_CARD,DEBIT_CARD,QR_PH,GCASH,MAYA,CHECK,ONLINE_BANK'],
            'transaction_channel_ref' => ['nullable', 'string', 'max:100'],
            'payor_name'              => ['nullable', 'string', 'max:255'],
            'payor_tin'               => ['nullable', 'string', 'max:30'],
            'payment_date'            => ['nullable', 'date'],
            'payment_type'            => ['nullable', 'string', 'in:PATIENT_COPAY,ADMISSION_DEPOSIT,HMO_SETTLEMENT,PHILHEALTH_SETTLEMENT'],
        ];
    }
}
