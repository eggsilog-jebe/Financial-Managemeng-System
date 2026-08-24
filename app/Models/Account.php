<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

final class Account extends Model
{
    protected $fillable = [
        'code',
        'name',
        'category',
        'normal_balance',
        'department',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    /**
     * Compute running balance from posted journal entry lines.
     * For DEBIT-normal accounts: balance = total debits - total credits.
     * For CREDIT-normal accounts: balance = total credits - total debits.
     */
    public function getCurrentBalanceAttribute(): string
    {
        $totalDebit  = $this->journalEntryLines->sum('debit');
        $totalCredit = $this->journalEntryLines->sum('credit');

        $balance = strtoupper((string) $this->normal_balance) === 'DEBIT'
            ? bcsub((string) $totalDebit, (string) $totalCredit, 4)
            : bcsub((string) $totalCredit, (string) $totalDebit, 4);

        return $balance;
    }

    /** @param Builder<Account> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
