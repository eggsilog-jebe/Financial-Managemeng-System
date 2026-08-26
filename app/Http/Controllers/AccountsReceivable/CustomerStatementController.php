<?php

declare(strict_types=1);

namespace App\Http\Controllers\AccountsReceivable;

use App\Http\Controllers\Controller;
use App\Models\PatientAccount;
use App\Services\Accounting\CustomerStatementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class CustomerStatementController extends Controller
{
    public function __construct(
        private readonly CustomerStatementService $statementService,
    ) {}

    public function index(Request $request): View
    {
        $patientId = $request->query('patient_id');
        $startDate = $request->query('start_date', date('Y-01-01'));
        $endDate = $request->query('end_date', date('Y-m-d'));

        $accounts = PatientAccount::orderBy('full_name')->get();
        $selectedAccount = null;
        $statement = null;

        if ($patientId) {
            $selectedAccount = PatientAccount::find((int) $patientId);
            if ($selectedAccount) {
                $statement = $this->statementService->generateStatement($selectedAccount->id, $startDate, $endDate);
            }
        }

        return view('accounts-receivable.customer-statements', compact(
            'accounts',
            'selectedAccount',
            'statement',
            'patientId',
            'startDate',
            'endDate',
        ));
    }

    public function print(Request $request): View
    {
        $patientId = (int) $request->query('patient_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $statement = $this->statementService->generateStatement($patientId, $startDate, $endDate);

        return view('accounts-receivable.statement-print', compact('statement'));
    }

    public function export(Request $request): StreamedResponse
    {
        $patientId = (int) $request->query('patient_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        return $this->statementService->exportStatementCsv($patientId, $startDate, $endDate);
    }
}
