<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

readonly final class CheckIssueData
{
    public function __construct(
        public int $disbursementVoucherId,
        public int $bankAccountId,
        public string $checkNumber,
        public string $checkDate,
        public string $payeeName,
        public string $amount,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            disbursementVoucherId: (int) $data['disbursement_voucher_id'],
            bankAccountId: (int) $data['bank_account_id'],
            checkNumber: trim((string) $data['check_number']),
            checkDate: (string) ($data['check_date'] ?? date('Y-m-d')),
            payeeName: trim((string) $data['payee_name']),
            amount: (string) $data['amount'],
        );
    }
}
