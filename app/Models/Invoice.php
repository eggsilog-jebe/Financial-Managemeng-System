<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'patient_account_id',
        'invoice_date',
        'total_amount',
        'insurance_covered',
        'patient_payable',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'total_amount' => 'decimal:4',
            'insurance_covered' => 'decimal:4',
            'patient_payable' => 'decimal:4',
        ];
    }

    public function patientAccount(): BelongsTo
    {
        return $this->belongsTo(PatientAccount::class);
    }
}
