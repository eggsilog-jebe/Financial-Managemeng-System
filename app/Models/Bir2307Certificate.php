<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Bir2307Certificate extends Model
{
    protected $table = 'bir_2307_certificates';

    protected $fillable = [
        'certificate_number',
        'purchase_bill_id',
        'vendor_id',
        'doctor_id',
        'period_from',
        'period_to',
        'payee_name',
        'payee_tin',
        'atc_code',
        'tax_base_amount',
        'tax_rate',
        'tax_withheld',
        'form_status',
    ];

    protected $casts = [
        'period_from'     => 'date',
        'period_to'       => 'date',
        'tax_base_amount' => 'decimal:4',
        'tax_rate'        => 'decimal:4',
        'tax_withheld'    => 'decimal:4',
    ];

    public function purchaseBill(): BelongsTo
    {
        return $this->belongsTo(PurchaseBill::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_id');
    }

    public function doctorProfile(): BelongsTo
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_id');
    }
}
