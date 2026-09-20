<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class AuditLogController extends Controller
{
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

        // High-level KPI telemetry for CFO/Auditor summary cards
        $today = now()->toDateString();
        $stats = [
            'total_logs'       => ActivityLog::count(),
            'logins_today'     => ActivityLog::where('event', 'login')->whereDate('created_at', $today)->count(),
            'failed_logins'    => ActivityLog::where('event', 'failed_login')->whereDate('created_at', $today)->count(),
            'mutations_today'  => ActivityLog::whereIn('event', ['created', 'updated', 'deleted', 'posted', 'reversed'])
                                    ->whereDate('created_at', $today)
                                    ->count(),
        ];

        // Unique filter options for the filter bar
        $modules = ActivityLog::distinct()->whereNotNull('module')->pluck('module')->sort()->values();
        $events = ActivityLog::distinct()->whereNotNull('event')->pluck('event')->sort()->values();
        $roles = ActivityLog::distinct()->whereNotNull('user_role')->pluck('user_role')->sort()->values();

        return view('accounting.audit-log', compact(
            'logs',
            'stats',
            'modules',
            'events',
            'roles',
            'event',
            'module',
            'role',
            'search',
            'dateFrom',
            'dateTo'
        ));
    }
}
