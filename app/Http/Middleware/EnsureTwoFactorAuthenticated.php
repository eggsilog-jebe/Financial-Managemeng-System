<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Auth\TwoFactorRememberService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces Two-Factor Authentication after the primary password check.
 *
 * Flow:
 * 1. User passes login (password correct).
 * 2. If user already verified 2FA on this device within the 8-hour window, 2FA is restored/bypassed.
 * 3. Otherwise, user is redirected to /two-factor-challenge.
 * 4. Once the challenge is passed → session['auth.2fa_passed'] = true, valid for 8 hours.
 */
final class EnsureTwoFactorAuthenticated
{
    public function __construct(
        private readonly TwoFactorRememberService $rememberService,
    ) {}

    /**
     * Route names that are exempt from the 2FA gate.
     *
     * @var string[]
     */
    private const EXEMPT_ROUTES = [
        'logout',
        'logout.get',
        'session.heartbeat',
        'workstation.pending',
        'workstation.status',
        'workstation.cancel',
        'two-factor.challenge',
        'two-factor.challenge.verify',
        'two-factor.setup',
        'two-factor.setup.store',
        'two-factor.setup.confirm',
        'two-factor.setup.destroy',
        'password.change',
        'password.change.update',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        // Skip routes exempt from 2FA gate
        if ($request->routeIs(...self::EXEMPT_ROUTES)) {
            return $next($request);
        }

        if (! $request->hasSession()) {
            return $next($request);
        }

        // 1. If the user has NOT completed 2FA enrollment yet → gate and force setup
        if (! $user->hasTwoFactorEnabled()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Two-factor authentication enrollment is mandatory.'], 403);
            }

            return redirect()->route('two-factor.setup')
                ->with('warning', '🔐 Two-Factor Authentication is mandatory for all hospital personnel. Please complete setup to continue.');
        }

        // 2. If the user has 2FA enabled but hasn't passed the challenge yet → gate and force challenge
        if (! $request->session()->get('auth.2fa_passed', false)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Two-factor authentication required.'], 403);
            }

            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
