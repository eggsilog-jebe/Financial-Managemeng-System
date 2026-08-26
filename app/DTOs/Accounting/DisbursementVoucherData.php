<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

readonly final class DisbursementVoucherData
{
    public function __construct(
        public int $purchaseBillId,
        public int $bankAccountId,
        public string $voucherDate,
        public string $amount,
        public string $paymentMethod = 'CHECK', // CHECK, PESONET_EFT, INSTAPAY, PETTY_CASH
        public ?string $payeeName = null,
        public ?string $checkOrEftRef = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            purchaseBillId: (int) $data['purchase_bill_id'],
            bankAccountId: (int) $data['bank_account_id'],
            voucherDate: (string) ($data['voucher_date'] ?? date('Y-m-d')),
            amount: (string) $data['amount'],
            paymentMethod: (string) ($data['payment_method'] ?? 'CHECK'),
            payeeName: ! empty($data['payee_name']) ? (string) $data['payee_name'] : null,
            checkOrEftRef: ! empty($data['check_or_eft_ref']) ? (string) $data['check_or_eft_ref'] : null,
        );
    }
}
