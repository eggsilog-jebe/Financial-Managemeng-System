<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PatientAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id_number',
        'patient_mrn',
        'full_name',
        'admission_type',
        'hmo_provider',
        'phone',
        'email',
        'address',
        'total_billed',
        'current_balance',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_billed'    => 'decimal:4',
            'current_balance' => 'decimal:4',
        ];
    }

    public function getPatientMrnAttribute(): string
    {
        return (string) ($this->attributes['patient_id_number'] ?? '');
    }

    public function setPatientMrnAttribute(?string $value): void
    {
        if ($value !== null) {
            $this->attributes['patient_id_number'] = trim($value);
        }
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** @param Builder<PatientAccount> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'Active');
    }

    /** @param Builder<PatientAccount> $query */
    public function scopeWithOutstandingBalance(Builder $query): Builder
    {
        return $query->where('current_balance', '>', 0);
    }
}
