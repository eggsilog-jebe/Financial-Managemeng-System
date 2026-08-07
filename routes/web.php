<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('dashboard');

// 1. General Ledger
Route::prefix('general-ledger')->name('gl.')->group(function () {
    Route::get('/chart-of-accounts', fn() => view('general-ledger.chart-of-accounts'))->name('chart-of-accounts');
    Route::get('/journal-entries', fn() => view('general-ledger.journal-entries'))->name('journal-entries');
    Route::get('/ledger-books', fn() => view('general-ledger.ledger-books'))->name('ledger-books');
    Route::get('/trial-balance', fn() => view('general-ledger.trial-balance'))->name('trial-balance');
    Route::get('/period-end-closing', fn() => view('general-ledger.period-end-closing'))->name('period-end-closing');
});

// 2. Accounts Payable
Route::prefix('accounts-payable')->name('ap.')->group(function () {
    Route::get('/vendor-management', fn() => view('accounts-payable.vendor-management'))->name('vendors');
    Route::get('/invoices-vouchers', fn() => view('accounts-payable.invoices-vouchers'))->name('invoices');
    Route::get('/purchase-bills', fn() => view('accounts-payable.purchase-bills'))->name('purchase-bills');
    Route::get('/payable-aging', fn() => view('accounts-payable.payable-aging'))->name('payable-aging');
    Route::get('/ap-payment-approvals', fn() => view('accounts-payable.ap-payment-approvals'))->name('ap-approvals');
});

// 3. Accounts Receivable
Route::prefix('accounts-receivable')->name('ar.')->group(function () {
    Route::get('/patient-accounts', fn() => view('accounts-receivable.patient-accounts'))->name('customers');
    Route::get('/invoicing-billing', fn() => view('accounts-receivable.invoicing-billing'))->name('billing');
    Route::get('/receivable-aging', fn() => view('accounts-receivable.receivable-aging'))->name('ar-aging');
    Route::get('/credit-notes', fn() => view('accounts-receivable.credit-notes'))->name('credit-notes');
    Route::get('/customer-statements', fn() => view('accounts-receivable.customer-statements'))->name('statements');
});

// 4. Disbursement Management
Route::prefix('disbursement-management')->name('disbursement.')->group(function () {
    Route::get('/payment-requests', fn() => view('disbursement.payment-requests'))->name('payment-requests');
    Route::get('/check-register', fn() => view('disbursement.check-register'))->name('check-register');
    Route::get('/eft-transfers', fn() => view('disbursement.eft-transfers'))->name('eft-transfers');
    Route::get('/disbursement-approvals', fn() => view('disbursement.disbursement-approvals'))->name('disbursement-approval');
    Route::get('/petty-cash', fn() => view('disbursement.petty-cash'))->name('petty-cash');
});

// 5. Collection Management
Route::prefix('collection-management')->name('collection.')->group(function () {
    Route::get('/payment-receipts', fn() => view('collection.payment-receipts'))->name('receipts');
    Route::get('/cashier-desk', fn() => view('collection.cashier-desk'))->name('cashier-desk');
    Route::get('/deposit-slips', fn() => view('collection.deposit-slips'))->name('deposit-slips');
    Route::get('/bank-deposits', fn() => view('collection.bank-deposits'))->name('bank-deposits');
    Route::get('/payment-gateway-logs', fn() => view('collection.payment-gateway-logs'))->name('payment-gateways');
});

// 6. Budget Management
Route::prefix('budget-management')->name('budget.')->group(function () {
    Route::get('/fiscal-planning', fn() => view('budget.fiscal-planning'))->name('fiscal-planning');
    Route::get('/budget-allocation', fn() => view('budget.budget-allocation'))->name('budget-allocation');
    Route::get('/departmental-budgets', fn() => view('budget.departmental-budgets'))->name('departmental-budgets');
    Route::get('/variance-analysis', fn() => view('budget.variance-analysis'))->name('variance-analysis');
    Route::get('/budget-reallocations', fn() => view('budget.budget-reallocations'))->name('reallocations');
});

// 7. Cash Management
Route::prefix('cash-management')->name('cash.')->group(function () {
    Route::get('/bank-accounts', fn() => view('cash.bank-accounts'))->name('bank-accounts');
    Route::get('/cash-flow-forecasting', fn() => view('cash.cash-flow-forecasting'))->name('cash-flow-forecast');
    Route::get('/bank-reconciliation', fn() => view('cash.bank-reconciliation'))->name('bank-reconciliation');
    Route::get('/fund-transfers', fn() => view('cash.fund-transfers'))->name('fund-transfers');
    Route::get('/liquidity-management', fn() => view('cash.liquidity-management'))->name('liquidity');
});

// 8. Financial Reporting & Analytics
Route::prefix('financial-reporting')->name('reporting.')->group(function () {
    Route::get('/balance-sheet', fn() => view('financial-reporting.balance-sheet'))->name('balance-sheet');
    Route::get('/profit-loss', fn() => view('financial-reporting.profit-loss'))->name('profit-loss');
    Route::get('/cash-flow-statement', fn() => view('financial-reporting.cash-flow-statement'))->name('cash-flow-statement');
    Route::get('/financial-kpi-dashboard', fn() => view('financial-reporting.financial-kpi-dashboard'))->name('kpi-dashboard');
    Route::get('/executive-reports', fn() => view('financial-reporting.executive-reports'))->name('executive-reports');
});

// 9. Tax Management
Route::prefix('tax-management')->name('tax.')->group(function () {
    Route::get('/tax-configuration', fn() => view('tax.tax-configuration'))->name('tax-config');
    Route::get('/withholding-tax', fn() => view('tax.withholding-tax'))->name('withholding-tax');
    Route::get('/tax-returns', fn() => view('tax.tax-returns'))->name('tax-returns');
    Route::get('/tax-exemptions', fn() => view('tax.tax-exemptions'))->name('tax-exemptions');
    Route::get('/tax-audit-trail', fn() => view('tax.tax-audit-trail'))->name('tax-audit');
});
