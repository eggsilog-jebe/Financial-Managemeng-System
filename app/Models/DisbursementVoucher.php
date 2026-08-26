<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class DisbursementVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_number',
        'purchase_bill_id',
        'payroll_run_id',
        'bank_account_id',
        'prepared_by',
        'voucher_date',
        'payee_name',
        'description',
        'gross_amount',
        'withheld_tax_amount',
        'net_disbursed_amount',
        'payment_method',
        'check_or_eft_ref',
        'status',
        'approved_by',
        'audited_by',
        'audited_at',
        'released_at',
    ];

    protected $casts = [
        'voucher_date'         => 'date',
        'gross_amount'         => 'decimal:4',
        'withheld_tax_amount'  => 'decimal:4',
        'net_disbursed_amount' => 'decimal:4',
        'audited_at'           => 'datetime',
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

    public function preparer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'audited_by');
    }

    public function pettyCashExpenses(): HasMany
    {
        return $this->hasMany(PettyCashExpense::class);
    }

    /** @param Builder<DisbursementVoucher> $query */
    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->whereIn('status', ['DRAFT', 'PREPARED', 'AUDITED']);
    }

    /** @param Builder<DisbursementVoucher> $query */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'APPROVED');
    }

    /** @param Builder<DisbursementVoucher> $query */
    public function scopeReleased(Builder $query): Builder
    {
        return $query->where('status', 'RELEASED');
    }
}
