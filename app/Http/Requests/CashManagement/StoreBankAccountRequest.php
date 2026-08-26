<?php

declare(strict_types=1);

namespace App\Http\Requests\CashManagement;

use Illuminate\Foundation\Http\FormRequest;

final class StoreBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'bank_name'       => ['required', 'string', 'max:255'],
            'account_number'  => ['required', 'string', 'max:100', 'unique:bank_accounts,account_number'],
            'gl_code'         => ['nullable', 'string', 'max:50'],
            'gl_account_id'   => ['nullable', 'integer', 'exists:accounts,id'],
            'purpose'         => ['required', 'string', 'max:255'],
            'currency'        => ['nullable', 'string', 'max:10'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'minimum_balance' => ['nullable', 'numeric', 'min:0'],
            'status'          => ['nullable', 'in:Active,Inactive,Frozen'],
            'is_active'       => ['nullable', 'boolean'],
        ];
    }
}
