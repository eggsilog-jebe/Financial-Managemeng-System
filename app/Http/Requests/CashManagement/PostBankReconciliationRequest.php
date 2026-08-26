<?php

declare(strict_types=1);

namespace App\Http\Requests\CashManagement;

use Illuminate\Foundation\Http\FormRequest;

final class PostBankReconciliationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_account_id'       => ['required', 'integer', 'exists:bank_accounts,id'],
            'statement_date'        => ['required', 'date'],
            'cutoff_date'           => ['required', 'date'],
            'statement_balance'     => ['required', 'numeric'],
            'book_balance'          => ['required', 'numeric'],
            'cleared_check_ids'     => ['nullable', 'array'],
            'cleared_check_ids.*'   => ['integer'],
            'cleared_deposit_ids'   => ['nullable', 'array'],
            'cleared_deposit_ids.*' => ['integer'],
            'notes'                 => ['nullable', 'string', 'max:1000'],
        ];
    }
}
