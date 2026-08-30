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
        $totalReceivable = (float) \App\Models\Invoice::whereNotIn('status', ['PAID', 'SETTLED', 'CANCELLED'])->sum('patient_payable');
        $hmoGuarantees = (float) \App\Models\HmoClaim::whereNotIn('status', ['PAID', 'SETTLED', 'CANCELLED', 'REJECTED'])->sum('claimed_amount');
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
        $mrn = ! empty($validated['patient_mrn']) ? $validated['patient_mrn'] : (! empty($validated['patient_id_number']) ? $validated['patient_id_number'] : null);
        if (empty($mrn)) {
            $mrn = 'MRN-' . date('Y') . '-' . str_pad((string) rand(10000, 99999), 5, '0', STR_PAD_LEFT);
        }
        $validated['patient_mrn'] = $mrn;
        $validated['patient_id_number'] = $mrn;

        $dto = PatientAccountData::fromArray($validated);
        $account = $this->patientService->createPatientAccount($dto);

        return redirect()->back()->with('success', "Patient Account [{$account->full_name}] (MRN: {$account->patient_id_number}) successfully registered.");
    }
}
