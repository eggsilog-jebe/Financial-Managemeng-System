<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Ingestion;

use App\DTOs\ClinicalBillableItemData;
use App\DTOs\PatientBillingIngestionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IngestBdmsPatientBillRequest;
use App\Http\Resources\InvoiceResource;
use App\Services\Accounting\BillingIngestionService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class IngestBdmsPatientBillController extends Controller
{
    /**
     * Ingest patient discharge bill from BDMS / HICS subsystem.
     * Route: POST /api/v1/ingest/patient-bill
     */
    public function __invoke(
        IngestBdmsPatientBillRequest $request,
        BillingIngestionService $service
    ): JsonResponse {
        $validated = $request->validated();

        $items = array_map(
            fn (array $line) => new ClinicalBillableItemData(
                itemCode: (string) $line['item_code'],
                description: (string) $line['description'],
                department: (string) $line['department'],
                revenueCategory: (string) $line['revenue_category'],
                quantity: (string) $line['quantity'],
                unitPrice: (string) $line['unit_price'],
                isVatable: (bool) ($line['is_vatable'] ?? true),
                isSeniorPwdEligible: (bool) ($line['is_senior_eligible'] ?? true),
            ),
            $validated['charge_lines']
        );

        $dto = new PatientBillingIngestionData(
            patientAccountId: (int) $validated['patient_id'],
            invoiceDate: (string) $validated['invoice_date'],
            items: $items,
            discountType: $validated['discount_type'] ?? null,
            idCardNumber: $validated['id_card_number'] ?? null,
            philhealthMemberPin: null,
            philhealthPrimaryIcd: null,
            philhealthPrimaryCaseCode: 'BDMS-INGESTED-CASE',
            philhealthPrimaryCaseRateAmount: isset($validated['philhealth_amount']) ? (string) $validated['philhealth_amount'] : '0.0000',
            philhealthSecondaryCaseCode: null,
            philhealthSecondaryCaseRateAmount: '0.0000',
            hmoProvider: $validated['hmo_provider'] ?? null,
            hmoLoaNumber: $validated['bdms_bill_number'] ?? null,
            hmoCardNumber: null,
            hmoApprovedLimit: isset($validated['hmo_amount']) ? (string) $validated['hmo_amount'] : '0.0000',
        );

        $invoice = $service->ingestAndPostPatientBill($dto);

        return (new InvoiceResource($invoice))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
