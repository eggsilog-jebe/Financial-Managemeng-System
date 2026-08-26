<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

final readonly class BankAccountData
{
    public function __construct(
        public string $name,
        public string $bankName,
        public string $accountNumber,
        public string $glCode,
        public ?int $glAccountId,
        public string $purpose,
        public string $currency,
        public string $openingBalance,
        public string $minimumBalance,
        public string $status,
        public bool $isActive,
    ) {}
}
