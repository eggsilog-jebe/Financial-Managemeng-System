<?php

declare(strict_types=1);

namespace Tests\Feature\Accounting;

use App\DTOs\Accounting\BudgetAllocationData;
use App\DTOs\Accounting\BudgetReallocationData;
use App\Models\BudgetAllocation;
use App\Models\BudgetEncumbrance;
use App\Models\BudgetReallocation;
use App\Models\FiscalPeriod;
use App\Models\User;
use App\Services\Accounting\BudgetMonitoringService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BudgetManagementModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $accountant;
    private User $manager;
    private User $cfo;
    private User $cashier;
    private BudgetMonitoringService $budgetService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->accountant = User::factory()->create(['role' => 'StaffAccountant', 'name' => 'Budget Analyst', 'email' => 'budget.analyst@hospital.local']);
        $this->manager = User::factory()->create(['role' => 'FinanceManager', 'name' => 'Finance Manager', 'email' => 'budget.mgr@hospital.local']);
        $this->cfo = User::factory()->create(['role' => 'CFO', 'name' => 'Chief Financial Officer', 'email' => 'cfo@hospital.local']);
        $this->cashier = User::factory()->create(['role' => 'Cashier', 'name' => 'Frontdesk Cashier', 'email' => 'cashier@hospital.local']);

        FiscalPeriod::create([
            'period_code'   => '2026-FY',
            'fiscal_year'   => '2026',
            'period_number' => 1,
            'start_date'    => '2026-01-01',
            'end_date'      => '2026-12-31',
            'status'        => 'OPEN',
        ]);

        $this->budgetService = app(BudgetMonitoringService::class);
    }

    /** @test */
    public function test_budget_allocation_creation_and_balance_initialization(): void
    {
        $this->actingAs($this->accountant);

        $payload = [
            'department'       => 'Pharmacy & Therapeutics',
            'department_code'  => 'CC-101',
            'category'         => 'Medical Supplies & Pharmaceuticals',
            'department_head'  => 'Dr. Regina Santos',
            'fiscal_year'      => '2026',
            'allocated_amount' => 5000000.00,
            'notes'            => 'Annual operating pharmaceutical procurement budget',
        ];

        $response = $this->post('/budget-management/allocations', $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $allocation = BudgetAllocation::where('department', 'Pharmacy & Therapeutics')->first();
        $this->assertNotNull($allocation);
        $this->assertEquals('5000000.0000', $allocation->allocated_amount);
        $this->assertEquals('0.0000', $allocation->spent_amount);
        $this->assertEquals('5000000.0000', $allocation->remaining_balance);
        $this->assertEquals('Approved', $allocation->status);

        // Prevent duplicate allocation for same department and fiscal year
        $duplicateResponse = $this->post('/budget-management/allocations', $payload);
        $duplicateResponse->assertRedirect();
        $duplicateResponse->assertSessionHas('error');
    }

    /** @test */
    public function test_budget_encumbrance_precommitment_and_available_capacity_check(): void
    {
        $allocation = BudgetAllocation::create([
            'department'        => 'ICU & Emergency Care',
            'department_code'   => 'CC-102',
            'fiscal_year'       => '2026',
            'allocated_amount'  => '4000000.0000',
            'spent_amount'      => '0.0000',
            'remaining_balance' => '4000000.0000',
            'status'            => 'Approved',
        ]);

        $this->actingAs($this->accountant);

        $encumbrancePayload = [
            'budget_allocation_id' => $allocation->id,
            'reference_type'       => 'PURCHASE_ORDER',
            'reference_number'     => 'PO-2026-ICU001',
            'amount'               => 1500000.00,
            'notes'                => 'Ventilator consumables & emergency crash cart supplies',
        ];

        $response = $this->post('/budget-management/encumber', $encumbrancePayload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $allocation->refresh();
        $this->assertEquals('1500000.0000', $allocation->active_encumbered_total);
        $this->assertEquals('2500000.0000', $allocation->available_unencumbered_balance);

        $enc = BudgetEncumbrance::where('reference_number', 'PO-2026-ICU001')->first();
        $this->assertNotNull($enc);
        $this->assertEquals('COMMITTED', $enc->status);
        $this->assertEquals('1500000.0000', $enc->encumbered_amount);
    }

    /** @test */
    public function test_budget_encumbrance_overdraft_prevention(): void
    {
        $allocation = BudgetAllocation::create([
            'department'        => 'Facilities & Maintenance',
            'department_code'   => 'CC-104',
            'fiscal_year'       => '2026',
            'allocated_amount'  => '1000000.0000',
            'spent_amount'      => '0.0000',
            'remaining_balance' => '1000000.0000',
            'status'            => 'Approved',
        ]);

        // Pre-commit 800,000 (leaving 200,000 available)
        $this->budgetService->encumberBudget($allocation->id, 'PO', 'PO-FAC-01', '800000.0000');

        $this->actingAs($this->accountant);

        // Attempt to encumber 300,000 (exceeds 200,000 unencumbered capacity)
        $excessPayload = [
            'budget_allocation_id' => $allocation->id,
            'reference_type'       => 'PURCHASE_ORDER',
            'reference_number'     => 'PO-FAC-02',
            'amount'               => 300000.00,
        ];

        $response = $this->post('/budget-management/encumber', $excessPayload);
        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Direct service invocation throws DomainException
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Budget Encumbrance Overdraft');
        $this->budgetService->encumberBudget($allocation->id, 'PO', 'PO-FAC-FAIL', '300000.0000');
    }

    /** @test */
    public function test_budget_encumbrance_safe_liquidation_and_expenditure_update(): void
    {
        $allocation = BudgetAllocation::create([
            'department'        => 'Cardiology Operating Room',
            'department_code'   => 'CC-105',
            'fiscal_year'       => '2026',
            'allocated_amount'  => '3000000.0000',
            'spent_amount'      => '0.0000',
            'remaining_balance' => '3000000.0000',
            'status'            => 'Approved',
        ]);

        $enc = $this->budgetService->encumberBudget($allocation->id, 'PO', 'PO-CARDIO-01', '500000.0000');

        // Liquidate encumbrance for actual invoice of 480,000
        $liquidated = $this->budgetService->liquidateEncumbrance($enc->id, '480000.0000');

        $this->assertEquals('LIQUIDATED', $liquidated->status);
        $this->assertEquals('480000.0000', $liquidated->liquidated_amount);

        $allocation->refresh();
        $this->assertEquals('480000.0000', $allocation->spent_amount);
        $this->assertEquals('2520000.0000', $allocation->remaining_balance);
        $this->assertEquals('0.0000', $allocation->active_encumbered_total);
        $this->assertEquals('2520000.0000', $allocation->available_unencumbered_balance);
    }

    /** @test */
    public function test_budget_encumbrance_double_liquidation_guard(): void
    {
        $allocation = BudgetAllocation::create([
            'department'        => 'Laboratory & Diagnostics',
            'department_code'   => 'CC-106',
            'fiscal_year'       => '2026',
            'allocated_amount'  => '2000000.0000',
            'spent_amount'      => '0.0000',
            'remaining_balance' => '2000000.0000',
            'status'            => 'Approved',
        ]);

        $enc = $this->budgetService->encumberBudget($allocation->id, 'PO', 'PO-LAB-01', '300000.0000');
        $this->budgetService->liquidateEncumbrance($enc->id, '300000.0000');

        // Attempting to liquidate again must throw DomainException
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('cannot be liquidated: Current status is already [LIQUIDATED]');
        $this->budgetService->liquidateEncumbrance($enc->id, '300000.0000');
    }

    /** @test */
    public function test_budget_encumbrance_cancellation_and_release(): void
    {
        $allocation = BudgetAllocation::create([
            'department'        => 'Radiology & Imaging',
            'department_code'   => 'CC-107',
            'fiscal_year'       => '2026',
            'allocated_amount'  => '2500000.0000',
            'spent_amount'      => '0.0000',
            'remaining_balance' => '2500000.0000',
            'status'            => 'Approved',
        ]);

        $enc = $this->budgetService->encumberBudget($allocation->id, 'PO', 'PO-RAD-CANCEL', '400000.0000');
        $this->assertEquals('400000.0000', $allocation->refresh()->active_encumbered_total);

        $this->actingAs($this->accountant);

        $response = $this->post("/budget-management/encumber/{$enc->id}/release", [
            'reason' => 'Vendor out of stock, purchase order cancelled',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $enc->refresh();
        $this->assertEquals('RELEASED', $enc->status);
        $this->assertEquals('0.0000', $allocation->refresh()->active_encumbered_total);
        $this->assertEquals('2500000.0000', $allocation->available_unencumbered_balance);
    }

    /** @test */
    public function test_inter_departmental_budget_reallocation_and_atomic_balance_shift(): void
    {
        $sourceAlloc = BudgetAllocation::create([
            'department'        => 'Hospital Administration',
            'department_code'   => 'CC-108',
            'fiscal_year'       => '2026',
            'allocated_amount'  => '3000000.0000',
            'spent_amount'      => '0.0000',
            'remaining_balance' => '3000000.0000',
            'status'            => 'Approved',
        ]);

        $destAlloc = BudgetAllocation::create([
            'department'        => 'Pediatric Emergency Wing',
            'department_code'   => 'CC-109',
            'fiscal_year'       => '2026',
            'allocated_amount'  => '1500000.0000',
            'spent_amount'      => '0.0000',
            'remaining_balance' => '1500000.0000',
            'status'            => 'Approved',
        ]);

        $this->actingAs($this->manager);

        $reallocPayload = [
            'source_budget_allocation_id'      => $sourceAlloc->id,
            'destination_budget_allocation_id' => $destAlloc->id,
            'amount'                           => 600000.00,
            'transfer_date'                    => '2026-02-15',
            'reason'                           => 'Surge in pediatric admissions requiring supplemental medicine allocation',
        ];

        $response = $this->post('/budget-management/reallocate', $reallocPayload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Source decreased (3,000,000 - 600,000 = 2,400,000)
        $sourceAlloc->refresh();
        $this->assertEquals('2400000.0000', $sourceAlloc->allocated_amount);
        $this->assertEquals('2400000.0000', $sourceAlloc->remaining_balance);

        // Destination increased (1,500,000 + 600,000 = 2,100,000)
        $destAlloc->refresh();
        $this->assertEquals('2100000.0000', $destAlloc->allocated_amount);
        $this->assertEquals('2100000.0000', $destAlloc->remaining_balance);

        $reallocation = BudgetReallocation::where('source_budget_allocation_id', $sourceAlloc->id)->first();
        $this->assertNotNull($reallocation);
        $this->assertEquals('600000.0000', $reallocation->amount);
        $this->assertEquals('APPROVED', $reallocation->status);
        $this->assertStringStartsWith('REAL-', $reallocation->reference_number);
    }

    /** @test */
    public function test_budget_reallocation_rejects_insufficient_unencumbered_funds_and_self_transfer(): void
    {
        $sourceAlloc = BudgetAllocation::create([
            'department'        => 'Dialysis Center',
            'department_code'   => 'CC-110',
            'fiscal_year'       => '2026',
            'allocated_amount'  => '1000000.0000',
            'spent_amount'      => '0.0000',
            'remaining_balance' => '1000000.0000',
            'status'            => 'Approved',
        ]);

        $destAlloc = BudgetAllocation::create([
            'department'        => 'Oncology Center',
            'department_code'   => 'CC-111',
            'fiscal_year'       => '2026',
            'allocated_amount'  => '1000000.0000',
            'spent_amount'      => '0.0000',
            'remaining_balance' => '1000000.0000',
            'status'            => 'Approved',
        ]);

        // Encumber 850,000 in Dialysis (available: 150,000)
        $this->budgetService->encumberBudget($sourceAlloc->id, 'PO', 'PO-DIALYSIS-01', '850000.0000');

        $this->actingAs($this->manager);

        // 1. Attempt to reallocate 200,000 when only 150,000 unencumbered available
        $excessPayload = [
            'source_budget_allocation_id'      => $sourceAlloc->id,
            'destination_budget_allocation_id' => $destAlloc->id,
            'amount'                           => 200000.00,
            'transfer_date'                    => '2026-02-15',
            'reason'                           => 'Reallocation exceeding available capacity',
        ];

        $res1 = $this->post('/budget-management/reallocate', $excessPayload);
        $res1->assertRedirect();
        $res1->assertSessionHas('error');

        // 2. Self transfer rejected
        $selfPayload = array_merge($excessPayload, [
            'destination_budget_allocation_id' => $sourceAlloc->id,
            'amount'                           => 50000.00,
        ]);

        $res2 = $this->post('/budget-management/reallocate', $selfPayload);
        $res2->assertRedirect();
        $res2->assertSessionHasErrors(['destination_budget_allocation_id']);
    }

    /** @test */
    public function test_variance_metrics_calculation_favorable_and_unfavorable(): void
    {
        $this->actingAs($this->cfo);

        BudgetAllocation::create([
            'department'        => 'Department A (Under-run)',
            'fiscal_year'       => '2026',
            'allocated_amount'  => '1000000.0000',
            'spent_amount'      => '400000.0000',
            'remaining_balance' => '600000.0000',
            'status'            => 'Approved',
        ]);

        BudgetAllocation::create([
            'department'        => 'Department B (High-burn)',
            'fiscal_year'       => '2026',
            'allocated_amount'  => '1000000.0000',
            'spent_amount'      => '900000.0000',
            'remaining_balance' => '1000000.0000',
            'status'            => 'Approved',
        ]);

        $response = $this->get('/budget-management/variance-analysis?fiscal_year=2026');
        $response->assertStatus(200);
        $response->assertSee('Department A (Under-run)');
        $response->assertSee('Department B (High-burn)');

        $deptResponse = $this->get('/budget-management/departmental-budgets?fiscal_year=2026');
        $deptResponse->assertStatus(200);
        $deptResponse->assertSee('Department A (Under-run)');
    }

    /** @test */
    public function test_budget_management_sod_role_authorization_gates(): void
    {
        // 1. Cashier cannot access budget module
        $this->actingAs($this->cashier);
        $resCashier = $this->get('/budget-management/fiscal-planning');
        $resCashier->assertStatus(403);

        // 2. StaffAccountant can view and store allocations, but CANNOT execute reallocations
        $this->actingAs($this->accountant);
        $resAcctView = $this->get('/budget-management/fiscal-planning');
        $resAcctView->assertStatus(200);

        $source = BudgetAllocation::create([
            'department'        => 'Dept Source SoD',
            'fiscal_year'       => '2026',
            'allocated_amount'  => '1000000.0000',
            'spent_amount'      => '0.0000',
            'remaining_balance' => '1000000.0000',
            'status'            => 'Approved',
        ]);
        $dest = BudgetAllocation::create([
            'department'        => 'Dept Dest SoD',
            'fiscal_year'       => '2026',
            'allocated_amount'  => '1000000.0000',
            'spent_amount'      => '0.0000',
            'remaining_balance' => '1000000.0000',
            'status'            => 'Approved',
        ]);

        $resAcctRealloc = $this->post('/budget-management/reallocate', [
            'source_budget_allocation_id'      => $source->id,
            'destination_budget_allocation_id' => $dest->id,
            'amount'                           => 10000.00,
            'transfer_date'                    => '2026-01-20',
            'reason'                           => 'Unauthorized accountant transfer attempt',
        ]);
        $resAcctRealloc->assertStatus(403);

        // 3. FinanceManager CAN execute reallocations
        $this->actingAs($this->manager);
        $resMgrRealloc = $this->post('/budget-management/reallocate', [
            'source_budget_allocation_id'      => $source->id,
            'destination_budget_allocation_id' => $dest->id,
            'amount'                           => 10000.00,
            'transfer_date'                    => '2026-01-20',
            'reason'                           => 'Authorized manager transfer',
        ]);
        $resMgrRealloc->assertRedirect();
    }
}
