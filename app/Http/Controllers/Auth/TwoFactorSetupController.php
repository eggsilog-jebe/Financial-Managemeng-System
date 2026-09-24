<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactorEnrollConfirmRequest;
use App\Models\ActivityLog;
use App\Services\Auth\EmailOtpService;
use App\Services\Auth\TwoFactorRememberService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

/**
 * Manages the Email OTP 2FA enrollment lifecycle:
 * - show()    → display the email OTP setup page
 * - store()   → send an OTP to the user's registered email (rate-limited)
 * - confirm() → verify the OTP code to activate 2FA
 * - destroy() → disable 2FA for the authenticated user
 */
final class TwoFactorSetupController extends Controller
{
    public function __construct(
        private readonly EmailOtpService          $emailOtpService,
        private readonly TwoFactorRememberService $twoFactorRememberService,
    ) {}

    /**
     * Display the 2FA email OTP setup / enrollment page.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('accounting.dashboard')
                ->with('info', 'Two-factor authentication is already enabled on your account.');
        }

        return view('auth.two-factor-setup', [
            'userEmail'      => $user->email,
            'maskedEmail'    => $this->maskEmail($user->email),
            'hasPendingOtp'  => $this->emailOtpService->hasPending($user),
        ]);
    }

    /**
     * Send an enrollment OTP to the user's registered email.
     * Rate limited: 3 sends per 5 minutes per user.
     */
    public function store(Request $request): JsonResponse
    {
        $user        = Auth::user();
        $throttleKey = 'otp_enroll_send.' . $user->id . '.' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return response()->json([
                'error'       => "Too many requests. Please wait {$seconds} seconds.",
                'retry_after' => $seconds,
            ], 429);
        }

        RateLimiter::hit($throttleKey, 300); // 5-minute window

        try {
            $this->emailOtpService->send($user);
        } catch (RuntimeException $e) {
            return response()->json([
                'error' => 'Failed to send OTP email. Please try again.',
            ], 503);
        }

        ActivityLog::logAuth(
            event:       '2fa_enrollment_otp_sent',
            user:        $user,
            description: "Enrollment OTP dispatched to [{$user->email}] for 2FA setup.",
            ip:          $request->ip(),
            userAgent:   $request->userAgent(),
        );

        return response()->json([
            'message'      => 'OTP sent to your registered email address.',
            'masked_email' => $this->maskEmail($user->email),
            'expires_in'   => 600,
        ]);
    }

    /**
     * Confirm 2FA enrollment by verifying the OTP code sent to the user's email.
     */
    public function confirm(TwoFactorEnrollConfirmRequest $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $this->emailOtpService->verify($user, $request->input('code'))) {
            return redirect()->route('two-factor.setup')
                ->withErrors(['code' => 'The verification code is incorrect or has expired. Please request a new code.']);
        }

        // Mark 2FA as enrolled — we only need two_factor_confirmed_at for email OTP
        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            // Clear any old TOTP secrets to avoid confusion
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        // Mark 2FA as passed for this session immediately
        $request->session()->put('auth.2fa_passed', true);
        $request->session()->put('auth.last_activity_at', now()->toIso8601String());

        $cookie = $this->twoFactorRememberService->forgetCookie($user);

        ActivityLog::logAuth(
            event:       '2fa_enrolled',
            user:        $user,
            description: "User [{$user->name}] ({$user->role}) successfully enrolled in Email OTP 2FA.",
            ip:          $request->ip(),
            userAgent:   $request->userAgent(),
        );

        return redirect()->intended(
            match ($user->role ?? 'StaffAccountant') {
                'Cashier' => route('collection.cashier-desk'),
                default   => route('accounting.dashboard'),
            }
        )->withCookie($cookie)->with('success', '✅ Email two-factor authentication has been enabled on your account.');
    }

    /**
     * Disable 2FA for the authenticated user.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $user->forceFill([
            'two_factor_confirmed_at'   => null,
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        $this->emailOtpService->invalidate($user);
        $request->session()->forget(['auth.2fa_passed', 'auth.2fa_expires_at']);
        $cookie = $this->twoFactorRememberService->forgetCookie($user);

        ActivityLog::logAuth(
            event:       '2fa_disabled',
            user:        $user,
            description: "User [{$user->name}] ({$user->role}) disabled Two-Factor Authentication.",
            ip:          $request->ip(),
            userAgent:   $request->userAgent(),
        );

        return redirect()->route('accounting.dashboard')
            ->withCookie($cookie)
            ->with('warning', '⚠️ Two-factor authentication has been disabled. Your account is now less secure.');
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);
        $masked = substr($local, 0, 2) . str_repeat('*', max(strlen($local) - 2, 3));
        return "{$masked}@{$domain}";
    }
}
