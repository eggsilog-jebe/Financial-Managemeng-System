<?php

declare(strict_types=1);

namespace Tests\Feature\Accounting;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $cfo;
    private User $accountant;
    private User $cashier;
    private User $auditor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cfo = User::factory()->create(['role' => 'CFO', 'email' => 'cfo@hospital.local']);
        $this->accountant = User::factory()->create(['role' => 'StaffAccountant', 'email' => 'accountant@hospital.local']);
        $this->cashier = User::factory()->create(['role' => 'Cashier', 'email' => 'cashier@hospital.local']);
        $this->auditor = User::factory()->create(['role' => 'Auditor', 'email' => 'auditor@hospital.local']);
    }

    /** @test */
    public function test_cashier_can_access_pos_but_is_forbidden_from_gl_and_period_closing(): void
    {
        $this->actingAs($this->cashier);

        // 1. Can access Cashier POS Desk
        $responsePos = $this->get('/accounting/cashier');
        $responsePos->assertStatus(200);

        // 2. Forbidden from General Ledger Browser
        $responseGl = $this->get('/accounting/general-ledger');
        $responseGl->assertStatus(403);

        // 3. Forbidden from CFO Period Closing
        $responsePeriodClose = $this->get('/accounting/period-close');
        $responsePeriodClose->assertStatus(403);
    }

    /** @test */
    public function test_staff_accountant_can_view_gl_and_reports_but_is_forbidden_from_period_closing(): void
    {
        $this->actingAs($this->accountant);

        // 1. Can view General Ledger Browser
        $responseGl = $this->get('/accounting/general-ledger');
        $responseGl->assertStatus(200);

        // 2. Can view Financial Reports Hub
        $responseReports = $this->get('/accounting/reports');
        $responseReports->assertStatus(200);

        // 3. Forbidden from CFO Period Closing
        $responsePeriodClose = $this->get('/accounting/period-close');
        $responsePeriodClose->assertStatus(403);
    }

    /** @test */
    public function test_cfo_has_unrestricted_access_to_all_accounting_routes(): void
    {
        $this->actingAs($this->cfo);

        $this->get('/accounting/dashboard')->assertStatus(200);
        $this->get('/accounting/cashier')->assertStatus(200);
        $this->get('/accounting/general-ledger')->assertStatus(200);
        $this->get('/accounting/reports')->assertStatus(200);
        $this->get('/accounting/period-close')->assertStatus(200);
    }

    /** @test */
    public function test_auditor_has_read_only_access_to_gl_and_reports_but_forbidden_from_pos_and_period_closing(): void
    {
        $this->actingAs($this->auditor);

        // Read access allowed
        $this->get('/accounting/general-ledger')->assertStatus(200);
        $this->get('/accounting/reports')->assertStatus(200);

        // Operational actions forbidden
        $this->get('/accounting/cashier')->assertStatus(403);
        $this->get('/accounting/period-close')->assertStatus(403);
    }
}
