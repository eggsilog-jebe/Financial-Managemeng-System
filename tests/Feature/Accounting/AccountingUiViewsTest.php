<?php

declare(strict_types=1);

namespace Tests\Feature\Accounting;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingUiViewsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'CFO']));
    }

    public function test_accounting_dashboard_renders_successfully(): void
    {
        $response = $this->get('/accounting/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Disbursement Management');
        $response->assertSee('Collection Management');
        $response->assertDontSee('Collection &amp; POS', false);
    }

    public function test_cashier_desk_renders_successfully(): void
    {
        $response = $this->get('/accounting/cashier');
        $response->assertStatus(200);
    }

    public function test_general_ledger_browser_renders_successfully(): void
    {
        $response = $this->get('/accounting/general-ledger');
        $response->assertStatus(200);
    }

    public function test_financial_reports_hub_renders_successfully(): void
    {
        $response = $this->get('/accounting/reports');
        $response->assertStatus(200);
    }

    public function test_sidebar_has_collapsible_burger_toggle(): void
    {
        $response = $this->get('/accounting/dashboard');
        $response->assertStatus(200);
        $response->assertSee('toggleSidebarCollapse()', false);
        $response->assertSee('sidebarCollapsed', false);
    }

    public function test_kpi_cards_render_with_professional_font(): void
    {
        $response = $this->get('/accounting/dashboard');
        $response->assertStatus(200);
        $response->assertSee('kpi-value font-sans', false);
        $response->assertDontSee('font-mono text-2xl font-bold tracking-tight text-slate-900', false);
    }
}
