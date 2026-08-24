<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PhilhealthClaim extends Model
{
    protected $fillable = [
        'invoice_id',
        'claim_series_number',
        'member_pin',
        'patient_pin',
        'membership_type',
        'primary_icd_code',
        'primary_case_rate_code',
        'primary_case_rate_amount',
        'secondary_case_rate_code',
        'secondary_case_rate_amount',
        'total_case_rate_amount',
        'hospital_fee_share',
        'professional_fee_share',
        'claim_status',
        'transmitted_at',
        'settled_at',
    ];

    protected $casts = [
        'primary_case_rate_amount'   => 'decimal:4',
        'secondary_case_rate_amount' => 'decimal:4',
        'total_case_rate_amount'     => 'decimal:4',
        'hospital_fee_share'         => 'decimal:4',
        'professional_fee_share'     => 'decimal:4',
        'transmitted_at'             => 'date',
        'settled_at'                 => 'date',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
