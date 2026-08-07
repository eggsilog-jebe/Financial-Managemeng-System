@php
  $isGl = request()->routeIs('gl.*');
  $isAp = request()->routeIs('ap.*');
  $isAr = request()->routeIs('ar.*');
  $isDisbursement = request()->routeIs('disbursement.*');
  $isCollection = request()->routeIs('collection.*');
  $isBudget = request()->routeIs('budget.*');
  $isCash = request()->routeIs('cash.*');
  $isReporting = request()->routeIs('reporting.*');
  $isTax = request()->routeIs('tax.*');
@endphp

<aside class="sidebar" id="app-sidebar" aria-label="Primary navigation">
  <div class="sidebar-panel">
    <header class="sidebar-header">
      <a class="sidebar-logo" href="{{ url('/') }}" aria-label="FMS Home">
        <span class="logo-icon" aria-hidden="true"><i class="ph-fill ph-bank"></i></span>
        <span class="logo-text">
          <strong>FMS</strong>
          <span class="brand-tagline">Financial Management System</span>
          <span class="brand-suite">Transaction Core</span>
        </span>
      </a>
    </header>

    <nav class="sidebar-nav" aria-label="FMS systems">
      <p class="nav-title">Overview</p>
      <ul class="nav-list">
        <li>
          <a class="nav-link{{ request()->routeIs('dashboard') ? ' active' : '' }}" href="{{ route('dashboard') }}" data-page="dashboard" data-nav-tooltip="Dashboard" aria-label="Dashboard" @if(request()->routeIs('dashboard')) aria-current="page" @endif>
            <i class="ph-fill ph-squares-four" aria-hidden="true"></i>
            <span class="nav-label">Dashboard</span>
          </a>
        </li>
      </ul>

      <p class="nav-title">Transaction Core Modules</p>
      <ul class="nav-list nav-domain-list">
        <!-- 1. General Ledger -->
        <li class="nav-accordion{{ $isGl ? ' is-expanded is-active' : '' }}">
          <button class="nav-link nav-link-button nav-accordion__toggle" type="button" aria-expanded="{{ $isGl ? 'true' : 'false' }}" aria-controls="nav-gl" aria-label="General Ledger" data-nav-tooltip="General Ledger">
            <i class="ph-fill ph-book-open" aria-hidden="true"></i>
            <span class="nav-label">General Ledger</span>
            <i class="ph ph-caret-down nav-chevron" aria-hidden="true"></i>
          </button>
          <ul class="nav-submenu" id="nav-gl" @if(!$isGl) hidden @endif>
            <li><a href="{{ route('gl.chart-of-accounts') }}" class="{{ request()->routeIs('gl.chart-of-accounts') ? 'active' : '' }}">Chart of Accounts</a></li>
            <li><a href="{{ route('gl.journal-entries') }}" class="{{ request()->routeIs('gl.journal-entries') ? 'active' : '' }}">Journal Entries</a></li>
            <li><a href="{{ route('gl.ledger-books') }}" class="{{ request()->routeIs('gl.ledger-books') ? 'active' : '' }}">Ledger Books</a></li>
            <li><a href="{{ route('gl.trial-balance') }}" class="{{ request()->routeIs('gl.trial-balance') ? 'active' : '' }}">Trial Balance</a></li>
            <li><a href="{{ route('gl.period-end-closing') }}" class="{{ request()->routeIs('gl.period-end-closing') ? 'active' : '' }}">Period End Closing</a></li>
          </ul>
        </li>

        <!-- 2. Accounts Payable (AP) -->
        <li class="nav-accordion{{ $isAp ? ' is-expanded is-active' : '' }}">
          <button class="nav-link nav-link-button nav-accordion__toggle" type="button" aria-expanded="{{ $isAp ? 'true' : 'false' }}" aria-controls="nav-ap" aria-label="Accounts Payable" data-nav-tooltip="Accounts Payable">
            <i class="ph-fill ph-receipt" aria-hidden="true"></i>
            <span class="nav-label">Accounts Payable (AP)</span>
            <i class="ph ph-caret-down nav-chevron" aria-hidden="true"></i>
          </button>
          <ul class="nav-submenu" id="nav-ap" @if(!$isAp) hidden @endif>
            <li><a href="{{ route('ap.vendors') }}" class="{{ request()->routeIs('ap.vendors') ? 'active' : '' }}">Vendor Management</a></li>
            <li><a href="{{ route('ap.invoices') }}" class="{{ request()->routeIs('ap.invoices') ? 'active' : '' }}">Invoices &amp; Vouchers</a></li>
            <li><a href="{{ route('ap.purchase-bills') }}" class="{{ request()->routeIs('ap.purchase-bills') ? 'active' : '' }}">Purchase Bills</a></li>
            <li><a href="{{ route('ap.payable-aging') }}" class="{{ request()->routeIs('ap.payable-aging') ? 'active' : '' }}">Payable Aging</a></li>
            <li><a href="{{ route('ap.ap-approvals') }}" class="{{ request()->routeIs('ap.ap-approvals') ? 'active' : '' }}">AP Payment Approvals</a></li>
          </ul>
        </li>

        <!-- 3. Accounts Receivable (AR) -->
        <li class="nav-accordion{{ $isAr ? ' is-expanded is-active' : '' }}">
          <button class="nav-link nav-link-button nav-accordion__toggle" type="button" aria-expanded="{{ $isAr ? 'true' : 'false' }}" aria-controls="nav-ar" aria-label="Accounts Receivable" data-nav-tooltip="Accounts Receivable">
            <i class="ph-fill ph-currency-circle-dollar" aria-hidden="true"></i>
            <span class="nav-label">Accounts Receivable (AR)</span>
            <i class="ph ph-caret-down nav-chevron" aria-hidden="true"></i>
          </button>
          <ul class="nav-submenu" id="nav-ar" @if(!$isAr) hidden @endif>
            <li><a href="{{ route('ar.customers') }}" class="{{ request()->routeIs('ar.customers') ? 'active' : '' }}">Patient Accounts</a></li>
            <li><a href="{{ route('ar.billing') }}" class="{{ request()->routeIs('ar.billing') ? 'active' : '' }}">Invoicing &amp; Billing</a></li>
            <li><a href="{{ route('ar.ar-aging') }}" class="{{ request()->routeIs('ar.ar-aging') ? 'active' : '' }}">Receivable Aging</a></li>
            <li><a href="{{ route('ar.credit-notes') }}" class="{{ request()->routeIs('ar.credit-notes') ? 'active' : '' }}">Credit Notes</a></li>
            <li><a href="{{ route('ar.statements') }}" class="{{ request()->routeIs('ar.statements') ? 'active' : '' }}">Customer Statements</a></li>
          </ul>
        </li>

        <!-- 4. Disbursement Management -->
        <li class="nav-accordion{{ $isDisbursement ? ' is-expanded is-active' : '' }}">
          <button class="nav-link nav-link-button nav-accordion__toggle" type="button" aria-expanded="{{ $isDisbursement ? 'true' : 'false' }}" aria-controls="nav-disbursement" aria-label="Disbursement Management" data-nav-tooltip="Disbursement">
            <i class="ph-fill ph-arrows-out" aria-hidden="true"></i>
            <span class="nav-label">Disbursement Management</span>
            <i class="ph ph-caret-down nav-chevron" aria-hidden="true"></i>
          </button>
          <ul class="nav-submenu" id="nav-disbursement" @if(!$isDisbursement) hidden @endif>
            <li><a href="{{ route('disbursement.payment-requests') }}" class="{{ request()->routeIs('disbursement.payment-requests') ? 'active' : '' }}">Payment Requests</a></li>
            <li><a href="{{ route('disbursement.check-register') }}" class="{{ request()->routeIs('disbursement.check-register') ? 'active' : '' }}">Check Register</a></li>
            <li><a href="{{ route('disbursement.eft-transfers') }}" class="{{ request()->routeIs('disbursement.eft-transfers') ? 'active' : '' }}">EFT Transfers</a></li>
            <li><a href="{{ route('disbursement.disbursement-approval') }}" class="{{ request()->routeIs('disbursement.disbursement-approval') ? 'active' : '' }}">Disbursement Approvals</a></li>
            <li><a href="{{ route('disbursement.petty-cash') }}" class="{{ request()->routeIs('disbursement.petty-cash') ? 'active' : '' }}">Petty Cash</a></li>
          </ul>
        </li>

        <!-- 5. Collection Management -->
        <li class="nav-accordion{{ $isCollection ? ' is-expanded is-active' : '' }}">
          <button class="nav-link nav-link-button nav-accordion__toggle" type="button" aria-expanded="{{ $isCollection ? 'true' : 'false' }}" aria-controls="nav-collection" aria-label="Collection Management" data-nav-tooltip="Collection">
            <i class="ph-fill ph-vault" aria-hidden="true"></i>
            <span class="nav-label">Collection Management</span>
            <i class="ph ph-caret-down nav-chevron" aria-hidden="true"></i>
          </button>
          <ul class="nav-submenu" id="nav-collection" @if(!$isCollection) hidden @endif>
            <li><a href="{{ route('collection.receipts') }}" class="{{ request()->routeIs('collection.receipts') ? 'active' : '' }}">Payment Receipts</a></li>
            <li><a href="{{ route('collection.cashier-desk') }}" class="{{ request()->routeIs('collection.cashier-desk') ? 'active' : '' }}">Cashier Desk</a></li>
            <li><a href="{{ route('collection.deposit-slips') }}" class="{{ request()->routeIs('collection.deposit-slips') ? 'active' : '' }}">Deposit Slips</a></li>
            <li><a href="{{ route('collection.bank-deposits') }}" class="{{ request()->routeIs('collection.bank-deposits') ? 'active' : '' }}">Bank Deposits</a></li>
            <li><a href="{{ route('collection.payment-gateways') }}" class="{{ request()->routeIs('collection.payment-gateways') ? 'active' : '' }}">Payment Gateway Logs</a></li>
          </ul>
        </li>

        <!-- 6. Budget Management -->
        <li class="nav-accordion{{ $isBudget ? ' is-expanded is-active' : '' }}">
          <button class="nav-link nav-link-button nav-accordion__toggle" type="button" aria-expanded="{{ $isBudget ? 'true' : 'false' }}" aria-controls="nav-budget" aria-label="Budget Management" data-nav-tooltip="Budget">
            <i class="ph-fill ph-calculator" aria-hidden="true"></i>
            <span class="nav-label">Budget Management</span>
            <i class="ph ph-caret-down nav-chevron" aria-hidden="true"></i>
          </button>
          <ul class="nav-submenu" id="nav-budget" @if(!$isBudget) hidden @endif>
            <li><a href="{{ route('budget.fiscal-planning') }}" class="{{ request()->routeIs('budget.fiscal-planning') ? 'active' : '' }}">Fiscal Planning</a></li>
            <li><a href="{{ route('budget.budget-allocation') }}" class="{{ request()->routeIs('budget.budget-allocation') ? 'active' : '' }}">Budget Allocation</a></li>
            <li><a href="{{ route('budget.departmental-budgets') }}" class="{{ request()->routeIs('budget.departmental-budgets') ? 'active' : '' }}">Departmental Budgets</a></li>
            <li><a href="{{ route('budget.variance-analysis') }}" class="{{ request()->routeIs('budget.variance-analysis') ? 'active' : '' }}">Variance Analysis</a></li>
            <li><a href="{{ route('budget.reallocations') }}" class="{{ request()->routeIs('budget.reallocations') ? 'active' : '' }}">Budget Reallocations</a></li>
          </ul>
        </li>

        <!-- 7. Cash Management -->
        <li class="nav-accordion{{ $isCash ? ' is-expanded is-active' : '' }}">
          <button class="nav-link nav-link-button nav-accordion__toggle" type="button" aria-expanded="{{ $isCash ? 'true' : 'false' }}" aria-controls="nav-cash" aria-label="Cash Management" data-nav-tooltip="Cash Management">
            <i class="ph-fill ph-coins" aria-hidden="true"></i>
            <span class="nav-label">Cash Management</span>
            <i class="ph ph-caret-down nav-chevron" aria-hidden="true"></i>
          </button>
          <ul class="nav-submenu" id="nav-cash" @if(!$isCash) hidden @endif>
            <li><a href="{{ route('cash.bank-accounts') }}" class="{{ request()->routeIs('cash.bank-accounts') ? 'active' : '' }}">Bank Accounts</a></li>
            <li><a href="{{ route('cash.cash-flow-forecast') }}" class="{{ request()->routeIs('cash.cash-flow-forecast') ? 'active' : '' }}">Cash Flow Forecasting</a></li>
            <li><a href="{{ route('cash.bank-reconciliation') }}" class="{{ request()->routeIs('cash.bank-reconciliation') ? 'active' : '' }}">Bank Reconciliation</a></li>
            <li><a href="{{ route('cash.fund-transfers') }}" class="{{ request()->routeIs('cash.fund-transfers') ? 'active' : '' }}">Fund Transfers</a></li>
            <li><a href="{{ route('cash.liquidity') }}" class="{{ request()->routeIs('cash.liquidity') ? 'active' : '' }}">Liquidity Management</a></li>
          </ul>
        </li>

        <!-- 8. Financial Reporting & Analytics -->
        <li class="nav-accordion{{ $isReporting ? ' is-expanded is-active' : '' }}">
          <button class="nav-link nav-link-button nav-accordion__toggle" type="button" aria-expanded="{{ $isReporting ? 'true' : 'false' }}" aria-controls="nav-reporting" aria-label="Financial Reporting & Analytics" data-nav-tooltip="Reporting">
            <i class="ph-fill ph-chart-line-up" aria-hidden="true"></i>
            <span class="nav-label">Financial Reporting</span>
            <i class="ph ph-caret-down nav-chevron" aria-hidden="true"></i>
          </button>
          <ul class="nav-submenu" id="nav-reporting" @if(!$isReporting) hidden @endif>
            <li><a href="{{ route('reporting.balance-sheet') }}" class="{{ request()->routeIs('reporting.balance-sheet') ? 'active' : '' }}">Balance Sheet</a></li>
            <li><a href="{{ route('reporting.profit-loss') }}" class="{{ request()->routeIs('reporting.profit-loss') ? 'active' : '' }}">Profit &amp; Loss (P&amp;L)</a></li>
            <li><a href="{{ route('reporting.cash-flow-statement') }}" class="{{ request()->routeIs('reporting.cash-flow-statement') ? 'active' : '' }}">Cash Flow Statement</a></li>
            <li><a href="{{ route('reporting.kpi-dashboard') }}" class="{{ request()->routeIs('reporting.kpi-dashboard') ? 'active' : '' }}">Financial KPI Dashboard</a></li>
            <li><a href="{{ route('reporting.executive-reports') }}" class="{{ request()->routeIs('reporting.executive-reports') ? 'active' : '' }}">Executive Reports</a></li>
          </ul>
        </li>

        <!-- 9. Tax Management -->
        <li class="nav-accordion{{ $isTax ? ' is-expanded is-active' : '' }}">
          <button class="nav-link nav-link-button nav-accordion__toggle" type="button" aria-expanded="{{ $isTax ? 'true' : 'false' }}" aria-controls="nav-tax" aria-label="Tax Management" data-nav-tooltip="Tax Management">
            <i class="ph-fill ph-percent" aria-hidden="true"></i>
            <span class="nav-label">Tax Management</span>
            <i class="ph ph-caret-down nav-chevron" aria-hidden="true"></i>
          </button>
          <ul class="nav-submenu" id="nav-tax" @if(!$isTax) hidden @endif>
            <li><a href="{{ route('tax.tax-config') }}" class="{{ request()->routeIs('tax.tax-config') ? 'active' : '' }}">Tax Configuration</a></li>
            <li><a href="{{ route('tax.withholding-tax') }}" class="{{ request()->routeIs('tax.withholding-tax') ? 'active' : '' }}">Withholding Tax (EWT/VAT)</a></li>
            <li><a href="{{ route('tax.tax-returns') }}" class="{{ request()->routeIs('tax.tax-returns') ? 'active' : '' }}">Tax Returns &amp; Filings</a></li>
            <li><a href="{{ route('tax.tax-exemptions') }}" class="{{ request()->routeIs('tax.tax-exemptions') ? 'active' : '' }}">Tax Exemptions</a></li>
            <li><a href="{{ route('tax.tax-audit') }}" class="{{ request()->routeIs('tax.tax-audit') ? 'active' : '' }}">Tax Audit Trail</a></li>
          </ul>
        </li>
      </ul>
    </nav>

    <footer class="sidebar-footer">
      <div class="sidebar-profile-wrap">
        <button class="sidebar-profile" id="profile-toggle" type="button" aria-label="Open account menu for FMS User" aria-haspopup="menu" aria-expanded="false" aria-controls="profile-menu">
          <span class="profile-avatar" aria-hidden="true">FU</span>
          <span class="profile-info">
            <span class="profile-name">FMS Administrator</span>
            <span class="profile-role">Transaction Core</span>
          </span>
          <i class="ph ph-caret-up-down profile-chevron" aria-hidden="true"></i>
        </button>
        <div class="profile-menu" id="profile-menu" role="menu" hidden>
          <a class="profile-menu-link" href="#profile" role="menuitem"><i class="ph ph-user" aria-hidden="true"></i>Profile</a>
          <a class="profile-menu-link" href="#settings" role="menuitem"><i class="ph ph-gear" aria-hidden="true"></i>Settings</a>
          <div class="profile-menu-divider" role="separator"></div>
          <p class="profile-menu-label" id="appearance-menu-label">Appearance</p>
          <div class="theme-options" role="group" aria-labelledby="appearance-menu-label">
            <button type="button" role="menuitemradio" data-theme-option="light" aria-checked="false"><i class="ph ph-sun" aria-hidden="true"></i>Light</button>
            <button type="button" role="menuitemradio" data-theme-option="dark" aria-checked="false"><i class="ph ph-moon" aria-hidden="true"></i>Dark</button>
            <button type="button" role="menuitemradio" data-theme-option="system" aria-checked="false"><i class="ph ph-desktop" aria-hidden="true"></i>System</button>
          </div>
          <div class="profile-menu-divider" role="separator"></div>
          <button class="profile-menu-link profile-menu-logout" type="button" role="menuitem" data-logout><i class="ph ph-sign-out" aria-hidden="true"></i>Logout</button>
        </div>
      </div>
    </footer>
  </div>
</aside>
