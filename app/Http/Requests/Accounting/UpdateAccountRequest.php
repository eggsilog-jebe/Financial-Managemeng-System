<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return true;
        }

        $role = $user->role ?? 'StaffAccountant';
        return in_array($role, ['StaffAccountant', 'FinanceManager', 'CFO', 'FinanceDirector'], true);
    }

    public function rules(): array
    {
        $accountId = $this->route('id') ?? $this->route('account');

        return [
            'code'           => ['required', 'string', 'max:30', Rule::unique('accounts', 'code')->ignore($accountId)],
            'name'           => ['required', 'string', 'max:255'],
            'category'       => ['required', 'string', Rule::in(['ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE'])],
            'normal_balance' => ['required', 'string', Rule::in(['DEBIT', 'CREDIT'])],
            'department'     => ['nullable', 'string', 'max:100'],
            'is_active'      => ['nullable', 'boolean'],
        ];
    }
}
