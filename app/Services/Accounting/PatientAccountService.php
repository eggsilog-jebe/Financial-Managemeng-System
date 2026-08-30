<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\Accounting\PatientAccountData;
use App\Models\PatientAccount;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class PatientAccountService
{
    public function __construct(
        private readonly CasAuditTrailService $auditTrailService,
    ) {}

    public function createPatientAccount(PatientAccountData $dto, ?int $userId = null): PatientAccount
    {
        return DB::transaction(function () use ($dto, $userId): PatientAccount {
            $account = PatientAccount::create($dto->toArray());

            $this->auditTrailService->logFinancialEvent(
                auditable: $account,
                action: 'INSERT',
                oldValues: null,
                newValues: $account->toArray(),
                userId: $userId ?? auth()->id() ?? 1,
                userName: auth()->user()?->name ?? 'Admissions Clerk',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $account;
        });
    }

    public function updatePatientAccount(PatientAccount $account, PatientAccountData $dto, ?int $userId = null): PatientAccount
    {
        return DB::transaction(function () use ($account, $dto, $userId): PatientAccount {
            $oldValues = $account->toArray();
            $account->update($dto->toArray());

            $this->auditTrailService->logFinancialEvent(
                auditable: $account,
                action: 'UPDATE',
                oldValues: $oldValues,
                newValues: $account->toArray(),
                userId: $userId ?? auth()->id() ?? 1,
                userName: auth()->user()?->name ?? 'Admissions Clerk',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $account;
        });
    }

    public function getPatientAccountsList(?string $search = null, bool $outstandingOnly = false, int $perPage = 15): LengthAwarePaginator
    {
        $query = PatientAccount::with([
            'invoices.philhealthClaim',
            'invoices.hmoClaims',
            'invoices.payments.officialReceipt',
            'invoices.statutoryDiscounts',
            'invoices.creditNotes',
            'creditNotes',
        ])->latest('id');

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('patient_id_number', 'LIKE', "%{$search}%")
                  ->orWhere('hmo_provider', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if ($outstandingOnly) {
            $query->where('current_balance', '>', 0);
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
