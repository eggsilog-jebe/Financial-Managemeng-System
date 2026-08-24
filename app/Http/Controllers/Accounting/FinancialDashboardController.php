<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\PurchaseBill;
use App\Services\Accounting\GeneralLedgerReportService;
use Illuminate\Contracts\View\View;

final class FinancialDashboardController extends Controller
{
    public function __invoke(GeneralLedgerReportService $reportService): View
    {
        $pnl = $reportService->getIncomeStatement();
        $balanceSheet = $reportService->getBalanceSheet();

        // 1. Total Revenue
        $totalRevenue = (float) $pnl['total_revenue'];

        // 2. Cash on Hand (Account 1010 + 1011)
        $cashOnHand = 0.0;
        $cashInBank = 0.0;
        foreach ($balanceSheet['assets'] as $asset) {
            if (in_array($asset['code'], ['1010', '1011'], true)) {
                $cashOnHand += (float) $asset['balance'];
            } elseif (in_array($asset['code'], ['1020', '1021'], true)) {
                $cashInBank += (float) $asset['balance'];
            }
        }

        // 3. Outstanding AR
        $outstandingAR = (float) Invoice::whereIn('status', ['UNPAID', 'PARTIAL'])->sum('patient_payable');

        // 4. Overdue / Pending AP
        $outstandingAP = (float) PurchaseBill::whereIn('status', ['UNPAID', 'PARTIAL'])->sum('total_amount');

        // 5. Recent Journal Entries with Eager Loading to prevent N+1
        $recentJournals = JournalEntry::with(['lines.account'])
            ->latest('id')
            ->limit(8)
            ->get();

        return view('accounting.dashboard', [
            'totalRevenue'   => $totalRevenue,
            'cashOnHand'     => $cashOnHand,
            'cashInBank'     => $cashInBank,
            'outstandingAR'  => $outstandingAR,
            'outstandingAP'  => $outstandingAP,
            'recentJournals' => $recentJournals,
            'isBalanced'     => $balanceSheet['is_balanced'],
        ]);
    }
}
