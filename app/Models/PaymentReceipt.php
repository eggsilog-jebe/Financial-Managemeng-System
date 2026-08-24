<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class PaymentReceipt extends Model
{
    protected $fillable = [
        'receipt_number',
        'payer_name',
        'amount_paid',
        'payment_method',
        'receipt_date',
        'cashier_name',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid' => 'decimal:4',
            'receipt_date' => 'date',
        ];
    }
}
