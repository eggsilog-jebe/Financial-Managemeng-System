<?php

declare(strict_types=1);

namespace App\DTOs;

readonly final class PayrollRunIngestionData
{
    /**
     * @param array<PayrollEmployeeItemData> $employees
     */
    public function __construct(
        public string $cutoffStart,
        public string $cutoffEnd,
        public string $payoutDate,
        public int $disbursementBankAccountId,
        public array $employees,
    ) {}
}
