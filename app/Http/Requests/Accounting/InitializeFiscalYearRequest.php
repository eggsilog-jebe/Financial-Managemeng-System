<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

final class InitializeFiscalYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return true;
        }

        $role = $user->role ?? 'StaffAccountant';
        return in_array($role, ['FinanceManager', 'CFO', 'FinanceDirector'], true);
    }

    public function rules(): array
    {
        return [
            'fiscal_year' => ['required', 'string', 'regex:/^\d{4}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'fiscal_year.required' => 'Fiscal Year is required.',
            'fiscal_year.regex'    => 'Fiscal Year must be a 4-digit year (e.g. 2026).',
        ];
    }
}
