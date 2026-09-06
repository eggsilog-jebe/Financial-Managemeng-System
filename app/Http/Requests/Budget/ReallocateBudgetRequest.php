<?php

declare(strict_types=1);

namespace App\Http\Requests\Budget;

use Illuminate\Foundation\Http\FormRequest;

final class ReallocateBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_budget_allocation_id'      => ['required', 'integer', 'exists:budget_allocations,id', 'different:destination_budget_allocation_id'],
            'destination_budget_allocation_id' => ['required', 'integer', 'exists:budget_allocations,id', 'different:source_budget_allocation_id'],
            'amount'                           => ['required', 'numeric', 'min:0.01'],
            'transfer_date'                    => ['required', 'date'],
            'reason'                           => ['required', 'string', 'max:1000'],
        ];
    }
}
