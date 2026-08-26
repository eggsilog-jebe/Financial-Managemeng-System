<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

readonly final class PaymentRequestData
{
    public function __construct(
        public int $bankAccountId,
        public string $voucherDate,
        public string $payeeName,
        public string $grossAmount,
        public string $withheldTaxAmount = '0.0000',
        public string $netDisbursedAmount = '0.0000',
        public string $paymentMethod = 'CHECK', // CHECK, PESONET_EFT, INSTAPAY, PETTY_CASH, TELEGRAPHIC_TRANSFER
        public ?int $purchaseBillId = null,
        public ?int $payrollRunId = null,
        public ?string $description = null,
        public ?string $voucherNumber = null,
        public ?int $preparedBy = null,
    ) {}

    public static function fromArray(array $data, ?int $userId = null): self
    {
        $gross = (string) ($data['gross_amount'] ?? $data['amount'] ?? '0.0000');
        $tax = (string) ($data['withheld_tax_amount'] ?? '0.0000');
        $net = (string) ($data['net_disbursed_amount'] ?? bcsub($gross, $tax, 4));

        return new self(
            bankAccountId: (int) $data['bank_account_id'],
            voucherDate: (string) ($data['voucher_date'] ?? date('Y-m-d')),
            payeeName: trim((string) $data['payee_name']),
            grossAmount: $gross,
            withheldTaxAmount: $tax,
            netDisbursedAmount: $net,
            paymentMethod: (string) ($data['payment_method'] ?? 'CHECK'),
            purchaseBillId: ! empty($data['purchase_bill_id']) ? (int) $data['purchase_bill_id'] : null,
            payrollRunId: ! empty($data['payroll_run_id']) ? (int) $data['payroll_run_id'] : null,
            description: ! empty($data['description']) ? trim((string) $data['description']) : (! empty($data['purpose']) ? trim((string) $data['purpose']) : null),
            voucherNumber: ! empty($data['voucher_number']) ? trim((string) $data['voucher_number']) : null,
            preparedBy: $userId ?? auth()->id(),
        );
    }
}
