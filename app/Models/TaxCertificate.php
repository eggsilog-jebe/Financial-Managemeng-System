<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class TaxCertificate extends Model
{
    protected $fillable = [
        'cert_number',
        'payee_name',
        'payee_role',
        'payee_type',
        'tin',
        'atc_code',
        'gross_income',
        'tax_withheld',
        'form_type',
    ];

    protected function casts(): array
    {
        return [
            'gross_income' => 'decimal:4',
            'tax_withheld' => 'decimal:4',
        ];
    }
}
