<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\PurchaseBillItemData;
use App\DTOs\VendorBillIngestionData;
use App\Http\Requests\IngestVendorBillRequest;
use App\Http\Resources\PurchaseBillResource;
use App\Services\Accounting\AccountsPayableService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class IngestVendorBillController extends Controller
{
    public function __invoke(
        IngestVendorBillRequest $request,
        AccountsPayableService $service
    ): JsonResponse {
        $validated = $request->validated();

        $items = array_map(
            fn (array $item) => new PurchaseBillItemData(
                itemCode: (string) $item['item_code'],
                description: (string) $item['description'],
                expenseType: (string) $item['expense_type'],
                quantity: (string) $item['quantity'],
                unitPrice: (string) $item['unit_price'],
                atcCode: (string) ($item['atc_code'] ?? 'WI158'),
            ),
            $validated['items']
        );

        $dto = new VendorBillIngestionData(
            vendorId: (int) $validated['vendor_id'],
            doctorId: isset($validated['doctor_id']) ? (int) $validated['doctor_id'] : null,
            billDate: (string) $validated['bill_date'],
            dueDate: (string) $validated['due_date'],
            poNumber: (string) $validated['po_number'],
            grnNumber: (string) $validated['grn_number'],
            vendorInvoiceNumber: (string) $validated['vendor_invoice_number'],
            poAmount: (string) $validated['po_amount'],
            grnAmount: (string) $validated['grn_amount'],
            items: $items,
        );

        $bill = $service->ingestVendorBillAndPostAP($dto);

        return (new PurchaseBillResource($bill))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
