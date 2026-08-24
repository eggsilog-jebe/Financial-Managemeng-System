<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BankStatementLine extends Model
{
    protected $fillable = [
        'bank_reconciliation_id',
        'transaction_date',
        'reference_number',
        'description',
        'amount',
        'match_status',
        'matched_journal_line_id',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount'           => 'decimal:4',
    ];

    public function bankReconciliation(): BelongsTo
    {
        return $this->belongsTo(BankReconciliation::class);
    }

    public function matchedJournalLine(): BelongsTo
    {
        return $this->belongsTo(JournalEntryLine::class, 'matched_journal_line_id');
    }
}
