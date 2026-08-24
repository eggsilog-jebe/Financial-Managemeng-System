<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PurchaseBill extends Model
{
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
            'bill_date' => 'date',
            'due_date' => 'date',
            'total_amount' => 'decimal:4',
            'paid_amount' => 'decimal:4',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BillItem::class);
    }

    public function threeWayMatch(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ThreeWayMatch::class);
    }

    public function birCertificate(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Bir2307Certificate::class);
    }
}
