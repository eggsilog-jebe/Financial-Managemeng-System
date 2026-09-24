<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Immutable DTO for the 2FA challenge submission.
 *
 * Two mutually exclusive modes:
 *   - TOTP    : $code is set, $recoveryCode is null
 *   - Recovery: $recoveryCode is set, $code is null
 */
final readonly class TwoFactorChallengeDTO
{
    public function __construct(
        public readonly ?string $code,
        public readonly ?string $recoveryCode,
    ) {}

    public function isRecoveryMode(): bool
    {
        return $this->recoveryCode !== null && $this->recoveryCode !== '';
    }

    public static function fromRequest(
        ?string $code,
        ?string $recoveryCode,
    ): self {
        return new self(
            code:         $code,
            recoveryCode: $recoveryCode,
        );
    }
}
