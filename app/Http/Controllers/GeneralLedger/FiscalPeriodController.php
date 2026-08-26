<?php

declare(strict_types=1);

namespace App\Http\Controllers\GeneralLedger;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\InitializeFiscalYearRequest;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Services\Accounting\PeriodClosingService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class FiscalPeriodController extends Controller
{
    public function __construct(
        private readonly PeriodClosingService $periodClosingService,
    ) {}

    public function index(Request $request): View
    {
        $fiscalYear = $request->query('fiscal_year') ?? (string) date('Y');

        $periods = FiscalPeriod::with(['closedByUser', 'closingJournalEntry.lines.account'])
            ->where('fiscal_year', $fiscalYear)
            ->orderBy('period_number')
            ->get();

        // If no periods found for current year, auto-initialize
        if ($periods->isEmpty()) {
            $this->periodClosingService->initializeFiscalYear($fiscalYear, $request->user()?->id);
            $periods = FiscalPeriod::with(['closedByUser', 'closingJournalEntry.lines.account'])
                ->where('fiscal_year', $fiscalYear)
                ->orderBy('period_number')
                ->get();
        }

        $allYears = FiscalPeriod::select('fiscal_year')->distinct()->orderByDesc('fiscal_year')->pluck('fiscal_year')->toArray();
        if (! in_array($fiscalYear, $allYears, true)) {
            $allYears[] = $fiscalYear;
            sort($allYears);
        }

        $activePeriod = now()->format('F Y');
        $unpostedEntriesCount = JournalEntry::where('status', '!=', 'POSTED')->count();
        $totalEntriesCount = JournalEntry::count();

        return view('general-ledger.period-end-closing', [
            'periods'              => $periods,
            'selectedYear'         => $fiscalYear,
            'allYears'             => $allYears,
            'activePeriod'         => $activePeriod,
            'unpostedEntriesCount' => $unpostedEntriesCount,
            'totalEntriesCount'    => $totalEntriesCount,
        ]);
    }

    public function initialize(InitializeFiscalYearRequest $request): Response
    {
        $year = (string) $request->validated('fiscal_year');
        $periods = $this->periodClosingService->initializeFiscalYear($year, $request->user()?->id);

        $msg = "Fiscal Year {$year} initialized with 12 monthly periods.";

        if ($request->wantsJson()) {
            return response()->json(['message' => $msg, 'periods' => $periods], 201);
        }

        return redirect()->route('gl.period-end-closing', ['fiscal_year' => $year])
            ->with('success', $msg);
    }

    public function lock(Request $request, int $id): Response
    {
        $user = $request->user();
        $role = $user?->role ?? 'StaffAccountant';

        if (! in_array($role, ['FinanceManager', 'CFO', 'FinanceDirector'], true) && $user !== null) {
            abort(403, "Access Denied: Only Finance Managers and CFOs can lock fiscal periods.");
        }

        try {
            $period = $this->periodClosingService->lockPeriod($id, (int) ($user?->id ?? 1));
        } catch (DomainException $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }

        $msg = "Fiscal Period [{$period->period_code}] has been LOCKED.";

        if ($request->wantsJson()) {
            return response()->json(['message' => $msg, 'period' => $period]);
        }

        return redirect()->route('gl.period-end-closing', ['fiscal_year' => $period->fiscal_year])
            ->with('success', $msg);
    }

    public function close(Request $request, int $id): Response
    {
        $user = $request->user();
        $role = $user?->role ?? 'StaffAccountant';

        if (! in_array($role, ['CFO', 'FinanceDirector'], true) && $user !== null) {
            abort(403, "Access Denied: Segregation of Duties requires CFO or Finance Director authorization to execute a hard period close.");
        }

        try {
            $result = $this->periodClosingService->closePeriodAndRollover($id, (int) ($user?->id ?? 1));
        } catch (DomainException $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }

        $period = $result['period'];
        $netIncomeFormatted = number_format((float) $result['net_income'], 2);
        $msg = "Fiscal Period [{$period->period_code}] CLOSED and AUDITED. Closing journal entry generated (Net Income Rollover: ₱{$netIncomeFormatted}).";

        if ($request->wantsJson()) {
            return response()->json(['message' => $msg, 'result' => $result]);
        }

        return redirect()->route('gl.period-end-closing', ['fiscal_year' => $period->fiscal_year])
            ->with('success', $msg);
    }
}
