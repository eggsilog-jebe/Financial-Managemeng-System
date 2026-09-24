<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'status',
    'must_change_password',
    'last_login_at',
    'last_login_ip',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'two_factor_confirmed_at',
])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'        => 'datetime',
            'last_login_at'            => 'datetime',
            'password'                 => 'hashed',
            'must_change_password'     => 'boolean',
            'two_factor_confirmed_at'  => 'datetime',
        ];
    }

    /** Whether this user account is active and allowed to log in. */
    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    /** Whether this user account is currently suspended. */
    public function isSuspended(): bool
    {
        return ($this->status ?? 'active') === 'suspended';
    }

    /**
     * Whether 2FA has been fully enrolled and confirmed for this user.
     * A user with a provisioned secret but not yet confirmed is NOT considered enabled.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }

    /** Human-readable role label for display. */
    public function roleLabel(): string
    {
        return match ($this->role) {
            'CFO'            => 'Chief Financial Officer',
            'FinanceManager' => 'Finance Manager',
            'StaffAccountant'=> 'Staff Accountant',
            'BillingClerk'   => 'Billing Clerk',
            'Cashier'        => 'Cashier',
            'Auditor'        => 'Internal Auditor',
            default          => $this->role ?? 'Unknown',
        };
    }

    /** Whether this user has Super Administrator / CFO privileges. */
    public function isSuperAdmin(): bool
    {
        return in_array($this->role, ['SuperAdmin', 'CFO', 'FinanceDirector'], true);
    }

    /** All workstations bound or requested for this user. */
    public function workstations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserWorkstation::class);
    }

    /** Approved and active workstations (max 3). */
    public function approvedWorkstations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserWorkstation::class)->where('status', UserWorkstation::STATUS_APPROVED);
    }

    /** Pending workstation authorization requests. */
    public function pendingWorkstations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserWorkstation::class)->where('status', UserWorkstation::STATUS_PENDING);
    }

    /** Active live sessions for this user. */
    public function activeSessions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserActiveSession::class)->where('is_terminated', false);
    }
}
