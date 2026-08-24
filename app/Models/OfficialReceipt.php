<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OfficialReceipt extends Model
{
    protected $fillable = [
        'or_number',
        'payment_id',
        'invoice_id',
        'patient_account_id',
        'or_date',
        'payor_name',
        'payor_tin',
        'vatable_sales',
        'vat_exempt_sales',
        'zero_rated_sales',
        'vat_amount',
        'total_amount_collected',
        'status',
    ];

    protected $casts = [
        'or_date'                => 'date',
        'vatable_sales'          => 'decimal:4',
        'vat_exempt_sales'       => 'decimal:4',
        'zero_rated_sales'       => 'decimal:4',
        'vat_amount'             => 'decimal:4',
        'total_amount_collected' => 'decimal:4',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function patientAccount(): BelongsTo
    {
        return $this->belongsTo(PatientAccount::class);
    }
}
