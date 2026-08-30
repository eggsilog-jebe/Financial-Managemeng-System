<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'vendor_code',
        'name',
        'tin',
        'tax_type',
        'default_ewt_rate',
        'default_atc_code',
        'contact_person',
        'email',
        'phone',
        'registered_address',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'payment_terms_days',
        'status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'payment_terms_days' => 'integer',
            'default_ewt_rate'   => 'decimal:4',
        ];
    }

    public function getVendorCodeAttribute(): string
    {
        return (string) ($this->attributes['code'] ?? '');
    }

    public function setVendorCodeAttribute(?string $value): void
    {
        if ($value !== null) {
            $this->attributes['code'] = trim($value);
        }
    }

    public function getIsActiveAttribute(): bool
    {
        return ($this->attributes['status'] ?? 'Active') === 'Active';
    }

    public function setIsActiveAttribute(bool|int|string|null $value): void
    {
        if ($value !== null) {
            $isActive = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            $this->attributes['status'] = $isActive ? 'Active' : 'Inactive';
        }
    }

    public function purchaseBills(): HasMany
    {
        return $this->hasMany(PurchaseBill::class);
    }

    public function birCertificates(): HasMany
    {
        return $this->hasMany(Bir2307Certificate::class);
    }

    /** @param Builder<Vendor> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'Active');
    }
}
