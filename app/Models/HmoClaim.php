<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class HmoClaim extends Model
{
    protected $fillable = [
        'invoice_id',
        'hmo_provider',
        'loa_number',
        'card_number',
        'approved_limit',
        'claimed_amount',
        'settled_amount',
        'status',
    ];

    protected $casts = [
        'approved_limit' => 'decimal:4',
        'claimed_amount' => 'decimal:4',
        'settled_amount' => 'decimal:4',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
