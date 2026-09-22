<?php

declare(strict_types=1);

namespace App\Http\Requests\UserSecurity;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'CFO';
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'role'  => ['required', 'string', 'in:CFO,FinanceManager,StaffAccountant,BillingClerk,Cashier,Auditor'],
        ];
    }
}
