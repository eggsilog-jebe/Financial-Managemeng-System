<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Ingestion;

use App\DTOs\PurchaseBillItemData;
use App\DTOs\VendorBillIngestionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IngestPsmVendorBillRequest;
use App\Http\Resources\PurchaseBillResource;
use App\Services\Accounting\AccountsPayableService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class IngestPsmVendorBillController extends Controller
{
    /**
     * Ingest supply chain vendor bill from PSM / SWS subsystem.
     * Route: POST /api/v1/ingest/vendor-bill
     */
    public function __invoke(
        IngestPsmVendorBillRequest $request,
        AccountsPayableService $service
    ): JsonResponse {
        $validated = $request->validated();

        $atcCode = $validated['atc_code'] ?? 'WI158';

        $items = array_map(
            fn (array $item) => new PurchaseBillItemData(
                itemCode: (string) $item['item_code'],
                description: (string) $item['description'],
                expenseType: (string) $item['expense_type'],
                quantity: (string) $item['quantity'],
                unitPrice: (string) $item['unit_price'],
                atcCode: $atcCode,
            ),
            $validated['items']
        );

        $dto = new VendorBillIngestionData(
            vendorId: (int) $validated['vendor_id'],
            doctorId: null,
            billDate: (string) $validated['bill_date'],
            dueDate: (string) $validated['due_date'],
            poNumber: (string) $validated['po_number'],
            grnNumber: (string) $validated['grn_reference'],
            vendorInvoiceNumber: (string) $validated['vendor_invoice_number'],
            poAmount: (string) $validated['invoice_amount'],
            grnAmount: (string) $validated['invoice_amount'],
            items: $items,
        );

        $bill = $service->ingestVendorBillAndPostAP($dto);

        return (new PurchaseBillResource($bill))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
