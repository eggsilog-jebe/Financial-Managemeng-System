<?php

declare(strict_types=1);

namespace App\Http\Controllers\AccountsReceivable;

use App\Http\Controllers\Controller;
use App\Models\GuaranteeLetter;
use App\Models\PatientAccount;
use App\Services\Accounting\MalasakitBillComputationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class MalasakitAssistanceController extends Controller
{
    public function __construct(
        private readonly MalasakitBillComputationService $waterfallService,
    ) {}

    /**
     * Display the Malasakit Center Financial Assistance & Guarantee Letter registry.
     */
    public function index(Request $request): View
    {
        $agency = $request->string('agency')->trim()->value() ?: null;
        $status = $request->string('status')->trim()->value() ?: null;
        $search = $request->string('search')->trim()->value() ?: null;

        $query = GuaranteeLetter::query()
            ->with(['patientAccount', 'creator'])
            ->byAgency($agency);

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('gl_number', 'like', "%{$search}%")
                    ->orWhere('diagnosis', 'like', "%{$search}%")
                    ->orWhereHas('patientAccount', function ($pq) use ($search) {
                        $pq->where('full_name', 'like', "%{$search}%")
                            ->orWhere('patient_id_number', 'like', "%{$search}%");
                    });
            });
        }

        $letters = $query->latest('id')->paginate(15)->withQueryString();

        // Financial KPIs for Public Hospital Assistance
        $totalAuthorized = GuaranteeLetter::sum('authorized_amount');
        $totalUtilized = GuaranteeLetter::sum('utilized_amount');
        $totalRemaining = GuaranteeLetter::where('status', 'ACTIVE')->sum('remaining_amount');
        $activeGlCount = GuaranteeLetter::where('status', 'ACTIVE')->count();

        // Patients for the encoding modal dropdown
        $patients = PatientAccount::select('id', 'full_name', 'patient_id_number', 'discount_category', 'is_nbb')
            ->orderBy('full_name')
            ->get();

        return view('accounts-receivable.malasakit-assistance', compact(
            'letters',
            'patients',
            'totalAuthorized',
            'totalUtilized',
            'totalRemaining',
            'activeGlCount',
            'agency',
            'status',
            'search'
        ));
    }

    /**
     * Store a newly issued Guarantee Letter from PCSO, DSWD, or DOH MAIP.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'gl_number'          => ['required', 'string', 'max:60', 'unique:guarantee_letters,gl_number'],
            'issuing_agency'     => ['required', 'string', 'in:PCSO,DSWD,DOH_MAIP,LGU,OTHER'],
            'patient_account_id' => ['required', 'exists:patient_accounts,id'],
            'authorized_amount'  => ['required', 'numeric', 'min:0.01'],
            'issued_date'        => ['required', 'date'],
            'valid_until'        => ['nullable', 'date', 'after_or_equal:issued_date'],
            'diagnosis'          => ['nullable', 'string', 'max:255'],
            'remarks'            => ['nullable', 'string'],
        ]);

        $amount = number_format((float) $validated['authorized_amount'], 4, '.', '');

        GuaranteeLetter::create([
            'gl_number'          => strtoupper(trim($validated['gl_number'])),
            'issuing_agency'     => $validated['issuing_agency'],
            'patient_account_id' => (int) $validated['patient_account_id'],
            'authorized_amount'  => $amount,
            'utilized_amount'    => '0.0000',
            'remaining_amount'   => $amount,
            'status'             => 'ACTIVE',
            'issued_date'        => $validated['issued_date'],
            'valid_until'        => $validated['valid_until'] ?? null,
            'diagnosis'          => $validated['diagnosis'] ?? null,
            'remarks'            => $validated['remarks'] ?? null,
            'created_by'         => auth()->id(),
        ]);

        return redirect()->route('ar.malasakit.index')
            ->with('success', "Guarantee Letter [{$validated['gl_number']}] for ₱" . number_format((float) $amount, 2) . " successfully registered under Malasakit assistance.");
    }

    /**
     * Interactive API endpoint for the real-time Malasakit Bill Waterfall Calculator.
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_account_id' => ['required', 'exists:patient_accounts,id'],
            'gross_amount'       => ['required', 'numeric', 'min:0'],
            'philhealth_amount'  => ['nullable', 'numeric', 'min:0'],
            'hmo_amount'         => ['nullable', 'numeric', 'min:0'],
        ]);

        $patient = PatientAccount::findOrFail((int) $validated['patient_account_id']);
        $gross = number_format((float) $validated['gross_amount'], 4, '.', '');
        $philhealth = number_format((float) ($validated['philhealth_amount'] ?? 0), 4, '.', '');
        $hmo = number_format((float) ($validated['hmo_amount'] ?? 0), 4, '.', '');

        $dummyItems = [
            [
                'description' => 'Hospital Inpatient & Pharmacy Charges',
                'quantity'    => '1',
                'unit_price'  => $gross,
                'is_vatable'  => true,
                'is_eligible' => true,
            ],
        ];

        $waterfall = $this->waterfallService->computeWaterfall(
            patient: $patient,
            items: $dummyItems,
            philhealthPrimaryCaseRate: $philhealth,
            hmoApprovedLimit: $hmo
        );

        return response()->json([
            'patient'   => [
                'name'              => $patient->full_name,
                'mrn'               => $patient->patient_id_number,
                'discount_category' => $patient->discount_category,
                'is_nbb'            => (bool) $patient->is_nbb,
                'patient_type'      => $patient->patient_type,
            ],
            'waterfall' => $waterfall,
        ]);
    }
}
