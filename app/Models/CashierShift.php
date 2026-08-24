<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CashierShift extends Model
{
    protected $fillable = [
        'shift_code',
        'cashier_id',
        'terminal_name',
        'opened_at',
        'closed_at',
        'opening_cash_float',
        'expected_cash',
        'actual_cash_counted',
        'cash_variance',
        'total_digital_collections',
        'total_collections',
        'status',
        'notes',
    ];

    protected $casts = [
        'opened_at'                 => 'datetime',
        'closed_at'                 => 'datetime',
        'opening_cash_float'        => 'decimal:4',
        'expected_cash'             => 'decimal:4',
        'actual_cash_counted'       => 'decimal:4',
        'cash_variance'             => 'decimal:4',
        'total_digital_collections' => 'decimal:4',
        'total_collections'         => 'decimal:4',
    ];

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function bankDeposits(): HasMany
    {
        return $this->hasMany(BankDeposit::class);
    }
}
