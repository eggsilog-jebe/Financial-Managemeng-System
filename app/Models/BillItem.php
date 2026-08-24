<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BillItem extends Model
{
    protected $fillable = [
        'purchase_bill_id',
        'item_code',
        'description',
        'expense_type',
        'quantity',
        'unit_price',
        'gross_amount',
        'atc_code',
        'ewt_rate',
        'ewt_amount',
        'net_payable',
    ];

    protected $casts = [
        'quantity'     => 'decimal:2',
        'unit_price'   => 'decimal:4',
        'gross_amount' => 'decimal:4',
        'ewt_rate'     => 'decimal:4',
        'ewt_amount'   => 'decimal:4',
        'net_payable'  => 'decimal:4',
    ];

    public function purchaseBill(): BelongsTo
    {
        return $this->belongsTo(PurchaseBill::class);
    }
}
