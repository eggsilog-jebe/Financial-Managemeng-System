<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class TaxRule extends Model
{
    protected $fillable = [
        'tax_code',
        'name',
        'atc_code',
        'category',
        'cat_type',
        'rate',
        'scope',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:4',
        ];
    }
}
