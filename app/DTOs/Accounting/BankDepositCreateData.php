<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

final readonly class BankDepositCreateData
{
    public function __construct(
        public int $bankAccountId,
        public ?int $cashierShiftId,
        public string $depositDate,
        public string $cashAmount,
        public string $checkAmount = '0.0000',
        public ?string $bankRef = null,
        public ?string $teller = null,
    ) {}
}
