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
        public ?string $splitCashAmount = null,
        public ?string $splitDigitalAmount = null,
        public ?string $splitDigitalChannel = null,
        public ?string $splitDigitalRef = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            invoiceId: (int) $data['invoice_id'],
            patientAccountId: isset($data['patient_account_id']) ? (int) $data['patient_account_id'] : null,
            cashierShiftId: isset($data['cashier_shift_id']) ? (int) $data['cashier_shift_id'] : null,
            paymentMethod: (string) ($data['payment_method'] ?? 'CASH'),
            amount: (string) $data['amount'],
            tenderedAmount: isset($data['tendered_amount']) ? (string) $data['tendered_amount'] : null,
            gatewayProvider: isset($data['gateway_provider']) ? (string) $data['gateway_provider'] : null,
            gatewayTransactionId: isset($data['gateway_transaction_id']) ? (string) $data['gateway_transaction_id'] : null,
            payorName: isset($data['payor_name']) ? (string) $data['payor_name'] : null,
            payorTin: isset($data['payor_tin']) ? (string) $data['payor_tin'] : null,
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
            paymentDate: isset($data['payment_date']) ? (string) $data['payment_date'] : null,
            splitCashAmount: isset($data['split_cash_amount']) ? (string) $data['split_cash_amount'] : null,
            splitDigitalAmount: isset($data['split_digital_amount']) ? (string) $data['split_digital_amount'] : null,
            splitDigitalChannel: isset($data['split_digital_channel']) ? (string) $data['split_digital_channel'] : null,
            splitDigitalRef: isset($data['split_digital_ref']) ? (string) $data['split_digital_ref'] : null,
        );
    }
}
