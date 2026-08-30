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

        // Unauthenticated passthrough is ONLY permitted in local/testing environments (demo mode).
        // In staging and production, unauthenticated requests must be rejected with 401.
        if (! $user) {
            if (! app()->environment('local', 'testing')) {
                abort(401, 'Unauthenticated: A valid authenticated session is required to access this financial module.');
            }

            return $next($request);
        }

        $userRole = $user->role ?? 'StaffAccountant';

        // CFO and FinanceDirector always have full superuser override
        if (in_array($userRole, ['CFO', 'FinanceDirector'], true) || in_array($userRole, $roles, true)) {
            return $next($request);
        }

        abort(403, "Access Denied: Your assigned role [{$userRole}] does not have authorization to access this financial module.");
    }
}
