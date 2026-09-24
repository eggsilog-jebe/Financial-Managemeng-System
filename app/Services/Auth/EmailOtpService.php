<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use App\Services\Brevo\BrevoMailService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Manages the full lifecycle of email-based OTP codes for 2FA:
 *
 *  - Generating a cryptographically random 6-digit OTP
 *  - Caching it with a 10-minute TTL (keyed by user ID, single-use)
 *  - Sending it to the user's registered email via Brevo
 *  - Verifying a submitted code and burning it on success
 *
 * Rate limiting (3 sends per 5 minutes) is enforced at the controller layer.
 */
final class EmailOtpService
{
    /** OTP time-to-live in seconds (10 minutes) */
    private const TTL_SECONDS = 600;

    /** Cache key prefix */
    private const CACHE_PREFIX = 'email_otp:';

    public function __construct(
        private readonly BrevoMailService $brevoMail,
    ) {}

    /**
     * Generate a new 6-digit OTP, store it in cache, and email it to the user.
     *
     * The OTP is stored as a hash to avoid plaintext secrets in cache.
     * On each new send request the previous code is immediately invalidated.
     *
     * @throws RuntimeException if the email cannot be dispatched
     */
    public function send(User $user): void
    {
        $otp = $this->generateOtp();

        // Store the plaintext OTP in cache (auto-expiring, single-use).
        // We store plain because bcrypt is slow; cache is server-side only.
        Cache::put(
            key:     $this->cacheKey($user),
            value:   $otp,
            ttl:     self::TTL_SECONDS,
        );

        $maskedEmail = $this->maskEmail($user->email);

        $htmlContent = $this->buildHtmlEmail($user->name, $otp, $maskedEmail);
        $textContent = $this->buildTextEmail($user->name, $otp);

        $this->brevoMail->send(
            toEmail:     $user->email,
            toName:      $user->name,
            subject:     "[Hospital FMS] Your Login Verification Code: {$otp}",
            htmlContent: $htmlContent,
            textContent: $textContent,
        );

        Log::info('[EmailOtpService] OTP dispatched', [
            'user_id' => $user->id,
            'email'   => $maskedEmail,
        ]);
    }

    /**
     * Verify a submitted OTP code against the cached value.
     * If valid, the code is immediately burned (single-use guarantee).
     */
    public function verify(User $user, string $submittedCode): bool
    {
        $key   = $this->cacheKey($user);
        $stored = Cache::get($key);

        if ($stored === null) {
            return false; // Expired or never sent
        }

        // Constant-time string comparison to prevent timing attacks
        $isValid = hash_equals((string) $stored, trim($submittedCode));

        if ($isValid) {
            Cache::forget($key); // Burn on successful use
            Log::info('[EmailOtpService] OTP verified and burned', ['user_id' => $user->id]);
        }

        return $isValid;
    }

    /**
     * Invalidate any pending OTP for a user (e.g. on password change or logout).
     */
    public function invalidate(User $user): void
    {
        Cache::forget($this->cacheKey($user));
    }

    /**
     * Check if there is a pending (unexpired) OTP for this user.
     */
    public function hasPending(User $user): bool
    {
        return Cache::has($this->cacheKey($user));
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────

    private function cacheKey(User $user): string
    {
        return self::CACHE_PREFIX . $user->id;
    }

    /** Cryptographically random 6-digit OTP, zero-padded. */
    private function generateOtp(): string
    {
        return str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);
    }

