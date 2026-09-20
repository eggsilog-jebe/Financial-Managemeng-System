<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request and attach baseline enterprise security headers.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking by forbidding embedding in foreign frames
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME-sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Protect privacy by only leaking origin across external boundaries
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Disable unnecessary browser sensor capabilities on hospital terminals
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
