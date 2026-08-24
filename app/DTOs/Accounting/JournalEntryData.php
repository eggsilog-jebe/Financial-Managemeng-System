<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

use App\DTOs\JournalLineData;

readonly final class JournalEntryData
{
    /**
     * @param array<int, JournalLineData|array> $lines
     */
    public function __construct(
        public string $entryDate,
        public string $description,
        public array $lines,
        public int $userId = 1,
        public ?string $referenceNumber = null,
        public string $type = 'GENERAL',
    ) {}

    public function getReferenceNumber(): string
    {
        return $this->referenceNumber ?? ('JE-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))));
    }
}
