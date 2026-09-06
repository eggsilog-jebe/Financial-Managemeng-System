<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

final readonly class BudgetEncumbranceData
{
    public function __construct(
        public int $budgetAllocationId,
        public string $referenceType,
        public string $referenceNumber,
        public string $amount,
        public ?string $notes = null,
    ) {}
}
