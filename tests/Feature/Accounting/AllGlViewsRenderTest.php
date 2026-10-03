<?php

declare(strict_types=1);

namespace Tests\Feature\Accounting;

use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AllGlViewsRenderTest extends TestCase
{
    use RefreshDatabase;

    private User $cfo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cfo = User::factory()->create([
            'role'  => 'CFO',
            'name'  => 'Chief Financial Officer',
            'email' => 'cfo@hospital.local',
        ]);
    }

    public function test_chart_of_accounts_renders_successfully(): void
    {
        $this->actingAs($this->cfo);
        $response = $this->get(route('gl.chart-of-accounts'));
        $response->assertStatus(200);
    }

    public function test_journal_entries_renders_successfully(): void
    {
        $this->actingAs($this->cfo);
        $response = $this->get(route('gl.journal-entries'));
        $response->assertStatus(200);
    }

    public function test_ledger_books_renders_successfully(): void
    {
        $this->actingAs($this->cfo);
        $response = $this->get(route('gl.ledger-books'));
        $response->assertStatus(200);
    }

    public function test_period_end_closing_renders_successfully(): void
    {
        $this->actingAs($this->cfo);
        $response = $this->get(route('gl.period-end-closing'));
        $response->assertStatus(200);
    }

    public function test_trial_balance_renders_successfully(): void
    {
        $this->actingAs($this->cfo);
        $response = $this->get(route('gl.trial-balance'));
        $response->assertStatus(200);
    }
}
