<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaxManagementController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\GeneralLedger\ChartOfAccountsController;
use App\Http\Controllers\GeneralLedger\JournalEntryController;
use App\Http\Controllers\GeneralLedger\LedgerBookController;
use App\Http\Controllers\GeneralLedger\TrialBalanceController;
use App\Http\Controllers\GeneralLedger\FiscalPeriodController;
use App\Http\Controllers\GeneralLedger\PostJournalEntryController;
use App\Http\Controllers\IngestClinicalBillablesController;
use App\Http\Controllers\IngestPayrollRunController;
use App\Http\Controllers\IngestVendorBillController;
use App\Http\Controllers\ProcessPaymentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Accounting\FinancialDashboardController;
use App\Http\Controllers\Accounting\GeneralLedgerBrowserController;
use App\Http\Controllers\Accounting\FinancialReportsViewController;
use App\Http\Controllers\Accounting\PeriodClosingViewController;
use App\Http\Controllers\Accounting\Export\ExportAndPrintController;
use App\Http\Controllers\AccountsPayable\VendorController;
use App\Http\Controllers\AccountsPayable\VendorInvoiceController;
use App\Http\Controllers\AccountsPayable\PurchaseBillController;
use App\Http\Controllers\AccountsPayable\PayableAgingController;
use App\Http\Controllers\AccountsPayable\PaymentApprovalController;
use App\Http\Controllers\AccountsReceivable\PatientAccountController;
use App\Http\Controllers\AccountsReceivable\PatientInvoiceController;
use App\Http\Controllers\AccountsReceivable\ReceivableAgingController;
use App\Http\Controllers\AccountsReceivable\CreditNoteController;
use App\Http\Controllers\AccountsReceivable\CustomerStatementController;
use App\Http\Controllers\Disbursement\PaymentRequestController;
use App\Http\Controllers\Disbursement\CheckRegisterController;
use App\Http\Controllers\Disbursement\EftTransferController;
use App\Http\Controllers\Disbursement\DisbursementApprovalController;
use App\Http\Controllers\Disbursement\PettyCashController;
use App\Http\Controllers\Collection\CashierDeskController;
use App\Http\Controllers\Collection\CashierShiftController;
use App\Http\Controllers\Collection\PaymentReceiptController;
use App\Http\Controllers\Collection\DepositSlipBatchController;
use App\Http\Controllers\Collection\BankDepositController;
use App\Http\Controllers\Collection\PaymentGatewayLogController;
use App\Http\Controllers\CashManagement\BankAccountController;
use App\Http\Controllers\CashManagement\CashFlowForecastController;
use App\Http\Controllers\CashManagement\BankReconciliationController;
use App\Http\Controllers\CashManagement\FundTransferController;
use App\Http\Controllers\CashManagement\LiquidityManagementController;
use App\Http\Controllers\FinancialReporting\BalanceSheetController;
use App\Http\Controllers\FinancialReporting\ProfitAndLossController;
use App\Http\Controllers\FinancialReporting\CashFlowStatementController;
use App\Http\Controllers\FinancialReporting\FinancialKpiDashboardController;
use App\Http\Controllers\FinancialReporting\ExecutiveReportPackageController;

// ─── External Subsystem Integration API Endpoints ──────────────────────────────
Route::post('/ingest-encounter-billing', \App\Http\Controllers\Api\V1\Ingestion\SimulateEncounterBillingApiController::class)->name('ingest-encounter-billing');
Route::post('/api/v1/ingest/encounter-billing', \App\Http\Controllers\Api\V1\Ingestion\SimulateEncounterBillingApiController::class)->name('api.v1.ingest.encounter-billing');

// ─── Public: Authentication Routes (no auth required) ────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    Route::get('/login/quick/{role}', [LoginController::class, 'quickLogin'])->name('login.quick');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout.get');

// ─── Root redirect ────────────────────────────────────────────────────────────
Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }
    return redirect()->route('accounting.dashboard');
})->name('home');

