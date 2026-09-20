<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\BankAccount;
use App\Models\BankDeposit;
use App\Models\BudgetAllocation;
use App\Models\GuaranteeLetter;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\PhilhealthClaim;
use App\Models\PurchaseBill;
use App\Services\Accounting\GeneralLedgerReportService;
use Illuminate\Contracts\View\View;

final class FinancialDashboardController extends Controller
{
    public function __invoke(GeneralLedgerReportService $reportService): View
    {
        $pnl = $reportService->getIncomeStatement();
        $balanceSheet = $reportService->getBalanceSheet();

        // 1. Core Financial Ledger Overview
        $totalRevenue = (float) $pnl['total_revenue'];

        $cashOnHand = 0.0;
        $cashInBank = 0.0;
        foreach ($balanceSheet['assets'] as $asset) {
            if (in_array($asset['code'], ['1010', '1011'], true)) {
                $cashOnHand += (float) $asset['balance'];
            } elseif (in_array($asset['code'], ['1020', '1021'], true)) {
                $cashInBank += (float) $asset['balance'];
            }
        }

        $outstandingAR = (float) Invoice::whereIn('status', ['UNPAID', 'PARTIAL'])->sum('patient_payable');
        $outstandingAP = (float) PurchaseBill::whereIn('status', ['UNPAID', 'PARTIAL'])->sum('total_amount');

        // Recent Journal Postings with lines and accounts eager-loaded
        $recentJournals = JournalEntry::with(['lines.account'])
            ->latest('id')
            ->limit(6)
            ->get();

        // 2. Public Hospital Fund-Source Utilization Telemetry (PhilHealth vs. Malasakit vs. Direct Copay)
        $philhealthTotal = (float) PhilhealthClaim::sum('total_case_rate_amount');
        $philhealthCount = PhilhealthClaim::count();

        $malasakitGlTotal = (float) GuaranteeLetter::sum('authorized_amount');
        $malasakitGlUtilized = (float) GuaranteeLetter::sum('utilized_amount');
        $malasakitRemaining = (float) GuaranteeLetter::sum('remaining_amount');
        $malasakitCount = GuaranteeLetter::count();

        $directCopayTotal = (float) Payment::sum('amount');
        $directCopayCount = Payment::count();

        $totalFundSources = $philhealthTotal + $malasakitGlUtilized + $directCopayTotal;
        $philhealthSharePct = $totalFundSources > 0 ? round(($philhealthTotal / $totalFundSources) * 100, 1) : 0.0;
        $malasakitSharePct = $totalFundSources > 0 ? round(($malasakitGlUtilized / $totalFundSources) * 100, 1) : 0.0;
        $directCopaySharePct = $totalFundSources > 0 ? round(($directCopayTotal / $totalFundSources) * 100, 1) : 0.0;
        $socialCoveragePct = $totalFundSources > 0 ? round((($philhealthTotal + $malasakitGlUtilized) / $totalFundSources) * 100, 1) : 0.0;

        // 3. Departmental Budget Exhaustion & Fiscal Burn-Rate Indicators
        $budgetAllocations = BudgetAllocation::with('encumbrances')
            ->orderByDesc('spent_amount')
            ->get();

        $totalBudgetAllocated = (float) $budgetAllocations->sum('allocated_amount');
        $totalBudgetSpent = (float) $budgetAllocations->sum('spent_amount');
        $totalBudgetRemaining = (float) $budgetAllocations->sum('remaining_balance');
        $overallBurnRate = $totalBudgetAllocated > 0 ? round(($totalBudgetSpent / $totalBudgetAllocated) * 100, 1) : 0.0;

        // Critical alerts: Departments with burn rate >= 85%
        $criticalBudgets = $budgetAllocations->filter(function (BudgetAllocation $b): bool {
            return $b->burn_rate >= 85.0;
        });

        // 4. Daily Collection & Intact Deposit Telemetry (COA Circular No. 2021-014 Compliance)
        $totalCashCollections = (float) Payment::where('payment_method', 'CASH')->sum('amount');
        $totalDeposited = (float) BankDeposit::whereIn('status', ['DEPOSITED', 'RECONCILED'])->sum('total_deposited');
        $pendingDeposits = (float) BankDeposit::where('status', 'PREPARED')->sum('total_deposited');
        $undepositedCollections = max(0.0, $totalCashCollections - $totalDeposited);

        // COA Circular No. 2021-014 Intact compliance check (undeposited cash should be deposited intact daily)
        $isCoaIntactCompliant = ($undepositedCollections <= 10000.0);

        // 5. System Security & Immutable Audit Trail Telemetry
        $recentAuditLogs = ActivityLog::latest('id')->limit(5)->get();
        $failedLoginsToday = ActivityLog::where('event', 'failed_login')
            ->whereDate('created_at', now()->toDateString())
            ->count();
        $mutationsToday = ActivityLog::whereIn('event', ['created', 'updated', 'deleted', 'posted', 'reversed'])
            ->whereDate('created_at', now()->toDateString())
            ->count();

        // 6. User Profile Context
        $currentUser = auth()->user();
        $userRole = $currentUser?->role ?? 'StaffAccountant';

        return view('accounting.dashboard', [
            // Core Financial KPIs
            'totalRevenue'            => $totalRevenue,
            'cashOnHand'              => $cashOnHand,
            'cashInBank'              => $cashInBank,
            'outstandingAR'           => $outstandingAR,
            'outstandingAP'           => $outstandingAP,
            'recentJournals'          => $recentJournals,
            'isBalanced'              => $balanceSheet['is_balanced'],

            // Fund-Source Utilization Telemetry
            'philhealthTotal'         => $philhealthTotal,
            'philhealthCount'         => $philhealthCount,
            'malasakitGlTotal'        => $malasakitGlTotal,
            'malasakitGlUtilized'     => $malasakitGlUtilized,
            'malasakitRemaining'      => $malasakitRemaining,
            'malasakitCount'          => $malasakitCount,
            'directCopayTotal'        => $directCopayTotal,
            'directCopayCount'        => $directCopayCount,
            'totalFundSources'        => $totalFundSources,
            'philhealthSharePct'      => $philhealthSharePct,
            'malasakitSharePct'       => $malasakitSharePct,
            'directCopaySharePct'     => $directCopaySharePct,
            'socialCoveragePct'       => $socialCoveragePct,

            // Budget Exhaustion & Burn-Rate Indicators
            'budgetAllocations'       => $budgetAllocations,
            'totalBudgetAllocated'    => $totalBudgetAllocated,
            'totalBudgetSpent'        => $totalBudgetSpent,
            'totalBudgetRemaining'    => $totalBudgetRemaining,
            'overallBurnRate'         => $overallBurnRate,
            'criticalBudgets'         => $criticalBudgets,

            // COA Circular 2021-014 Daily Intact Deposit Status
            'totalCashCollections'    => $totalCashCollections,
            'totalDeposited'          => $totalDeposited,
            'pendingDeposits'         => $pendingDeposits,
            'undepositedCollections'  => $undepositedCollections,
            'isCoaIntactCompliant'    => $isCoaIntactCompliant,

            // Security & Audit Telemetry
            'recentAuditLogs'         => $recentAuditLogs,
            'failedLoginsToday'       => $failedLoginsToday,
            'mutationsToday'          => $mutationsToday,
            'currentUser'             => $currentUser,
            'userRole'                => $userRole,
        ]);
    }
}
