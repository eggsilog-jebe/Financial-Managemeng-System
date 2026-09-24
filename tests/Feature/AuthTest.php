<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Sign in');
        $response->assertSee('Email address');
        $response->assertSee('Password');
        $response->assertDontSee('Instant 1-Click Demo Login');
        $response->assertDontSee('value="password"', false);
    }

    public function test_quick_login_route_is_removed(): void
    {
        $response = $this->get('/login/quick/cfo');

        $response->assertStatus(404);
    }

    public function test_standard_login_cfo_succeeds_and_redirects_to_two_factor_challenge(): void
    {
        User::factory()->create([
            'email'    => 'cfo@hospital.gov.ph',
            'role'     => 'CFO',
            'password' => Hash::make('EnterpriseSecure123!'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'cfo@hospital.gov.ph',
            'password' => 'EnterpriseSecure123!',
        ]);

        $response->assertRedirect(route('two-factor.challenge'));
        $this->assertAuthenticated();
        $this->assertSame('CFO', auth()->user()->role);
    }

    public function test_standard_login_cashier_redirects_to_two_factor_challenge(): void
    {
        $user = User::factory()->create([
            'email'    => 'cashier@hospital.gov.ph',
            'role'     => 'Cashier',
            'password' => Hash::make('EnterpriseSecure123!'),
        ]);

        $deviceUuid = 'ws-cashier-pos-1';
        \App\Models\UserWorkstation::create([
            'user_id'          => $user->id,
            'device_uuid'      => $deviceUuid,
            'workstation_name' => 'Cashier POS Terminal 1',
            'status'           => \App\Models\UserWorkstation::STATUS_APPROVED,
            'approved_at'      => now(),
        ]);

        $response = $this->withHeaders(['X-Workstation-UUID' => $deviceUuid])
            ->post('/login', [
                'email'       => 'cashier@hospital.gov.ph',
                'password'    => 'EnterpriseSecure123!',
                'device_uuid' => $deviceUuid,
            ]);

        $response->assertRedirect(route('two-factor.challenge'));
        $this->assertAuthenticated();
        $this->assertSame('Cashier', auth()->user()->role);
    }

    public function test_login_with_invalid_credentials_fails(): void
    {
        User::factory()->create([
            'email'    => 'cfo@hospital.gov.ph',
            'password' => Hash::make('CorrectPassword123!'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email'    => 'cfo@hospital.gov.ph',
            'password' => 'WrongPassword!',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_logout_invalidates_session_and_redirects_to_login(): void
    {
        $user = User::factory()->create([
            'email' => 'cfo@hospital.gov.ph',
            'role'  => 'CFO',
        ]);

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
