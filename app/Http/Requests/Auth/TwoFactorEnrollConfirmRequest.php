<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the OTP code submitted during 2FA enrollment (email OTP setup).
 * The user receives a 6-digit code via email and enters it to confirm enrollment.
 */
final class TwoFactorEnrollConfirmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'digits:6'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Please enter the 6-digit verification code sent to your email.',
            'code.digits'   => 'The verification code must be exactly 6 digits.',
        ];
    }
}