// ─── Protected: All routes below require authentication ───────────────────────
Route::middleware(['auth'])->group(function () {

    // Legacy dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // 1. General Ledger Module
    Route::prefix('general-ledger')->name('gl.')->group(function () {
        // Chart of Accounts
        Route::get('/chart-of-accounts', [ChartOfAccountsController::class, 'index'])->name('chart-of-accounts');
        Route::middleware(['role:StaffAccountant,FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::post('/chart-of-accounts', [ChartOfAccountsController::class, 'store'])->name('chart-of-accounts.store');
            Route::match(['put', 'patch'], '/chart-of-accounts/{id}', [ChartOfAccountsController::class, 'update'])->name('chart-of-accounts.update');
            Route::post('/chart-of-accounts/{id}/toggle-status', [ChartOfAccountsController::class, 'toggleStatus'])->name('chart-of-accounts.toggle-status');
        });

        // Journal Entries
        Route::get('/journal-entries', [JournalEntryController::class, 'index'])->name('journal-entries');
        Route::post('/journal-entries', [JournalEntryController::class, 'store'])->name('journal-entries.store');
        Route::post('/post-entry', PostJournalEntryController::class)->name('post-entry');

        Route::middleware(['role:FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::post('/journal-entries/{id}/post', [JournalEntryController::class, 'post'])->name('journal-entries.post');
            Route::post('/journal-entries/{id}/reverse', [JournalEntryController::class, 'reverse'])->name('journal-entries.reverse');
        });

        // Ledger Books
        Route::get('/ledger-books', [LedgerBookController::class, 'index'])->name('ledger-books');
        Route::get('/ledger-books/export', [LedgerBookController::class, 'export'])->name('ledger-books.export');

        // Trial Balance
        Route::get('/trial-balance', [TrialBalanceController::class, 'index'])->name('trial-balance');
        Route::get('/trial-balance/export', [TrialBalanceController::class, 'export'])->name('trial-balance.export');

        // Period-End Closing
        Route::get('/period-end-closing', [FiscalPeriodController::class, 'index'])->name('period-end-closing');
        Route::middleware(['role:FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::post('/period-end-closing/initialize', [FiscalPeriodController::class, 'initialize'])->name('period-end-closing.initialize');
            Route::post('/period-end-closing/{id}/lock', [FiscalPeriodController::class, 'lock'])->name('period-end-closing.lock');
        });
        Route::middleware(['role:CFO,FinanceDirector'])->group(function () {
            Route::post('/period-end-closing/{id}/close', [FiscalPeriodController::class, 'close'])->name('period-end-closing.close');
        });
    });

    // 2. Accounts Payable
    Route::prefix('accounts-payable')->name('ap.')->group(function () {
        // Vendor Management
        Route::middleware(['role:StaffAccountant,FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::get('/vendors', [VendorController::class, 'index'])->name('vendors.index');
            Route::get('/vendor-management', [VendorController::class, 'index'])->name('vendors');
            Route::post('/vendors', [VendorController::class, 'store'])->name('vendors.store');
            Route::put('/vendors/{id}', [VendorController::class, 'update'])->name('vendors.update');
            Route::patch('/vendors/{id}/toggle', [VendorController::class, 'toggle'])->name('vendors.toggle');
            Route::post('/vendors/{id}/toggle-status', [VendorController::class, 'toggle'])->name('vendors.toggle-status');
        });

        // Invoices & Vouchers Hub
        Route::middleware(['role:StaffAccountant,FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::get('/invoices-vouchers', [VendorInvoiceController::class, 'index'])->name('invoices');
            Route::post('/invoices-vouchers/prepare-voucher', [VendorInvoiceController::class, 'prepareVoucher'])->name('invoices.prepare-voucher');
            Route::post('/vouchers/prepare', [VendorInvoiceController::class, 'prepareVoucher'])->name('vouchers.prepare');
        });

        // Purchase Bills & 3-Way Matching
        Route::middleware(['role:StaffAccountant,FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::get('/purchase-bills', [PurchaseBillController::class, 'index'])->name('purchase-bills');
            Route::post('/purchase-bills', [PurchaseBillController::class, 'store'])->name('purchase-bills.store');
            Route::post('/ingest-bill', IngestVendorBillController::class)->name('ingest-bill');
        });
        Route::middleware(['role:FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::post('/purchase-bills/{id}/approve', [PurchaseBillController::class, 'approve'])->name('purchase-bills.approve');
        });

        // Payable Aging Schedule
        Route::middleware(['role:StaffAccountant,FinanceManager,CFO,FinanceDirector,Auditor'])->group(function () {
            Route::get('/payable-aging', [PayableAgingController::class, 'index'])->name('payable-aging');
            Route::get('/payable-aging/export', [PayableAgingController::class, 'export'])->name('payable-aging.export');
        });

        // AP Payment Approvals & Disbursement
        Route::middleware(['role:StaffAccountant,FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::get('/payment-approvals', [PaymentApprovalController::class, 'index'])->name('payment-approvals.index');
            Route::get('/ap-payment-approvals', [PaymentApprovalController::class, 'index'])->name('ap-approvals');
        });
        Route::middleware(['role:FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::post('/payment-approvals/{id}/approve', [PaymentApprovalController::class, 'approve'])->name('payment-approvals.approve');
        });
        Route::middleware(['role:CFO,FinanceDirector'])->group(function () {
            Route::post('/payment-approvals/{id}/release', [PaymentApprovalController::class, 'release'])->name('payment-approvals.release');
        });
    });

    // 3. Accounts Receivable
    Route::prefix('accounts-receivable')->name('ar.')->group(function () {
        // Patient Accounts
        Route::middleware(['role:StaffAccountant,BillingClerk,Cashier,FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::get('/patients', [PatientAccountController::class, 'index'])->name('patients.index');
            Route::get('/patient-accounts', [PatientAccountController::class, 'index'])->name('customers');
            Route::post('/patients', [PatientAccountController::class, 'store'])->name('patients.store');
        });

        // Invoicing & Patient Billing
        Route::middleware(['role:StaffAccountant,BillingClerk,FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::get('/invoices', [PatientInvoiceController::class, 'index'])->name('invoices.index');
            Route::get('/invoicing-billing', [PatientInvoiceController::class, 'index'])->name('billing');
            Route::post('/invoices', [PatientInvoiceController::class, 'store'])->name('invoices.store');
            Route::get('/invoices/{id}/print', [PatientInvoiceController::class, 'print'])->name('invoices.print');
            Route::post('/ingest-billables', IngestClinicalBillablesController::class)->name('ingest-billables');
        });

        // Receivable Aging Schedule
        Route::middleware(['role:StaffAccountant,BillingClerk,FinanceManager,CFO,FinanceDirector,Auditor'])->group(function () {
            Route::get('/receivable-aging', [ReceivableAgingController::class, 'index'])->name('ar-aging');
            Route::get('/receivable-aging/export', [ReceivableAgingController::class, 'export'])->name('ar-aging.export');
        });

        // Credit Notes & Statutory Discounts
        Route::middleware(['role:StaffAccountant,BillingClerk,FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::get('/credit-notes', [CreditNoteController::class, 'index'])->name('credit-notes');
            Route::post('/credit-notes', [CreditNoteController::class, 'store'])->name('credit-notes.store');
        });
        Route::middleware(['role:FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::post('/credit-notes/{id}/approve', [CreditNoteController::class, 'approve'])->name('credit-notes.approve');
            Route::post('/credit-notes/{id}/post', [CreditNoteController::class, 'postCreditNote'])->name('credit-notes.post');
            Route::post('/credit-notes/{id}/void', [CreditNoteController::class, 'void'])->name('credit-notes.void');
        });

        // Customer Statements of Account (SOA)
        Route::middleware(['role:StaffAccountant,BillingClerk,FinanceManager,CFO,FinanceDirector,Auditor'])->group(function () {
            Route::get('/customer-statements', [CustomerStatementController::class, 'index'])->name('statements');
            Route::get('/customer-statements/print', [CustomerStatementController::class, 'print'])->name('statements.print');
            Route::get('/customer-statements/export', [CustomerStatementController::class, 'export'])->name('statements.export');
        });
    });

    // 4. Disbursement Management
    Route::prefix('disbursement-management')->name('disbursement.')->group(function () {
        // Payment Requests
        Route::middleware(['role:StaffAccountant,BillingClerk,FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::get('/payment-requests', [PaymentRequestController::class, 'index'])->name('payment-requests');
            Route::post('/payment-requests', [PaymentRequestController::class, 'store'])->name('payment-requests.store');
        });
        Route::middleware(['role:Auditor,FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::post('/payment-requests/{id}/audit', [PaymentRequestController::class, 'audit'])->name('payment-requests.audit');
            Route::post('/payment-requests/{id}/void', [PaymentRequestController::class, 'void'])->name('payment-requests.void');
        });
        Route::post('/ingest-payroll', IngestPayrollRunController::class)->name('ingest-payroll');

        // Check Register & Printing
        Route::middleware(['role:StaffAccountant,Cashier,FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::get('/check-register', [CheckRegisterController::class, 'index'])->name('check-register');
            Route::post('/check-register', [CheckRegisterController::class, 'store'])->name('check-register.store');
            Route::get('/check-register/{id}/print', [CheckRegisterController::class, 'print'])->name('check-register.print');
            Route::post('/check-register/{id}/clear', [CheckRegisterController::class, 'clear'])->name('check-register.clear');
        });

        // EFT & Electronic Payouts
        Route::middleware(['role:StaffAccountant,FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::get('/eft-transfers', [EftTransferController::class, 'index'])->name('eft-transfers');
            Route::post('/eft-transfers', [EftTransferController::class, 'store'])->name('eft-transfers.store');
            Route::get('/eft-transfers/export', [EftTransferController::class, 'export'])->name('eft-transfers.export');
        });
        Route::middleware(['role:FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::post('/eft-transfers/{id}/approve', [EftTransferController::class, 'approve'])->name('eft-transfers.approve');
        });

        // Disbursement Approvals & Release Workstation
        Route::middleware(['role:StaffAccountant,FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::get('/disbursement-approvals', [DisbursementApprovalController::class, 'index'])->name('disbursement-approval');
        });
        Route::middleware(['role:FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::post('/disbursement-approvals/{id}/approve', [DisbursementApprovalController::class, 'approve'])->name('disbursement-approvals.approve');
        });
        Route::middleware(['role:CFO,FinanceDirector'])->group(function () {
            Route::post('/disbursement-approvals/{id}/release', [DisbursementApprovalController::class, 'release'])->name('disbursement-approvals.release');
        });

        // Petty Cash Custody & Replenishment
        Route::middleware(['role:Cashier,StaffAccountant,FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::get('/petty-cash', [PettyCashController::class, 'index'])->name('petty-cash');
            Route::post('/petty-cash/funds', [PettyCashController::class, 'storeFund'])->name('petty-cash.funds.store');
            Route::post('/petty-cash/expense', [PettyCashController::class, 'storeExpense'])->name('petty-cash.expense');
            Route::post('/petty-cash/replenish', [PettyCashController::class, 'replenish'])->name('petty-cash.replenish');
        });
    });

    // 5. Collection Management
    Route::prefix('collection-management')->name('collection.')->group(function () {
        // Cashier Desk & Shift Lifecycle
        Route::middleware(['role:Cashier,StaffAccountant,FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::get('/cashier-desk', [CashierDeskController::class, 'index'])->name('cashier-desk');
            Route::post('/cashier-desk/collect', [CashierDeskController::class, 'collect'])->name('cashier-desk.collect');
            Route::post('/shifts/open', [CashierShiftController::class, 'open'])->name('shifts.open');
            Route::post('/shifts/close', [CashierShiftController::class, 'close'])->name('shifts.close');
        });
        Route::middleware(['role:FinanceManager,CFO,FinanceDirector,Auditor'])->group(function () {
            Route::post('/shifts/{id}/reconcile', [CashierShiftController::class, 'reconcile'])->name('shifts.reconcile');
        });

        // Payment Receipts Hub
        Route::middleware(['role:Cashier,StaffAccountant,BillingClerk,FinanceManager,CFO,FinanceDirector,Auditor'])->group(function () {
            Route::get('/payment-receipts', [PaymentReceiptController::class, 'index'])->name('receipts');
            Route::get('/payment-receipts/{id}/print', [PaymentReceiptController::class, 'print'])->name('receipts.print');
        });
        Route::middleware(['role:FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::post('/payment-receipts/{id}/void', [PaymentReceiptController::class, 'voidReceipt'])->name('receipts.void');
        });
        Route::post('/process-payment', ProcessPaymentController::class)->name('process-payment');

        // Deposit Slips & Batching
        Route::middleware(['role:Cashier,StaffAccountant,FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::get('/deposit-slips', [DepositSlipBatchController::class, 'index'])->name('deposit-slips');
        });

        // Bank Deposits & Clearing
        Route::middleware(['role:Cashier,StaffAccountant,FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::get('/bank-deposits', [BankDepositController::class, 'index'])->name('bank-deposits');
            Route::post('/bank-deposits', [BankDepositController::class, 'store'])->name('bank-deposits.store');
        });
        Route::middleware(['role:FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::post('/bank-deposits/{id}/clear', [BankDepositController::class, 'clear'])->name('bank-deposits.clear');
            Route::post('/bank-deposits/{id}/reject', [BankDepositController::class, 'reject'])->name('bank-deposits.reject');
        });

        // Payment Gateway Logs
        Route::middleware(['role:StaffAccountant,FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::get('/payment-gateway-logs', [PaymentGatewayLogController::class, 'index'])->name('payment-gateways');
            Route::post('/payment-gateway-logs/{id}/retrigger-gl', [PaymentGatewayLogController::class, 'retriggerGl'])->name('payment-gateways.retrigger-gl');
        });
    });

    // 6. Budget Management
    Route::prefix('budget-management')->name('budget.')->group(function () {
        Route::get('/fiscal-planning', [BudgetController::class, 'fiscalPlanning'])->name('fiscal-planning');
        Route::get('/budget-allocation', [BudgetController::class, 'budgetAllocation'])->name('budget-allocation');
        Route::get('/departmental-budgets', [BudgetController::class, 'departmentalBudgets'])->name('departmental-budgets');
        Route::get('/variance-analysis', [BudgetController::class, 'varianceAnalysis'])->name('variance-analysis');
        Route::get('/budget-reallocations', [BudgetController::class, 'budgetReallocations'])->name('reallocations');
    });

    // 7. Cash Management
    Route::prefix('cash-management')->name('cash.')->group(function () {
        // Bank Accounts Directory
        Route::middleware(['role:StaffAccountant,FinanceManager,CFO,FinanceDirector,Auditor'])->group(function () {
            Route::get('/bank-accounts', [BankAccountController::class, 'index'])->name('bank-accounts');
        });
        Route::middleware(['role:StaffAccountant,FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::post('/bank-accounts', [BankAccountController::class, 'store'])->name('bank-accounts.store');
        });
        Route::middleware(['role:FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::put('/bank-accounts/{id}', [BankAccountController::class, 'update'])->name('bank-accounts.update');
            Route::patch('/bank-accounts/{id}/toggle', [BankAccountController::class, 'toggle'])->name('bank-accounts.toggle');
        });

        // Cash Flow Forecasting Engine
        Route::middleware(['role:StaffAccountant,FinanceManager,CFO,FinanceDirector,Auditor'])->group(function () {
            Route::get('/cash-flow-forecasting', [CashFlowForecastController::class, 'index'])->name('cash-flow-forecast');
            Route::get('/cash-flow-forecasting/export', [CashFlowForecastController::class, 'export'])->name('cash-flow-forecast.export');
        });

        // Bank Reconciliation Terminal
        Route::middleware(['role:StaffAccountant,FinanceManager,CFO,FinanceDirector,Auditor'])->group(function () {
            Route::get('/bank-reconciliation', [BankReconciliationController::class, 'index'])->name('bank-reconciliation');
        });
        Route::middleware(['role:StaffAccountant,FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::post('/bank-reconciliation/post', [BankReconciliationController::class, 'post'])->name('bank-reconciliation.post');
        });

        // Inter-Account Fund Transfers
        Route::middleware(['role:StaffAccountant,FinanceManager,CFO,FinanceDirector,Auditor'])->group(function () {
            Route::get('/fund-transfers', [FundTransferController::class, 'index'])->name('fund-transfers');
        });
        Route::middleware(['role:FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::post('/fund-transfers', [FundTransferController::class, 'store'])->name('fund-transfers.store');
        });

        // Liquidity Management & Ratios
        Route::middleware(['role:StaffAccountant,FinanceManager,CFO,FinanceDirector,Auditor'])->group(function () {
            Route::get('/liquidity-management', [LiquidityManagementController::class, 'index'])->name('liquidity');
            Route::get('/liquidity-management/export', [LiquidityManagementController::class, 'export'])->name('liquidity.export');
        });
    });

    // 8. Financial Reporting & Analytics
    Route::prefix('financial-reporting')->name('reporting.')->group(function () {
        // Balance Sheet Statement
        Route::middleware(['role:StaffAccountant,FinanceManager,CFO,FinanceDirector,Auditor'])->group(function () {
            Route::get('/balance-sheet', [BalanceSheetController::class, 'index'])->name('balance-sheet');
            Route::get('/balance-sheet/export', [BalanceSheetController::class, 'export'])->name('balance-sheet.export');
        });

        // Profit & Loss / Income Statement
        Route::middleware(['role:StaffAccountant,FinanceManager,CFO,FinanceDirector,Auditor'])->group(function () {
            Route::get('/profit-loss', [ProfitAndLossController::class, 'index'])->name('profit-loss');
            Route::get('/profit-and-loss', [ProfitAndLossController::class, 'index'])->name('profit-and-loss');
            Route::get('/profit-loss/export', [ProfitAndLossController::class, 'export'])->name('profit-loss.export');
        });

        // Statement of Cash Flows (PAS 7)
        Route::middleware(['role:StaffAccountant,FinanceManager,CFO,FinanceDirector,Auditor'])->group(function () {
            Route::get('/cash-flow-statement', [CashFlowStatementController::class, 'index'])->name('cash-flow-statement');
            Route::get('/cash-flow-statement/export', [CashFlowStatementController::class, 'export'])->name('cash-flow-statement.export');
        });

        // Financial KPI Dashboard
        Route::middleware(['role:StaffAccountant,FinanceManager,CFO,FinanceDirector,Auditor'])->group(function () {
            Route::get('/financial-kpi-dashboard', [FinancialKpiDashboardController::class, 'index'])->name('financial-kpi-dashboard');
            Route::get('/kpi-dashboard', [FinancialKpiDashboardController::class, 'index'])->name('kpi-dashboard');
            Route::get('/kpi-dashboard/export', [FinancialKpiDashboardController::class, 'export'])->name('kpi-dashboard.export');
        });

        // Executive Reports Dossier
        Route::middleware(['role:FinanceManager,CFO,FinanceDirector,Auditor'])->group(function () {
            Route::get('/executive-reports', [ExecutiveReportPackageController::class, 'index'])->name('executive-reports');
        });
    });

    // 9. Tax Management
    Route::prefix('tax-management')->name('tax.')->group(function () {
        Route::get('/tax-configuration', [TaxManagementController::class, 'taxConfiguration'])->name('tax-config');
        Route::get('/withholding-tax', [TaxManagementController::class, 'withholdingTax'])->name('withholding-tax');
        Route::get('/tax-returns', [TaxManagementController::class, 'taxReturns'])->name('tax-returns');
        Route::get('/tax-exemptions', [TaxManagementController::class, 'taxExemptions'])->name('tax-exemptions');
        Route::get('/tax-audit-trail', [TaxManagementController::class, 'taxAuditTrail'])->name('tax-audit');
    });

    // 10. Accounting UI Interfaces
    Route::prefix('accounting')->name('accounting.')->group(function () {

        // Executive Dashboard (All authenticated roles)
        Route::get('/dashboard', FinancialDashboardController::class)->name('dashboard');

        // Cashier POS & Official Receipts
        Route::middleware(['role:Cashier,StaffAccountant,FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::get('/cashier', [CashierDeskController::class, 'index'])->name('cashier');
            Route::post('/cashier/pay', [CashierDeskController::class, 'collect'])->name('cashier.pay');
            Route::get('/print/or/{id}', [ExportAndPrintController::class, 'printOfficialReceipt'])->name('print.or');
        });

        // General Ledger Browser
        Route::middleware(['role:StaffAccountant,FinanceManager,CFO,FinanceDirector,Auditor'])->group(function () {
            Route::get('/general-ledger', [GeneralLedgerBrowserController::class, 'index'])->name('general-ledger.index');
        });

        // GL Reversals
        Route::middleware(['role:FinanceManager,CFO,FinanceDirector'])->group(function () {
            Route::post('/general-ledger/{id}/reverse', [GeneralLedgerBrowserController::class, 'reverse'])->name('general-ledger.reverse');
        });

        // Financial Reports & BIR Exports
        Route::middleware(['role:StaffAccountant,FinanceManager,CFO,FinanceDirector,Auditor'])->group(function () {
            Route::get('/reports', [FinancialReportsViewController::class, 'index'])->name('reports.index');
            Route::get('/print/bir-2307/{id}', [ExportAndPrintController::class, 'printBir2307'])->name('print.bir2307');
            Route::get('/export/trial-balance-csv', [ExportAndPrintController::class, 'downloadTrialBalanceCsv'])->name('export.trial-balance-csv');
            Route::get('/export/general-ledger-csv', [ExportAndPrintController::class, 'downloadGeneralLedgerCsv'])->name('export.general-ledger-csv');
        });

        // Period-End Closing & Hard Locking
        Route::middleware(['role:CFO,FinanceDirector'])->group(function () {
            Route::get('/period-close', [PeriodClosingViewController::class, 'index'])->name('period-close.index');
            Route::post('/period-close/lock', [PeriodClosingViewController::class, 'lock'])->name('period-close.lock');
        });
    });

}); // end auth middleware group
