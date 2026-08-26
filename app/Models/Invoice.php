<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'patient_account_id',
        'invoice_date',
        'due_date',
        'total_amount',
        'insurance_covered',
        'discount_amount',
        'vat_amount',
        'patient_payable',
        'paid_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date'      => 'date',
            'due_date'          => 'date',
            'total_amount'      => 'decimal:4',
            'insurance_covered' => 'decimal:4',
            'discount_amount'   => 'decimal:4',
            'vat_amount'        => 'decimal:4',
            'patient_payable'   => 'decimal:4',
            'paid_amount'       => 'decimal:4',
        ];
    }

    public function patientAccount(): BelongsTo
    {
        return $this->belongsTo(PatientAccount::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function philhealthClaim(): HasOne
    {
        return $this->hasOne(PhilhealthClaim::class);
    }

    public function hmoClaims(): HasMany
    {
        return $this->hasMany(HmoClaim::class);
    }

    public function statutoryDiscounts(): HasMany
    {
        return $this->hasMany(StatutoryDiscount::class);
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getGrossAmountAttribute(): string
    {
        return (string) $this->total_amount;
    }

    public function getNetTotalAttribute(): string
    {
        return (string) $this->patient_payable;
    }

    public function getBalanceDueAttribute(): string
    {
        $payable = (string) $this->patient_payable;
        $paid = (string) $this->paid_amount;
        return bcsub($payable, $paid, 4);
    }

    /** @param Builder<Invoice> $query */
    public function scopeUnsettled(Builder $query): Builder
    {
        return $query->whereIn('status', ['UNPAID', 'PARTIAL']);
    }

    /** @param Builder<Invoice> $query */
    public function scopeSettled(Builder $query): Builder
    {
        return $query->whereIn('status', ['SETTLED', 'PAID']);
    }
}
