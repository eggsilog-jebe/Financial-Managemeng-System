<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

final class ReleaseDisbursementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_account_id' => ['nullable', 'integer', 'exists:bank_accounts,id'],
            'check_number'    => ['nullable', 'string', 'max:50'],
            'check_date'      => ['nullable', 'date'],
            'notes'           => ['nullable', 'string', 'max:500'],
            'eft_reference'   => ['nullable', 'string', 'max:50'],
        ];
    }
}
