<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Universal User Activity Audit Middleware
 *
 * Automatically records every authenticated user action, navigation,
 * record lookup, export, and operational transaction into the immutable Audit Trail.
 */
final class AuditUserActivity
{
    /**
     * Named routes that must be excluded from audit logging to prevent log pollution.
     *
     * @var string[]
     */
    private const EXCLUDED_ROUTES = [
        'session.heartbeat',
        'workstation.status',
        'user-security.workstations.poll',
        'login',
        'login.post',
        'logout',
        'logout.get',
    ];

    /**
     * Path prefixes excluded from activity audit (polling, health checks, assets).
     *
     * @var string[]
     */
    private const EXCLUDED_PATH_PREFIXES = [
        'session/heartbeat',
        'workstation/status',
        'user-security/workstations/poll',
        'up',
        '_debugbar',
        'livewire',
    ];

    /**
     * Sensitive input parameters that must always be redacted in audit payloads.
     *
     * @var string[]
     */
    private const SENSITIVE_INPUTS = [
        '_token',
        'password',
        'password_confirmation',
        'current_password',
        'secret',
        'code',
        'recovery_code',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'card_number',
        'cvv',
        'pin',
    ];

    /**
     * Handle an incoming request.
     * Passes the request immediately down the pipeline so the client receives the response with zero delay.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Debounce identical GET requests from the same user within 5 seconds in session
        if ($request->isMethod('GET') && $this->isDuplicateGetRequest($request)) {
            $request->attributes->set('audit_skip', true);
        }

        return $next($request);
    }

    /**
     * Handle tasks after the HTTP response has been sent to the browser.
     * Executes the immutable audit record write asynchronously outside the user's critical path.
     */
    public function terminate(Request $request, Response $response): void
    {
        if ($request->attributes->get('audit_skip', false)) {
            return;
        }

        try {
            // Only audit authenticated requests
            $user = Auth::user();
            if (! $user) {
                return;
            }

            // Skip excluded routes or path prefixes
            if ($this->shouldExclude($request)) {
                return;
            }

            // Only audit successful or redirected operations (HTTP 200..399)
            if ($response->getStatusCode() >= 400) {
                return;
            }

            $this->recordActivity($request, $user);
        } catch (\Throwable) {
            // Silently ignore failures during terminate to prevent disruptive crashes
        }
    }

