<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BankReconciliation extends Model
{
    protected $fillable = [
        'bank_account_id',
        'statement_date',
        'statement_balance',
        'book_balance',
        'variance',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'statement_date' => 'date',
            'statement_balance' => 'decimal:4',
            'book_balance' => 'decimal:4',
            'variance' => 'decimal:4',
        ];
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function lines(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BankStatementLine::class);
    }
}
