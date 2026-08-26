<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

readonly final class StatementFilterData
{
    public function __construct(
        public int $patientAccountId,
        public ?string $startDate = null,
        public ?string $endDate = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            patientAccountId: (int) ($data['patient_account_id'] ?? $data['patient_id'] ?? 0),
            startDate: ! empty($data['start_date']) ? (string) $data['start_date'] : null,
            endDate: ! empty($data['end_date']) ? (string) $data['end_date'] : null,
        );
    }
}
