<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\Auth\TwoFactorRememberService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

final class LoginController extends Controller
{
    public function __construct(
        private readonly TwoFactorRememberService $twoFactorRememberService,
    ) {}
    /**
     * Show the Login Screen.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        // 1. Normalize email input (trim whitespace and convert to lowercase)
        $rawEmail        = (string) $request->input('email', '');
        $normalizedEmail = Str::lower(trim($rawEmail));
        $request->merge(['email' => $normalizedEmail]);

        // 2. Strict Input Validation (caps string lengths to prevent hashing DoS)
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:128'],
        ]);

        // 3. Composite Rate Limiting (Account + IP Key)
        $throttleKey = Str::transliterate($normalizedEmail . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            ActivityLog::logAuth(
                event:       'rate_limited',
                user:        null,
                description: "Brute-force protection: Rate limit exceeded for [{$normalizedEmail}] from IP [{$request->ip()}]. Locked for {$seconds} seconds.",
                ip:          $request->ip(),
                userAgent:   $request->userAgent()
            );

            return back()->withErrors([
                'email' => "Too many authentication attempts. Please try again in {$seconds} seconds.",
            ])->onlyInput('email');
        }

        // 4. Authentication Attempt (Strict hospital shared workstation safety: zero persistent cookies)
        if (Auth::attempt($credentials, false)) {
            RateLimiter::clear($throttleKey);

            $request->session()->regenerate();
            $request->session()->save();

            $user = Auth::user();

            // Block suspended accounts immediately
            if ($user->isSuspended()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                ActivityLog::logAuth(
                    event:       'login_blocked_suspended',
                    user:        $user,
                    description: "Suspended user [{$user->name}] attempted login.",
                    ip:          $request->ip(),
                    userAgent:   $request->userAgent()
                );

                return back()->withErrors([
                    'email' => 'This hospital user account has been suspended by an administrator. Please contact the CFO.',
                ])->onlyInput('email');
            }

            $request->session()->put('auth.last_activity_at', now()->toIso8601String());

            ActivityLog::logAuth(
                event:       'login',
                user:        $user,
                description: "User [{$user->name}] ({$user->role}) logged in successfully.",
                ip:          $request->ip(),
                userAgent:   $request->userAgent()
            );

            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            // Mark 2FA as not yet verified for this new login session — verification required on every login
            $request->session()->put('auth.2fa_passed', false);

            // If user has 2FA enabled, always redirect to the challenge page
            if ($user->hasTwoFactorEnabled()) {
                return redirect()->route('two-factor.challenge');
            }

            // No 2FA yet — redirect to setup so they can enroll
            if (! $user->hasTwoFactorEnabled()) {
                return redirect()->route('two-factor.setup')
                    ->with('info', '🔐 For your hospital account security, please set up Two-Factor Authentication before continuing.');
            }

            // Redirect appropriately based on user role
            return match ($user->role ?? 'StaffAccountant') {
                'Cashier' => redirect()->intended(route('collection.cashier-desk')),
                default   => redirect()->intended(route('accounting.dashboard')),
            };
        }

        // 5. Failed Attempt Handling
        RateLimiter::hit($throttleKey, 60);

        ActivityLog::logAuth(
            event:       'failed_login',
            user:        null,
            description: "Failed login attempt for email [{$credentials['email']}].",
            ip:          $request->ip(),
            userAgent:   $request->userAgent()
        );

        return back()->withErrors([
            'email' => 'The provided credentials do not match our registered hospital records.',
        ])->onlyInput('email');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            ActivityLog::logAuth(
                event:       'logout',
                user:        $user,
                description: "User [{$user->name}] ({$user->role}) logged out.",
                ip:          $request->ip(),
                userAgent:   $request->userAgent()
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $response = redirect()->route('login');

        if ($user) {
            $response->withCookie($this->twoFactorRememberService->forgetCookie($user));
        }

        return $response;
    }
}
