<?php

declare(strict_types=1);

namespace App\Http\Controllers\FinancialReporting;

use App\Http\Controllers\Controller;
use App\Services\Accounting\Reporting\ReportBundleService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class ExecutiveReportPackageController extends Controller
{
    public function __construct(
        private readonly ReportBundleService $bundleService
    ) {}

    public function index(Request $request): View
    {
        $cutoffDate = $request->input('cutoff_date', date('Y-m-d'));
        $dossier = $this->bundleService->compileExecutiveDossier($cutoffDate);

        $viewName = view()->exists('accounting.reports.executive.index')
            ? 'accounting.reports.executive.index'
            : 'financial-reporting.executive-reports';

        return view($viewName, $dossier);
    }
}
