<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Immutable DTO for the 2FA challenge submission.
 *
 * Three mutually exclusive modes:
 *   - TOTP    : $code is set, $recoveryCode and $emailOtp are null
 *   - Recovery: $recoveryCode is set, $code and $emailOtp are null
 *   - Email   : $emailOtp is set, $code and $recoveryCode are null
 */
final readonly class TwoFactorChallengeDTO
{
    public function __construct(
        public readonly ?string $code,
        public readonly ?string $recoveryCode,
        public readonly ?string $emailOtp,
    ) {}

    public function isRecoveryMode(): bool
    {
        return $this->recoveryCode !== null && $this->recoveryCode !== '';
    }

    public function isEmailOtpMode(): bool
    {
        return $this->emailOtp !== null && $this->emailOtp !== '';
    }

    public static function fromRequest(
        ?string $code,
        ?string $recoveryCode,
        ?string $emailOtp = null,
    ): self {
        return new self(
            code:         $code,
            recoveryCode: $recoveryCode,
            emailOtp:     $emailOtp,
        );
    }
}
