<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Handles all TOTP Two-Factor Authentication business logic.
 * All secrets stored encrypted at rest; never in plain text.
 */
final class TwoFactorAuthService
{
    public function __construct(
        private readonly Google2FA $google2fa,
    ) {}

    /**
     * Generate a new Base32 TOTP secret string.
     */
    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey(32);
    }

    /**
     * Return the decrypted plain-text secret for a user.
     * Returns null if the user has no 2FA secret stored.
     */
    public function getDecryptedSecret(User $user): ?string
    {
        if (! $user->two_factor_secret) {
            return null;
        }

        return Crypt::decryptString($user->two_factor_secret);
    }

    /**
     * Build and return an inline SVG QR code for the TOTP enrollment.
     */
    public function getQrCodeSvg(User $user): string
    {
        $secret = $this->getDecryptedSecret($user);

        if (! $secret) {
            return '';
        }

        $otpAuthUrl = $this->google2fa->getQRCodeUrl(
            company:    config('app.name', 'Hospital FMS'),
            holder:     $user->email,
            secret:     $secret,
        );

        $renderer = new ImageRenderer(
            new RendererStyle(192),
            new SvgImageBackEnd(),
        );

        $writer = new Writer($renderer);

        return $writer->writeString($otpAuthUrl);
    }

    /**
     * Generate 8 cryptographically random one-time recovery codes.
     * Returns the plain-text codes for display and the hashed JSON for storage.
     *
     * @return array{ plain: string[], hashed: string }
     */
    public function generateRecoveryCodes(): array
    {
        $codes = Collection::times(8, fn (): string => Str::upper(Str::random(5)) . '-' . Str::upper(Str::random(5)));

        $plain  = $codes->all();
        $hashed = $codes->map(fn (string $code): string => Hash::make($code))->toJson();

        return [
            'plain'  => $plain,
            'hashed' => $hashed,
        ];
    }

    /**
     * Verify a 6-digit TOTP code against the user's secret.
     * Allows a 1-window clock drift (±30 seconds).
     */
    public function verifyCode(User $user, string $code): bool
    {
        $secret = $this->getDecryptedSecret($user);

        if (! $secret) {
            return false;
        }

        // Sanitize: strip spaces and non-digits
        $sanitized = preg_replace('/\D/', '', $code);

        try {
            return (bool) $this->google2fa->verifyKey($secret, $sanitized, 1);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Verify a recovery code, and if valid, burn it (one-time use).
     */
    public function verifyRecoveryCode(User $user, string $input): bool
    {
        if (! $user->two_factor_recovery_codes) {
            return false;
        }

        /** @var string[] $hashed */
        $hashed = json_decode(
            Crypt::decryptString($user->two_factor_recovery_codes),
            true,
        ) ?? [];

        foreach ($hashed as $index => $hashedCode) {
            if (Hash::check($input, $hashedCode)) {
                // Burn the used code
                unset($hashed[$index]);

                $user->forceFill([
                    'two_factor_recovery_codes' => Crypt::encryptString(json_encode(array_values($hashed))),
                ])->save();

                return true;
            }
        }

        return false;
    }

    /**
     * Confirm enrollment: set two_factor_confirmed_at to now.
     * Called after the user successfully enters their first TOTP code.
     */
    public function confirmEnrollment(User $user): void
    {
        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();
    }

    /**
     * Disable 2FA: clear all 2FA fields.
     */
    public function disable(User $user): void
    {
        $user->forceFill([
            'two_factor_secret'        => null,
            'two_factor_recovery_codes'=> null,
            'two_factor_confirmed_at'  => null,
        ])->save();
    }

    /**
     * Provision 2FA for a user: generate & save encrypted secret + recovery codes.
     * Does NOT confirm enrollment; the user must verify a code first.
     *
     * @return string[] Plain-text recovery codes to show the user once
     */
    public function provision(User $user): array
    {
        $secret         = $this->generateSecret();
        $recoveryCodes  = $this->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_secret'         => Crypt::encryptString($secret),
            'two_factor_recovery_codes' => Crypt::encryptString($recoveryCodes['hashed']),
            'two_factor_confirmed_at'   => null,
        ])->save();

        return $recoveryCodes['plain'];
    }
}
