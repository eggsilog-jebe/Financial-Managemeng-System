<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

final readonly class CashierShiftCloseData
{
    public function __construct(
        public string $actualCashCounted,
        public ?string $varianceReason = null,
        public ?int $verifiedBy = null,
    ) {}
}
