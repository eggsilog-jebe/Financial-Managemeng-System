<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class TaxReturn extends Model
{
    protected $fillable = [
        'return_number',
        'form_type',
        'period_covered',
        'tax_due',
        'status',
        'filing_date',
    ];

    protected function casts(): array
    {
        return [
            'tax_due' => 'decimal:4',
            'filing_date' => 'date',
        ];
    }
}
