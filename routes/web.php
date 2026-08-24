<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GeneralLedgerController;
use App\Http\Controllers\CashManagementController;
use App\Http\Controllers\TaxManagementController;
use App\Http\Controllers\AccountsPayableController;
use App\Http\Controllers\AccountsReceivableController;
use App\Http\Controllers\DisbursementController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\FinancialReportingController;
use App\Http\Controllers\GeneralLedger\PostJournalEntryController;

Route::get('/', DashboardController::class)->name('dashboard');

// 1. General Ledger
Route::prefix('general-ledger')->name('gl.')->group(function () {
    Route::get('/chart-of-accounts', [GeneralLedgerController::class, 'chartOfAccounts'])->name('chart-of-accounts');
    Route::get('/journal-entries', [GeneralLedgerController::class, 'journalEntries'])->name('journal-entries');
    Route::post('/post-entry', PostJournalEntryController::class)->name('post-entry');
    Route::get('/ledger-books', [GeneralLedgerController::class, 'ledgerBooks'])->name('ledger-books');
    Route::get('/trial-balance', [GeneralLedgerController::class, 'trialBalance'])->name('trial-balance');
    Route::get('/period-end-closing', [GeneralLedgerController::class, 'periodEndClosing'])->name('period-end-closing');
});

// 2. Accounts Payable
Route::prefix('accounts-payable')->name('ap.')->group(function () {
    Route::get('/vendor-management', [AccountsPayableController::class, 'vendors'])->name('vendors');
    Route::get('/invoices-vouchers', [AccountsPayableController::class, 'invoices'])->name('invoices');
    Route::get('/purchase-bills', [AccountsPayableController::class, 'purchaseBills'])->name('purchase-bills');
    Route::get('/payable-aging', [AccountsPayableController::class, 'payableAging'])->name('payable-aging');
    Route::get('/ap-payment-approvals', [AccountsPayableController::class, 'apApprovals'])->name('ap-approvals');
});

// 3. Accounts Receivable
Route::prefix('accounts-receivable')->name('ar.')->group(function () {
    Route::get('/patient-accounts', [AccountsReceivableController::class, 'patientAccounts'])->name('customers');
    Route::get('/invoicing-billing', [AccountsReceivableController::class, 'invoicingBilling'])->name('billing');
    Route::get('/receivable-aging', [AccountsReceivableController::class, 'receivableAging'])->name('ar-aging');
    Route::get('/credit-notes', [AccountsReceivableController::class, 'creditNotes'])->name('credit-notes');
    Route::get('/customer-statements', [AccountsReceivableController::class, 'customerStatements'])->name('statements');
});

// 4. Disbursement Management
Route::prefix('disbursement-management')->name('disbursement.')->group(function () {
    Route::get('/payment-requests', [DisbursementController::class, 'paymentRequests'])->name('payment-requests');
    Route::get('/check-register', [DisbursementController::class, 'checkRegister'])->name('check-register');
    Route::get('/eft-transfers', [DisbursementController::class, 'eftTransfers'])->name('eft-transfers');
    Route::get('/disbursement-approvals', [DisbursementController::class, 'disbursementApprovals'])->name('disbursement-approval');
    Route::get('/petty-cash', [DisbursementController::class, 'pettyCash'])->name('petty-cash');
});

// 5. Collection Management
Route::prefix('collection-management')->name('collection.')->group(function () {
    Route::get('/payment-receipts', [CollectionController::class, 'paymentReceipts'])->name('receipts');
    Route::get('/cashier-desk', [CollectionController::class, 'cashierDesk'])->name('cashier-desk');
    Route::get('/deposit-slips', [CollectionController::class, 'depositSlips'])->name('deposit-slips');
    Route::get('/bank-deposits', [CollectionController::class, 'bankDeposits'])->name('bank-deposits');
    Route::get('/payment-gateway-logs', [CollectionController::class, 'paymentGatewayLogs'])->name('payment-gateways');
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
    Route::get('/bank-accounts', [CashManagementController::class, 'bankAccounts'])->name('bank-accounts');
    Route::get('/cash-flow-forecasting', [CashManagementController::class, 'cashFlowForecasting'])->name('cash-flow-forecast');
    Route::get('/bank-reconciliation', [CashManagementController::class, 'bankReconciliation'])->name('bank-reconciliation');
    Route::get('/fund-transfers', [CashManagementController::class, 'fundTransfers'])->name('fund-transfers');
    Route::get('/liquidity-management', [CashManagementController::class, 'liquidityManagement'])->name('liquidity');
});

// 8. Financial Reporting & Analytics
Route::prefix('financial-reporting')->name('reporting.')->group(function () {
    Route::get('/balance-sheet', [FinancialReportingController::class, 'balanceSheet'])->name('balance-sheet');
    Route::get('/profit-loss', [FinancialReportingController::class, 'profitLoss'])->name('profit-loss');
    Route::get('/cash-flow-statement', [FinancialReportingController::class, 'cashFlowStatement'])->name('cash-flow-statement');
    Route::get('/financial-kpi-dashboard', [FinancialReportingController::class, 'kpiDashboard'])->name('kpi-dashboard');
    Route::get('/executive-reports', [FinancialReportingController::class, 'executiveReports'])->name('executive-reports');
});

// 9. Tax Management
Route::prefix('tax-management')->name('tax.')->group(function () {
    Route::get('/tax-configuration', [TaxManagementController::class, 'taxConfiguration'])->name('tax-config');
    Route::get('/withholding-tax', [TaxManagementController::class, 'withholdingTax'])->name('withholding-tax');
    Route::get('/tax-returns', [TaxManagementController::class, 'taxReturns'])->name('tax-returns');
    Route::get('/tax-exemptions', [TaxManagementController::class, 'taxExemptions'])->name('tax-exemptions');
    Route::get('/tax-audit-trail', [TaxManagementController::class, 'taxAuditTrail'])->name('tax-audit');
});
