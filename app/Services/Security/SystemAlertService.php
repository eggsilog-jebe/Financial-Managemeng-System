<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Models\ActivityLog;
use App\Models\BudgetAllocation;
use App\Models\User;
use App\Models\UserWorkstation;
use Illuminate\Http\Request;

final class SystemAlertService
{
    /**
     * Compile real-time system, security, and administrative alerts for the authenticated user.
     *
     * @return array{
     *     alerts: array<int, array<string, mixed>>,
     *     count: int,
     *     has_alerts: bool,
     *     highest_severity: string,
     *     unacknowledged_failed_logins: int,
     *     pending_workstations: int
     * }
     */
    public function getLiveAlertsForUser(?User $user, ?string $acknowledgedAt = null): array
    {
        $alerts = [];
        $unacknowledgedFailedLogins = 0;
        $pendingWorkstations = 0;

        // 1. Security Alert: Failed Logins Telemetry
        try {
            $today = now()->toDateString();
            $totalFailedLoginsToday = ActivityLog::where('event', 'failed_login')
                ->whereDate('created_at', $today)
                ->count();

            $unacknowledgedFailedLogins = $totalFailedLoginsToday;
            if ($acknowledgedAt) {
                $unacknowledgedFailedLogins = ActivityLog::where('event', 'failed_login')
                    ->whereDate('created_at', $today)
                    ->where('created_at', '>', $acknowledgedAt)
                    ->count();
            }

            if ($unacknowledgedFailedLogins > 0) {
                $isCritical = $unacknowledgedFailedLogins >= 5;
                $alerts[] = [
                    'id'              => 'failed_logins',
                    'type'            => 'security',
                    'severity'        => $isCritical ? 'critical' : 'warning',
                    'title'           => "Security Alert — {$totalFailedLoginsToday} Failed Login Attempt" . ($totalFailedLoginsToday !== 1 ? 's' : '') . ' Today',
                    'description'     => 'Review the Audit Hub immediately for suspicious authentication patterns.',
                    'count'           => $unacknowledgedFailedLogins,
                    'time'            => 'Today',
                    'icon'            => 'ph-shield-warning',
                    'action_url'      => route('accounting.audit-log'),
                    'action_text'     => 'View Audit Hub',
                    'can_acknowledge' => true,
                    'acknowledge_url' => route('system-alerts.acknowledge'),
                ];
            }
        } catch (\Throwable) {
            // Gracefully ignore database connection issues
        }

        // 2. Terminal Security: Pending Workstation Authorization Requests
        if ($user && in_array($user->role, ['CFO', 'FinanceDirector', 'SuperAdmin'], true)) {
            try {
                $pendingWorkstations = UserWorkstation::where('status', UserWorkstation::STATUS_PENDING)->count();
                if ($pendingWorkstations > 0) {
                    $alerts[] = [
                        'id'              => 'pending_workstations',
                        'type'            => 'terminal',
                        'severity'        => 'warning',
                        'title'           => "{$pendingWorkstations} Workstation Authorization Request" . ($pendingWorkstations !== 1 ? 's' : '') . ' Pending',
                        'description'     => 'Unauthorized terminals are requesting access to the hospital financial system.',
                        'count'           => $pendingWorkstations,
                        'time'            => 'Pending review',
                        'icon'            => 'ph-desktop',
                        'action_url'      => route('user-security.workstations'),
                        'action_text'     => 'Review Requests',
                        'can_acknowledge' => false,
                        'acknowledge_url' => null,
                    ];
                }
            } catch (\Throwable) {
                // Gracefully ignore database connection issues
            }
        }

        // 3. Fiscal Budget Alert: Departments nearing or exceeding budget threshold (>85%)
        if ($user && in_array($user->role, ['CFO', 'FinanceDirector', 'FinanceManager', 'StaffAccountant'], true)) {
            try {
                $criticalBudgets = BudgetAllocation::where('fiscal_year', '2026')
                    ->get()
                    ->filter(function (BudgetAllocation $budget) {
                        return bccomp((string) $budget->allocated_amount, '0.0000', 4) > 0
                            && (float) bcdiv((string) $budget->spent_amount, (string) $budget->allocated_amount, 4) >= 0.85;
                    });

                if ($criticalBudgets->isNotEmpty()) {
                    $deptNames = $criticalBudgets->pluck('department_name')->take(2)->join(', ');
                    if ($criticalBudgets->count() > 2) {
                        $deptNames .= ' +' . ($criticalBudgets->count() - 2) . ' more';
                    }

                    $alerts[] = [
                        'id'              => 'budget_burn',
                        'type'            => 'budget',
                        'severity'        => 'warning',
                        'title'           => "Budget Warning — {$criticalBudgets->count()} Department" . ($criticalBudgets->count() !== 1 ? 's' : '') . ' Over 85% Cap',
                        'description'     => "{$deptNames} approaching or exceeding allocated fiscal limits.",
                        'count'           => $criticalBudgets->count(),
                        'time'            => 'FY 2026',
                        'icon'            => 'ph-chart-pie-slice',
                        'action_url'      => route('accounting.budgets.index'),
                        'action_text'     => 'View Budgets',
                        'can_acknowledge' => false,
                        'acknowledge_url' => null,
                    ];
                }
            } catch (\Throwable) {
                // Gracefully ignore
            }
        }

        // Determine highest severity
        $highestSeverity = 'none';
        foreach ($alerts as $item) {
            if ($item['severity'] === 'critical') {
                $highestSeverity = 'critical';
                break;
            }
            if ($item['severity'] === 'warning') {
                $highestSeverity = 'warning';
            }
        }

        return [
            'alerts'                       => $alerts,
            'count'                        => count($alerts),
            'has_alerts'                   => count($alerts) > 0,
            'highest_severity'             => $highestSeverity,
            'unacknowledged_failed_logins' => $unacknowledgedFailedLogins,
            'pending_workstations'         => $pendingWorkstations,
        ];
    }
}
