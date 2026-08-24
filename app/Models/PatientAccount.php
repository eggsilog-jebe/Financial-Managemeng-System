<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PatientAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id_number',
        'full_name',
        'admission_type',
        'hmo_provider',
        'total_billed',
        'current_balance',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_billed' => 'decimal:4',
            'current_balance' => 'decimal:4',
        ];
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
