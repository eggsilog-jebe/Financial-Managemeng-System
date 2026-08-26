<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class BankReconciliation extends Model
{
    protected $fillable = [
        'bank_account_id',
        'statement_date',
        'cutoff_date',
        'statement_balance',
        'book_balance',
        'variance',
        'cleared_deposits',
        'cleared_disbursements',
        'status',
        'reconciled_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'statement_date'        => 'date',
            'cutoff_date'           => 'date',
            'statement_balance'     => 'decimal:4',
            'book_balance'          => 'decimal:4',
            'variance'              => 'decimal:4',
            'cleared_deposits'      => 'decimal:4',
            'cleared_disbursements' => 'decimal:4',
        ];
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function reconciler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(BankStatementLine::class);
    }
}
