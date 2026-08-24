<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'item_code',
        'description',
        'department',
        'revenue_category',
        'quantity',
        'unit_price',
        'gross_amount',
        'is_vatable',
        'is_senior_pwd_eligible',
    ];

    protected $casts = [
        'quantity'               => 'decimal:2',
        'unit_price'             => 'decimal:4',
        'gross_amount'           => 'decimal:4',
        'is_vatable'             => 'boolean',
        'is_senior_pwd_eligible' => 'boolean',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
