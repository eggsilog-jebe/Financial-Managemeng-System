<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

final readonly class BudgetAllocationData
{
    public function __construct(
        public string $department,
        public string $fiscalYear,
        public string $allocatedAmount,
        public ?string $departmentCode = null,
        public ?string $category = null,
        public ?string $departmentHead = null,
        public ?string $status = 'Approved',
        public ?string $notes = null,
    ) {}
}
