<?php

declare(strict_types=1);

namespace Tests\Feature\Accounting;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingUiViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_accounting_dashboard_renders_successfully(): void
    {
        $response = $this->get('/accounting/dashboard');
        $response->assertStatus(200);
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
}
