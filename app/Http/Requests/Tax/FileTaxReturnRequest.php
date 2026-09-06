<?php

declare(strict_types=1);

namespace App\Http\Requests\Tax;

use Illuminate\Foundation\Http\FormRequest;

final class FileTaxReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'form_type'      => ['required', 'string', 'max:30'],
            'period_covered' => ['required', 'string', 'max:50'],
            'tax_due'        => ['required', 'numeric', 'min:0'],
            'filing_date'    => ['required', 'date'],
        ];
    }
}
