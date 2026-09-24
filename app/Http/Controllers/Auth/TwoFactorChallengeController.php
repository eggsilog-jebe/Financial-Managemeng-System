<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\DTOs\TwoFactorChallengeDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactorChallengeRequest;
use App\Models\ActivityLog;
use App\Services\Auth\TwoFactorAuthService;
use App\Services\Auth\TwoFactorRememberService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Handles the TOTP second factor of authentication:
 * - show()   -> displays the TOTP challenge page (6-digit code from Google Authenticator)
 * - verify() -> verifies the TOTP code or a one-time recovery code
 */
final class TwoFactorChallengeController extends Controller
{
    public function __construct(
        private readonly TwoFactorAuthService     $twoFactorService,
        private readonly TwoFactorRememberService $twoFactorRememberService,
    ) {}

    /**
     * Display the TOTP 2FA challenge page.
     */
    public function show(Request $request): View|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // If 2FA is not enrolled, redirect to mandatory TOTP setup
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

        return view('auth.two-factor-challenge');
    }

    /**
     * Verify the submitted TOTP code or recovery code.
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

        $dto = TwoFactorChallengeDTO::fromRequest(
            code:         $request->input('code'),
            recoveryCode: $request->input('recovery_code'),
        );

        $passed = false;
        $method = 'TOTP';

        if ($dto->isRecoveryMode()) {
            $method = 'recovery code';
            $passed = $this->twoFactorService->verifyRecoveryCode($user, (string) $dto->recoveryCode);
        } else {
            $method = 'TOTP';
            $passed = $this->twoFactorService->verifyCode($user, (string) $dto->code);
        }

        if (! $passed) {
            RateLimiter::hit($throttleKey, 60);

            ActivityLog::logAuth(
                event:       '2fa_failed',
                user:        $user,
                description: "Failed 2FA attempt for user [{$user->name}] ({$user->role}) -- {$method} was invalid.",
                ip:          $request->ip(),
                userAgent:   $request->userAgent(),
            );

            $errorMessage = $dto->isRecoveryMode()
                ? 'The recovery code you entered is invalid or has already been used.'
                : 'The authenticator code is incorrect. Please open Google Authenticator and try again.';

            return back()->withErrors(['code' => $errorMessage]);
        }

        // 2FA passed
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
}