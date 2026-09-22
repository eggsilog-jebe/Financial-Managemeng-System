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
])]
#[Hidden(['password', 'remember_token'])]
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
            'email_verified_at'    => 'datetime',
            'last_login_at'        => 'datetime',
            'password'             => 'hashed',
            'must_change_password' => 'boolean',
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
}
