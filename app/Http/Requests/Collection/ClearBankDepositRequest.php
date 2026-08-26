<?php

declare(strict_types=1);

namespace App\Http\Requests\Collection;

use Illuminate\Foundation\Http\FormRequest;

final class ClearBankDepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_reference_number' => ['required', 'string', 'max:100'],
            'validated_by_teller'   => ['nullable', 'string', 'max:100'],
        ];
    }
}
