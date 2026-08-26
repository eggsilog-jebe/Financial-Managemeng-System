<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class PurchaseBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_number',
        'vendor_id',
        'bill_date',
        'due_date',
        'total_amount',
        'paid_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'bill_date'    => 'date',
            'due_date'     => 'date',
            'total_amount' => 'decimal:4',
            'paid_amount'  => 'decimal:4',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class);
    }

    public function threeWayMatch(): HasOne
    {
        return $this->hasOne(ThreeWayMatch::class);
    }

    public function birCertificate(): HasOne
    {
        return $this->hasOne(Bir2307Certificate::class);
    }

    public function disbursementVouchers(): HasMany
    {
        return $this->hasMany(DisbursementVoucher::class);
    }

    public function getGrossAmountAttribute(): string
    {
        return (string) $this->total_amount;
    }

    public function getWithholdingTaxAmountAttribute(): string
    {
        if ($this->relationLoaded('birCertificate') && $this->birCertificate) {
            return (string) $this->birCertificate->tax_withheld;
        }

        if ($this->relationLoaded('items') && $this->items->isNotEmpty()) {
            return (string) $this->items->sum('ewt_amount');
        }

        return (string) ($this->birCertificate()?->value('tax_withheld') ?? '0.0000');
    }

    public function getNetPayableAmountAttribute(): string
    {
        $gross = (string) $this->total_amount;
        $tax = $this->getWithholdingTaxAmountAttribute();
        return bcsub($gross, $tax, 4);
    }

    public function getBalanceDueAttribute(): string
    {
        $gross = (string) $this->total_amount;
        $paid = (string) $this->paid_amount;
        return bcsub($gross, $paid, 4);
    }

    public function getVendorInvoiceNumberAttribute(): string
    {
        if ($this->relationLoaded('threeWayMatch') && $this->threeWayMatch) {
            return (string) $this->threeWayMatch->vendor_invoice_number;
        }
        return (string) ($this->threeWayMatch()?->value('vendor_invoice_number') ?? $this->bill_number);
    }

    /** @param Builder<PurchaseBill> $query */
    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE']);
    }

    /** @param Builder<PurchaseBill> $query */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'APPROVED');
    }
}
