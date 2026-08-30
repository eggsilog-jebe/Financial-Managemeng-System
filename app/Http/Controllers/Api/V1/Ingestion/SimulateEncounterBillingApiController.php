<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Ingestion;

use App\Http\Controllers\Controller;
use App\Models\PatientAccount;
use App\Services\Accounting\InvoiceBillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class SimulateEncounterBillingApiController extends Controller
{
    /**
     * Ingest external SPRS/BDMS clinical encounter bill via Postman HTTP POST.
     * Route: POST /api/v1/ingest/encounter-billing
     */
    public function __invoke(Request $request, InvoiceBillingService $billingService): JsonResponse
    {
        $validated = $request->validate([
            'patient_name'        => ['required', 'string', 'max:255'],
            'date_of_birth'       => ['nullable', 'date'],
            'gender'              => ['nullable', 'string', 'in:Male,Female,Other,MALE,FEMALE'],
            'admission_type'      => ['required', 'string', 'in:INPATIENT,OUTPATIENT,EMERGENCY'],
            'discount_category'   => ['nullable', 'string', 'in:NONE,SENIOR_CITIZEN,PWD,EMPLOYEE_SUBSIDY,CHARITY'],
            'id_card_number'      => ['nullable', 'string', 'max:50'],
            'hmo_provider'        => ['nullable', 'string', 'max:100'],
            'phone'               => ['nullable', 'string', 'max:50'],
            'email'               => ['nullable', 'email', 'max:100'],
            'address'             => ['nullable', 'string', 'max:255'],
            'philhealth_amount'   => ['nullable', 'numeric', 'min:0'],
            'hmo_limit'           => ['nullable', 'numeric', 'min:0'],
            'items'               => ['nullable', 'array'],
            'items.*.department'  => ['required', 'string', 'max:50'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity'    => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'  => ['required', 'numeric', 'min:0'],
        ]);

        $name = $validated['patient_name'];
        $dob = $validated['date_of_birth'] ?? '1985-06-15';
        $gender = ucfirst(strtolower((string) ($validated['gender'] ?? 'Female')));
        $type = strtoupper((string) $validated['admission_type']);
        $discount = strtoupper((string) ($validated['discount_category'] ?? 'NONE'));
        $idNumber = $validated['id_card_number'] ?? null;
        $hmoProvider = ! empty($validated['hmo_provider']) && $validated['hmo_provider'] !== 'None' ? $validated['hmo_provider'] : null;
        $phone = $validated['phone'] ?? ('+63 917 ' . rand(100, 999) . ' ' . rand(1000, 9999));
        $email = $validated['email'] ?? (strtolower(str_replace(' ', '.', (string) $name)) . '@hospital.test');
        $address = $validated['address'] ?? 'Metro Manila, Philippines';
        $philhealth = (string) ($validated['philhealth_amount'] ?? ($type === 'INPATIENT' ? '15000.00' : '0.00'));
        $hmoLimit = (string) ($validated['hmo_limit'] ?? ($hmoProvider ? '30000.00' : '0.00'));

        $items = $validated['items'] ?? null;
        if (empty($items)) {
            if ($type === 'INPATIENT') {
                $items = [
                    ['department' => 'ROOM_AND_BOARD', 'description' => 'Inpatient Room & Board (3 Days)', 'quantity' => 3, 'unit_price' => 3500.00],
                    ['department' => 'PHARMACY', 'description' => 'IV Fluids & Antibiotics Package', 'quantity' => 1, 'unit_price' => 4500.00],
                    ['department' => 'LABORATORY', 'description' => 'Complete Blood Count & Blood Chem Panel', 'quantity' => 1, 'unit_price' => 2200.00],
                    ['department' => 'RADIOLOGY', 'description' => 'Chest X-Ray PA View', 'quantity' => 1, 'unit_price' => 1200.00],
                ];
            } else {
                $items = [
                    ['department' => 'CONSULTATION', 'description' => 'Specialist Consultation Fee', 'quantity' => 1, 'unit_price' => 1500.00],
                    ['department' => 'LABORATORY', 'description' => 'Routine Urinalysis & Fecalysis', 'quantity' => 1, 'unit_price' => 650.00],
                ];
            }
        }

        $result = DB::transaction(function () use ($name, $dob, $gender, $type, $discount, $idNumber, $hmoProvider, $phone, $email, $address, $philhealth, $hmoLimit, $items, $billingService): array {
            $patient = PatientAccount::create([
                'patient_id_number' => 'MRN-2026-' . strtoupper(substr(uniqid(), -5)),
                'full_name'         => $name,
                'date_of_birth'     => $dob,
                'gender'            => $gender,
                'admission_type'    => $type,
                'discount_category' => $discount,
                'id_card_number'    => $idNumber,
                'hmo_provider'      => $hmoProvider,
                'phone'             => $phone,
                'email'             => $email,
                'address'           => $address,
                'status'            => 'Active',
            ]);

            return $billingService->createAndPostEncounterInvoice([
                'patient_account_id' => $patient->id,
                'invoice_date'       => now()->toDateString(),
                'statutory_discount' => $discount,
                'osca_pwd_id'         => $idNumber,
                'philhealth_amount'  => $philhealth,
                'hmo_provider'       => $hmoProvider,
                'hmo_amount'          => $hmoLimit,
                'items'               => $items,
            ]);
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Clinical encounter ingested & posted to General Ledger successfully.',
            'data'    => [
                'invoice_number'     => $result['invoice']->invoice_number,
                'patient_mrn'        => $result['invoice']->patientAccount?->patient_id_number,
                'patient_name'       => $result['invoice']->patientAccount?->full_name,
                'admission_type'     => $result['invoice']->patientAccount?->admission_type,
                'total_gross'        => (float) $result['invoice']->total_amount,
                'statutory_discount' => (float) $result['invoice']->discount_amount,
                'philhealth_benefit' => (float) ($result['invoice']->philhealthClaim?->total_case_rate_amount ?? 0),
                'hmo_coverage'       => (float) ($result['invoice']->hmoClaims->sum('claimed_amount') ?? 0),
                'patient_copay_due'  => (float) $result['invoice']->patient_payable,
                'status'             => $result['invoice']->status,
            ],
        ], Response::HTTP_CREATED);
    }
}
