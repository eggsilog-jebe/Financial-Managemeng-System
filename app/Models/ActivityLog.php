<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Request;

final class ActivityLog extends Model
{
    /**
     * Audit logs are immutable (append-only). There is no updated_at column.
     */
    public const UPDATED_AT = null;

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'user_email',
        'event',
        'module',
        'auditable_type',
        'auditable_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * User who initiated the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper to log authentication events (login, logout, failed_login).
     */
    public static function logAuth(
        string $event,
        ?User $user,
        string $description,
        ?string $ip = null,
        ?string $userAgent = null
    ): self {
        return self::create([
            'user_id'     => $user?->id,
            'user_name'   => $user?->name ?? 'Guest / Unauthenticated',
            'user_role'   => $user?->role ?? 'Guest',
            'user_email'  => $user?->email,
            'event'       => $event,
            'module'      => 'Authentication',
            'description' => $description,
            'ip_address'  => $ip ?? Request::ip(),
            'user_agent'  => $userAgent ?? Request::userAgent(),
            'url'         => Request::fullUrl(),
        ]);
    }

    /**
     * Helper to log entity lifecycle mutations (create, update, delete, post, reverse).
     *
     * @param array<string, mixed>|null $oldValues
     * @param array<string, mixed>|null $newValues
     */
    public static function logModel(
        string $event,
        Model $model,
        string $module,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null
    ): self {
        $user = auth()->user();

        return self::create([
            'user_id'        => $user?->id,
            'user_name'      => $user?->name ?? 'System Process',
            'user_role'      => $user?->role ?? 'System',
            'user_email'     => $user?->email,
            'event'          => $event,
            'module'         => $module,
            'auditable_type' => $model::class,
            'auditable_id'   => $model->getKey(),
            'description'    => $description,
            'old_values'     => $oldValues,
            'new_values'     => $newValues,
            'ip_address'     => Request::ip(),
            'user_agent'     => Request::userAgent(),
            'url'            => Request::fullUrl(),
        ]);
    }

    /**
     * Scope: Filter by event type.
     */
    public function scopeOfEvent(Builder $query, ?string $event): Builder
    {
        return $event ? $query->where('event', $event) : $query;
    }

    /**
     * Scope: Filter by module.
     */
    public function scopeOfModule(Builder $query, ?string $module): Builder
    {
        return $module ? $query->where('module', $module) : $query;
    }

    /**
     * Scope: Filter by user role.
     */
    public function scopeOfRole(Builder $query, ?string $role): Builder
    {
        return $role ? $query->where('user_role', $role) : $query;
    }

    /**
     * Scope: Filter by date range.
     */
    public function scopeDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }

    /**
     * Scope: Free-text search across description, user, and module.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $subQuery) use ($term) {
            $subQuery->where('description', 'like', "%{$term}%")
                ->orWhere('user_name', 'like', "%{$term}%")
                ->orWhere('user_email', 'like', "%{$term}%")
                ->orWhere('module', 'like', "%{$term}%")
                ->orWhere('ip_address', 'like', "%{$term}%");
        });
    }
}
