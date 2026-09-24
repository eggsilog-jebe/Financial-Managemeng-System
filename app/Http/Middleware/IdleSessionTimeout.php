<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Server-side idle session timeout guard.
 *
 * On every authenticated request, this middleware:
 * 1. Records the timestamp of the last activity in the session.
 * 2. Checks if the user has been idle longer than the configured threshold.
 * 3. If so, logs the timeout event and forces a logout.
 *
 * This acts as a hard server-side enforcement independent of the client-side
 * idle-monitor.js. Even if JS is disabled, the session will expire.
 */
final class IdleSessionTimeout
{
    /** Idle threshold in seconds (15 minutes). */
    private const IDLE_THRESHOLD_SECONDS = 900;

    /**
     * Routes that should never trigger the idle check (e.g. heartbeat, logout, 2FA).
     *
     * @var string[]
     */
    private const EXEMPT_ROUTES = [
        'logout',
        'logout.get',
        'session.heartbeat',
        'login',
        'login.post',
        'two-factor.challenge',
        'two-factor.challenge.verify',
        'two-factor.email-otp.send',
        'two-factor.setup',
        'two-factor.setup.store',
        'two-factor.setup.confirm',
        'two-factor.setup.destroy',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Only applies to authenticated users
        if (! Auth::check()) {
            return $next($request);
        }

        // Skip exempt routes (heartbeat, logout, login, 2FA flows)
        if ($request->routeIs(...self::EXEMPT_ROUTES)) {
            return $next($request);
        }

        $lastActivity = $request->session()->get('auth.last_activity_at');
        $idleThreshold = (int) config('session.idle_timeout', 28800);

        if ($lastActivity !== null) {
            $idleSeconds = now()->diffInSeconds($lastActivity);

            if ($idleSeconds > $idleThreshold) {
                $user = Auth::user();

                ActivityLog::logAuth(
                    event:       'idle_timeout_logout',
                    user:        $user,
                    description: "User [{$user->name}] ({$user->role}) was automatically logged out after {$idleSeconds}s of inactivity.",
                    ip:          $request->ip(),
                    userAgent:   $request->userAgent(),
                );

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Session expired due to inactivity.'], 401);
                }

                return redirect()->route('login')
                    ->with('session_expired', 'Your session expired due to inactivity. Please sign in again.');
            }
        }

        // Refresh the last activity timestamp on every active request
        $request->session()->put('auth.last_activity_at', now()->toIso8601String());

        return $next($request);
    }
}
