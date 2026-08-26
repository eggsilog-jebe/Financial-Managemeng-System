<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

readonly final class DisbursementReleaseData
{
    public function __construct(
        public ?string $checkNumber = null,
        public ?string $checkDate = null,
        public ?string $notes = null,
        public ?string $eftReference = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            checkNumber: ! empty($data['check_number']) ? trim((string) $data['check_number']) : null,
            checkDate: ! empty($data['check_date']) ? (string) $data['check_date'] : date('Y-m-d'),
            notes: ! empty($data['notes']) ? trim((string) $data['notes']) : null,
            eftReference: ! empty($data['eft_reference']) ? trim((string) $data['eft_reference']) : null,
        );
    }
}
