<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CreditNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_note_number',
        'invoice_id',
        'patient_account_id',
        'issue_date',
        'amount',
        'reason',
        'status',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'amount'     => 'decimal:4',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function patientAccount(): BelongsTo
    {
        return $this->belongsTo(PatientAccount::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope query to only include posted/applied credit notes.
     *
     * @param Builder<CreditNote> $query
     * @return Builder<CreditNote>
     */
    public function scopePosted(Builder $query): Builder
    {
        return $query->whereIn('status', ['POSTED', 'APPLIED']);
    }

    /**
     * Scope query to only include draft credit notes pending approval.
     *
     * @param Builder<CreditNote> $query
     * @return Builder<CreditNote>
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'DRAFT');
    }

    /**
     * Scope query to only include credit notes for a specific invoice.
     *
     * @param Builder<CreditNote> $query
     * @param int $invoiceId
     * @return Builder<CreditNote>
     */
    public function scopeForInvoice(Builder $query, int $invoiceId): Builder
    {
        return $query->where('invoice_id', $invoiceId);
    }
}
