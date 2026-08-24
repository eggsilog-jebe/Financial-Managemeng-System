<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CreditNote extends Model
{
    protected $fillable = [
        'credit_note_number',
        'invoice_id',
        'patient_account_id',
        'issue_date',
        'amount',
        'reason',
        'status',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'amount'     => 'decimal:4',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function patientAccount(): BelongsTo
    {
        return $this->belongsTo(PatientAccount::class);
    }
}
