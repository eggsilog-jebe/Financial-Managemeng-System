<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\ClinicalBillableItemData;
use App\DTOs\PatientBillingIngestionData;
use App\Http\Requests\IngestClinicalBillablesRequest;
use App\Http\Resources\InvoiceResource;
use App\Services\Accounting\BillingIngestionService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class IngestClinicalBillablesController extends Controller
{
    public function __invoke(
        IngestClinicalBillablesRequest $request,
        BillingIngestionService $service
    ): JsonResponse {
        $validated = $request->validated();

        $items = array_map(
            fn (array $item) => new ClinicalBillableItemData(
                itemCode: (string) $item['item_code'],
                description: (string) $item['description'],
                department: (string) $item['department'],
                revenueCategory: (string) $item['revenue_category'],
                quantity: (string) $item['quantity'],
                unitPrice: (string) $item['unit_price'],
                isVatable: (bool) ($item['is_vatable'] ?? true),
                isSeniorPwdEligible: (bool) ($item['is_senior_pwd_eligible'] ?? true),
            ),
            $validated['items']
        );

        $dto = new PatientBillingIngestionData(
            patientAccountId: (int) $validated['patient_account_id'],
            invoiceDate: (string) $validated['invoice_date'],
            items: $items,
            discountType: $validated['discount_type'] ?? null,
            idCardNumber: $validated['id_card_number'] ?? null,
            philhealthMemberPin: $validated['philhealth_member_pin'] ?? null,
            philhealthPrimaryIcd: $validated['philhealth_primary_icd'] ?? null,
            philhealthPrimaryCaseCode: $validated['philhealth_primary_case_code'] ?? null,
            philhealthPrimaryCaseRateAmount: isset($validated['philhealth_primary_case_rate_amount']) ? (string) $validated['philhealth_primary_case_rate_amount'] : '0.0000',
            philhealthSecondaryCaseCode: $validated['philhealth_secondary_case_code'] ?? null,
            philhealthSecondaryCaseRateAmount: isset($validated['philhealth_secondary_case_rate_amount']) ? (string) $validated['philhealth_secondary_case_rate_amount'] : '0.0000',
            hmoProvider: $validated['hmo_provider'] ?? null,
            hmoLoaNumber: $validated['hmo_loa_number'] ?? null,
            hmoCardNumber: $validated['hmo_card_number'] ?? null,
            hmoApprovedLimit: isset($validated['hmo_approved_limit']) ? (string) $validated['hmo_approved_limit'] : '0.0000',
        );

        $invoice = $service->ingestAndPostPatientBill($dto);

        return (new InvoiceResource($invoice))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
