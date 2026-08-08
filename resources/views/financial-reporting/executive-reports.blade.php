@extends('layouts.app')

@section('title', 'Executive Reports - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'executive-reports')

@section('content')
<div class="container-fluid p-4">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Financial Reporting</li>
          <li class="breadcrumb-item active">Executive Summaries</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Executive Financial Packs &amp; Board Briefs</h1>
      <p class="text-muted small mb-0">Compiled quarterly and annual executive packs and management briefs for the Hospital Board of Directors.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" type="button"><i class="ph ph-folder-open me-1"></i> Report Archive</button>
      <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#compileReportModal"><i class="ph ph-plus-circle me-1"></i> Compile Board Pack Draft</button>
    </div>
  </div>

  <!-- Metric Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Published Board Packs</span>
          <span class="badge bg-success-subtle text-success p-2 rounded-2"><i class="ph ph-file-pdf fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">6 Reports</h4>
        <span class="fs-xs text-muted">Audited &amp; Board Approved</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Pending Draft Packs</span>
          <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i class="ph ph-pencil-simple fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">1 Draft</h4>
        <span class="fs-xs text-muted">Q3 2026 Preliminary Brief</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">Next Board Meeting</span>
          <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i class="ph ph-calendar-blank fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">Sep 15, 2026</h4>
        <span class="fs-xs text-muted">Quarterly Financial Review</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="text-muted small fw-medium">External Audit Status</span>
          <span class="badge bg-info-subtle text-info p-2 rounded-2"><i class="ph ph-shield-check fs-5"></i></span>
        </div>
        <h4 class="fw-bold mb-0 text-dark">Compliant</h4>
        <span class="fs-xs text-muted">Unqualified Audit Opinion</span>
      </div>
    </div>
  </div>

  <!-- Filter Toolbar -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <div class="row g-2 align-items-center">
        <div class="col-md-4">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-light border-end-0"><i class="ph ph-magnifying-glass text-muted"></i></span>
            <input type="text" class="form-control bg-light border-start-0" placeholder="Search Report Title, Author, or Period...">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Reporting Years</option>
            <option value="2026">FY 2026 Reports</option>
            <option value="2025">FY 2025 Reports</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm bg-light">
            <option value="">All Statuses</option>
            <option value="published">Published &amp; Approved</option>
            <option value="draft">Under Draft</option>
          </select>
        </div>
        <div class="col-md-2 text-end">
          <button class="btn btn-sm btn-light border w-100"><i class="ph ph-funnel me-1"></i> Filter</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Data Table Card -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Executive Report Title</th>
              <th>Reporting Period</th>
              <th>Author / Officer</th>
              <th>Generated Date</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <div class="fw-bold text-dark">Q2 2026 Executive Financial Performance Pack</div>
                <span class="fs-xs text-muted">Includes Balance Sheet, P&amp;L, Cash Flow &amp; KPI Analytics</span>
              </td>
              <td>Apr 01 - Jun 30, 2026</td>
              <td>Office of the Chief Financial Officer</td>
              <td>2026-07-10</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Published to Board</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-primary py-1 px-2"><i class="ph ph-download-simple me-1"></i> Download Brief</button>
                <button class="btn btn-sm btn-light border p-1" title="Edit Draft Commentary"><i class="ph ph-pencil"></i></button>
              </td>
            </tr>
            <tr>
              <td>
                <div class="fw-bold text-dark">Q1 2026 Quarterly Board Financial Summary</div>
                <span class="fs-xs text-muted">Includes EBITDA margin analysis &amp; ARPOB yield</span>
              </td>
              <td>Jan 01 - Mar 31, 2026</td>
              <td>Office of the Chief Financial Officer</td>
              <td>2026-04-12</td>
              <td><span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Published to Board</span></td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-primary py-1 px-2"><i class="ph ph-download-simple me-1"></i> Download Brief</button>
                <button class="btn btn-sm btn-light border p-1" title="View Audit Trail"><i class="ph ph-eye"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Compile Executive Board Pack -->
<div class="modal fade" id="compileReportModal" tabindex="-1" aria-labelledby="compileReportModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="compileReportModalLabel"><i class="ph ph-plus-circle me-2 text-primary"></i>Compile Executive Board Pack</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form onsubmit="event.preventDefault(); alert('Executive Board Pack compiled successfully!'); bootstrap.Modal.getInstance(document.getElementById('compileReportModal')).hide();">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label small fw-semibold">Report Package Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm" placeholder="e.g. Q3 2026 Executive Financial Performance Pack" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold">Reporting Period <span class="text-danger">*</span></label>
              <select class="form-select form-select-sm" required>
                <option value="q3">Q3 2026 (Jul - Sep)</option>
                <option value="q2">Q2 2026 (Apr - Jun)</option>
                <option value="annual">Full Year FY 2026</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Author / Executive Officer</label>
              <input type="text" class="form-control form-control-sm" value="Office of the Chief Financial Officer">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Target Presentation Date</label>
              <input type="date" class="form-control form-control-sm" value="2026-09-15">
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">CFO Executive Summary &amp; Management Notes <span class="text-danger">*</span></label>
              <textarea class="form-control form-control-sm" rows="3" placeholder="Add executive commentary regarding hospital profitability, cost controls, inpatient bed yields, and capital equipment ROIs..." required></textarea>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Include Statements in Package</label>
              <div class="d-flex gap-3 fs-xs mt-1">
                <div class="form-check"><input class="form-check-input" type="checkbox" checked id="incBs"><label class="form-check-label" for="incBs">Balance Sheet</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" checked id="incPl"><label class="form-check-label" for="incPl">Income Statement (P&amp;L)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" checked id="incCf"><label class="form-check-label" for="incCf">Cash Flow Statement</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" checked id="incKpi"><label class="form-check-label" for="incKpi">Healthcare KPI Analytics</label></div>
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary"><i class="ph ph-file-pdf me-1"></i> Compile &amp; Publish Package</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
