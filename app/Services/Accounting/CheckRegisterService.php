<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\Accounting\CheckIssueData;
use App\Models\BankAccount;
use App\Models\CheckRegister;
use App\Models\DisbursementVoucher;
use DomainException;
use Illuminate\Support\Facades\DB;

final class CheckRegisterService
{
    public function __construct(
        private readonly CasAuditTrailService $auditTrailService,
    ) {}

    /**
     * Issue an official physical bank check for a disbursement voucher.
     */
    public function issueCheck(CheckIssueData $dto): CheckRegister
    {
        return DB::transaction(function () use ($dto): CheckRegister {
            $voucher = DisbursementVoucher::findOrFail($dto->disbursementVoucherId);
            $bank = BankAccount::findOrFail($dto->bankAccountId);

            $existing = CheckRegister::where('bank_account_id', $bank->id)
                ->where('check_number', $dto->checkNumber)
                ->first();

            if ($existing) {
                throw new DomainException("Check number [{$dto->checkNumber}] has already been issued on bank [{$bank->bank_name}].");
            }

            $check = CheckRegister::create([
                'disbursement_voucher_id' => $voucher->id,
                'bank_account_id'         => $bank->id,
                'check_number'            => $dto->checkNumber,
                'check_date'              => $dto->checkDate,
                'payee_name'              => $dto->payeeName,
                'amount'                  => $dto->amount,
                'status'                  => 'ISSUED',
            ]);

            $voucher->update([
                'payment_method'   => 'CHECK',
                'check_or_eft_ref' => $dto->checkNumber,
            ]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $check,
                action: 'INSERT',
                oldValues: null,
                newValues: $check->toArray(),
                userId: auth()->id(),
                userName: auth()->user()?->name ?? 'Treasury Officer',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $check->loadMissing(['disbursementVoucher', 'bankAccount']);
        });
    }

    /**
     * Clear check upon bank reconciliation.
     */
    public function clearCheck(int $checkId): CheckRegister
    {
        return DB::transaction(function () use ($checkId): CheckRegister {
            $check = CheckRegister::findOrFail($checkId);

            if ($check->status === 'CLEARED') {
                throw new DomainException("Check [{$check->check_number}] is already CLEARED.");
            }

            $oldValues = $check->toArray();
            $check->update([
                'status'     => 'CLEARED',
                'cleared_at' => now(),
            ]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $check,
                action: 'UPDATE',
                oldValues: $oldValues,
                newValues: $check->toArray(),
                userId: auth()->id(),
                userName: auth()->user()?->name ?? 'Treasury Officer',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $check->loadMissing(['disbursementVoucher', 'bankAccount']);
        });
    }
}
