<?php

declare(strict_types=1);

namespace App\Http\Requests\Budget;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEncumbranceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'budget_allocation_id' => ['required', 'integer', 'exists:budget_allocations,id'],
            'reference_type'       => ['required', 'string', 'max:50'],
            'reference_number'     => ['required', 'string', 'max:50'],
            'amount'               => ['required', 'numeric', 'min:0.01'],
            'notes'                => ['nullable', 'string', 'max:500'],
        ];
    }
}
