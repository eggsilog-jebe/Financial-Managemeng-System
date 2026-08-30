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

            // Prevent Duplicate Statutory Discounts on same invoice (RA 9994 / RA 10754)
            $statutoryTypes = ['SENIOR_CITIZEN_DISCOUNT', 'PWD_DISCOUNT', 'SENIOR_CITIZEN', 'PWD'];
            if (in_array($dto->reason, $statutoryTypes, true)) {
                $hasInitialStatutory = $invoice->statutoryDiscounts()->exists();
                $hasExistingStatutoryCN = $invoice->creditNotes()
                    ->whereIn('reason', $statutoryTypes)
                    ->whereIn('status', ['POSTED', 'APPLIED', 'DRAFT'])
                    ->exists();

                if ($hasInitialStatutory || $hasExistingStatutoryCN) {
                    throw new DomainException(
                        "Only one statutory discount (Senior Citizen or PWD) is allowed per invoice under RA 9994 / RA 10754. Please void the existing statutory credit note first."
                    );
                }
            }

            if ($dto->creditNoteNumber) {
                $creditNoteNum = $dto->creditNoteNumber;
            } else {
                $countToday = CreditNote::whereDate('created_at', today())->count() + 1;
                $creditNoteNum = 'CN-' . date('Ymd') . '-' . str_pad((string) $countToday, 4, '0', STR_PAD_LEFT);
            }

            $patientAccountId = $dto->patientAccountId ?: $invoice->patient_account_id;

            $creditNote = CreditNote::create([
                'credit_note_number' => $creditNoteNum,
                'invoice_id'         => $invoice->id,
                'patient_account_id' => $patientAccountId,
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
                userId: $userId ?? auth()->id() ?? 1,
                userName: auth()->user()?->name ?? 'Billing Officer',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            if (! $dto->saveAsDraft) {
                return $this->approveCreditNote($creditNote->id, $userId ?? auth()->id() ?? 1);
            }

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

            if ($creditNote->status === 'VOID') {
                throw new DomainException("Cannot approve a voided Credit Note [{$creditNote->credit_note_number}].");
            }

            $oldValues = $creditNote->toArray();
            $invoice = $creditNote->invoice;
            $patient = $creditNote->patientAccount;
            $amount = (string) $creditNote->amount;

            // 1. Update Credit Note status
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
            $newStatus = bccomp($newPayable, (string) $invoice->paid_amount, 4) <= 0 ? 'SETTLED' : 'PARTIAL';

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

            // 4. Determine Expense / Contra-Revenue Account
            $isCharity = stripos($creditNote->reason, 'CHARITY') !== false || stripos($creditNote->reason, 'INDIGENT') !== false;
            $expenseCode = $isCharity ? '4930' : '4910';
            $expenseName = $isCharity ? 'Charity / Indigent Care Allowances' : 'Statutory Discounts Allowed (Senior/PWD)';

            $discountAcc = Account::firstOrCreate(
                ['code' => $expenseCode],
                ['name' => $expenseName, 'category' => 'EXPENSE', 'normal_balance' => 'DEBIT']
            );

            $arPatientAcc = Account::firstOrCreate(
                ['code' => '1110'],
                ['name' => 'Accounts Receivable - Patient Copay', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']
            );

            // 5. Post Balanced Double-Entry Journal (DR 4910/4930, CR 1110)
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

            // 6. CAS Audit Trail
            $this->auditTrailService->logFinancialEvent(
                auditable: $creditNote,
                action: 'POST',
                oldValues: $oldValues,
                newValues: $creditNote->toArray(),
                userId: $userId,
                userName: auth()->user()?->name ?? 'Finance Approver',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $creditNote->loadMissing(['invoice', 'patientAccount', 'approvedBy']);
        });
    }

    /**
     * Void a credit note, restore invoice/patient balances, and post reversing journal entry.
     */
    public function voidCreditNote(int $creditNoteId, string $reason, int $userId): CreditNote
    {
        return DB::transaction(function () use ($creditNoteId, $reason, $userId): CreditNote {
            $creditNote = CreditNote::with(['invoice', 'patientAccount'])->findOrFail($creditNoteId);

            if ($creditNote->status === 'VOID') {
                throw new DomainException("Credit Note [{$creditNote->credit_note_number}] is already voided.");
            }

            $oldValues = $creditNote->toArray();
            $invoice = $creditNote->invoice;
            $patient = $creditNote->patientAccount;
            $amount = (string) $creditNote->amount;

            // If the credit note was previously posted, reverse the ledger balance impacts
            if ($creditNote->status === 'POSTED' || $creditNote->status === 'APPLIED') {
                // 1. Restore Invoice Patient Payable & Discount Amount
                if ($invoice) {
                    $restoredPayable = bcadd((string) $invoice->patient_payable, $amount, 4);
                    $restoredDiscount = bcsub((string) $invoice->discount_amount, $amount, 4);
                    if (bccomp($restoredDiscount, '0.0000', 4) < 0) {
                        $restoredDiscount = '0.0000';
                    }

                    $invoice->update([
                        'patient_payable' => $restoredPayable,
                        'discount_amount' => $restoredDiscount,
                        'status'          => 'PARTIAL',
                    ]);
                }

                // 2. Restore Patient Account Current Balance
                if ($patient) {
                    $restoredBalance = bcadd((string) $patient->current_balance, $amount, 4);
                    $patient->update(['current_balance' => $restoredBalance]);
                }

                // 3. Post Reversing Journal Entry (DR 1110, CR 4910/4930)
                $isCharity = stripos($creditNote->reason, 'CHARITY') !== false || stripos($creditNote->reason, 'INDIGENT') !== false;
                $expenseCode = $isCharity ? '4930' : '4910';
                $expenseName = $isCharity ? 'Charity / Indigent Care Allowances' : 'Statutory Discounts Allowed (Senior/PWD)';

                $discountAcc = Account::firstOrCreate(
                    ['code' => $expenseCode],
                    ['name' => $expenseName, 'category' => 'EXPENSE', 'normal_balance' => 'DEBIT']
                );

                $arPatientAcc = Account::firstOrCreate(
                    ['code' => '1110'],
                    ['name' => 'Accounts Receivable - Patient Copay', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']
                );

                $journalLines = [
                    new JournalLineData(
                        accountId: $arPatientAcc->id,
                        debit: $amount,
                        credit: '0.0000',
                        memo: "Reversal of AR credit note for {$creditNote->credit_note_number}"
                    ),
                    new JournalLineData(
                        accountId: $discountAcc->id,
                        debit: '0.0000',
                        credit: $amount,
                        memo: "Reversal of discount allowance on {$invoice?->invoice_number} ({$reason})"
                    ),
                ];

                $entryData = new JournalEntryData(
                    referenceNumber: 'JE-REV-CN-' . $creditNote->credit_note_number,
                    entryDate: date('Y-m-d'),
                    description: "Void Credit Note Reversal for {$creditNote->credit_note_number}. Reason: {$reason}",
                    type: 'ADJUSTING',
                    postedBy: $userId,
                    lines: $journalLines
                );

                $this->journalEntryService->createAndPostEntry($entryData);
            }

            // 4. Mark status as VOID
            $creditNote->update([
                'status' => 'VOID',
            ]);

            // 5. CAS Audit Trail
            $this->auditTrailService->logFinancialEvent(
                auditable: $creditNote,
                action: 'UPDATE',
                oldValues: $oldValues,
                newValues: array_merge($creditNote->toArray(), ['void_reason' => $reason]),
                userId: $userId,
                userName: auth()->user()?->name ?? 'Finance Approver',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $creditNote->loadMissing(['invoice', 'patientAccount']);
        });
    }
}
