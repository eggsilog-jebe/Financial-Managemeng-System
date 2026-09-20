<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EndToEndSystemVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verify CFO has full access to executive governance modules.
     */
    public function test_cfo_has_access_to_period_closing_and_audit_log(): void
    {
        $cfo = User::factory()->create([
            'email' => 'cfo@hospital.gov.ph',
            'name'  => 'Chief Financial Officer',
            'role'  => 'CFO',
        ]);

        $this->actingAs($cfo)->get(route('accounting.dashboard'))->assertStatus(200);
        $this->actingAs($cfo)->get(route('accounting.audit-log'))->assertStatus(200);
        $this->actingAs($cfo)->get('/accounting/period-close')->assertStatus(200);
    }

    /**
     * Verify Cashier is strictly restricted to POS counter and blocked from CFO areas.
     */
    public function test_cashier_is_forbidden_from_period_closing_and_audit_log(): void
    {
        $cashier = User::factory()->create([
            'email' => 'cashier@hospital.gov.ph',
            'name'  => 'Hospital Cashier',
            'role'  => 'Cashier',
        ]);

        // Allowed: Cashier POS counter
        $this->actingAs($cashier)->get(route('accounting.cashier'))->assertStatus(200);

        // Forbidden: Period Close and Audit Trail
        $this->actingAs($cashier)->get('/accounting/period-close')->assertStatus(403);
        $this->actingAs($cashier)->get(route('accounting.audit-log'))->assertStatus(403);
    }

    /**
     * Verify Billing Clerk is restricted from Period Close and Audit Log.
     */
    public function test_billing_clerk_is_forbidden_from_period_closing_and_audit_log(): void
    {
        $billing = User::factory()->create([
            'email' => 'billing@hospital.gov.ph',
            'name'  => 'Billing Clerk',
            'role'  => 'BillingClerk',
        ]);

        // Allowed: Malasakit assistance desk
        $this->actingAs($billing)->get(route('ar.malasakit.index'))->assertStatus(200);

        // Forbidden: Period Closing and Audit Log
        $this->actingAs($billing)->get('/accounting/period-close')->assertStatus(403);
        $this->actingAs($billing)->get(route('accounting.audit-log'))->assertStatus(403);
    }

    /**
     * Verify Internal Auditor can inspect logs, but cannot close period.
     */
    public function test_auditor_can_inspect_audit_log_but_cannot_close_period(): void
    {
        $auditor = User::factory()->create([
            'email' => 'auditor@hospital.gov.ph',
            'name'  => 'Internal Auditor',
            'role'  => 'Auditor',
        ]);

        // Allowed for Auditor: Audit Trail
        $this->actingAs($auditor)->get(route('accounting.audit-log'))->assertStatus(200);

        // Forbidden: Period Closing
        $this->actingAs($auditor)->get('/accounting/period-close')->assertStatus(403);
    }
}
