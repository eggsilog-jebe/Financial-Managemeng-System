<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

final readonly class FundTransferData
{
    public function __construct(
        public int $sourceBankAccountId,
        public int $destinationBankAccountId,
        public string $amount,
        public string $transferDate,
        public string $transferMethod,
        public ?string $memo,
        public ?int $createdBy,
    ) {}
}
