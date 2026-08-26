<?php

declare(strict_types=1);

namespace App\Http\Requests\Collection;

use Illuminate\Foundation\Http\FormRequest;

final class OpenShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'terminal_name'      => ['required', 'string', 'max:50'],
            'opening_cash_float' => ['required', 'numeric', 'min:0'],
        ];
    }
}