    /**
     * Check if the request matches excluded routes or prefixes.
     */
    private function shouldExclude(Request $request): bool
    {
        if ($request->routeIs(...self::EXCLUDED_ROUTES)) {
            return true;
        }

        $path = trim($request->path(), '/');

        foreach (self::EXCLUDED_PATH_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Prevent log spam by suppressing identical GET views within 5 seconds.
     */
    private function isDuplicateGetRequest(Request $request): bool
    {
        if (! $request->hasSession()) {
            return false;
        }

        $cacheKey = 'audit_view_' . md5($request->fullUrl());
        $lastVisitedAt = $request->session()->get($cacheKey);

        if ($lastVisitedAt && (now()->timestamp - $lastVisitedAt) < 5) {
            return true;
        }

        $request->session()->put($cacheKey, now()->timestamp);

        return false;
    }

    /**
     * Persist the user activity into the immutable ActivityLog repository.
     */
    private function recordActivity(Request $request, $user): void
    {
        $module = $this->resolveModule($request);
        $event = $this->resolveEvent($request);
        $description = $this->resolveDescription($request, $user, $module, $event);
        $payload = $this->extractPayload($request);

        ActivityLog::create([
            'user_id'     => $user->id,
            'user_name'   => $user->name,
            'user_role'   => $user->role ?? 'User',
            'user_email'  => $user->email,
            'event'       => $event,
            'module'      => $module,
            'description' => $description,
            'old_values'  => null,
            'new_values'  => ! empty($payload) ? $payload : null,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'url'         => $request->fullUrl(),
        ]);
    }

    /**
     * Resolve the hospital financial domain module based on URL prefix.
     */
    private function resolveModule(Request $request): string
    {
        $path = trim($request->path(), '/');

        return match (true) {
            str_starts_with($path, 'general-ledger')      => 'General Ledger',
            str_starts_with($path, 'accounts-payable')    => 'Accounts Payable (AP)',
            str_starts_with($path, 'accounts-receivable') => 'Patient Billing (AR)',
            str_starts_with($path, 'disbursements')       => 'Disbursements',
            str_starts_with($path, 'collection')          => 'Cashier POS & Collections',
            str_starts_with($path, 'cash-management')     => 'Cash Management',
            str_starts_with($path, 'fiscal-budget') || str_starts_with($path, 'budget') => 'Fiscal Budgets',
            str_starts_with($path, 'financial-reporting') => 'Financial Reporting',
            str_starts_with($path, 'tax-management')      => 'Tax & Compliance',
            str_starts_with($path, 'user-security')       => 'User & Security',
            str_starts_with($path, 'accounting')          => 'Accounting Management',
            str_starts_with($path, 'two-factor')          => 'Two-Factor Auth',
            str_starts_with($path, 'workstation')         => 'Workstation Security',
            default                                       => 'Hospital FMS Core',
        };
    }

    /**
     * Determine the event type from HTTP method, route name, and URL keywords.
     */
    private function resolveEvent(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();
        $routeName = (string) $request->route()?->getName();

        if ($method === 'GET') {
            if (str_contains($path, 'export') || str_contains($path, 'download') || str_contains($path, 'csv')) {
                return 'exported';
            }
            if (str_contains($path, 'print')) {
                return 'printed';
            }
            return 'viewed';
        }

        // Action-specific events for POST / PUT / PATCH / DELETE
        return match (true) {
            str_contains($routeName, 'approve')    => 'approved',
            str_contains($routeName, 'reject')     => 'rejected',
            str_contains($routeName, 'revoke')     => 'revoked',
            str_contains($routeName, 'terminate')  => 'session_terminated',
            str_contains($routeName, 'post')       => 'posted',
            str_contains($routeName, 'reverse')    => 'reversed',
            str_contains($routeName, 'lock')       => 'locked',
            str_contains($routeName, 'close')      => 'closed',
            str_contains($routeName, 'pay') || str_contains($routeName, 'collect') => 'collected',
            str_contains($routeName, 'toggle')     => 'toggled',
            str_contains($routeName, 'destroy')    => 'deleted',
            $method === 'DELETE'                   => 'deleted',
            $method === 'PUT', $method === 'PATCH' => 'updated',
            $method === 'POST'                     => 'submitted',
            default                                => 'action',
        };
    }

    /**
     * Generate a natural human-readable description of the user's action.
     */
    private function resolveDescription(Request $request, $user, string $module, string $event): string
    {
        $roleLabel = $user->roleLabel() ?? $user->role ?? 'User';
        $path = trim($request->path(), '/');
        $resourceName = $this->humanizePath($path);

        return match ($event) {
            'viewed'             => "User [{$user->name}] ({$roleLabel}) accessed {$module}: {$resourceName}.",
            'exported'           => "User [{$user->name}] ({$roleLabel}) exported {$module} data ({$resourceName}).",
            'printed'            => "User [{$user->name}] ({$roleLabel}) printed document from {$module} ({$resourceName}).",
            'approved'           => "User [{$user->name}] ({$roleLabel}) approved request in {$module}.",
            'rejected'           => "User [{$user->name}] ({$roleLabel}) rejected request in {$module}.",
            'revoked'            => "User [{$user->name}] ({$roleLabel}) revoked authorization in {$module}.",
            'session_terminated' => "User [{$user->name}] ({$roleLabel}) terminated an active user session.",
            'posted'             => "User [{$user->name}] ({$roleLabel}) posted transaction in {$module}.",
            'reversed'           => "User [{$user->name}] ({$roleLabel}) initiated transaction reversal in {$module}.",
            'locked'             => "User [{$user->name}] ({$roleLabel}) locked accounting period in {$module}.",
            'closed'             => "User [{$user->name}] ({$roleLabel}) finalized period close in {$module}.",
            'collected'          => "User [{$user->name}] ({$roleLabel}) processed collection/payment in {$module}.",
            'submitted'          => "User [{$user->name}] ({$roleLabel}) submitted {$resourceName} transaction.",
            'updated'            => "User [{$user->name}] ({$roleLabel}) modified {$resourceName} record.",
            'deleted'            => "User [{$user->name}] ({$roleLabel}) deleted {$resourceName} record.",
            default              => "User [{$user->name}] ({$roleLabel}) performed {$event} on {$resourceName}.",
        };
    }

    /**
     * Convert URI paths into natural title strings.
     */
    private function humanizePath(string $path): string
    {
        $segments = explode('/', $path);
        $last = end($segments) ?: 'dashboard';

        // Strip numbers from last segment if it's an ID
        if (is_numeric($last) && count($segments) > 1) {
            $last = $segments[count($segments) - 2] . ' #' . $last;
        }

        return ucwords(str_replace(['-', '_'], ' ', $last));
    }

    /**
     * Extract and sanitize request parameters (query and input body).
     *
     * @return array<string, mixed>
     */
    private function extractPayload(Request $request): array
    {
        $data = $request->isMethod('GET') ? $request->query() : $request->except(self::SENSITIVE_INPUTS);

        // Sanitize any nested sensitive keys
        $sanitized = [];
        foreach ($data as $key => $val) {
            if (in_array(strtolower((string) $key), self::SENSITIVE_INPUTS, true)) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($val)) {
                $sanitized[$key] = array_map(function ($item) {
                    return is_string($item) && strlen($item) > 255 ? substr($item, 0, 255) . '...' : $item;
                }, $val);
            } elseif (is_string($val)) {
                $sanitized[$key] = strlen($val) > 255 ? substr($val, 0, 255) . '...' : $val;
            } else {
                $sanitized[$key] = $val;
            }
        }

        return $sanitized;
    }
}
