<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class PaymentRequest extends Model
{
    protected $fillable = [
        'request_number',
        'department',
        'payee_name',
        'amount',
        'purpose',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
        ];
    }
}
