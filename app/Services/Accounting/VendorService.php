<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\Accounting\VendorData;
use App\Models\Vendor;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class VendorService
{
    public function __construct(
        private readonly CasAuditTrailService $auditTrailService,
    ) {}

    public function createVendor(VendorData $dto, ?int $userId = null): Vendor
    {
        return DB::transaction(function () use ($dto, $userId): Vendor {
            $vendor = Vendor::create($dto->toArray());

            $this->auditTrailService->logFinancialEvent(
                auditable: $vendor,
                action: 'INSERT',
                oldValues: null,
                newValues: $vendor->toArray(),
                userId: $userId ?? auth()->id() ?? 1,
                userName: auth()->user()?->name ?? 'AP Specialist',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $vendor;
        });
    }

    public function updateVendor(Vendor $vendor, VendorData $dto, ?int $userId = null): Vendor
    {
        return DB::transaction(function () use ($vendor, $dto, $userId): Vendor {
            $oldValues = $vendor->toArray();
            $vendor->update($dto->toArray());

            $this->auditTrailService->logFinancialEvent(
                auditable: $vendor,
                action: 'UPDATE',
                oldValues: $oldValues,
                newValues: $vendor->toArray(),
                userId: $userId ?? auth()->id() ?? 1,
                userName: auth()->user()?->name ?? 'AP Specialist',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $vendor;
        });
    }

    public function toggleVendorStatus(Vendor $vendor, ?int $userId = null): Vendor
    {
        return DB::transaction(function () use ($vendor, $userId): Vendor {
            $oldStatus = $vendor->status;
            $newStatus = ($oldStatus === 'Active') ? 'Inactive' : 'Active';

            $vendor->update(['status' => $newStatus]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $vendor,
                action: 'UPDATE',
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => $newStatus],
                userId: $userId ?? auth()->id() ?? 1,
                userName: auth()->user()?->name ?? 'AP Specialist',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $vendor;
        });
    }

    /**
     * @return Collection<int, Vendor>
     */
    public function getVendorsList(?string $status = null, ?string $search = null): Collection
    {
        $query = Vendor::with('purchaseBills')->orderBy('name');

        if ($status) {
            $query->where('status', ucfirst($status));
        }

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('tin', 'LIKE', "%{$search}%")
                  ->orWhere('contact_person', 'LIKE', "%{$search}%");
            });
        }

        return $query->get();
    }
}
