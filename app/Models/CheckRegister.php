<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CheckRegister extends Model
{
    protected $fillable = [
        'disbursement_voucher_id',
        'bank_account_id',
        'check_number',
        'check_date',
        'payee_name',
        'amount',
        'status',
        'cleared_at',
    ];

    protected $casts = [
        'check_date' => 'date',
        'amount'     => 'decimal:4',
        'cleared_at' => 'date',
    ];

    public function disbursementVoucher(): BelongsTo
    {
        return $this->belongsTo(DisbursementVoucher::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }
}
