<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

readonly final class PettyCashExpenseData
{
    public function __construct(
        public int $pettyCashFundId,
        public string $expenseDate,
        public string $payee,
        public string $department,
        public string $particulars,
        public string $amount,
        public ?string $receiptRef = null,
        public ?string $voucherNumber = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            pettyCashFundId: (int) $data['petty_cash_fund_id'],
            expenseDate: (string) ($data['expense_date'] ?? date('Y-m-d')),
            payee: trim((string) $data['payee']),
            department: (string) ($data['department'] ?? 'ADMIN'),
            particulars: trim((string) $data['particulars']),
            amount: (string) $data['amount'],
            receiptRef: ! empty($data['receipt_ref']) ? trim((string) $data['receipt_ref']) : null,
            voucherNumber: ! empty($data['voucher_number']) ? trim((string) $data['voucher_number']) : null,
        );
    }
}
