<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\BankDeposit;
use App\Models\BudgetAllocation;
use App\Models\GuaranteeLetter;
use App\Models\PatientAccount;
use App\Models\Payment;
use App\Models\PhilhealthClaim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CfoDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_cfo_can_access_dashboard_with_all_executive_widgets(): void
    {
        $cfo = User::factory()->create([
            'email' => 'cfo@hospital.gov.ph',
            'name'  => 'Chief Financial Officer',
            'role'  => 'CFO',
        ]);

        $response = $this->actingAs($cfo)->get(route('accounting.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Executive Financial Overview');
        $response->assertSee('Chief Financial Officer');
        $response->assertSee('Public Hospital Fund Sources & Universal Healthcare Co-Pay Coverage');
        $response->assertSee('Departmental Fiscal Budget & Expenditure Burn Rate (FY 2026)');
        $response->assertSee('Treasury Daily Collection & Deposit');
        $response->assertSee('COA Circular No. 2021-014');
        $response->assertSee('Security & Audit Trail Telemetry');
    }

    public function test_public_hospital_fund_sources_breakdown_calculates_correctly(): void
    {
        $cfo = User::factory()->create([
            'email' => 'cfo@hospital.gov.ph',
            'role'  => 'CFO',
        ]);

        $patient = PatientAccount::factory()->create();

        // Create Guarantee Letter
        GuaranteeLetter::create([
            'gl_number'          => 'GL-TEST-001',
            'patient_account_id' => $patient->id,
            'issuing_agency'     => 'DOH_MAIP',
            'authorized_amount'  => '50000.0000',
            'utilized_amount'    => '30000.0000',
            'remaining_amount'   => '20000.0000',
            'status'             => 'UTILIZED',
            'issued_date'        => now()->toDateString(),
            'valid_until'        => now()->addMonths(3)->toDateString(),
            'created_by'         => $cfo->id,
        ]);

        $response = $this->actingAs($cfo)->get(route('accounting.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Malasakit Center Subsidies');
        $response->assertSee('30,000.00'); // Utilized amount
    }

    public function test_budget_exhaustion_alert_is_triggered_when_burn_rate_exceeds_threshold(): void
    {
        $cfo = User::factory()->create([
            'email' => 'cfo@hospital.gov.ph',
            'role'  => 'CFO',
        ]);

        // Create department with 90% burn rate (over 85% critical threshold)
        BudgetAllocation::create([
            'department'        => 'Pharmacy & Medical Supplies',
            'department_code'   => 'PHARM-TEST',
            'category'          => 'Pharmaceuticals',
            'department_head'   => 'Chief Pharmacist',
            'fiscal_year'       => '2026',
            'allocated_amount'  => '10000000.0000',
            'spent_amount'      => '9000000.0000',
            'remaining_balance' => '1000000.0000',
            'status'            => 'Approved',
        ]);

        $response = $this->actingAs($cfo)->get(route('accounting.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Approaching Budget Exhaustion');
        $response->assertSee('Pharmacy & Medical Supplies');
        $response->assertSee('90.0% Spent');
    }

    public function test_security_telemetry_renders_failed_login_and_mutation_counts(): void
    {
        $cfo = User::factory()->create([
            'email' => 'cfo@hospital.gov.ph',
            'role'  => 'CFO',
        ]);

        // Record a failed login activity log
        ActivityLog::create([
            'event'         => 'failed_login',
            'module'        => 'Authentication',
            'auditable_type'=> null,
            'auditable_id'  => null,
            'user_id'       => null,
            'user_name'     => 'Unknown Attacker',
            'user_role'     => 'Guest',
            'ip_address'    => '192.168.1.105',
            'description'   => 'Failed login attempt for unknown account',
        ]);

        $response = $this->actingAs($cfo)->get(route('accounting.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Failed Logins Today');
        $response->assertSee('1 Alerts');
        $response->assertSee('192.168.1.105');
    }

    public function test_role_tailored_quick_actions_adapt_for_different_hospital_roles(): void
    {
        $auditor = User::factory()->create([
            'email' => 'auditor@hospital.gov.ph',
            'name'  => 'Internal Auditor',
            'role'  => 'Auditor',
        ]);

        $response = $this->actingAs($auditor)->get(route('accounting.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Audit Trail System');
        $response->assertSee('GL Subsidies Audit');
    }
}
