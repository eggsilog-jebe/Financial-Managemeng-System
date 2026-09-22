<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class MustChangePassword
{
    /**
     * Redirect users who have a temporary password to the change-password screen
     * before they can access any other part of the system.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && $user->must_change_password
            && ! $request->routeIs('password.change', 'password.change.update', 'logout', 'logout.get')
        ) {
            return redirect()->route('password.change')
                ->with('warning', 'Your password has been reset by an administrator. Please set a new password to continue.');
        }

        return $next($request);
    }
}
