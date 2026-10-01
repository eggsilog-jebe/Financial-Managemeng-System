<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class AuditLogController extends Controller
{
    public function __construct(
        private readonly \App\Services\Cache\MultiLayerCacheService $cacheService,
    ) {}

    /**
     * Display the filterable System Audit Trail log viewer.
     */
    public function index(Request $request): View
    {
        $event = $request->string('event')->trim()->value() ?: null;
        $module = $request->string('module')->trim()->value() ?: null;
        $role = $request->string('role')->trim()->value() ?: null;
        $search = $request->string('search')->trim()->value() ?: null;
        $dateFrom = $request->string('date_from')->trim()->value() ?: null;
        $dateTo = $request->string('date_to')->trim()->value() ?: null;

        $query = ActivityLog::query()
            ->ofEvent($event)
            ->ofModule($module)
            ->ofRole($role)
            ->search($search)
            ->dateRange($dateFrom, $dateTo)
            ->latest('id');

        $logs = $query->paginate(25)->withQueryString();

        // High-level KPI telemetry cached with multi-layer single-flight lock
        $today = now()->toDateString();
        $stats = $this->cacheService->remember("audit:kpi_stats:{$today}", 60, function () use ($today): array {
            return [
                'total_logs'       => ActivityLog::count(),
                'logins_today'     => ActivityLog::where('event', 'login')->whereDate('created_at', $today)->count(),
                'failed_logins'    => ActivityLog::where('event', 'failed_login')->whereDate('created_at', $today)->count(),
                'mutations_today'  => ActivityLog::whereDate('created_at', $today)->count(),
            ];
        }, ['audit'], 30);

        $acknowledgedAt = session('security_alert_acknowledged_at');


        // Curated, structured event categories for high-signal auditing
        $definedEventGroups = [
            'Security & Authentication' => [
                'login'                       => 'Login (Success)',
                'failed_login'                => 'Failed Login Attempt',
                'logout'                      => 'Logout',
                'session_displaced'           => 'Session Displaced (Concurrent Login)',
                'session_displaced_logged_out'=> 'Displaced Session Terminated',
            ],
            'Two-Factor Authentication (2FA)' => [
                '2fa_passed'                  => '2FA Verification Success',
                '2fa_failed'                  => '2FA Verification Failed',
                '2fa_totp_enrolled'           => '2FA TOTP Enrolled',
                '2fa_totp_reprovisioned'      => '2FA TOTP Reset / Re-enrolled',
                'email_otp_sent'              => 'Email OTP Dispatched',
                'workstation_auth_requested'  => 'Workstation Authorization Requested',
                'workstation_approved'        => 'Workstation Approved',
                'workstation_revoked'         => 'Workstation Revoked',
            ],
            'Data Mutations & Actions' => [
                'created'                     => 'Record Created',
                'updated'                     => 'Record Updated',
                'submitted'                   => 'Transaction / Batch Submitted',
                'approved'                    => 'Transaction / Approval Granted',
                'revoked'                     => 'Record / Access Revoked',
                'toggled'                     => 'Setting / Status Toggled',
            ],
            'Audit & Inspection' => [
                'viewed'                      => 'Record / Page Viewed',
            ],
        ];

        // Unique filter options for the filter bar cached for 10 minutes (stored as pure primitive arrays)
        $filterOptions = $this->cacheService->remember('audit:filter_options_v3', 600, function (): array {
            return [
                'modules' => ActivityLog::distinct()->whereNotNull('module')->pluck('module')->filter()->sort()->values()->all(),
                'events'  => ActivityLog::distinct()->whereNotNull('event')->pluck('event')->filter()->sort()->values()->all(),
                'roles'   => ActivityLog::distinct()->whereNotNull('user_role')->pluck('user_role')->filter()->sort()->values()->all(),
            ];
        }, ['audit'], 120);

        $modules = is_iterable($filterOptions['modules'] ?? null) ? $filterOptions['modules'] : [];
        $rawEvents = is_iterable($filterOptions['events'] ?? null) ? $filterOptions['events'] : [];
        $roles   = is_iterable($filterOptions['roles'] ?? null) ? $filterOptions['roles'] : [];

        // Build organized event groups for Blade dropdown, including any miscellaneous logged events
        $eventGroups = [];
        $mappedKeys = [];

        foreach ($definedEventGroups as $groupLabel => $items) {
            $groupEntries = [];
            foreach ($items as $evKey => $evDisplay) {
                if (in_array($evKey, $rawEvents, true)) {
                    $groupEntries[$evKey] = $evDisplay;
                    $mappedKeys[] = $evKey;
                }
            }
            if (!empty($groupEntries)) {
                $eventGroups[$groupLabel] = $groupEntries;
            }
        }

        // Catch-all for any other dynamic events
        $unmapped = array_diff($rawEvents, $mappedKeys);
        if (!empty($unmapped)) {
            $otherGroup = [];
            foreach ($unmapped as $uEv) {
                if (is_string($uEv) && $uEv !== '') {
                    $otherGroup[$uEv] = ucfirst(str_replace('_', ' ', $uEv));
                }
            }
            if (!empty($otherGroup)) {
                $eventGroups['Other Events'] = $otherGroup;
            }
        }

        $unacknowledgedFailedLogins = ActivityLog::where('event', 'failed_login')
            ->whereDate('created_at', $today)
            ->when($acknowledgedAt, fn ($q) => $q->where('created_at', '>', $acknowledgedAt))
            ->count();

        return view('accounting.audit-log', compact(
            'logs',
            'stats',
            'modules',
            'eventGroups',
            'roles',
            'event',
            'module',
            'role',
            'search',
            'dateFrom',
            'dateTo',
            'acknowledgedAt',
            'unacknowledgedFailedLogins'
        ));
    }

    /**
     * Acknowledge the failed logins / security alerts for the current session.
     */
    public function acknowledgeAlert(Request $request): \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $request->session()->put('security_alert_acknowledged_at', now()->toIso8601String());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Security alert acknowledged.',
            ]);
        }

        return back()->with('success', 'Security alert acknowledged. The dashboard and audit trail warnings have been dismissed.');
    }
}

