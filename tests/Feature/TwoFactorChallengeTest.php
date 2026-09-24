<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

final class TwoFactorChallengeTest extends TestCase
{
    use RefreshDatabase;

    private Google2FA $google2fa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->google2fa = new Google2FA();
    }

    public function test_challenge_page_renders_for_authenticated_user_with_2fa(): void
    {
        $secret = $this->google2fa->generateSecretKey(32);

        $user = User::factory()->create([
            'email'                   => 'cfo@hospital.gov.ph',
            'role'                    => 'CFO',
            'two_factor_confirmed_at' => now(),
            'two_factor_secret'       => Crypt::encryptString($secret),
        ]);

        $response = $this->actingAs($user)
            ->withSession(['auth.2fa_passed' => false])
            ->get('/two-factor-challenge');

        $response->assertStatus(200);
        $response->assertSee('Enter Authenticator Code');
        $response->assertSee('Google Authenticator');
        $response->assertSee('Verify &amp; Sign In', false);
    }

    public function test_challenge_verifies_successfully_with_valid_totp_code(): void
    {
        $secret = $this->google2fa->generateSecretKey(32);

        $user = User::factory()->create([
            'email'                   => 'cfo@hospital.gov.ph',
            'role'                    => 'CFO',
            'two_factor_confirmed_at' => now(),
            'two_factor_secret'       => Crypt::encryptString($secret),
        ]);

        $validTotp = $this->google2fa->getCurrentOtp($secret);

        $response = $this->actingAs($user)->post('/two-factor-challenge', [
            'code' => $validTotp,
        ]);

        $response->assertRedirect(route('accounting.dashboard'));
        $this->assertTrue(session('auth.2fa_passed'));
    }

    public function test_challenge_verifies_successfully_with_recovery_code(): void
    {
        $secret = $this->google2fa->generateSecretKey(32);
        $recoveryCode = 'ABCDE-12345';

        $user = User::factory()->create([
            'email'                     => 'cfo@hospital.gov.ph',
            'role'                      => 'CFO',
            'two_factor_confirmed_at'   => now(),
            'two_factor_secret'         => Crypt::encryptString($secret),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode([Hash::make($recoveryCode)])),
        ]);

        $response = $this->actingAs($user)->post('/two-factor-challenge', [
            'recovery_code' => $recoveryCode,
        ]);

        $response->assertRedirect(route('accounting.dashboard'));
        $this->assertTrue(session('auth.2fa_passed'));
    }

    public function test_challenge_fails_with_invalid_totp_code(): void
    {
        $secret = $this->google2fa->generateSecretKey(32);

        $user = User::factory()->create([
            'email'                   => 'cfo@hospital.gov.ph',
            'role'                    => 'CFO',
            'two_factor_confirmed_at' => now(),
            'two_factor_secret'       => Crypt::encryptString($secret),
        ]);

        $response = $this->from('/two-factor-challenge')
            ->actingAs($user)
            ->withSession(['auth.2fa_passed' => false])
            ->post('/two-factor-challenge', [
                'code' => '000000',
            ]);

        $response->assertRedirect('/two-factor-challenge');
        $response->assertSessionHasErrors('code');
        $this->assertFalse(session('auth.2fa_passed', false));
    }

    public function test_challenge_validation_rejects_empty_submission(): void
    {
        $secret = $this->google2fa->generateSecretKey(32);

        $user = User::factory()->create([
            'email'                   => 'cfo@hospital.gov.ph',
            'role'                    => 'CFO',
            'two_factor_confirmed_at' => now(),
            'two_factor_secret'       => Crypt::encryptString($secret),
        ]);

        $response = $this->from('/two-factor-challenge')
            ->actingAs($user)
            ->withSession(['auth.2fa_passed' => false])
            ->post('/two-factor-challenge', [
                'code' => '',
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

    public function test_unconfirmed_user_logging_in_redirects_to_totp_setup(): void
    {
        $user = User::factory()->create([
            'email'                   => 'staff@hospital.gov.ph',
            'role'                    => 'StaffAccountant',
            'password'                => Hash::make('HospitalSecret123!'),
            'two_factor_confirmed_at' => null,
            'two_factor_secret'       => null,
        ]);

        $deviceUuid = 'ws-staff-setup-terminal';
        \App\Models\UserWorkstation::create([
            'user_id'          => $user->id,
            'device_uuid'      => $deviceUuid,
            'workstation_name' => 'Staff Accounting Terminal',
            'status'           => \App\Models\UserWorkstation::STATUS_APPROVED,
            'approved_at'      => now(),
        ]);

        $response = $this->withHeaders(['X-Workstation-UUID' => $deviceUuid])
            ->post('/login', [
                'email'       => 'staff@hospital.gov.ph',
                'password'    => 'HospitalSecret123!',
                'device_uuid' => $deviceUuid,
            ]);

        $response->assertRedirect(route('two-factor.setup'));
    }

    public function test_setup_page_displays_qr_code_for_user(): void
    {
        $user = User::factory()->create([
            'email'                   => 'staff@hospital.gov.ph',
            'role'                    => 'StaffAccountant',
            'two_factor_confirmed_at' => null,
            'two_factor_secret'       => null,
        ]);

        $response = $this->actingAs($user)->get('/two-factor-setup');

        $response->assertStatus(200);
        $response->assertSee('Set Up Google Authenticator');
        $response->assertSee('qr-container', false);
    }

    public function test_setup_confirmation_activates_2fa_with_valid_totp(): void
    {
        $user = User::factory()->create([
            'email'                   => 'staff@hospital.gov.ph',
            'role'                    => 'StaffAccountant',
            'two_factor_confirmed_at' => null,
            'two_factor_secret'       => null,
        ]);

        // First visit setup page to provision secret
        $this->actingAs($user)->get('/two-factor-setup');

        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);

        $secret = Crypt::decryptString($user->two_factor_secret);
        $validCode = $this->google2fa->getCurrentOtp($secret);

        $response = $this->actingAs($user)->post(route('two-factor.setup.confirm'), [
            'code' => $validCode,
        ]);

        $response->assertRedirect(route('accounting.dashboard'));
        $user->refresh();
        $this->assertTrue($user->hasTwoFactorEnabled());
        $this->assertTrue(session('auth.2fa_passed'));
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
        $secret = $this->google2fa->generateSecretKey(32);

        $user = User::factory()->create([
            'email'                   => 'cfo@hospital.gov.ph',
            'role'                    => 'CFO',
            'password'                => Hash::make('SecurePassword123!'),
            'two_factor_confirmed_at' => now(),
            'two_factor_secret'       => Crypt::encryptString($secret),
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
