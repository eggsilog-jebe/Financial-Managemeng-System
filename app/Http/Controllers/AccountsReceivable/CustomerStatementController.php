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
        $admissionType = $request->query('admission_type');
        $search = $request->query('search');
        $outstandingOnly = $request->boolean('outstanding_only');
        $startDate = $request->query('start_date', date('Y-01-01'));
        $endDate = $request->query('end_date', date('Y-m-d'));

        $query = PatientAccount::query();

        if ($admissionType && in_array(strtoupper($admissionType), ['INPATIENT', 'OUTPATIENT', 'EMERGENCY'], true)) {
            $query->whereRaw('UPPER(admission_type) = ?', [strtoupper($admissionType)]);
        }

        if ($outstandingOnly) {
            $query->where('current_balance', '>', 0);
        }

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('patient_id_number', 'LIKE', "%{$search}%");
            });
        }

        $accounts = $query->orderBy('full_name')->get();
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
            'admissionType',
            'search',
            'outstandingOnly',
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
