<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\PurchaseBill;
use App\Models\ThreeWayMatch;
use DomainException;
use Illuminate\Support\Facades\DB;

final class ThreeWayMatchingService
{
    public function __construct(
        private readonly CasAuditTrailService $auditTrailService,
    ) {}

    /**
     * Evaluate 3-Way Match variance between Purchase Order, Goods Receipt, and Vendor Sales Invoice.
     */
    public function evaluateMatch(string $poAmount, string $grnAmount, string $invoiceAmount): array
    {
        $priceVariance = bcsub($invoiceAmount, $poAmount, 4);
        $receiptVariance = bcsub($invoiceAmount, $grnAmount, 4);

        $status = 'MATCHED';

        if (bccomp($priceVariance, '0.0000', 4) !== 0) {
            $status = bccomp($priceVariance, '0.0000', 4) > 0 ? 'PRICE_MISMATCH' : 'PRICE_VARIANCE';
        } elseif (bccomp($receiptVariance, '0.0000', 4) !== 0) {
            $status = 'QTY_MISMATCH';
        }

        return [
            'po_amount'        => $poAmount,
            'grn_amount'       => $grnAmount,
            'invoice_amount'   => $invoiceAmount,
            'price_variance'   => $priceVariance,
            'receipt_variance' => $receiptVariance,
            'match_status'     => $status,
            'is_matched'       => $status === 'MATCHED',
        ];
    }

    /**
     * Approve 3-Way Match reconciliation for a purchase bill.
     */
    public function approveMatch(int $purchaseBillId, int $userId): PurchaseBill
    {
        return DB::transaction(function () use ($purchaseBillId, $userId): PurchaseBill {
            $bill = PurchaseBill::with('threeWayMatch')->findOrFail($purchaseBillId);

            if ($bill->threeWayMatch) {
                $bill->threeWayMatch->update([
                    'approved_by'  => $userId,
                    'approved_at'  => now(),
                    'match_status' => 'MATCHED',
                ]);
            }

            $bill->update(['status' => 'APPROVED']);

            $this->auditTrailService->logFinancialEvent(
                auditable: $bill,
                action: 'UPDATE',
                oldValues: ['status' => 'DRAFT'],
                newValues: ['status' => 'APPROVED'],
                userId: $userId,
                userName: auth()->user()?->name ?? 'Finance Approver',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $bill->loadMissing(['vendor', 'threeWayMatch', 'birCertificate']);
        });
    }
}
