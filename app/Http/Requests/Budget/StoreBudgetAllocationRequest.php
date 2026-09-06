<?php

declare(strict_types=1);

namespace App\Http\Requests\Budget;

use Illuminate\Foundation\Http\FormRequest;

final class StoreBudgetAllocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department'       => ['required', 'string', 'max:150'],
            'department_code'  => ['nullable', 'string', 'max:30'],
            'category'         => ['nullable', 'string', 'max:100'],
            'department_head'  => ['nullable', 'string', 'max:100'],
            'fiscal_year'      => ['required', 'string', 'max:10'],
            'allocated_amount' => ['required', 'numeric', 'min:0.01'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }
}
