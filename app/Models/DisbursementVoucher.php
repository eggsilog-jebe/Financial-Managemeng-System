<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class DisbursementVoucher extends Model
{
    protected $fillable = [
        'voucher_number',
        'purchase_bill_id',
        'payroll_run_id',
        'bank_account_id',
        'voucher_date',
        'payee_name',
        'gross_amount',
        'withheld_tax_amount',
        'net_disbursed_amount',
        'payment_method',
        'check_or_eft_ref',
        'status',
        'approved_by',
        'released_at',
    ];

    protected $casts = [
        'voucher_date'         => 'date',
        'gross_amount'         => 'decimal:4',
        'withheld_tax_amount'  => 'decimal:4',
        'net_disbursed_amount' => 'decimal:4',
        'released_at'          => 'datetime',
    ];

    public function purchaseBill(): BelongsTo
    {
        return $this->belongsTo(PurchaseBill::class);
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function checkRegister(): HasOne
    {
        return $this->hasOne(CheckRegister::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
