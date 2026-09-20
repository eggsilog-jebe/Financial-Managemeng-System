<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class GuaranteeLetter extends Model
{
    use HasFactory;

    protected $table = 'guarantee_letters';

    protected $fillable = [
        'gl_number',
        'issuing_agency',
        'patient_account_id',
        'invoice_id',
        'authorized_amount',
        'utilized_amount',
        'remaining_amount',
        'status',
        'issued_date',
        'valid_until',
        'diagnosis',
        'remarks',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'authorized_amount' => 'decimal:4',
            'utilized_amount'   => 'decimal:4',
            'remaining_amount'  => 'decimal:4',
            'issued_date'       => 'date',
            'valid_until'       => 'date',
        ];
    }

    /**
     * Patient to whom this guarantee letter was issued.
     */
    public function patientAccount(): BelongsTo
    {
        return $this->belongsTo(PatientAccount::class);
    }

    /**
     * Invoice to which this guarantee letter was applied.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Officer who encoded this guarantee letter.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Filter active letters with remaining balance.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'ACTIVE')
            ->where('remaining_amount', '>', 0);
    }

    /**
     * Scope: Filter by issuing government agency.
     */
    public function scopeByAgency(Builder $query, ?string $agency): Builder
    {
        return $agency ? $query->where('issuing_agency', $agency) : $query;
    }
}
