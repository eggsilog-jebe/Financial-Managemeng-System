<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\Auth\EmailOtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class TwoFactorChallengeTest extends TestCase
{
    use RefreshDatabase;

    public function test_challenge_page_renders_for_authenticated_user_with_2fa(): void
    {
        $user = User::factory()->create([
            'email'                   => 'cfo@hospital.gov.ph',
            'role'                    => 'CFO',
            'two_factor_confirmed_at' => now(),
            'two_factor_secret'       => null,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['auth.2fa_passed' => false])
            ->get('/two-factor-challenge');

        $response->assertStatus(200);
        $response->assertSee('Check Your Email');
        $response->assertSee('Verify & Sign In', false);
    }

    public function test_challenge_verifies_successfully_with_email_otp_parameter(): void
    {
        $user = User::factory()->create([
            'email'                   => 'cfo@hospital.gov.ph',
            'role'                    => 'CFO',
            'two_factor_confirmed_at' => now(),
            'two_factor_secret'       => null,
        ]);

        // Place OTP in cache as EmailOtpService does
        Cache::put('email_otp:' . $user->id, '132381', 600);

        $response = $this->actingAs($user)->post('/two-factor-challenge', [
            'email_otp' => '132381',
        ]);

        $response->assertRedirect(route('accounting.dashboard'));
        $this->assertTrue(session('auth.2fa_passed'));
        $this->assertFalse(Cache::has('email_otp:' . $user->id)); // Burned
    }

    public function test_challenge_verifies_successfully_with_code_parameter(): void
    {
        $user = User::factory()->create([
            'email'                   => 'cfo@hospital.gov.ph',
            'role'                    => 'CFO',
            'two_factor_confirmed_at' => now(),
            'two_factor_secret'       => null,
        ]);

        Cache::put('email_otp:' . $user->id, '132381', 600);

        $response = $this->actingAs($user)->post('/two-factor-challenge', [
            'code' => '132381',
        ]);

        $response->assertRedirect(route('accounting.dashboard'));
        $this->assertTrue(session('auth.2fa_passed'));
    }

    public function test_challenge_fails_with_invalid_otp(): void
    {
        $user = User::factory()->create([
            'email'                   => 'cfo@hospital.gov.ph',
            'role'                    => 'CFO',
            'two_factor_confirmed_at' => now(),
            'two_factor_secret'       => null,
        ]);

        Cache::put('email_otp:' . $user->id, '132381', 600);

        $response = $this->from('/two-factor-challenge')
            ->actingAs($user)
            ->withSession(['auth.2fa_passed' => false])
            ->post('/two-factor-challenge', [
                'email_otp' => '999999',
            ]);

        $response->assertRedirect('/two-factor-challenge');
        $response->assertSessionHasErrors('code');
        $this->assertFalse(session('auth.2fa_passed', false));
    }

    public function test_challenge_validation_rejects_empty_submission(): void
    {
        $user = User::factory()->create([
            'email'                   => 'cfo@hospital.gov.ph',
            'role'                    => 'CFO',
            'two_factor_confirmed_at' => now(),
            'two_factor_secret'       => null,
        ]);

        $response = $this->from('/two-factor-challenge')
            ->actingAs($user)
            ->withSession(['auth.2fa_passed' => false])
            ->post('/two-factor-challenge', [
                'email_otp' => '',
                'code'      => '',
            ]);

        $response->assertRedirect('/two-factor-challenge');
        $response->assertSessionHasErrors(['code' => 'Please enter your 6-digit authentication code or a recovery code.']);
    }

    public function test_authenticated_user_with_2fa_passed_is_redirected_away_from_challenge_page(): void
    {
        $user = User::factory()->create([
            'email'                   => 'cfo@hospital.gov.ph',
            'role'                    => 'CFO',
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->withSession(['auth.2fa_passed' => true])
            ->get('/two-factor-challenge');

        $response->assertRedirect(route('accounting.dashboard'));
    }

    public function test_authenticated_user_is_redirected_away_from_login_page(): void
    {
        $user = User::factory()->create([
            'email'                   => 'cfo@hospital.gov.ph',
            'role'                    => 'CFO',
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect(route('accounting.dashboard'));
    }

    public function test_anti_bfcache_headers_are_emitted_on_auth_and_application_pages(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('Cache-Control');
        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);
        $response->assertHeader('Pragma', 'no-cache');
    }

    public function test_every_login_requires_two_factor_verification_without_device_bypass(): void
    {
        $user = User::factory()->create([
            'email'                   => 'cfo@hospital.gov.ph',
            'role'                    => 'CFO',
            'password'                => \Illuminate\Support\Facades\Hash::make('SecurePassword123!'),
            'two_factor_confirmed_at' => now(),
            'two_factor_secret'       => null,
        ]);

        // Submit credentials
        $response = $this->post('/login', [
            'email'    => 'cfo@hospital.gov.ph',
            'password' => 'SecurePassword123!',
        ]);

        $response->assertRedirect(route('two-factor.challenge'));
        $this->assertFalse(session('auth.2fa_passed', true));

        // Attempting to visit dashboard before 2FA verification must redirect to 2FA challenge
        $dashboardResponse = $this->get('/accounting/dashboard');
        $dashboardResponse->assertRedirect(route('two-factor.challenge'));
    }
}
