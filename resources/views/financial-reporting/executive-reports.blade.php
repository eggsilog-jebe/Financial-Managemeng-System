@extends('layouts.app')

@section('title', 'Executive Reports - Financial Reporting | FMS')
@section('module', 'reporting')
@section('page', 'executive-reports')

@section('content')
<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 fs-xs">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Overview</a></li>
          <li class="breadcrumb-item">Financial Reporting</li>
          <li class="breadcrumb-item active">Executive Summaries</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0 font-weight-bold">Executive Financial Summaries</h1>
      <p class="text-muted small mb-0">Compiled quarterly and annual executive packs for the Hospital Board of Directors.</p>
    </div>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Report Title</th><th>Reporting Period</th><th>Author / CFO</th><th>Generated Date</th><th>Action</th></tr>
        </thead>
        <tbody>
          <tr>
            <td class="fw-semibold">Q2 2026 Executive Financial Performance Pack</td>
            <td>Apr 01 - Jun 30, 2026</td>
            <td>Office of the Chief Financial Officer</td>
            <td>2026-07-10</td>
            <td><button class="btn btn-outline-primary btn-sm"><i class="ph ph-download-simple me-1"></i> Download Brief</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
