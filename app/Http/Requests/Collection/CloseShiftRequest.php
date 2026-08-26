<?php

declare(strict_types=1);

namespace App\Http\Requests\Collection;

use Illuminate\Foundation\Http\FormRequest;

final class CloseShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shift_id'            => ['nullable', 'exists:cashier_shifts,id'],
            'actual_cash_counted' => ['required', 'numeric', 'min:0'],
            'variance_reason'     => ['nullable', 'string', 'max:500'],
        ];
    }
}
