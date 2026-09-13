<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Instant 1-Click Demo Login');
        $response->assertSee('CFO Executive');
        $response->assertSee('Staff Accountant');
        $response->assertSee('Cashier Supervisor');
        $response->assertSee('BIR CAS Auditor');
    }

    public function test_quick_login_cfo_works_and_auto_provisions(): void
    {
        $response = $this->get('/login/quick/cfo');

        $response->assertRedirect(route('accounting.dashboard'));
        $this->assertAuthenticated();
        $this->assertSame('CFO', auth()->user()->role);
    }

    public function test_quick_login_accountant_works(): void
    {
        $response = $this->get('/login/quick/accountant');

        $response->assertRedirect(route('accounting.dashboard'));
        $this->assertAuthenticated();
        $this->assertSame('StaffAccountant', auth()->user()->role);
    }

    public function test_quick_login_cashier_works_and_redirects_to_cashier_desk(): void
    {
        $response = $this->get('/login/quick/cashier');

        $response->assertRedirect(route('collection.cashier-desk'));
        $this->assertAuthenticated();
        $this->assertSame('Cashier', auth()->user()->role);
    }

    public function test_quick_login_auditor_works(): void
    {
        $response = $this->get('/login/quick/auditor');

        $response->assertRedirect(route('accounting.dashboard'));
        $this->assertAuthenticated();
        $this->assertSame('Auditor', auth()->user()->role);
    }

    public function test_standard_login_with_default_password_succeeds(): void
    {
        User::factory()->create([
            'email' => 'cfo@hospital.test',
            'role'  => 'CFO',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'cfo@hospital.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('accounting.dashboard'));
        $this->assertAuthenticated();
    }
}