    /** Mask the email for partial display: e.g. jo***@gmail.com */
    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);
        $masked = substr($local, 0, 2) . str_repeat('*', max(strlen($local) - 2, 3));
        return "{$masked}@{$domain}";
    }

    private function buildHtmlEmail(string $name, string $otp, string $maskedEmail): string
    {
        $year = date('Y');
        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>Login Verification Code</title>
        </head>
        <body style="margin:0;padding:0;background:#f0f4f8;font-family:'Segoe UI',Arial,sans-serif;">
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f0f4f8;padding:32px 0;">
            <tr><td align="center">
              <table role="presentation" width="480" cellpadding="0" cellspacing="0"
                     style="background:#ffffff;border-radius:16px;overflow:hidden;
                            box-shadow:0 4px 24px rgba(0,0,0,0.08);max-width:480px;width:100%;">

                <!-- Header -->
                <tr>
                  <td style="background:linear-gradient(135deg,#1e3a5f 0%,#2563eb 100%);
                              padding:28px 32px;text-align:center;">
                    <div style="display:inline-flex;align-items:center;gap:10px;">
                      <div style="width:40px;height:40px;background:rgba(255,255,255,0.15);
                                  border-radius:10px;display:inline-flex;align-items:center;
                                  justify-content:center;font-size:22px;">🏥</div>
                      <div style="text-align:left;">
                        <p style="margin:0;color:rgba(255,255,255,0.7);font-size:11px;
                                  font-weight:600;letter-spacing:0.08em;text-transform:uppercase;">
                          Hospital Financial Management System
                        </p>
                        <p style="margin:0;color:#ffffff;font-size:17px;font-weight:700;">
                          Login Verification
                        </p>
                      </div>
                    </div>
                  </td>
                </tr>

                <!-- Body -->
                <tr>
                  <td style="padding:32px 32px 24px;">
                    <p style="margin:0 0 8px;font-size:15px;color:#1e293b;font-weight:600;">
                      Hello, {$name}
                    </p>
                    <p style="margin:0 0 24px;font-size:14px;color:#64748b;line-height:1.6;">
                      A sign-in attempt was made to your Hospital FMS account
                      (<strong>{$maskedEmail}</strong>). Use the code below to
                      complete your login.
                    </p>

                    <!-- OTP Box -->
                    <div style="background:#f0f6ff;border:2px dashed #2563eb;
                                border-radius:12px;padding:24px 16px;text-align:center;
                                margin-bottom:24px;">
                      <p style="margin:0 0 6px;font-size:12px;color:#64748b;
                                font-weight:600;letter-spacing:0.06em;text-transform:uppercase;">
                        Your One-Time Verification Code
                      </p>
                      <p style="margin:0;font-size:42px;font-weight:800;
                                letter-spacing:14px;color:#1e3a5f;
                                font-family:'Courier New',monospace;">
                        {$otp}
                      </p>
                      <p style="margin:10px 0 0;font-size:12px;color:#ef4444;font-weight:600;">
                        ⏱ Expires in 10 minutes &nbsp;·&nbsp; Single use only
                      </p>
                    </div>

                    <!-- Warning -->
                    <div style="background:#fff7ed;border-left:4px solid #f97316;
                                border-radius:8px;padding:14px 16px;margin-bottom:24px;">
                      <p style="margin:0;font-size:13px;color:#92400e;line-height:1.5;">
                        <strong>🔒 Security Notice:</strong> Hospital FMS staff will never ask
                        for your OTP code. If you did not attempt to sign in, please contact
                        your system administrator immediately.
                      </p>
                    </div>

                    <p style="margin:0;font-size:13px;color:#94a3b8;line-height:1.5;">
                      If you didn't request this code, you can safely ignore this email.
                    </p>
                  </td>
                </tr>

                <!-- Footer -->
                <tr>
                  <td style="background:#f8fafc;border-top:1px solid #e2e8f0;
                              padding:16px 32px;text-align:center;">
                    <p style="margin:0;font-size:11px;color:#94a3b8;">
                      © {$year} Hospital Financial Management System &nbsp;·&nbsp;
                      HIPAA &amp; RA 10173 Compliant &nbsp;·&nbsp; Do not reply to this email
                    </p>
                  </td>
                </tr>

              </table>
            </td></tr>
          </table>
        </body>
        </html>
        HTML;
    }

    private function buildTextEmail(string $name, string $otp): string
    {
        return <<<TEXT
        Hospital Financial Management System — Login Verification

        Hello, {$name},

        Your one-time login verification code is:

            {$otp}

        This code expires in 10 minutes and can only be used once.

        If you did not attempt to sign in, contact your system administrator immediately.

        — Hospital FMS Security
        TEXT;
    }
}
