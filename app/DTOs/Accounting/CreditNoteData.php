<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

use App\Http\Requests\Accounting\StoreCreditNoteRequest;

readonly final class CreditNoteData
{
    public function __construct(
        public int $invoiceId,
        public string $amount,
        public string $reason,
        public string $issueDate,
        public ?int $patientAccountId = null,
        public ?string $status = 'DRAFT',
        public ?int $approvedBy = null,
        public ?string $creditNoteNumber = null,
        public bool $saveAsDraft = true,
    ) {}

    public static function fromRequest(StoreCreditNoteRequest $request): self
    {
        $validated = $request->validated();

        $saveAsDraft = true;
        if ($request->has('save_as_draft')) {
            $saveAsDraft = $request->boolean('save_as_draft');
        } elseif ($request->has('is_draft')) {
            $saveAsDraft = $request->boolean('is_draft');
        }

        return new self(
            invoiceId: (int) $validated['invoice_id'],
            amount: (string) $validated['amount'],
            reason: (string) ($validated['reason'] ?? $validated['adjustment_type'] ?? 'BILLING_ADJUSTMENT'),
            issueDate: (string) ($validated['issue_date'] ?? date('Y-m-d')),
            patientAccountId: isset($validated['patient_account_id']) ? (int) $validated['patient_account_id'] : null,
            status: (string) ($validated['status'] ?? ($saveAsDraft ? 'DRAFT' : 'POSTED')),
            approvedBy: isset($validated['approved_by']) ? (int) $validated['approved_by'] : null,
            creditNoteNumber: ! empty($validated['credit_note_number']) ? (string) $validated['credit_note_number'] : null,
            saveAsDraft: $saveAsDraft,
        );
    }

    public static function fromArray(array $data): self
    {
        $saveAsDraft = true;
        if (isset($data['save_as_draft'])) {
            $saveAsDraft = (bool) $data['save_as_draft'];
        } elseif (isset($data['is_draft'])) {
            $saveAsDraft = (bool) $data['is_draft'];
        }

        return new self(
            invoiceId: (int) $data['invoice_id'],
            amount: (string) $data['amount'],
            reason: (string) $data['reason'],
            issueDate: (string) ($data['issue_date'] ?? date('Y-m-d')),
            patientAccountId: isset($data['patient_account_id']) ? (int) $data['patient_account_id'] : null,
            status: (string) ($data['status'] ?? ($saveAsDraft ? 'DRAFT' : 'POSTED')),
            approvedBy: isset($data['approved_by']) ? (int) $data['approved_by'] : null,
            creditNoteNumber: ! empty($data['credit_note_number']) ? (string) $data['credit_note_number'] : null,
            saveAsDraft: $saveAsDraft,
        );
    }
}
