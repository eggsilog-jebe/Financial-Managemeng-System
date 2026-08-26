<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

final class StoreDisbursementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_account_id'      => ['required', 'exists:bank_accounts,id'],
            'voucher_date'         => ['required', 'date'],
            'payee_name'           => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string', 'max:255'],
            'purpose'              => ['nullable', 'string', 'max:255'],
            'gross_amount'         => ['required', 'numeric', 'gt:0'],
            'withheld_tax_amount'  => ['nullable', 'numeric', 'min:0'],
            'net_disbursed_amount' => ['nullable', 'numeric', 'gt:0'],
            'payment_method'       => ['required', 'string', 'in:CHECK,PESONET_EFT,INSTAPAY,PETTY_CASH,TELEGRAPHIC_TRANSFER'],
            'purchase_bill_id'     => ['nullable', 'exists:purchase_bills,id'],
            'payroll_run_id'       => ['nullable', 'exists:payroll_runs,id'],
        ];
    }
}
