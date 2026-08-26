<?php

declare(strict_types=1);

namespace App\Http\Controllers\AccountsReceivable;

use App\DTOs\Accounting\PatientAccountData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StorePatientAccountRequest;
use App\Models\PatientAccount;
use App\Services\Accounting\PatientAccountService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PatientAccountController extends Controller
{
    public function __construct(
        private readonly PatientAccountService $patientService,
    ) {}

    public function index(Request $request): View
    {
        $search = $request->query('search');
        $outstandingOnly = $request->boolean('outstanding_only');

        $accounts = $this->patientService->getPatientAccountsList($search, $outstandingOnly);
        $totalReceivable = PatientAccount::sum('current_balance');
        $hmoGuarantees = PatientAccount::whereNotNull('hmo_provider')->where('hmo_provider', '!=', '')->sum('current_balance');
        $totalActive = PatientAccount::where('status', 'Active')->count();

        return view('accounts-receivable.patient-accounts', compact(
            'accounts',
            'totalReceivable',
            'hmoGuarantees',
            'totalActive',
            'search',
            'outstandingOnly',
        ));
    }

    public function store(StorePatientAccountRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $mrn = $validated['patient_mrn'] ?? $validated['patient_id_number'] ?? ('MRN-' . date('Ymd') . '-' . rand(1000, 9999));
        $validated['patient_mrn'] = $mrn;

        $dto = PatientAccountData::fromArray($validated);
        $account = $this->patientService->createPatientAccount($dto);

        return redirect()->back()->with('success', "Patient Account [{$account->full_name}] (MRN: {$account->patient_id_number}) successfully registered.");
    }
}
