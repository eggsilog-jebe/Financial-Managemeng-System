<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BudgetEncumbrance extends Model
{
    protected $fillable = [
        'budget_allocation_id',
        'reference_type',
        'reference_number',
        'encumbered_amount',
        'liquidated_amount',
        'status',
    ];

    protected $casts = [
        'encumbered_amount' => 'decimal:4',
        'liquidated_amount' => 'decimal:4',
    ];

    public function budgetAllocation(): BelongsTo
    {
        return $this->belongsTo(BudgetAllocation::class);
    }
}
