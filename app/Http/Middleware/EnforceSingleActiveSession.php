<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use App\Models\UserActiveSession;
use App\Services\Security\ActiveSessionManagerService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces Single Active Session & Workstation Access on every authenticated request.
 *
 * If another user or workstation logs into the same account, this middleware terminates
 * the previous session immediately and redirects to login with an explicit displacement security warning.
 */
final class EnforceSingleActiveSession
{
    public function __construct(
        private readonly ActiveSessionManagerService $sessionManager,
    ) {}

    /**
     * Routes exempt from session displacement checks.
     *
     * @var string[]
     */
    private const EXEMPT_ROUTES = [
        'login',
        'login.post',
        'logout',
        'logout.get',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        if ($request->routeIs(...self::EXEMPT_ROUTES)) {
            return $next($request);
        }

        if (! $request->hasSession()) {
            return $next($request);
        }

        $sessionId = $request->session()->getId();
        $terminationReason = $this->sessionManager->checkSessionDisplacement($sessionId, $user);

        if ($terminationReason !== null) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            ActivityLog::logAuth(
                event:       'session_displaced_logged_out',
                user:        $user,
                description: "User [{$user->name}] was kicked out due to [{$terminationReason}].",
                ip:          $request->ip(),
                userAgent:   $request->userAgent()
            );

            $warning = match ($terminationReason) {
                UserActiveSession::REASON_DISPLACED =>
                    '⚠️ Security Displacement Alert: Your account was accessed from another computer or workstation. Only one concurrent session is authorized per hospital personnel. Your previous session has been terminated.',
                UserActiveSession::REASON_ADMIN_REVOKED =>
                    '⚠️ Security Notice: Your active session was terminated by a Super Administrator.',
                UserActiveSession::REASON_WORKSTATION_REVOKED =>
                    '⚠️ Security Notice: Authorization for this workstation was revoked by a Super Administrator.',
                default =>
                    '⚠️ Security Alert: Your session has been terminated.',
            };

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $warning,
                    'reason'  => $terminationReason,
                ], 401);
            }

            return redirect()->route('login')
                ->with('displacement_warning', $warning)
                ->withErrors(['email' => $warning]);
        }

        // Touch the session activity timestamp
        $this->sessionManager->touchSession($sessionId);

        return $next($request);
    }
}
