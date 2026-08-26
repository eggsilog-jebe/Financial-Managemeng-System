<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CheckRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'disbursement_voucher_id',
        'bank_account_id',
        'check_number',
        'check_date',
        'payee_name',
        'amount',
        'status',
        'cleared_at',
    ];

    protected $casts = [
        'check_date' => 'date',
        'amount'     => 'decimal:4',
        'cleared_at' => 'date',
    ];

    public function disbursementVoucher(): BelongsTo
    {
        return $this->belongsTo(DisbursementVoucher::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function getAmountInWordsAttribute(): string
    {
        $amount = (float) $this->amount;
        $pesos = (int) floor($amount);
        $cents = (int) round(($amount - $pesos) * 100);

        $words = self::convertNumberToWords($pesos);

        return trim("{$words} Pesos and {$cents}/100 Only");
    }

    public static function convertNumberToWords(int $number): string
    {
        if ($number === 0) {
            return 'Zero';
        }

        $units = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $scales = ['', 'Thousand', 'Million', 'Billion'];

        $chunks = [];
        while ($number > 0) {
            $chunks[] = $number % 1000;
            $number = (int) floor($number / 1000);
        }

        $words = [];
        foreach ($chunks as $i => $chunk) {
            if ($chunk === 0) {
                continue;
            }

            $chunkWords = [];
            $hundreds = (int) floor($chunk / 100);
            $remainder = $chunk % 100;

            if ($hundreds > 0) {
                $chunkWords[] = $units[$hundreds] . ' Hundred';
            }

            if ($remainder > 0) {
                if ($remainder < 20) {
                    $chunkWords[] = $units[$remainder];
                } else {
                    $t = (int) floor($remainder / 10);
                    $u = $remainder % 10;
                    $chunkWords[] = $tens[$t] . ($u > 0 ? '-' . $units[$u] : '');
                }
            }

            if (! empty($scales[$i])) {
                $chunkWords[] = $scales[$i];
            }

            array_unshift($words, implode(' ', $chunkWords));
        }

        return implode(' ', $words);
    }

    /** @param Builder<CheckRegister> $query */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', ['ISSUED', 'RELEASED']);
    }

    /** @param Builder<CheckRegister> $query */
    public function scopeCleared(Builder $query): Builder
    {
        return $query->where('status', 'CLEARED');
    }
}
