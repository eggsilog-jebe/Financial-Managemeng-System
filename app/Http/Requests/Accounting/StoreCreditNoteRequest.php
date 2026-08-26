<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

final class StoreCreditNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_id' => ['required', 'exists:invoices,id'],
            'amount'     => ['required', 'numeric', 'gt:0'],
            'reason'     => ['required', 'string', 'max:255'],
            'issue_date' => ['nullable', 'date'],
        ];
    }
}
