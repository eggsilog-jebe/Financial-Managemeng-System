<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

final class IssueCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'disbursement_voucher_id' => ['required', 'exists:disbursement_vouchers,id'],
            'bank_account_id'         => ['required', 'exists:bank_accounts,id'],
            'check_number'            => ['required', 'string', 'max:50'],
            'check_date'              => ['required', 'date'],
            'payee_name'              => ['required', 'string', 'max:255'],
            'amount'                  => ['required', 'numeric', 'gt:0'],
        ];
    }
}
