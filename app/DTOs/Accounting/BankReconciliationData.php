<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

final readonly class BankReconciliationData
{
    /**
     * @param array<int> $clearedCheckIds
     * @param array<int> $clearedDepositIds
     */
    public function __construct(
        public int $bankAccountId,
        public string $statementDate,
        public string $cutoffDate,
        public string $statementBalance,
        public string $bookBalance,
        public array $clearedCheckIds,
        public array $clearedDepositIds,
        public ?string $notes,
        public int $reconciledBy,
    ) {}
}
