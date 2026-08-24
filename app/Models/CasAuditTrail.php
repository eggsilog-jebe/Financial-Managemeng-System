<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CasAuditTrail extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'event_uuid',
        'user_id',
        'user_name',
        'ip_address',
        'auditable_type',
        'auditable_id',
        'action',
        'old_values',
        'new_values',
        'record_hash',
        'previous_hash',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
