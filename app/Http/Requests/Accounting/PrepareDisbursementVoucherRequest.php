<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

final class PrepareDisbursementVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purchase_bill_id' => ['required', 'exists:purchase_bills,id'],
            'bank_account_id'  => ['required', 'exists:bank_accounts,id'],
            'voucher_date'     => ['nullable', 'date'],
            'amount'           => ['required', 'numeric', 'gt:0'],
            'payment_method'   => ['required', 'string', 'in:CHECK,PESONET_EFT,INSTAPAY,PETTY_CASH,TELEGRAPHIC_TRANSFER'],
            'payee_name'       => ['nullable', 'string', 'max:255'],
            'check_or_eft_ref' => ['nullable', 'string', 'max:50'],
        ];
    }
}
