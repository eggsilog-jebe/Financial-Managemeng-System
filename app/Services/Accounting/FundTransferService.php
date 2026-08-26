<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\Accounting\FundTransferData;
use App\DTOs\JournalEntryData;
use App\DTOs\JournalLineData;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\FundTransfer;
use DomainException;
use Illuminate\Support\Facades\DB;

final class FundTransferService
{
    public function __construct(
        private readonly JournalEntryService $journalEntryService,
        private readonly CasAuditTrailService $auditTrailService,
    ) {}

    public function executeTransfer(FundTransferData $dto): FundTransfer
    {
        return DB::transaction(function () use ($dto): FundTransfer {
            if ($dto->sourceBankAccountId === $dto->destinationBankAccountId) {
                throw new DomainException("Source and Destination bank accounts must be different.");
            }

            $sourceBank = BankAccount::findOrFail($dto->sourceBankAccountId);
            $destBank = BankAccount::findOrFail($dto->destinationBankAccountId);

            if (! $sourceBank->is_active || $sourceBank->status !== 'Active') {
                throw new DomainException("Source bank account [{$sourceBank->name}] is not active.");
            }

            if (! $destBank->is_active || $destBank->status !== 'Active') {
                throw new DomainException("Destination bank account [{$destBank->name}] is not active.");
            }

            $amount = (string) $dto->amount;
            if (bccomp($amount, '0.0000', 4) <= 0) {
                throw new DomainException("Transfer amount must be strictly greater than 0.");
            }

            $sourceBal = (string) $sourceBank->balance;
            if (bccomp($sourceBal, $amount, 4) < 0) {
                throw new DomainException("Insufficient funds in source account [{$sourceBank->name}]. Current Balance: ₱" . number_format((float) $sourceBal, 2) . ", Requested: ₱" . number_format((float) $amount, 2));
            }

            // 1. Generate Transfer Reference
            $todayStr = date('Ymd', strtotime($dto->transferDate));
            $countToday = FundTransfer::whereDate('transfer_date', $dto->transferDate)->count() + 1;
            $refNumber = 'TRF-' . $todayStr . '-' . str_pad((string) $countToday, 4, '0', STR_PAD_LEFT);

            // 2. Mutate Bank Balances atomically
            $newSourceBal = bcsub($sourceBal, $amount, 4);
            $sourceBank->update(['balance' => $newSourceBal]);

            $destBal = (string) $destBank->balance;
            $newDestBal = bcadd($destBal, $amount, 4);
            $destBank->update(['balance' => $newDestBal]);

            // 3. Resolve GL Accounts
            $sourceGl = null;
            if ($sourceBank->gl_account_id) {
                $sourceGl = Account::find($sourceBank->gl_account_id);
            } elseif ($sourceBank->gl_code) {
                $sourceGl = Account::where('code', $sourceBank->gl_code)->first();
            }
            if (! $sourceGl) {
                $sourceGl = Account::firstOrCreate(
                    ['code' => $sourceBank->gl_code ?: '1020'],
                    ['name' => "Cash in Bank - {$sourceBank->bank_name}", 'category' => 'ASSET', 'normal_balance' => 'DEBIT']
                );
            }

            $destGl = null;
            if ($destBank->gl_account_id) {
                $destGl = Account::find($destBank->gl_account_id);
            } elseif ($destBank->gl_code) {
                $destGl = Account::where('code', $destBank->gl_code)->first();
            }
            if (! $destGl) {
                $destGl = Account::firstOrCreate(
                    ['code' => $destBank->gl_code ?: '1021'],
                    ['name' => "Cash in Bank - {$destBank->bank_name}", 'category' => 'ASSET', 'normal_balance' => 'DEBIT']
                );
            }

            // 4. Post Balanced Double-Entry General Ledger Entry:
            // DR: Destination Bank Account GL
            // CR: Source Bank Account GL
            $lines = [
                new JournalLineData(
                    accountId: $destGl->id,
                    debit: $amount,
                    credit: '0.0000',
                    memo: "Inter-account transfer inflow from {$sourceBank->bank_name} ({$sourceBank->account_number})"
                ),
                new JournalLineData(
                    accountId: $sourceGl->id,
                    debit: '0.0000',
                    credit: $amount,
                    memo: "Inter-account transfer outflow to {$destBank->bank_name} ({$destBank->account_number})"
                ),
            ];

            $je = $this->journalEntryService->createAndPostEntry(new JournalEntryData(
                referenceNumber: 'JE-' . $refNumber,
                entryDate: $dto->transferDate,
                description: "Inter-Account Fund Transfer [{$refNumber}] - {$sourceBank->name} to {$destBank->name}" . ($dto->memo ? ": {$dto->memo}" : ''),
                lines: $lines,
                type: 'GENERAL'
            ));

            // 5. Create FundTransfer Record
            $transfer = FundTransfer::create([
                'reference_number'            => $refNumber,
                'source_bank_account_id'      => $sourceBank->id,
                'destination_bank_account_id' => $destBank->id,
                'source_account'              => $sourceBank->name,
                'source_number'               => $sourceBank->account_number,
                'destination_account'         => $destBank->name,
                'destination_number'          => $destBank->account_number,
                'journal_entry_id'            => $je->id,
                'amount'                      => $amount,
                'memo'                        => $dto->memo,
                'transfer_method'             => $dto->transferMethod ?: 'INSTAPAY_PESONET',
                'transfer_date'               => $dto->transferDate,
                'status'                      => 'Completed & Posted',
                'created_by'                  => $dto->createdBy ?? auth()->id(),
            ]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $transfer,
                action: 'INSERT',
                oldValues: null,
                newValues: $transfer->toArray(),
                userId: $dto->createdBy ?? auth()->id() ?? 1,
                userName: auth()->user()?->name ?? 'Treasury Officer',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $transfer->loadMissing(['sourceBank', 'destinationBank', 'journalEntry', 'author']);
        });
    }
}
