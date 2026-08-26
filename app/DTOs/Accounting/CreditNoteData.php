<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

readonly final class CreditNoteData
{
    public function __construct(
        public int $invoiceId,
        public string $amount,
        public string $reason,
        public string $issueDate,
        public ?string $creditNoteNumber = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            invoiceId: (int) $data['invoice_id'],
            amount: (string) $data['amount'],
            reason: (string) $data['reason'],
            issueDate: (string) ($data['issue_date'] ?? date('Y-m-d')),
            creditNoteNumber: ! empty($data['credit_note_number']) ? (string) $data['credit_note_number'] : null,
        );
    }
}
