<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

final class ReverseJournalEntryRequest extends FormRequest
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
            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'A justification reason for the journal entry reversal is required for CAS audit compliance.',
        ];
    }
}
