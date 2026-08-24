<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StatutoryDiscount extends Model
{
    protected $fillable = [
        'invoice_id',
        'discount_type',
        'id_card_number',
        'vat_exempt_amount',
        'discount_rate',
        'discount_amount',
    ];

    protected $casts = [
        'vat_exempt_amount' => 'decimal:4',
        'discount_rate'      => 'decimal:4',
        'discount_amount'    => 'decimal:4',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
