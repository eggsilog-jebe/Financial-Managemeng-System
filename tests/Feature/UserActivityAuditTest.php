<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserActivityAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_page_view_is_automatically_audited(): void
    {
        $user = User::factory()->create([
            'role'                    => 'CFO',
            'two_factor_confirmed_at' => now(),
        ]);

        $this->actingAs($user)
            ->withSession(['auth.2fa_passed' => true])
            ->get(route('accounting.dashboard'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id'   => $user->id,
            'user_role' => 'CFO',
            'event'     => 'viewed',
            'module'    => 'Accounting Management',
        ]);
    }

    public function test_chart_of_accounts_creation_is_audited_by_observer(): void
    {
        $user = User::factory()->create([
            'role'                    => 'CFO',
            'two_factor_confirmed_at' => now(),
        ]);

        $this->actingAs($user)
            ->withSession(['auth.2fa_passed' => true]);

        $account = Account::create([
            'code'           => '1010-001',
            'name'           => 'Petty Cash Operating Fund',
            'category'       => 'ASSET',
            'normal_balance' => 'DEBIT',
            'is_active'      => true,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'event'          => 'created',
            'module'         => 'General Ledger',
            'auditable_type' => Account::class,
            'auditable_id'   => $account->id,
        ]);
    }

    public function test_sensitive_attributes_are_strictly_redacted_in_audit_diff(): void
    {
        $user = User::factory()->create([
            'role'                    => 'CFO',
            'two_factor_confirmed_at' => now(),
        ]);

        $this->actingAs($user)
            ->withSession(['auth.2fa_passed' => true]);

        $targetUser = User::create([
            'name'     => 'New Staff Accountant',
            'email'    => 'accountant@hospital.gov.ph',
            'role'     => 'StaffAccountant',
            'password' => bcrypt('SecretHospitalPass123!'),
            'status'   => 'active',
        ]);

        $log = ActivityLog::where('auditable_type', User::class)
            ->where('auditable_id', $targetUser->id)
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('[REDACTED]', $log->new_values['password'] ?? null);
    }

    public function test_audit_log_page_renders_with_activity_badges(): void
    {
        $user = User::factory()->create([
            'role'                    => 'CFO',
            'two_factor_confirmed_at' => now(),
        ]);

        ActivityLog::create([
            'user_id'     => $user->id,
            'user_name'   => $user->name,
            'user_role'   => 'CFO',
            'user_email'  => $user->email,
            'event'       => 'viewed',
            'module'      => 'General Ledger',
            'description' => "User [{$user->name}] accessed General Ledger Chart of Accounts.",
            'ip_address'  => '127.0.0.1',
            'created_at'  => now(),
        ]);

        $response = $this->actingAs($user)
            ->withSession(['auth.2fa_passed' => true])
            ->get(route('accounting.audit-log'));

        $response->assertStatus(200);
        $response->assertSee('Audit Trail Records');
        $response->assertSee('VIEWED');
        $response->assertSee('General Ledger Chart of Accounts');
    }

    public function test_polling_endpoints_are_excluded_from_audit_logging(): void
    {
        $user = User::factory()->create([
            'role'                    => 'CFO',
            'two_factor_confirmed_at' => now(),
        ]);

        $initialCount = ActivityLog::count();

        $this->actingAs($user)
            ->withSession(['auth.2fa_passed' => true])
            ->get(route('user-security.workstations.poll'));

        $this->assertEquals($initialCount, ActivityLog::count());
    }
}
