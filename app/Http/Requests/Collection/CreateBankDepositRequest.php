<?php

declare(strict_types=1);

namespace App\Http\Requests\Collection;

use Illuminate\Foundation\Http\FormRequest;

final class CreateBankDepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_account_id'  => ['required', 'exists:bank_accounts,id'],
            'cashier_shift_id' => ['nullable', 'exists:cashier_shifts,id'],
            'deposit_date'     => ['required', 'date'],
            'cash_amount'      => ['required', 'numeric', 'min:0'],
            'check_amount'     => ['nullable', 'numeric', 'min:0'],
            'bank_reference_number' => ['nullable', 'string', 'max:100'],
            'validated_by_teller'   => ['nullable', 'string', 'max:100'],
        ];
    }
}
