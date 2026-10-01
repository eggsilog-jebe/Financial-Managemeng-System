<?php

declare(strict_types=1);

namespace App\Http\Requests\UserSecurity;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['CFO', 'FinanceDirector'], true)
            || ($this->user() && $this->user()->can('access-user-management'));
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
            'name'  => trim((string) $this->input('name')),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'     => ['required', 'string', 'in:CFO,FinanceManager,StaffAccountant,BillingClerk,Cashier,Auditor'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required'     => 'Please enter the personnel user\'s full name.',
            'email.required'    => 'A hospital email address is required.',
            'email.email'       => 'Please provide a valid email format (e.g. user@hospital.gov.ph).',
            'email.unique'      => 'This email address is already registered to an existing hospital account.',
            'role.required'     => 'Please select an assigned system role.',
            'role.in'           => 'The selected authorization role is invalid.',
            'password.required' => 'A temporary password is required for account provisioning.',
            'password.confirmed'=> 'The password confirmation does not match the temporary password.',
            'password.min'      => 'The temporary password must contain at least 8 characters.',
        ];
    }
}
