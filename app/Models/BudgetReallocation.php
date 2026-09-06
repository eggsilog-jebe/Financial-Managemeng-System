<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BudgetReallocation extends Model
{
    protected $fillable = [
        'reference_number',
        'source_budget_allocation_id',
        'destination_budget_allocation_id',
        'source_department',
        'destination_department',
        'fiscal_year',
        'amount',
        'transfer_date',
        'reason',
        'status',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'        => 'decimal:4',
            'transfer_date' => 'date',
        ];
    }

    public function sourceAllocation(): BelongsTo
    {
        return $this->belongsTo(BudgetAllocation::class, 'source_budget_allocation_id');
    }

    public function destinationAllocation(): BelongsTo
    {
        return $this->belongsTo(BudgetAllocation::class, 'destination_budget_allocation_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
