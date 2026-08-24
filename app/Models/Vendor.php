<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Vendor extends Model
{
    protected $fillable = [
        'code',
        'name',
        'tin',
        'contact_person',
        'email',
        'phone',
        'status',
    ];

    public function purchaseBills(): HasMany
    {
        return $this->hasMany(PurchaseBill::class);
    }
}
