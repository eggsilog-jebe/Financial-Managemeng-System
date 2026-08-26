@extends('layouts.app')

@section('title', 'Payable Aging Schedule - Accounts Payable | FMS')
@section('module', 'ap')
@section('page', 'payable-aging')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Accounts Payable</li>
          <li class="breadcrumb-item active">Payable Aging</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Accounts Payable (AP) Aging Schedule</h1>
    </div>
    <div class="d-flex align-items-center gap-2">
      <x-integration-badge 
          type="internal" 
          :internalModules="['Invoices & Vouchers', 'Vendor Management']" 
          :tables="['vendor_invoices', 'vendors']"
          description="Tracks outstanding supplier payables categorized by vendor credit terms and aging brackets." 
      />
      <a href="{{ route('ap.payable-aging.export', ['as_of_date' => $asOfDate]) }}" class="btn btn-outline-secondary btn-sm">
        <i class="ph ph-file-csv me-1"></i> Export Aging CSV
      </a>
      <button class="btn btn-secondary btn-sm" type="button" onclick="window.print()">
        <i class="ph ph-printer me-1"></i> Print Schedule
      </button>
    </div>
  </div>

  <!-- Aging Metric Cards (5 Buckets) -->
  <div class="row g-3 mb-4">
    <div class="col-md-2">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">Current (Not Due)</span>
          <span class="badge bg-success-subtle text-success p-1 rounded-2"><i class="ph ph-check-circle fs-6"></i></span>
        </div>
        <h5 class="fw-bold text-dark mb-0 font-monospace">₱{{ number_format((float) $totalCurrent, 2) }}</h5>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">1 - 30 Days</span>
          <span class="badge bg-info-subtle text-info p-1 rounded-2"><i class="ph ph-clock fs-6"></i></span>
        </div>
        <h5 class="fw-bold text-dark mb-0 font-monospace">₱{{ number_format((float) $total1To30, 2) }}</h5>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">31 - 60 Days</span>
          <span class="badge bg-warning-subtle text-warning p-1 rounded-2"><i class="ph ph-hourglass fs-6"></i></span>
        </div>
        <h5 class="fw-bold text-warning mb-0 font-monospace">₱{{ number_format((float) $total31To60, 2) }}</h5>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">61 - 90 Days</span>
          <span class="badge bg-danger-subtle text-danger p-1 rounded-2"><i class="ph ph-warning fs-6"></i></span>
        </div>
        <h5 class="fw-bold text-danger mb-0 font-monospace">₱{{ number_format((float) $total61To90, 2) }}</h5>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small">90+ Days Overdue</span>
          <span class="badge bg-dark-subtle text-dark p-1 rounded-2"><i class="ph ph-shield-warning fs-6"></i></span>
        </div>
        <h5 class="fw-bold text-danger mb-0 font-monospace">₱{{ number_format((float) $total90Plus, 2) }}</h5>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card border-0 shadow-sm rounded-3 p-3 bg-primary-subtle text-primary border-primary">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="fw-semibold small">Grand Total AP</span>
          <span class="badge bg-primary text-white p-1 rounded-2"><i class="ph ph-trend-down fs-6"></i></span>
        </div>
        <h5 class="fw-bold text-primary mb-0 font-monospace">₱{{ number_format((float) $grandTotalPayable, 2) }}</h5>
      </div>
    </div>
  </div>

  <!-- Filter Controls -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('ap.payable-aging') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label small fw-semibold mb-1">As-Of Cutoff Date:</label>
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-light"><i class="ph ph-calendar"></i></span>
            <input type="date" name="as_of_date" class="form-control" value="{{ $asOfDate }}" onchange="this.form.submit()">
          </div>
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold mb-1">Filter by Vendor:</label>
          <select name="vendor_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">-- All Vendors &amp; Suppliers --</option>
            @foreach($allVendors as $v)
              <option value="{{ $v->id }}" {{ $selectedVendorId == $v->id ? 'selected' : '' }}>{{ $v->name }} ({{ $v->code }})</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4 text-end">
          <a href="{{ route('ap.payable-aging') }}" class="btn btn-sm btn-light border"><i class="ph ph-arrow-counter-clockwise me-1"></i> Reset Cutoff</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Aging Schedule Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="agingTable" class="table table-hover align-middle mb-0 fs-xs">
          <thead class="table-light">
            <tr>
              <th>Vendor Code</th>
              <th>Supplier Legal Name</th>
              <th>TIN</th>
              <th>Terms</th>
              <th class="text-end">Current (₱)</th>
              <th class="text-end">1 - 30 Days (₱)</th>
              <th class="text-end">31 - 60 Days (₱)</th>
              <th class="text-end">61 - 90 Days (₱)</th>
              <th class="text-end">90+ Days (₱)</th>
              <th class="text-end fw-bold">Total Due (₱)</th>
            </tr>
          </thead>
          <tbody>
            @forelse($vendors as $v)
            <tr>
              <td><span class="font-monospace fw-bold text-primary">{{ $v['vendor_code'] }}</span></td>
              <td>
                <div class="fw-bold text-dark">{{ $v['vendor_name'] }}</div>
                <div class="text-muted fs-xs">{{ $v['bills_count'] }} open invoice(s)</div>
              </td>
              <td><span class="font-monospace text-muted">{{ $v['tin'] }}</span></td>
              <td><span class="badge bg-light text-dark border">Net {{ $v['terms'] }}</span></td>
              <td class="text-end font-monospace text-success">₱{{ number_format((float) $v['current'], 2) }}</td>
              <td class="text-end font-monospace">₱{{ number_format((float) $v['days_1_30'], 2) }}</td>
              <td class="text-end font-monospace {{ $v['days_31_60'] > 0 ? 'text-warning fw-semibold' : 'text-muted' }}">₱{{ number_format((float) $v['days_31_60'], 2) }}</td>
              <td class="text-end font-monospace {{ $v['days_61_90'] > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">₱{{ number_format((float) $v['days_61_90'], 2) }}</td>
              <td class="text-end font-monospace {{ $v['days_90_plus'] > 0 ? 'text-danger fw-bold' : 'text-muted' }}">₱{{ number_format((float) $v['days_90_plus'], 2) }}</td>
              <td class="text-end font-monospace fw-bold text-dark fs-6">₱{{ number_format((float) $v['total_due'], 2) }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="10" class="text-center py-4 text-muted">No open Accounts Payable records found for this cutoff date.</td>
            </tr>
            @endforelse
          </tbody>
          <tfoot class="table-light fw-bold">
            <tr>
              <td colspan="4" class="text-uppercase">Total AP Liabilities:</td>
              <td class="text-end font-monospace text-success">₱{{ number_format((float) $totalCurrent, 2) }}</td>
              <td class="text-end font-monospace">₱{{ number_format((float) $total1To30, 2) }}</td>
              <td class="text-end font-monospace text-warning">₱{{ number_format((float) $total31To60, 2) }}</td>
              <td class="text-end font-monospace text-danger">₱{{ number_format((float) $total61To90, 2) }}</td>
              <td class="text-end font-monospace text-danger">₱{{ number_format((float) $total90Plus, 2) }}</td>
              <td class="text-end font-monospace text-primary fs-6">₱{{ number_format((float) $grandTotalPayable, 2) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between">
      <span class="text-muted fs-xs">Reporting {{ count($vendors) }} Vendor Accounts as of {{ $asOfDate }}</span>
    </div>
  </div>
</div>
@endsection
