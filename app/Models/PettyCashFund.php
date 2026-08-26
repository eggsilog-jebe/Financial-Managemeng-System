<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PettyCashFund extends Model
{
    use HasFactory;

    protected $fillable = [
        'fund_name',
        'custodian_name',
        'float_limit',
        'current_balance',
        'gl_code',
        'status',
    ];

    protected $casts = [
        'float_limit'     => 'decimal:4',
        'current_balance' => 'decimal:4',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(PettyCashExpense::class);
    }

    public function unreplenishedExpenses(): HasMany
    {
        return $this->hasMany(PettyCashExpense::class)->where('status', 'UNREPLENISHED');
    }
}
