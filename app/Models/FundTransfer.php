<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class FundTransfer extends Model
{
    protected $fillable = [
        'reference_number',
        'source_account',
        'source_number',
        'destination_account',
        'destination_number',
        'amount',
        'transfer_method',
        'transfer_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'transfer_date' => 'date',
        ];
    }
}
