<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return true; // fallback for local demo mode
        }

        $role = $user->role ?? 'StaffAccountant';
        return in_array($role, ['StaffAccountant', 'FinanceManager', 'CFO', 'FinanceDirector'], true);
    }

    public function rules(): array
    {
        return [
            'code'           => ['required', 'string', 'max:30', 'unique:accounts,code'],
            'name'           => ['required', 'string', 'max:255'],
            'category'       => ['required', 'string', Rule::in(['ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE'])],
            'normal_balance' => ['required', 'string', Rule::in(['DEBIT', 'CREDIT'])],
            'department'     => ['nullable', 'string', 'max:100'],
            'is_active'      => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required'           => 'Account code is required.',
            'code.unique'             => 'This Account Code already exists in the Chart of Accounts.',
            'name.required'           => 'Account title / description is required.',
            'category.required'       => 'Please select a valid account classification category.',
            'normal_balance.required' => 'Normal balance direction (DEBIT/CREDIT) is mandatory.',
        ];
    }
}
