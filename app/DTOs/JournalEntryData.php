<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class JournalEntryData
{
    /**
     * @param array<int, JournalLineData> $lines
     */
    public function __construct(
        public string $referenceNumber,
        public string $entryDate,
        public string $description,
        public string $type = 'GENERAL',
        public ?int $postedBy = null,
        public array $lines = [],
    ) {}
}
