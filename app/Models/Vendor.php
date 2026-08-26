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
        'contact_person',
        'email',
        'phone',
        'payment_terms_days',
        'status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'payment_terms_days' => 'integer',
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
