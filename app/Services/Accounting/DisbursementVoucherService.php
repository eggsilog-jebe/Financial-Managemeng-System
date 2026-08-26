<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\Accounting\PaymentRequestData;
use App\Models\BankAccount;
use App\Models\DisbursementVoucher;
use App\Models\PayrollRun;
use App\Models\PurchaseBill;
use DomainException;
use Illuminate\Support\Facades\DB;

final class DisbursementVoucherService
{
    public function __construct(
        private readonly CasAuditTrailService $auditTrailService,
    ) {}

    /**
     * Create a new disbursement voucher requisition.
     */
    public function createPaymentRequest(PaymentRequestData $dto): DisbursementVoucher
    {
        return DB::transaction(function () use ($dto): DisbursementVoucher {
            $bank = BankAccount::findOrFail($dto->bankAccountId);

            $voucherNum = $dto->voucherNumber ?? ('DV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))));

            $voucher = DisbursementVoucher::create([
                'voucher_number'       => $voucherNum,
                'purchase_bill_id'     => $dto->purchaseBillId,
                'payroll_run_id'       => $dto->payrollRunId,
                'bank_account_id'      => $bank->id,
                'prepared_by'          => $dto->preparedBy ?? auth()->id(),
                'voucher_date'         => $dto->voucherDate,
                'payee_name'           => $dto->payeeName,
                'description'          => $dto->description,
                'gross_amount'         => $dto->grossAmount,
                'withheld_tax_amount'  => $dto->withheldTaxAmount,
                'net_disbursed_amount' => $dto->netDisbursedAmount,
                'payment_method'       => $dto->paymentMethod,
                'status'               => 'PREPARED',
            ]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $voucher,
                action: 'INSERT',
                oldValues: null,
                newValues: $voucher->toArray(),
                userId: $dto->preparedBy ?? auth()->id(),
                userName: auth()->user()?->name ?? 'Disbursement Clerk',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $voucher->loadMissing(['bankAccount', 'purchaseBill', 'payrollRun', 'preparer']);
        });
    }

    /**
     * Audit a disbursement voucher (Internal Audit gate).
     */
    public function auditPaymentRequest(int $voucherId, int $auditorId): DisbursementVoucher
    {
        return DB::transaction(function () use ($voucherId, $auditorId): DisbursementVoucher {
            $voucher = DisbursementVoucher::findOrFail($voucherId);

            if ($voucher->status !== 'PREPARED' && $voucher->status !== 'DRAFT') {
                throw new DomainException("Voucher [{$voucher->voucher_number}] cannot be audited in status [{$voucher->status}].");
            }

            $oldValues = $voucher->toArray();
            $voucher->update([
                'status'     => 'AUDITED',
                'audited_by' => $auditorId,
                'audited_at' => now(),
            ]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $voucher,
                action: 'UPDATE',
                oldValues: $oldValues,
                newValues: $voucher->toArray(),
                userId: $auditorId,
                userName: auth()->user()?->name ?? 'Internal Auditor',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $voucher->loadMissing(['bankAccount', 'purchaseBill', 'payrollRun', 'auditor']);
        });
    }

    /**
     * Void a disbursement voucher and rollback state.
     */
    public function voidPaymentRequest(int $voucherId, int $userId, string $reason): DisbursementVoucher
    {
        return DB::transaction(function () use ($voucherId, $userId, $reason): DisbursementVoucher {
            $voucher = DisbursementVoucher::with('checkRegister')->findOrFail($voucherId);

            if ($voucher->status === 'RELEASED') {
                throw new DomainException("Released disbursement voucher cannot be voided directly. Create a reversal transaction instead.");
            }

            $oldValues = $voucher->toArray();
            $voucher->update([
                'status'      => 'VOIDED',
                'description' => trim($voucher->description . " [VOIDED: {$reason}]"),
            ]);

            if ($voucher->checkRegister) {
                $voucher->checkRegister->update(['status' => 'VOID']);
            }

            $this->auditTrailService->logFinancialEvent(
                auditable: $voucher,
                action: 'UPDATE',
                oldValues: $oldValues,
                newValues: $voucher->toArray(),
                userId: $userId,
                userName: auth()->user()?->name ?? 'Disbursement Approver',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $voucher;
        });
    }
}
