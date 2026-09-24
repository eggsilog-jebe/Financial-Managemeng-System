<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\DTOs\TwoFactorChallengeDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactorChallengeRequest;
use App\Models\ActivityLog;
use App\Services\Auth\EmailOtpService;
use App\Services\Auth\TwoFactorAuthService;
use App\Services\Auth\TwoFactorRememberService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

/**
 * Handles the second factor of authentication:
 * - show()          → displays the TOTP/email challenge page
 * - verify()        → verifies TOTP code, recovery code, or email OTP
 * - sendEmailOtp()  → generates & emails an OTP code (rate-limited)
 */
final class TwoFactorChallengeController extends Controller
{
    public function __construct(
        private readonly TwoFactorAuthService     $twoFactorService,
        private readonly EmailOtpService          $emailOtpService,
        private readonly TwoFactorRememberService $twoFactorRememberService,
    ) {}

    /**
     * Display the 2FA challenge page.
     */
    public function show(Request $request): View|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // If 2FA is not enabled on account, redirect to mandatory setup
        if (! $user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.setup');
        }

        // If already passed, skip to role dashboard
        if ($request->session()->get('auth.2fa_passed', false)) {
            $target = match ($user->role ?? 'StaffAccountant') {
                'Cashier' => route('collection.cashier-desk'),
                default   => route('accounting.dashboard'),
            };

            return redirect()->to($target);
        }

        return view('auth.two-factor-challenge', [
            'hasPendingEmailOtp' => $this->emailOtpService->hasPending($user),
            'userEmail'          => $this->maskEmail($user->email),
        ]);
    }

    /**
     * Send a one-time password to the user's registered email address.
     * Rate limited: 3 sends per 5 minutes per user.
     */
    public function sendEmailOtp(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $user        = Auth::user();
        $throttleKey = 'email_otp_send.' . $user->id . '.' . $request->ip();

        // Allow max 3 sends per 5 minutes
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return response()->json([
                'error'         => "Too many requests. Please wait {$seconds} seconds.",
                'retry_after'   => $seconds,
            ], 429);
        }

        RateLimiter::hit($throttleKey, 300); // 5-minute decay

        try {
            $this->emailOtpService->send($user);
        } catch (RuntimeException $e) {
            return response()->json([
                'error' => 'Failed to send OTP email. Please try again or use your authenticator app.',
            ], 503);
        }

        ActivityLog::logAuth(
            event:       'email_otp_sent',
            user:        $user,
            description: "Email OTP dispatched to [{$user->email}] for 2FA challenge.",
            ip:          $request->ip(),
            userAgent:   $request->userAgent(),
        );

        return response()->json([
            'message'      => 'OTP sent to your registered email address.',
            'masked_email' => $this->maskEmail($user->email),
            'expires_in'   => 600, // seconds
        ]);
    }

    /**
     * Verify the submitted TOTP code, recovery code, or email OTP.
     */
    public function verify(TwoFactorChallengeRequest $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user        = Auth::user();
        $throttleKey = '2fa.' . $user->id . '.' . $request->ip();

        // Rate limit: 5 attempts per minute
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            ActivityLog::logAuth(
                event:       '2fa_rate_limited',
                user:        $user,
                description: "2FA rate limit exceeded for user [{$user->name}] from IP [{$request->ip()}]. Locked for {$seconds}s.",
                ip:          $request->ip(),
                userAgent:   $request->userAgent(),
            );

            return back()->withErrors([
                'code' => "Too many attempts. Please wait {$seconds} seconds before trying again.",
            ]);
        }

        $code         = $request->input('code');
        $emailOtp     = $request->input('email_otp');
        $recoveryCode = $request->input('recovery_code');

        // Fallback: If user has no TOTP secret (email OTP user), treat any submitted code as email OTP
        if (empty($emailOtp) && ! empty($code) && $user->two_factor_secret === null) {
            $emailOtp = $code;
        }

        $dto = TwoFactorChallengeDTO::fromRequest(
            code:         $code,
            recoveryCode: $recoveryCode,
            emailOtp:     $emailOtp,
        );

        $passed = false;
        $method = 'email OTP';

        if ($dto->isEmailOtpMode() || ($user->two_factor_secret === null && ! $dto->isRecoveryMode())) {
            $submittedOtp = $dto->emailOtp ?? $dto->code;
            $method = 'email OTP';
            $passed = $this->emailOtpService->verify($user, (string) $submittedOtp);
        } elseif ($dto->isRecoveryMode()) {
            $method = 'recovery code';
            $passed = $this->twoFactorService->verifyRecoveryCode($user, (string) $dto->recoveryCode);
        } else {
            // Legacy TOTP path (for users who enrolled before the email OTP migration)
            $method = 'TOTP';
            $passed = $this->twoFactorService->verifyCode($user, (string) $dto->code);
        }

        if (! $passed) {
            RateLimiter::hit($throttleKey, 60);

            ActivityLog::logAuth(
                event:       '2fa_failed',
                user:        $user,
                description: "Failed 2FA attempt for user [{$user->name}] ({$user->role}) — {$method} was invalid.",
                ip:          $request->ip(),
                userAgent:   $request->userAgent(),
            );

            $errorMessage = match (true) {
                $dto->isRecoveryMode()  => 'The recovery code you entered is invalid or has already been used.',
                $dto->isEmailOtpMode()  => 'The email OTP is incorrect or has expired. Please request a new code.',
                default                 => 'The authentication code is incorrect. Please check your authenticator app and try again.',
            };

            return back()->withErrors(['code' => $errorMessage]);
        }

        // ✅ 2FA passed
        RateLimiter::clear($throttleKey);

        $request->session()->put('auth.2fa_passed', true);
        $request->session()->put('auth.last_activity_at', now()->toIso8601String());

        $cookie = $this->twoFactorRememberService->forgetCookie($user);

        ActivityLog::logAuth(
            event:       '2fa_passed',
            user:        $user,
            description: "User [{$user->name}] ({$user->role}) completed 2FA verification successfully via {$method}.",
            ip:          $request->ip(),
            userAgent:   $request->userAgent(),
        );

        return redirect()->intended(
            match ($user->role ?? 'StaffAccountant') {
                'Cashier' => route('collection.cashier-desk'),
                default   => route('accounting.dashboard'),
            }
        )->withCookie($cookie);
    }

    // ─── Private Helpers ─────────────────────────────────────────────────

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);
        $masked = substr($local, 0, 2) . str_repeat('*', max(strlen($local) - 2, 3));
        return "{$masked}@{$domain}";
    }
}
