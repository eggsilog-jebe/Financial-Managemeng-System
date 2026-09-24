<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the TOTP challenge submission on the /two-factor-challenge page.
 * The user submits either a 6-digit TOTP code OR a recovery code — never both.
 */
final class TwoFactorChallengeRequest extends FormRequest
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
            'code'          => ['nullable', 'string', 'digits:6'],
            'email_otp'     => ['nullable', 'string', 'digits:6'],
            'recovery_code' => ['nullable', 'string', 'max:12'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.digits'          => 'The authentication code must be exactly 6 digits.',
            'email_otp.digits'     => 'The verification code must be exactly 6 digits.',
            'recovery_code.max'    => 'The recovery code format is invalid.',
        ];
    }

    /**
     * Normalize input strings before validation.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('code') && is_string($this->input('code'))) {
            $merge['code'] = preg_replace('/\s+/', '', $this->input('code'));
        }

        if ($this->has('email_otp') && is_string($this->input('email_otp'))) {
            $merge['email_otp'] = preg_replace('/\s+/', '', $this->input('email_otp'));
        }

        if ($this->has('recovery_code') && is_string($this->input('recovery_code'))) {
            $merge['recovery_code'] = strtoupper(trim($this->input('recovery_code')));
        }

        if (! empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * Add cross-field validation: at least one factor must be present.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $v): void {
            $code         = $this->input('code');
            $emailOtp     = $this->input('email_otp');
            $recoveryCode = $this->input('recovery_code');

            if (empty($code) && empty($emailOtp) && empty($recoveryCode)) {
                $v->errors()->add('code', 'Please enter your 6-digit authentication code or a recovery code.');
            }
        });
    }
}
