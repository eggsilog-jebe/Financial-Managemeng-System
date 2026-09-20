<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_login_creates_audit_log_entry(): void
    {
        $cfo = User::factory()->create([
            'email'    => 'cfo@hospital.gov.ph',
            'role'     => 'CFO',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
        ]);

        $this->post('/login', [
            'email'    => 'cfo@hospital.gov.ph',
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'event'     => 'login',
            'user_role' => 'CFO',
            'module'    => 'Authentication',
        ]);
    }

    public function test_failed_login_creates_audit_log_entry(): void
    {
        $this->post('/login', [
            'email'    => 'intruder@unknown.com',
            'password' => 'badpassword',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'event'  => 'failed_login',
            'module' => 'Authentication',
        ]);
    }

    public function test_cfo_can_access_audit_log_viewer(): void
    {
        $cfo = User::factory()->create([
            'email' => 'cfo@hospital.gov.ph',
            'role'  => 'CFO',
        ]);

        $response = $this->actingAs($cfo)->get(route('accounting.audit-log'));

        $response->assertStatus(200);
        $response->assertSee('System Audit Trail');
    }

    public function test_auditor_can_access_audit_log_viewer(): void
    {
        $auditor = User::factory()->create([
            'email' => 'auditor@hospital.gov.ph',
            'role'  => 'Auditor',
        ]);

        $response = $this->actingAs($auditor)->get(route('accounting.audit-log'));

        $response->assertStatus(200);
        $response->assertSee('System Audit Trail');
    }

    public function test_cashier_is_forbidden_from_accessing_audit_log(): void
    {
        $cashier = User::factory()->create([
            'email' => 'cashier@hospital.gov.ph',
            'role'  => 'Cashier',
        ]);

        $response = $this->actingAs($cashier)->get(route('accounting.audit-log'));

        $response->assertStatus(403);
    }
}
