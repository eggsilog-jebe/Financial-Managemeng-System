<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PettyCashExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'petty_cash_fund_id',
        'voucher_number',
        'expense_date',
        'payee',
        'department',
        'particulars',
        'amount',
        'receipt_ref',
        'disbursement_voucher_id',
        'status',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount'       => 'decimal:4',
    ];

    public function pettyCashFund(): BelongsTo
    {
        return $this->belongsTo(PettyCashFund::class);
    }

    public function disbursementVoucher(): BelongsTo
    {
        return $this->belongsTo(DisbursementVoucher::class);
    }
}
