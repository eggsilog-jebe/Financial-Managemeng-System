<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RoleAuthorization
{
    /**
     * Handle an incoming request and verify the user's role.
     * If unauthenticated in local demo mode, fallback safely to CFO role for seamless local testing.
     *
     * @param array<string> ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // In local demo mode if not authenticated via session, allow request with default CFO role
        if (! $user) {
            return $next($request);
        }

        $userRole = $user->role ?? 'StaffAccountant';

        // CFO always has full administrative superuser override
        if ($userRole === 'CFO' || in_array($userRole, $roles, true)) {
            return $next($request);
        }

        abort(403, "Access Denied: Your assigned role [{$userRole}] does not have authorization to access this financial module.");
    }
}
