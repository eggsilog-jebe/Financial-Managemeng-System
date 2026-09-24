<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

final class LoginSecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('cfo@hospital.gov.ph|127.0.0.1');
    }

    public function test_login_page_emits_enterprise_security_headers(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_login_form_omits_remember_me_for_shared_workstation_security(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertDontSee('name="remember"', false);
    }

    public function test_login_rejects_passwords_exceeding_128_characters(): void
    {
        $oversizedPassword = str_repeat('A', 129);

        $response = $this->from('/login')->post('/login', [
            'email'    => 'cfo@hospital.gov.ph',
            'password' => $oversizedPassword,
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_login_normalizes_whitespace_and_casing_on_email(): void
    {
        $user = User::factory()->create([
            'email'    => 'accountant@hospital.gov.ph',
            'role'     => 'Accountant',
            'password' => Hash::make('CorrectPassword123!'),
        ]);

        $deviceUuid = 'ws-accountant-norm-1';
        \App\Models\UserWorkstation::create([
            'user_id'          => $user->id,
            'device_uuid'      => $deviceUuid,
            'workstation_name' => 'Accountant Workstation 1',
            'status'           => \App\Models\UserWorkstation::STATUS_APPROVED,
            'approved_at'      => now(),
        ]);

        $response = $this->withHeaders(['X-Workstation-UUID' => $deviceUuid])
            ->post('/login', [
                'email'       => '   AcCoUnTanT@Hospital.Gov.PH   ',
                'password'    => 'CorrectPassword123!',
                'device_uuid' => $deviceUuid,
            ]);

        $response->assertRedirect(route('two-factor.challenge'));
        $this->assertAuthenticated();
        $this->assertSame('accountant@hospital.gov.ph', auth()->user()->email);
    }

    public function test_composite_rate_limiter_blocks_repeated_failed_attempts_and_logs_audit_event(): void
    {
        User::factory()->create([
            'email'    => 'cfo@hospital.gov.ph',
            'role'     => 'CFO',
            'password' => Hash::make('CorrectPassword123!'),
        ]);

        // Attempt 5 incorrect logins to exhaust the throttle limit
        for ($i = 0; $i < 5; $i++) {
            $this->from('/login')->post('/login', [
                'email'    => 'cfo@hospital.gov.ph',
                'password' => 'WrongPassword!',
            ]);
        }

        // 6th attempt should be blocked immediately by RateLimiter
        $blockedResponse = $this->from('/login')->post('/login', [
            'email'    => 'cfo@hospital.gov.ph',
            'password' => 'WrongPassword!',
        ]);

        $blockedResponse->assertRedirect('/login');
        $blockedResponse->assertSessionHasErrors('email');

        // Verify security event is audited in activity_logs
        $this->assertDatabaseHas('activity_logs', [
            'module' => 'Authentication',
            'event'  => 'rate_limited',
        ]);
    }

    public function test_unauthenticated_request_to_role_guarded_route_is_redirected_to_login_not_auto_logged_in(): void
    {
        // Prior to hardening, RoleAuthorization in local/testing would automatically authenticate as CFO
        $response = $this->get('/accounting/dashboard');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
