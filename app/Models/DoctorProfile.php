<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class DoctorProfile extends Model
{
    protected $fillable = [
        'doctor_code',
        'full_name',
        'tin',
        'specialization',
        'email',
        'phone',
        'ewt_rate_type',
        'default_ewt_rate',
        'has_sworn_declaration',
        'status',
    ];

    protected $casts = [
        'default_ewt_rate'      => 'decimal:4',
        'has_sworn_declaration' => 'boolean',
    ];

    public function birCertificates(): HasMany
    {
        return $this->hasMany(Bir2307Certificate::class, 'doctor_id');
    }
}
