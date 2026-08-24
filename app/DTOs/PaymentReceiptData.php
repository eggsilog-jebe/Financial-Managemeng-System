<?php

declare(strict_types=1);

namespace App\DTOs;

readonly final class PaymentReceiptData
{
    public function __construct(
        public int $patientAccountId,
        public ?int $invoiceId,
        public ?int $cashierShiftId,
        public string $amount,
        public string $paymentMethod, // CASH, GCASH, MAYA, QR_PH, CREDIT_CARD, DEBIT_CARD, CHECK, ONLINE_BANK
        public ?string $transactionChannelRef = null,
        public string $payorName = 'Walk-In / Patient',
        public ?string $payorTin = null,
        public string $paymentDate = '',
        public string $paymentType = 'PATIENT_COPAY',
    ) {}
}
