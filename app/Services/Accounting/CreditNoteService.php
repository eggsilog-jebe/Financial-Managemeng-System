<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\Accounting\CreditNoteData;
use App\DTOs\JournalEntryData;
use App\DTOs\JournalLineData;
use App\Models\Account;
use App\Models\CreditNote;
use App\Models\Invoice;
use DomainException;
use Illuminate\Support\Facades\DB;

final class CreditNoteService
{
    public function __construct(
        private readonly JournalEntryService $journalEntryService,
        private readonly CasAuditTrailService $auditTrailService,
    ) {}

    /**
     * Create a draft/pending Credit Note adjustment for an invoice.
     */
    public function createCreditNote(CreditNoteData $dto, ?int $userId = null): CreditNote
    {
        return DB::transaction(function () use ($dto, $userId): CreditNote {
            $invoice = Invoice::with('patientAccount')->findOrFail($dto->invoiceId);

            $openBalance = $invoice->balance_due;
            $amount = $dto->amount;

            if (bccomp($amount, '0.0000', 4) <= 0) {
                throw new DomainException("Credit Note adjustment amount must be greater than zero.");
            }

            if (bccomp($amount, $openBalance, 4) > 0) {
                throw new DomainException("Credit Note amount (₱{$amount}) exceeds open patient copay balance (₱{$openBalance}).");
            }

            $creditNoteNum = $dto->creditNoteNumber ?? ('CN-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))));

            $creditNote = CreditNote::create([
                'credit_note_number' => $creditNoteNum,
                'invoice_id'         => $invoice->id,
                'patient_account_id' => $invoice->patient_account_id,
                'issue_date'         => $dto->issueDate,
                'amount'             => $amount,
                'reason'             => $dto->reason,
                'status'             => 'DRAFT',
                'approved_by'        => null,
            ]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $creditNote,
                action: 'INSERT',
                oldValues: null,
                newValues: $creditNote->toArray(),
                userId: $userId ?? auth()->id(),
                userName: auth()->user()?->name ?? 'Billing Clerk',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $creditNote->loadMissing(['invoice', 'patientAccount']);
        });
    }

    /**
     * Approve and post Credit Note adjustment to General Ledger and patient ledger.
     */
    public function approveCreditNote(int $creditNoteId, int $userId): CreditNote
    {
        return DB::transaction(function () use ($creditNoteId, $userId): CreditNote {
            $creditNote = CreditNote::with(['invoice', 'patientAccount'])->findOrFail($creditNoteId);

            if ($creditNote->status === 'POSTED' || $creditNote->status === 'APPLIED') {
                throw new DomainException("Credit Note [{$creditNote->credit_note_number}] has already been posted.");
            }

            $oldValues = $creditNote->toArray();
            $invoice = $creditNote->invoice;
            $patient = $creditNote->patientAccount;
            $amount = (string) $creditNote->amount;

            // 1. Update Credit Note
            $creditNote->update([
                'status'      => 'POSTED',
                'approved_by' => $userId,
            ]);

            // 2. Adjust Invoice Patient Payable & Discount Amount
            $newPayable = bcsub((string) $invoice->patient_payable, $amount, 4);
            if (bccomp($newPayable, '0.0000', 4) < 0) {
                $newPayable = '0.0000';
            }
            $newDiscount = bcadd((string) $invoice->discount_amount, $amount, 4);
            $newStatus = bccomp($newPayable, (string) $invoice->paid_amount, 4) <= 0 ? 'SETTLED' : $invoice->status;

            $invoice->update([
                'patient_payable' => $newPayable,
                'discount_amount' => $newDiscount,
                'status'          => $newStatus,
            ]);

            // 3. Decrement Patient Account Current Balance
            if ($patient) {
                $newBalance = bcsub((string) $patient->current_balance, $amount, 4);
                if (bccomp($newBalance, '0.0000', 4) < 0) {
                    $newBalance = '0.0000';
                }
                $patient->update(['current_balance' => $newBalance]);
            }

            // 4. Post Double-Entry Journal: DR 5010 Discounts & Allowances, CR 1010 AR Patients
            $discountAcc = Account::firstOrCreate(['code' => '5010'], ['name' => 'Senior Citizen, PWD & Special Allowances', 'category' => 'EXPENSE', 'normal_balance' => 'DEBIT']);
            $arPatientAcc = Account::firstOrCreate(['code' => '1010'], ['name' => 'Accounts Receivable - Patients', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']);

            $journalLines = [
                new JournalLineData(
                    accountId: $discountAcc->id,
                    debit: $amount,
                    credit: '0.0000',
                    memo: "Credit note discount [{$creditNote->reason}] on {$invoice->invoice_number}"
                ),
                new JournalLineData(
                    accountId: $arPatientAcc->id,
                    debit: '0.0000',
                    credit: $amount,
                    memo: "AR credit reduction for {$creditNote->credit_note_number}"
                ),
            ];

            $entryData = new JournalEntryData(
                referenceNumber: 'JE-CN-' . $creditNote->credit_note_number,
                entryDate: $creditNote->issue_date ? $creditNote->issue_date->format('Y-m-d') : date('Y-m-d'),
                description: "Credit note adjustment for {$invoice->invoice_number} ({$creditNote->reason})",
                type: 'GENERAL',
                postedBy: $userId,
                lines: $journalLines
            );

            $this->journalEntryService->createAndPostEntry($entryData);

            // 5. CAS Audit Trail
            $this->auditTrailService->logFinancialEvent(
                auditable: $creditNote,
                action: 'POST',
                oldValues: $oldValues,
                newValues: $creditNote->toArray(),
                userId: $userId,
                userName: auth()->user()?->name ?? 'Finance Approver',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $creditNote->loadMissing(['invoice', 'patientAccount', 'approver']);
        });
    }
}
