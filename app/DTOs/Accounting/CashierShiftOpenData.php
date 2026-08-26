<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

final readonly class CashierShiftOpenData
{
    public function __construct(
        public int $cashierId,
        public string $terminalName,
        public string $openingCashFloat,
    ) {}
}
