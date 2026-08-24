<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\PaymentReceiptData;
use App\Http\Requests\ProcessPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Services\Accounting\CollectionService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ProcessPaymentController extends Controller
{
    public function __invoke(
        ProcessPaymentRequest $request,
        CollectionService $service
    ): JsonResponse {
        $validated = $request->validated();

        $dto = new PaymentReceiptData(
            patientAccountId: (int) $validated['patient_account_id'],
            invoiceId: isset($validated['invoice_id']) ? (int) $validated['invoice_id'] : null,
            cashierShiftId: isset($validated['cashier_shift_id']) ? (int) $validated['cashier_shift_id'] : null,
            amount: (string) $validated['amount'],
            paymentMethod: (string) $validated['payment_method'],
            transactionChannelRef: $validated['transaction_channel_ref'] ?? null,
            payorName: $validated['payor_name'] ?? 'Walk-In / Patient',
            payorTin: $validated['payor_tin'] ?? null,
            paymentDate: $validated['payment_date'] ?? date('Y-m-d'),
            paymentType: $validated['payment_type'] ?? 'PATIENT_COPAY',
        );

        $payment = $service->processCollection($dto);

        return (new PaymentResource($payment))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
