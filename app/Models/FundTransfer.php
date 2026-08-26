<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class FundTransfer extends Model
{
    protected $fillable = [
        'reference_number',
        'source_bank_account_id',
        'destination_bank_account_id',
        'source_account',
        'source_number',
        'destination_account',
        'destination_number',
        'journal_entry_id',
        'amount',
        'memo',
        'transfer_method',
        'transfer_date',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'        => 'decimal:4',
            'transfer_date' => 'date',
        ];
    }

    public function sourceBank(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'source_bank_account_id');
    }

    public function destinationBank(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'destination_bank_account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
