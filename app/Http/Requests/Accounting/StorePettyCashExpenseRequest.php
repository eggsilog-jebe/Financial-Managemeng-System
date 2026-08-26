<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

final class StorePettyCashExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'petty_cash_fund_id' => ['required', 'exists:petty_cash_funds,id'],
            'expense_date'       => ['required', 'date'],
            'payee'              => ['required', 'string', 'max:255'],
            'department'         => ['required', 'string', 'max:50'],
            'particulars'        => ['required', 'string', 'max:255'],
            'amount'             => ['required', 'numeric', 'gt:0'],
            'receipt_ref'        => ['nullable', 'string', 'max:100'],
        ];
    }
}
