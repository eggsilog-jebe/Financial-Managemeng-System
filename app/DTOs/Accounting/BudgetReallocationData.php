<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

final readonly class BudgetReallocationData
{
    public function __construct(
        public int $sourceAllocationId,
        public int $destinationAllocationId,
        public string $amount,
        public string $transferDate,
        public string $reason,
        public ?int $approvedBy = null,
    ) {}
}
