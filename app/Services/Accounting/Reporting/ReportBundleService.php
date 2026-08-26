<?php

declare(strict_types=1);

namespace App\Services\Accounting\Reporting;

final class ReportBundleService
{
    public function __construct(
        private readonly BalanceSheetService $balanceSheetService,
        private readonly ProfitAndLossService $pnlService,
        private readonly CashFlowService $cashFlowService,
        private readonly FinancialKpiService $kpiService,
    ) {}

    /**
     * Compile Consolidated Executive Financial Report Dossier.
     */
    public function compileExecutiveDossier(?string $cutoffDate = null): array
    {
        $cutoff = $cutoffDate ?: date('Y-m-d');
        $startOfYear = date('Y-01-01', strtotime($cutoff));

        $balanceSheet = $this->balanceSheetService->getBalanceSheetData($cutoff);
        $pnl = $this->pnlService->getProfitAndLossData($startOfYear, $cutoff);
        $cashFlow = $this->cashFlowService->getCashFlowData($startOfYear, $cutoff);
        $kpis = $this->kpiService->getKpiMetrics();

        return [
            'hospital_name'   => 'St. Jude General Hospital & Medical Center',
            'hospital_tin'    => '004-982-114-000-VAT',
            'dossier_title'   => 'Executive Financial & Operational Dossier',
            'fiscal_year'     => date('Y', strtotime($cutoff)),
            'as_of_date'      => $cutoff,
            'period_covered'  => "From {$startOfYear} To {$cutoff}",
            'generated_at'    => date('Y-m-d H:i:s'),
            'balance_sheet'   => $balanceSheet['current'],
            'profit_and_loss' => $pnl,
            'cash_flow'       => $cashFlow,
            'kpis'            => $kpis,
            'signatories'     => [
                ['role' => 'Chief Accountant / Comptroller', 'name' => 'Maria Santos, CPA', 'title' => 'Comptroller & Financial Reporting Officer'],
                ['role' => 'Finance Director', 'name' => 'Arthur Pendelton, MBA', 'title' => 'Director of Financial Operations'],
                ['role' => 'Chief Financial Officer', 'name' => 'Dr. Victoria Valderama, MD, CFO', 'title' => 'Executive VP & Chief Financial Officer'],
            ],
        ];
    }
}
