<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class FiscalPeriod extends Model
{
    protected $fillable = [
        'period_code',
        'fiscal_year',
        'period_number',
        'start_date',
        'end_date',
        'status',
        'closed_by',
        'closed_at',
        'closing_journal_entry_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'closed_at'  => 'datetime',
    ];

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function closingJournalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'closing_journal_entry_id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'OPEN';
    }

    public function isLocked(): bool
    {
        return $this->status === 'LOCKED';
    }

    public function isClosed(): bool
    {
        return in_array($this->status, ['CLOSED', 'AUDITED'], true);
    }
}
