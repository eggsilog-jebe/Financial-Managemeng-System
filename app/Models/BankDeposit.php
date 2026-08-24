<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BankDeposit extends Model
{
    protected $fillable = [
        'deposit_reference',
        'bank_account_id',
        'cashier_shift_id',
        'deposit_date',
        'cash_amount',
        'check_amount',
        'total_deposited',
        'bank_reference_number',
        'validated_by_teller',
        'status',
    ];

    protected $casts = [
        'deposit_date'    => 'date',
        'cash_amount'     => 'decimal:4',
        'check_amount'    => 'decimal:4',
        'total_deposited' => 'decimal:4',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function cashierShift(): BelongsTo
    {
        return $this->belongsTo(CashierShift::class);
    }
}
