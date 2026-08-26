<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

readonly final class TrialBalanceFilterData
{
    public function __construct(
        public ?string $asOfDate = null,
        public bool $hideZeroBalances = false,
        public ?string $category = null,
        public ?string $search = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            asOfDate: ! empty($data['as_of_date']) ? (string) $data['as_of_date'] : null,
            hideZeroBalances: filter_var($data['hide_zero_balances'] ?? false, FILTER_VALIDATE_BOOLEAN),
            category: ! empty($data['category']) ? strtoupper((string) $data['category']) : null,
            search: ! empty($data['search']) ? trim((string) $data['search']) : null,
        );
    }
}
