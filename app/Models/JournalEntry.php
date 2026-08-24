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
                throw new DomainException("Posted Journal Entry [{$entry->reference_number}] is immutable and cannot be updated.");
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
}
