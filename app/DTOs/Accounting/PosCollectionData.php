<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

final readonly class PosCollectionData
{
    public function __construct(
        public int $invoiceId,
        public ?int $patientAccountId,
        public ?int $cashierShiftId,
        public string $paymentMethod,
        public string $amount,
        public ?string $tenderedAmount = null,
        public ?string $gatewayProvider = null,
        public ?string $gatewayTransactionId = null,
        public ?string $payorName = null,
        public ?string $payorTin = null,
        public ?string $notes = null,
        public ?string $paymentDate = null,
    ) {}
}
