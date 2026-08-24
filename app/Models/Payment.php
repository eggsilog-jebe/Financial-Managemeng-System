<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Payment extends Model
{
    protected $fillable = [
        'payment_reference',
        'invoice_id',
        'patient_account_id',
        'cashier_shift_id',
        'payment_date',
        'amount',
        'payment_method',
        'transaction_channel_ref',
        'payment_type',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:4',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function patientAccount(): BelongsTo
    {
        return $this->belongsTo(PatientAccount::class);
    }

    public function cashierShift(): BelongsTo
    {
        return $this->belongsTo(CashierShift::class);
    }

    public function officialReceipt(): HasOne
    {
        return $this->hasOne(OfficialReceipt::class);
    }
}
