<?php

declare(strict_types=1);

namespace Tests\Feature\Accounting;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class SystemWideViewIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'  => 'CFO',
            'name'  => 'System Super Administrator',
            'email' => 'superadmin@hospital.gov.ph',
        ]);
    }

    #[DataProvider('primaryGetRoutesProvider')]
    public function test_primary_system_views_render_without_error(string $uri, string $name): void
    {
        $this->actingAs($this->admin);

        $response = $this->get($uri);

        $this->assertNotEquals(
            500,
            $response->status(),
            "Route [{$name}] at URI [{$uri}] crashed with HTTP 500 Internal Server Error."
        );

        $this->assertTrue(
            in_array($response->status(), [200, 302], true),
            "Route [{$name}] at URI [{$uri}] returned unexpected status: {$response->status()}."
        );
    }

    public static function primaryGetRoutesProvider(): array
    {
        return [
            // General Ledger
            'GL Chart of Accounts'      => ['/general-ledger/chart-of-accounts', 'gl.chart-of-accounts'],
            'GL Journal Entries'        => ['/general-ledger/journal-entries', 'gl.journal-entries'],
            'GL Ledger Books'           => ['/general-ledger/ledger-books', 'gl.ledger-books'],
            'GL Period-End Closing'     => ['/general-ledger/period-end-closing', 'gl.period-end-closing'],
            'GL Trial Balance'          => ['/general-ledger/trial-balance', 'gl.trial-balance'],

            // Accounts Payable
            'AP Vendors'                => ['/accounts-payable/vendors', 'ap.vendors.index'],
            'AP Invoices & Vouchers'    => ['/accounts-payable/invoices-vouchers', 'ap.invoices'],
            'AP Purchase Bills'         => ['/accounts-payable/purchase-bills', 'ap.purchase-bills'],
            'AP Payable Aging'          => ['/accounts-payable/payable-aging', 'ap.payable-aging'],
            'AP Payment Approvals'      => ['/accounts-payable/payment-approvals', 'ap.payment-approvals.index'],

            // Accounts Receivable
            'AR Patient Accounts'       => ['/accounts-receivable/patients', 'ar.patients.index'],
            'AR Invoices & Billing'     => ['/accounts-receivable/invoices', 'ar.invoices.index'],
            'AR Credit Notes'           => ['/accounts-receivable/credit-notes', 'ar.credit-notes'],
            'AR Customer Statements'    => ['/accounts-receivable/customer-statements', 'ar.statements'],
            'AR Aging Schedule'         => ['/accounts-receivable/receivable-aging', 'ar.ar-aging'],
            'AR Malasakit Assistance'   => ['/accounts-receivable/malasakit-assistance', 'ar.malasakit.index'],

            // Cash & Treasury
            'Cash Bank Accounts'        => ['/cash-management/bank-accounts', 'cash.bank-accounts'],
            'Cash Bank Reconciliation'  => ['/cash-management/bank-reconciliation', 'cash.bank-reconciliation'],
            'Cash Flow Forecasting'     => ['/cash-management/cash-flow-forecasting', 'cash.cash-flow-forecast'],
            'Cash Fund Transfers'       => ['/cash-management/fund-transfers', 'cash.fund-transfers'],
            'Cash Liquidity Management' => ['/cash-management/liquidity-management', 'cash.liquidity'],

            // Collection & Cashier
            'Collection Cashier Desk'   => ['/collection-management/cashier-desk', 'collection.cashier-desk'],
            'Collection Bank Deposits'  => ['/collection-management/bank-deposits', 'collection.bank-deposits'],
            'Collection Deposit Slips'  => ['/collection-management/deposit-slips', 'collection.deposit-slips'],
            'Collection Gateway Logs'   => ['/collection-management/payment-gateway-logs', 'collection.payment-gateways'],
            'Collection Receipts'       => ['/collection-management/payment-receipts', 'collection.receipts'],

            // Disbursement Management
            'Disbursement Check Register'   => ['/disbursement-management/check-register', 'disbursement.check-register'],
            'Disbursement Approvals'        => ['/disbursement-management/disbursement-approvals', 'disbursement.disbursement-approval'],
            'Disbursement EFT Transfers'    => ['/disbursement-management/eft-transfers', 'disbursement.eft-transfers'],
            'Disbursement Payment Requests' => ['/disbursement-management/payment-requests', 'disbursement.payment-requests'],
            'Disbursement Petty Cash'       => ['/disbursement-management/petty-cash', 'disbursement.petty-cash'],

            // Budget Management
            'Budget Allocation'         => ['/budget-management/budget-allocation', 'budget.budget-allocation'],
            'Budget Departmental'       => ['/budget-management/departmental-budgets', 'budget.departmental-budgets'],
            'Budget Fiscal Planning'    => ['/budget-management/fiscal-planning', 'budget.fiscal-planning'],
            'Budget Reallocations'      => ['/budget-management/budget-reallocations', 'budget.reallocations'],
            'Budget Variance Analysis'  => ['/budget-management/variance-analysis', 'budget.variance-analysis'],

            // Tax Management
            'Tax Audit Trail'           => ['/tax-management/tax-audit-trail', 'tax.tax-audit'],
            'Tax Configuration'         => ['/tax-management/tax-configuration', 'tax.tax-config'],
            'Tax Exemptions'            => ['/tax-management/tax-exemptions', 'tax.tax-exemptions'],
            'Tax Returns'               => ['/tax-management/tax-returns', 'tax.tax-returns'],
            'Tax Withholding'           => ['/tax-management/withholding-tax', 'tax.withholding-tax'],

            // Financial Reporting
            'Reporting Balance Sheet'   => ['/financial-reporting/balance-sheet', 'reporting.balance-sheet'],
            'Reporting Cash Flow'       => ['/financial-reporting/cash-flow-statement', 'reporting.cash-flow-statement'],
            'Reporting Changes in Equity' => ['/financial-reporting/statement-of-changes-in-equity', 'reporting.equity'],
            'Reporting Executive Reports' => ['/financial-reporting/executive-reports', 'reporting.executive-reports'],
            'Reporting KPI Dashboard'   => ['/financial-reporting/financial-kpi-dashboard', 'reporting.financial-kpi-dashboard'],
            'Reporting Profit and Loss' => ['/financial-reporting/profit-and-loss', 'reporting.profit-and-loss'],

            // User Security
            'Security Audit Trail'      => ['/user-security/audit-trail', 'user-security.audit-trail'],
            'Security Users List'       => ['/user-security/users', 'user-security.users'],
            'Security Users Create'     => ['/user-security/users/create', 'user-security.users.create'],
            'Security Workstations'     => ['/user-security/workstations', 'user-security.workstations'],
        ];
    }
}
