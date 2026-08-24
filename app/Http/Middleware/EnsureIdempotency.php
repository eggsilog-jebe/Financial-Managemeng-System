<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

final class EnsureIdempotency
{
    /**
     * Intercept requests with the X-Idempotency-Key header to prevent duplicate financial transaction postings.
     * Caches response payloads for 24 hours.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $idempotencyKey = $request->header('X-Idempotency-Key');

        // Only enforce on mutation requests (POST, PUT, PATCH, DELETE)
        if (! $idempotencyKey || ! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $next($request);
        }

        $cacheKey = 'idempotency:' . md5($idempotencyKey . ':' . $request->path());

        // Check if an identical request has already been processed within the 24-hour window
        if (Cache::has($cacheKey)) {
            $cachedData = Cache::get($cacheKey);

            return response()->json(
                $cachedData['content'],
                $cachedData['status'],
                array_merge($cachedData['headers'], [
                    'X-Idempotency-Replay' => 'true',
                    'X-Idempotency-Key'    => $idempotencyKey,
                ])
            );
        }

        /** @var Response $response */
        $response = $next($request);

        // Cache successful and client-safe response payloads for 24 hours (86400 seconds)
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 400) {
            $content = json_decode($response->getContent() ?: '{}', true);

            Cache::put($cacheKey, [
                'status'  => $response->getStatusCode(),
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'content' => $content,
            ], now()->addHours(24));
        }

        return $response;
    }
}
