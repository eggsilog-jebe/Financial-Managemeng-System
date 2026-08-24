<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ThreeWayMatch extends Model
{
    protected $fillable = [
        'purchase_bill_id',
        'po_number',
        'grn_number',
        'vendor_invoice_number',
        'po_amount',
        'grn_amount',
        'invoice_amount',
        'price_variance',
        'quantity_variance',
        'match_status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'po_amount'         => 'decimal:4',
        'grn_amount'        => 'decimal:4',
        'invoice_amount'    => 'decimal:4',
        'price_variance'    => 'decimal:4',
        'quantity_variance' => 'decimal:2',
        'approved_at'       => 'datetime',
    ];

    public function purchaseBill(): BelongsTo
    {
        return $this->belongsTo(PurchaseBill::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
