<?php

declare(strict_types=1);

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class JournalEntry extends Model
{
    protected $fillable = [
        'reference_number',
        'entry_date',
        'description',
        'type',
        'status',
        'reversed_by_entry_id',
        'posted_by',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'posted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (JournalEntry $entry): void {
            if ($entry->getOriginal('status') === 'POSTED') {
                $isReversal = $entry->isDirty('status') && $entry->status === 'REVERSED';
                if (! $isReversal) {
                    throw new DomainException("Posted Journal Entry [{$entry->reference_number}] is immutable and cannot be updated.");
                }
            }
        });

        static::deleting(function (JournalEntry $entry): void {
            if ($entry->status === 'POSTED') {
                throw new DomainException("Posted Journal Entry [{$entry->reference_number}] cannot be deleted. Generate a reversal entry instead.");
            }
        });
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function reversedByEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'reversed_by_entry_id');
    }

    public function reversalOf(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(JournalEntry::class, 'reversed_by_entry_id');
    }

    public function getTotalDebitAttribute(): string
    {
        return (string) $this->lines->sum('debit');
    }

    public function getTotalCreditAttribute(): string
    {
        return (string) $this->lines->sum('credit');
    }

    /** @param \Illuminate\Database\Eloquent\Builder<JournalEntry> $query */
    public function scopePosted(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'POSTED');
    }

    /** @param \Illuminate\Database\Eloquent\Builder<JournalEntry> $query */
    public function scopeDraft(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'DRAFT');
    }

    /** @param \Illuminate\Database\Eloquent\Builder<JournalEntry> $query */
    public function scopeReversed(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'REVERSED');
    }
}
