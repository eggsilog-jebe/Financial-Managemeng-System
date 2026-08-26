<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\Accounting\BankAccountData;
use App\Models\Account;
use App\Models\BankAccount;
use DomainException;
use Illuminate\Support\Facades\DB;

final class BankAccountService
{
    public function __construct(
        private readonly CasAuditTrailService $auditTrailService
    ) {}

    public function createBankAccount(BankAccountData $dto): BankAccount
    {
        return DB::transaction(function () use ($dto): BankAccount {
            $existing = BankAccount::where('account_number', $dto->accountNumber)->first();
            if ($existing) {
                throw new DomainException("Bank account with number [{$dto->accountNumber}] already exists.");
            }

            $glAccount = null;
            if ($dto->glAccountId) {
                $glAccount = Account::find($dto->glAccountId);
            } elseif ($dto->glCode) {
                $glAccount = Account::where('code', $dto->glCode)->first();
            }

            if (! $glAccount && $dto->glCode) {
                $glAccount = Account::firstOrCreate(
                    ['code' => $dto->glCode],
                    ['name' => "Cash in Bank - {$dto->bankName}", 'category' => 'ASSET', 'normal_balance' => 'DEBIT']
                );
            }

            $account = BankAccount::create([
                'name'            => $dto->name,
                'bank_name'       => $dto->bankName,
                'account_number'  => $dto->accountNumber,
                'gl_code'         => $dto->glCode ?: ($glAccount?->code ?? '1020'),
                'gl_account_id'   => $glAccount?->id,
                'purpose'         => $dto->purpose,
                'currency'        => $dto->currency ?: 'PHP',
                'opening_balance' => $dto->openingBalance,
                'balance'         => $dto->openingBalance,
                'minimum_balance' => $dto->minimumBalance ?: '50000.0000',
                'status'          => $dto->status,
                'is_active'       => $dto->isActive,
            ]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $account,
                action: 'INSERT',
                oldValues: null,
                newValues: $account->toArray(),
                userId: auth()->id() ?? 1,
                userName: auth()->user()?->name ?? 'Treasury Manager',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $account;
        });
    }

    public function updateBankAccount(int $id, BankAccountData $dto): BankAccount
    {
        return DB::transaction(function () use ($id, $dto): BankAccount {
            $account = BankAccount::findOrFail($id);

            $existing = BankAccount::where('account_number', $dto->accountNumber)
                ->where('id', '!=', $id)
                ->first();
            if ($existing) {
                throw new DomainException("Another bank account with number [{$dto->accountNumber}] already exists.");
            }

            $oldValues = $account->toArray();

            $glAccount = null;
            if ($dto->glAccountId) {
                $glAccount = Account::find($dto->glAccountId);
            } elseif ($dto->glCode) {
                $glAccount = Account::where('code', $dto->glCode)->first();
            }

            $account->update([
                'name'            => $dto->name,
                'bank_name'       => $dto->bankName,
                'account_number'  => $dto->accountNumber,
                'gl_code'         => $dto->glCode ?: ($glAccount?->code ?? $account->gl_code),
                'gl_account_id'   => $glAccount?->id ?? $account->gl_account_id,
                'purpose'         => $dto->purpose,
                'currency'        => $dto->currency,
                'minimum_balance' => $dto->minimumBalance,
                'status'          => $dto->status,
                'is_active'       => $dto->isActive,
            ]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $account,
                action: 'UPDATE',
                oldValues: $oldValues,
                newValues: $account->toArray(),
                userId: auth()->id() ?? 1,
                userName: auth()->user()?->name ?? 'Treasury Manager',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $account;
        });
    }

    public function toggleStatus(int $id): BankAccount
    {
        return DB::transaction(function () use ($id): BankAccount {
            $account = BankAccount::findOrFail($id);
            $oldValues = $account->toArray();

            $newActive = ! $account->is_active;
            $newStatus = $newActive ? 'Active' : 'Inactive';

            $account->update([
                'is_active' => $newActive,
                'status'    => $newStatus,
            ]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $account,
                action: 'UPDATE',
                oldValues: $oldValues,
                newValues: $account->toArray(),
                userId: auth()->id() ?? 1,
                userName: auth()->user()?->name ?? 'Treasury Manager',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $account;
        });
    }
}
