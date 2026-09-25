<?php

declare(strict_types=1);

namespace App\Http\Controllers\AccountsPayable;

use App\DTOs\Accounting\VendorData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreVendorRequest;
use App\Http\Requests\Accounting\UpdateVendorRequest;
use App\Models\PurchaseBill;
use App\Models\Vendor;
use App\Services\Accounting\VendorService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class VendorController extends Controller
{
    public function __construct(
        private readonly VendorService $vendorService,
        private readonly \App\Services\Cache\MultiLayerCacheService $cacheService,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $vendors = $this->vendorService->getVendorsList($status, $search);

        $summary = $this->cacheService->remember('ap:vendors:summary_metrics', 300, function (): array {
            return [
                'totalActiveVendors' => Vendor::where('status', 'Active')->count(),
                'totalApLiability'   => (float) PurchaseBill::whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE', 'APPROVED'])->sum('total_amount'),
                'totalEwt'           => (string) (\App\Models\Bir2307Certificate::sum('tax_withheld') ?? '0.0000'),
            ];
        }, ['vendors', 'ap'], 60);

        return view('accounts-payable.vendor-management', [
            'vendors'            => $vendors,
            'totalActiveVendors' => $summary['totalActiveVendors'],
            'totalApLiability'   => $summary['totalApLiability'],
            'totalEwt'           => $summary['totalEwt'],
            'search'             => $search,
            'status'             => $status,
        ]);
    }

    public function store(StoreVendorRequest $request): RedirectResponse
    {
        $dto = VendorData::fromArray($request->validated());

        // Generate code if missing
        if (empty($dto->code)) {
            $code = 'VND-' . str_pad((string) (Vendor::count() + 1), 4, '0', STR_PAD_LEFT);
            $dto = new VendorData(
                code: $code,
                name: $dto->name,
                tin: $dto->tin,
                taxType: $dto->taxType,
                defaultEwtRate: $dto->defaultEwtRate,
                defaultAtcCode: $dto->defaultAtcCode,
                contactPerson: $dto->contactPerson,
                phone: $dto->phone,
                email: $dto->email,
                registeredAddress: $dto->registeredAddress,
                bankName: $dto->bankName,
                bankAccountNumber: $dto->bankAccountNumber,
                bankAccountName: $dto->bankAccountName,
                paymentTermsDays: $dto->paymentTermsDays,
                isActive: $dto->isActive,
            );
        }

        $this->vendorService->createVendor($dto);

        return redirect()->back()->with('success', "Vendor [{$dto->name}] successfully registered in Masterfile.");
    }

    public function update(UpdateVendorRequest $request, int|string $id): RedirectResponse
    {
        $vendor = Vendor::findOrFail((int) $id);
        $dto = VendorData::fromArray($request->validated());

        $this->vendorService->updateVendor($vendor, $dto);

        return redirect()->back()->with('success', "Vendor [{$vendor->name}] updated successfully.");
    }

    public function toggle(int|string $id): RedirectResponse
    {
        $vendor = Vendor::findOrFail((int) $id);
        $this->vendorService->toggleVendorStatus($vendor);

        return redirect()->back()->with('success', "Vendor [{$vendor->name}] status changed to {$vendor->status}.");
    }
}
