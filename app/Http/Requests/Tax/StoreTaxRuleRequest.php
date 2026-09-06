<?php

declare(strict_types=1);

namespace App\Http\Requests\Tax;

use Illuminate\Foundation\Http\FormRequest;

final class StoreTaxRuleRequest extends FormRequest
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
            'tax_code' => ['required', 'string', 'max:40', 'unique:tax_rules,tax_code'],
            'name'     => ['required', 'string', 'max:255'],
            'atc_code' => ['required', 'string', 'max:30'],
            'category' => ['required', 'string', 'max:50'],
            'cat_type' => ['required', 'string', 'max:30'],
            'rate'     => ['required', 'numeric', 'min:0', 'max:100'],
            'scope'    => ['nullable', 'string'],
        ];
    }
}
