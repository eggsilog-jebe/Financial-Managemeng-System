<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class BudgetAllocation extends Model
{
    protected $fillable = [
        'department',
        'fiscal_year',
        'allocated_amount',
        'spent_amount',
        'remaining_balance',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'allocated_amount' => 'decimal:4',
            'spent_amount' => 'decimal:4',
            'remaining_balance' => 'decimal:4',
        ];
    }

    public function encumbrances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BudgetEncumbrance::class);
    }
}
