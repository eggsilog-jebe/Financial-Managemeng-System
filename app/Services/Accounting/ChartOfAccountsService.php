<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\Accounting\AccountData;
use App\Models\Account;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class ChartOfAccountsService
{
    public function __construct(
        private readonly CasAuditTrailService $auditTrailService,
    ) {}

    /**
     * Create a new Account in the Chart of Accounts.
     */
    public function createAccount(AccountData $dto, ?int $userId = null, ?string $userName = null, ?string $ipAddress = null): Account
    {
        return DB::transaction(function () use ($dto, $userId, $userName, $ipAddress): Account {
            $account = Account::create($dto->toArray());

            $this->auditTrailService->logFinancialEvent(
                auditable: $account,
                action: 'INSERT',
                oldValues: null,
                newValues: $account->toArray(),
                userId: $userId ?? auth()->id() ?? 1,
                userName: $userName ?? auth()->user()?->name ?? 'System Accountant',
                ipAddress: $ipAddress ?? request()?->ip() ?? '127.0.0.1',
            );

            return $account;
        });
    }

    /**
     * Update an existing Account.
     */
    public function updateAccount(Account $account, AccountData $dto, ?int $userId = null, ?string $userName = null, ?string $ipAddress = null): Account
    {
        return DB::transaction(function () use ($account, $dto, $userId, $userName, $ipAddress): Account {
            $oldValues = $account->toArray();

            // If attempting to deactivate, assert no active journal entry lines
            if ($account->is_active && ! $dto->isActive && ! $account->canBeDeactivated()) {
                throw new DomainException("Cannot deactivate Account [{$account->code} - {$account->name}]: It contains existing transaction lines in the General Ledger.");
            }

            $account->update($dto->toArray());

            $this->auditTrailService->logFinancialEvent(
                auditable: $account,
                action: 'UPDATE',
                oldValues: $oldValues,
                newValues: $account->toArray(),
                userId: $userId ?? auth()->id() ?? 1,
                userName: $userName ?? auth()->user()?->name ?? 'System Accountant',
                ipAddress: $ipAddress ?? request()?->ip() ?? '127.0.0.1',
            );

            return $account;
        });
    }

    /**
     * Soft-toggle account active status.
     */
    public function toggleAccountStatus(Account $account, ?int $userId = null, ?string $userName = null, ?string $ipAddress = null): Account
    {
        return DB::transaction(function () use ($account, $userId, $userName, $ipAddress): Account {
            $newStatus = ! $account->is_active;

            if ($account->is_active && ! $newStatus && ! $account->canBeDeactivated()) {
                throw new DomainException("Cannot deactivate Account [{$account->code} - {$account->name}]: It contains existing transaction lines in the General Ledger.");
            }

            $oldValues = ['is_active' => $account->is_active];
            $account->update(['is_active' => $newStatus]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $account,
                action: 'UPDATE',
                oldValues: $oldValues,
                newValues: ['is_active' => $newStatus],
                userId: $userId ?? auth()->id() ?? 1,
                userName: $userName ?? auth()->user()?->name ?? 'System Accountant',
                ipAddress: $ipAddress ?? request()?->ip() ?? '127.0.0.1',
            );

            return $account;
        });
    }

    /**
     * Retrieve all accounts with eager-loaded relations and optional filtering.
     *
     * @return Collection<int, Account>
     */
    public function getAccountsList(?string $category = null, ?bool $isActive = null, ?string $search = null): Collection
    {
        $query = Account::with(['journalEntryLines.journalEntry' => function ($q) {
            $q->where('status', 'POSTED');
        }])->orderBy('code');

        if ($category) {
            $query->where('category', strtoupper($category));
        }

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%")
                  ->orWhere('department', 'LIKE', "%{$search}%");
            });
        }

        return $query->get();
    }
}
