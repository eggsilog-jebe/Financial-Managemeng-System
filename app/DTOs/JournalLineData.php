<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class JournalLineData
{
    public function __construct(
        public int $accountId,
        public string $debit,
        public string $credit,
        public ?string $memo = null,
    ) {}
}
