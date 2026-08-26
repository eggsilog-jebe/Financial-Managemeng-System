<?php

declare(strict_types=1);

namespace App\Http\Requests\CashManagement;

use Illuminate\Foundation\Http\FormRequest;

final class ExecuteFundTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_bank_account_id'      => ['required', 'integer', 'exists:bank_accounts,id', 'different:destination_bank_account_id'],
            'destination_bank_account_id' => ['required', 'integer', 'exists:bank_accounts,id', 'different:source_bank_account_id'],
            'amount'                      => ['required', 'numeric', 'min:0.01'],
            'transfer_date'               => ['required', 'date'],
            'transfer_method'             => ['nullable', 'string', 'max:100'],
            'memo'                        => ['nullable', 'string', 'max:500'],
        ];
    }
}
