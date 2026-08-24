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
        @can('access-general-ledger')
        <li>
          <a class="nav-link{{ request()->routeIs('accounting.dashboard') || request()->routeIs('dashboard') ? ' active' : '' }}" href="{{ route('accounting.dashboard') }}" data-page="dashboard" data-nav-tooltip="Dashboard" aria-label="Dashboard">
            <i class="ph-fill ph-squares-four" aria-hidden="true"></i>
            <span class="nav-label">Executive Dashboard</span>
          </a>
        </li>
        @endcan

        @can('access-cashier-pos')
        <li>
          <a class="nav-link{{ request()->routeIs('accounting.cashier.*') ? ' active' : '' }}" href="{{ route('accounting.cashier.index') }}" data-page="cashier" data-nav-tooltip="Cashier POS" aria-label="Cashier POS">
            <i class="ph-fill ph-hand-coins" aria-hidden="true"></i>
            <span class="nav-label">Cashier POS Desk</span>
            <span class="badge bg-warning-subtle text-warning ms-auto fs-xs">POS</span>
          </a>
        </li>
        @endcan

        @can('access-general-ledger')
        <li>
          <a class="nav-link{{ request()->routeIs('accounting.general-ledger.*') ? ' active' : '' }}" href="{{ route('accounting.general-ledger.index') }}" data-page="gl-browser" data-nav-tooltip="GL Browser" aria-label="GL Browser">
            <i class="ph-fill ph-book-open-text" aria-hidden="true"></i>
            <span class="nav-label">Journal Browser</span>
          </a>
        </li>
        @endcan

        @can('access-financial-reports')
        <li>
          <a class="nav-link{{ request()->routeIs('accounting.reports.*') ? ' active' : '' }}" href="{{ route('accounting.reports.index') }}" data-page="reports-hub" data-nav-tooltip="Reports Hub" aria-label="Reports Hub">
            <i class="ph-fill ph-chart-line-up" aria-hidden="true"></i>
            <span class="nav-label">Financial Reports Hub</span>
          </a>
        </li>
        @endcan

        @can('access-period-closing')
        <li>
          <a class="nav-link{{ request()->routeIs('accounting.period-close.*') ? ' active' : '' }}" href="{{ route('accounting.period-close.index') }}" data-page="period-close" data-nav-tooltip="Period-End Locks" aria-label="Period-End Locks">
            <i class="ph-fill ph-lock-key" aria-hidden="true"></i>
            <span class="nav-label">Period-End Locks</span>
            <span class="badge bg-danger-subtle text-danger ms-auto fs-xs">CFO</span>
          </a>
        </li>
        @endcan
      </ul>

      <p class="nav-title">Transaction Core Modules</p>
      <ul class="nav-list nav-domain-list">
        <!-- 1. General Ledger -->
        @can('access-general-ledger')
        <li class="nav-accordion{{ $isGl ? ' is-expanded is-active' : '' }}">
          <button class="nav-link nav-link-button nav-accordion__toggle" type="button" data-href="{{ route('gl.chart-of-accounts') }}" aria-expanded="{{ $isGl ? 'true' : 'false' }}" aria-controls="nav-gl" aria-label="General Ledger" data-nav-tooltip="General Ledger">
            <i class="ph-fill ph-book-open" aria-hidden="true"></i>
            <span class="nav-label">General Ledger</span>
            <i class="ph ph-caret-down nav-chevron" aria-hidden="true"></i>
          </button>
          <ul class="nav-submenu" id="nav-gl" @if(!$isGl) hidden @endif>
            <li><a href="{{ route('gl.chart-of-accounts') }}" class="{{ request()->routeIs('gl.chart-of-accounts') ? 'active' : '' }}">Chart of Accounts</a></li>
            <li><a href="{{ route('gl.journal-entries') }}" class="{{ request()->routeIs('gl.journal-entries') ? 'active' : '' }}">Journal Entries</a></li>
            <li><a href="{{ route('gl.ledger-books') }}" class="{{ request()->routeIs('gl.ledger-books') ? 'active' : '' }}">Ledger Books</a></li>
            <li><a href="{{ route('gl.trial-balance') }}" class="{{ request()->routeIs('gl.trial-balance') ? 'active' : '' }}">Trial Balance</a></li>
            @can('access-period-closing')
            <li><a href="{{ route('gl.period-end-closing') }}" class="{{ request()->routeIs('gl.period-end-closing') ? 'active' : '' }}">Period End Closing</a></li>
            @endcan
          </ul>
        </li>
        @endcan

        <!-- 2. Accounts Payable (AP) -->
        @can('access-ap-procurement')
        <li class="nav-accordion{{ $isAp ? ' is-expanded is-active' : '' }}">
          <button class="nav-link nav-link-button nav-accordion__toggle" type="button" data-href="{{ route('ap.vendors') }}" aria-expanded="{{ $isAp ? 'true' : 'false' }}" aria-controls="nav-ap" aria-label="Accounts Payable" data-nav-tooltip="Accounts Payable">
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
        @endcan

        <!-- 3. Accounts Receivable (AR) -->
        @can('access-ar-billing')
        <li class="nav-accordion{{ $isAr ? ' is-expanded is-active' : '' }}">
          <button class="nav-link nav-link-button nav-accordion__toggle" type="button" data-href="{{ route('ar.customers') }}" aria-expanded="{{ $isAr ? 'true' : 'false' }}" aria-controls="nav-ar" aria-label="Accounts Receivable" data-nav-tooltip="Accounts Receivable">
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
        @endcan

        <!-- 4. Disbursement Management -->
        @can('access-disbursements')
        <li class="nav-accordion{{ $isDisbursement ? ' is-expanded is-active' : '' }}">
          <button class="nav-link nav-link-button nav-accordion__toggle" type="button" data-href="{{ route('disbursement.payment-requests') }}" aria-expanded="{{ $isDisbursement ? 'true' : 'false' }}" aria-controls="nav-disbursement" aria-label="Disbursement Management" data-nav-tooltip="Disbursement">
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
        @endcan

        <!-- 5. Collection Management -->
        @can('access-cashier-pos')
        <li class="nav-accordion{{ $isCollection ? ' is-expanded is-active' : '' }}">
          <button class="nav-link nav-link-button nav-accordion__toggle" type="button" data-href="{{ route('collection.receipts') }}" aria-expanded="{{ $isCollection ? 'true' : 'false' }}" aria-controls="nav-collection" aria-label="Collection Management" data-nav-tooltip="Collection">
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
        @endcan

        <!-- 6. Budget Management -->
        @can('access-disbursements')
        <li class="nav-accordion{{ $isBudget ? ' is-expanded is-active' : '' }}">
          <button class="nav-link nav-link-button nav-accordion__toggle" type="button" data-href="{{ route('budget.fiscal-planning') }}" aria-expanded="{{ $isBudget ? 'true' : 'false' }}" aria-controls="nav-budget" aria-label="Budget Management" data-nav-tooltip="Budget">
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
        @endcan

        <!-- 7. Cash Management -->
        @can('access-disbursements')
        <li class="nav-accordion{{ $isCash ? ' is-expanded is-active' : '' }}">
          <button class="nav-link nav-link-button nav-accordion__toggle" type="button" data-href="{{ route('cash.bank-accounts') }}" aria-expanded="{{ $isCash ? 'true' : 'false' }}" aria-controls="nav-cash" aria-label="Cash Management" data-nav-tooltip="Cash Management">
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
        @endcan

        <!-- 8. Financial Reporting & Analytics -->
        @can('access-financial-reports')
        <li class="nav-accordion{{ $isReporting ? ' is-expanded is-active' : '' }}">
          <button class="nav-link nav-link-button nav-accordion__toggle" type="button" data-href="{{ route('reporting.balance-sheet') }}" aria-expanded="{{ $isReporting ? 'true' : 'false' }}" aria-controls="nav-reporting" aria-label="Financial Reporting & Analytics" data-nav-tooltip="Reporting">
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
        @endcan

        <!-- 9. Tax Management -->
        @can('access-financial-reports')
        <li class="nav-accordion{{ $isTax ? ' is-expanded is-active' : '' }}">
          <button class="nav-link nav-link-button nav-accordion__toggle" type="button" data-href="{{ route('tax.tax-config') }}" aria-expanded="{{ $isTax ? 'true' : 'false' }}" aria-controls="nav-tax" aria-label="Tax Management" data-nav-tooltip="Tax Management">
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
        @endcan
      </ul>
    </nav>

    <footer class="sidebar-footer">
      <div class="sidebar-profile-wrap">
        <button class="sidebar-profile" id="profile-toggle" type="button" aria-label="Open account menu for FMS User" aria-haspopup="menu" aria-expanded="false" aria-controls="profile-menu">
          <span class="profile-avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}</span>
          <span class="profile-info">
            <span class="profile-name">{{ auth()->user()->name ?? 'Executive Demo User' }}</span>
            <span class="profile-role badge bg-primary-subtle text-primary border border-primary-subtle py-0 px-2 mt-1">{{ auth()->user()->role ?? 'CFO' }}</span>
          </span>
          <i class="ph ph-caret-up-down profile-chevron" aria-hidden="true"></i>
        </button>
        <div class="profile-menu" id="profile-menu" role="menu" hidden>
          <a class="profile-menu-link" href="{{ route('accounting.dashboard') }}" role="menuitem"><i class="ph ph-squares-four" aria-hidden="true"></i>Dashboard</a>
          <a class="profile-menu-link" href="{{ route('login') }}" role="menuitem"><i class="ph ph-arrows-clockwise" aria-hidden="true"></i>Switch Demo Role</a>
          <div class="profile-menu-divider" role="separator"></div>
          <form method="POST" action="{{ route('logout') }}" id="logout-form">
            @csrf
            <button class="profile-menu-link text-danger border-0 bg-transparent w-100 text-start" type="submit" role="menuitem">
              <i class="ph ph-sign-out text-danger" aria-hidden="true"></i>Logout
            </button>
          </form>
        </div>
      </div>
    </footer>
  </div>
</aside>
