<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class BudgetAllocation extends Model
{
    protected $fillable = [
        'department',
        'department_code',
        'category',
        'department_head',
        'fiscal_year',
        'allocated_amount',
        'spent_amount',
        'remaining_balance',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'allocated_amount'  => 'decimal:4',
            'spent_amount'      => 'decimal:4',
            'remaining_balance' => 'decimal:4',
        ];
    }

    public function encumbrances(): HasMany
    {
        return $this->hasMany(BudgetEncumbrance::class);
    }

    public function activeEncumbrances(): HasMany
    {
        return $this->hasMany(BudgetEncumbrance::class)->where('status', 'COMMITTED');
    }

    public function reallocationsOut(): HasMany
    {
        return $this->hasMany(BudgetReallocation::class, 'source_budget_allocation_id');
    }

    public function reallocationsIn(): HasMany
    {
        return $this->hasMany(BudgetReallocation::class, 'destination_budget_allocation_id');
    }

    /**
     * Compute total active encumbrances using bcmath to prevent float loss.
     */
    public function getActiveEncumberedTotalAttribute(): string
    {
        $sum = '0.0000';
        foreach ($this->activeEncumbrances as $enc) {
            $sum = bcadd($sum, (string) $enc->encumbered_amount, 4);
        }
        return $sum;
    }

    /**
     * Compute available unencumbered budget capacity.
     */
    public function getAvailableUnencumberedBalanceAttribute(): string
    {
        return bcsub((string) $this->remaining_balance, $this->active_encumbered_total, 4);
    }

    /**
     * Compute expenditure burn rate percentage.
     */
    public function getBurnRateAttribute(): float
    {
        $allocated = (string) $this->allocated_amount;
        if (bccomp($allocated, '0.0000', 4) <= 0) {
            return 0.0;
        }

        $rate = bcmul(bcdiv((string) $this->spent_amount, $allocated, 4), '100', 2);
        return (float) $rate;
    }
}
