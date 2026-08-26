<?php

declare(strict_types=1);

namespace App\Http\Requests\CashManagement;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->input('id');

        return [
            'name'            => ['required', 'string', 'max:255'],
            'bank_name'       => ['required', 'string', 'max:255'],
            'account_number'  => ['required', 'string', 'max:100', 'unique:bank_accounts,account_number,' . $id],
            'gl_code'         => ['nullable', 'string', 'max:50'],
            'gl_account_id'   => ['nullable', 'integer', 'exists:accounts,id'],
            'purpose'         => ['required', 'string', 'max:255'],
            'currency'        => ['nullable', 'string', 'max:10'],
            'minimum_balance' => ['nullable', 'numeric', 'min:0'],
            'status'          => ['nullable', 'in:Active,Inactive,Frozen'],
            'is_active'       => ['nullable', 'boolean'],
        ];
    }
}
