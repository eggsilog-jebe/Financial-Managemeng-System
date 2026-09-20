@extends('layouts.app')

@section('title', 'Malasakit Center & Government Assistance')
@section('module', 'accounts-receivable')
@section('page', 'malasakit')

@section('content')
<div class="container-fluid p-4">
  <!-- Header -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
      <h1 class="h3 mb-1 font-weight-bold">
        <i class="ph-fill ph-heart text-danger me-2 align-middle"></i>Malasakit Center Financial Assistance &amp; Subsidies
      </h1>
      <p class="text-muted mb-0">
        RA 11463 (Malasakit Centers Act) &bull; RA 11223 (UHC Act) &bull; PCSO, DSWD (AICS), DOH-MAIP Guarantee Letters
      </p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#waterfallCalcModal">
        <i class="ph-bold ph-calculator me-1"></i> Simulate Bill Waterfall
      </button>
      <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addGlModal">
        <i class="ph-bold ph-plus me-1"></i> Register Guarantee Letter (GL)
      </button>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
      <i class="ph-bold ph-check-circle fs-5 me-2 align-middle"></i>
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
      <i class="ph-bold ph-warning-octagon fs-5 me-2 align-middle"></i>
      <strong>Action Failed:</strong> {{ $errors->first() }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- KPI Overview Cards -->
  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
        <div class="d-flex align-items-center">
          <div class="bg-primary-subtle text-primary p-3 rounded-3 me-3">
            <i class="ph-bold ph-file-text fs-4"></i>
          </div>
          <div>
            <div class="text-muted small fw-medium">Total Subsidies Authorized</div>
            <div class="fs-4 fw-bold text-dark">₱ {{ number_format((float) $totalAuthorized, 2) }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
        <div class="d-flex align-items-center">
          <div class="bg-success-subtle text-success p-3 rounded-3 me-3">
            <i class="ph-bold ph-check-circle fs-4"></i>
          </div>
          <div>
            <div class="text-muted small fw-medium">Total Utilized by Patients</div>
            <div class="fs-4 fw-bold text-success">₱ {{ number_format((float) $totalUtilized, 2) }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
        <div class="d-flex align-items-center">
          <div class="bg-danger-subtle text-danger p-3 rounded-3 me-3">
            <i class="ph-bold ph-coins fs-4"></i>
          </div>
          <div>
            <div class="text-muted small fw-medium">Remaining Active GL Funds</div>
            <div class="fs-4 fw-bold text-danger">₱ {{ number_format((float) $totalRemaining, 2) }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 bg-white p-3 h-100">
        <div class="d-flex align-items-center">
          <div class="bg-warning-subtle text-warning-emphasis p-3 rounded-3 me-3">
            <i class="ph-bold ph-users-three fs-4"></i>
          </div>
          <div>
            <div class="text-muted small fw-medium">Active Guarantee Letters</div>
            <div class="fs-4 fw-bold text-dark">{{ number_format($activeGlCount) }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Toolbar -->
  <div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('ar.malasakit.index') }}" class="row g-2 align-items-center">
        <div class="col-md-4">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-light border-end-0"><i class="ph ph-magnifying-glass"></i></span>
            <input type="text" name="search" class="form-control border-start-0" placeholder="Search GL number, patient name, MRN..." value="{{ $search }}">
          </div>
        </div>

        <div class="col-md-3">
          <select name="agency" class="form-select form-select-sm">
            <option value="">All Government Agencies</option>
            <option value="PCSO" @selected($agency === 'PCSO')>PCSO (Charity Fund)</option>
            <option value="DSWD" @selected($agency === 'DSWD')>DSWD (AICS Program)</option>
            <option value="DOH_MAIP" @selected($agency === 'DOH_MAIP')>DOH-MAIP (Indigent Grant)</option>
            <option value="LGU" @selected($agency === 'LGU')>LGU / Mayor / Governor</option>
            <option value="OTHER" @selected($agency === 'OTHER')>Office of the President / Other</option>
          </select>
        </div>

        <div class="col-md-3">
          <select name="status" class="form-select form-select-sm">
            <option value="">All Statuses</option>
            <option value="ACTIVE" @selected($status === 'ACTIVE')>ACTIVE (Funds Available)</option>
            <option value="APPLIED" @selected($status === 'APPLIED')>APPLIED</option>
            <option value="DEPLETED" @selected($status === 'DEPLETED')>DEPLETED</option>
            <option value="EXPIRED" @selected($status === 'EXPIRED')>EXPIRED</option>
          </select>
        </div>

        <div class="col-md-2 d-flex gap-1">
          <button type="submit" class="btn btn-primary btn-sm w-100">
            <i class="ph ph-funnel me-1"></i> Filter
          </button>
          <a href="{{ route('ar.malasakit.index') }}" class="btn btn-light btn-sm border" title="Reset">
            <i class="ph ph-arrow-counter-clockwise"></i>
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Guarantee Letters Registry Table -->
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 fw-bold text-dark">
        <i class="ph ph-receipt text-muted me-1"></i> Registered Guarantee Letters (GL)
        <span class="badge bg-light text-dark ms-2 font-monospace">{{ $letters->total() }} records</span>
      </h6>
      <span class="text-muted small">
        <i class="ph ph-shield-check text-success me-1"></i> Multi-Agency Malasakit Desk
      </span>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
        <thead class="table-light">
          <tr>
            <th scope="col" style="width: 170px;">GL Reference No.</th>
            <th scope="col" style="width: 140px;">Issuing Agency</th>
            <th scope="col">Patient Name &amp; MRN</th>
            <th scope="col">Diagnosis / Case</th>
            <th scope="col" class="text-end" style="width: 130px;">Authorized</th>
            <th scope="col" class="text-end" style="width: 130px;">Utilized</th>
            <th scope="col" class="text-end" style="width: 130px;">Remaining</th>
            <th scope="col" style="width: 110px;">Status</th>
            <th scope="col" style="width: 110px;">Issued Date</th>
          </tr>
        </thead>
        <tbody>
          @forelse($letters as $letter)
            <tr>
              <td>
                <span class="font-monospace fw-bold text-primary">{{ $letter->gl_number }}</span>
              </td>
              <td>
                @php
                  $agencyBadge = match($letter->issuing_agency) {
                    'PCSO' => 'bg-primary-subtle text-primary border-primary-subtle',
                    'DSWD' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                    'DOH_MAIP' => 'bg-success-subtle text-success border-success-subtle',
                    'LGU' => 'bg-info-subtle text-info border-info-subtle',
                    default => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                  };
                @endphp
                <span class="badge {{ $agencyBadge }} border py-1 px-2" style="font-size: 11px;">
                  {{ $letter->issuing_agency }}
                </span>
              </td>
              <td>
                <div class="fw-semibold text-dark">{{ $letter->patientAccount?->full_name ?? '—' }}</div>
                <div class="text-muted small font-monospace">MRN: {{ $letter->patientAccount?->patient_id_number ?? 'N/A' }}</div>
              </td>
              <td>
                <span class="text-secondary text-truncate d-inline-block" style="max-width: 220px;" title="{{ $letter->diagnosis }}">
                  {{ $letter->diagnosis ?: 'Clinical Admission' }}
                </span>
              </td>
              <td class="text-end font-monospace fw-semibold text-dark">
                ₱ {{ number_format((float) $letter->authorized_amount, 2) }}
              </td>
              <td class="text-end font-monospace text-muted">
                ₱ {{ number_format((float) $letter->utilized_amount, 2) }}
              </td>
              <td class="text-end font-monospace fw-bold text-success">
                ₱ {{ number_format((float) $letter->remaining_amount, 2) }}
              </td>
              <td>
                @php
                  $statusBadge = match($letter->status) {
                    'ACTIVE' => 'bg-success',
                    'APPLIED' => 'bg-info text-dark',
                    'DEPLETED' => 'bg-secondary',
                    'EXPIRED' => 'bg-danger',
                    default => 'bg-light text-dark'
                  };
                @endphp
                <span class="badge {{ $statusBadge }} py-1 px-2">
                  {{ $letter->status }}
                </span>
              </td>
              <td>
                <div class="text-muted small">{{ $letter->issued_date->format('M d, Y') }}</div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center py-5 text-muted">
                <i class="ph ph-heart-break fs-1 d-block mb-2 text-danger opacity-50"></i>
                <p class="mb-0 fw-medium">No Guarantee Letters registered yet.</p>
                <button type="button" class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addGlModal">
                  <i class="ph-bold ph-plus me-1"></i> Register the First Guarantee Letter
                </button>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($letters->hasPages())
      <div class="card-footer bg-white py-3 border-0">
        {{ $letters->links() }}
      </div>
    @endif
  </div>
</div>

<!-- Modal 1: Register Guarantee Letter -->
<div class="modal fade" id="addGlModal" tabindex="-1" aria-labelledby="addGlModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form method="POST" action="{{ route('ar.malasakit.store') }}">
        @csrf
        <div class="modal-header bg-light">
          <h5 class="modal-title fw-bold" id="addGlModalLabel">
            <i class="ph-bold ph-file-plus text-primary me-1"></i> Register New Guarantee Letter (GL)
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-dark">Patient Account <span class="text-danger">*</span></label>
              <select name="patient_account_id" class="form-select" required>
                <option value="">-- Select Patient --</option>
                @foreach($patients as $pt)
                  <option value="{{ $pt->id }}">
                    {{ $pt->full_name }} (MRN: {{ $pt->patient_id_number }})
                    @if($pt->is_nbb) [NBB] @endif
                    @if($pt->discount_category) [{{ $pt->discount_category }}] @endif
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold text-dark">Issuing Government Agency <span class="text-danger">*</span></label>
              <select name="issuing_agency" class="form-select" required>
                <option value="PCSO">PCSO — Philippine Charity Sweepstakes Office</option>
                <option value="DSWD">DSWD — AICS Assistance to Individuals in Crisis</option>
                <option value="DOH_MAIP" selected>DOH-MAIP — Medical Assistance for Indigent Patients</option>
                <option value="LGU">LGU — City / Provincial Government</option>
                <option value="OTHER">Office of the President / Congressional GL</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold text-dark">GL Reference Number <span class="text-danger">*</span></label>
              <input type="text" name="gl_number" class="form-control font-monospace" placeholder="e.g. PCSO-2026-00412" required>
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold text-dark">Authorized Amount (₱) <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">₱</span>
                <input type="number" step="0.01" min="1" name="authorized_amount" class="form-control font-monospace" placeholder="25000.00" required>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold text-dark">Date Issued <span class="text-danger">*</span></label>
              <input type="date" name="issued_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold text-dark">Valid Until</label>
              <input type="date" name="valid_until" class="form-control" value="{{ date('Y-m-d', strtotime('+60 days')) }}">
            </div>

            <div class="col-12">
              <label class="form-label small fw-semibold text-dark">Clinical Diagnosis / Indication</label>
              <input type="text" name="diagnosis" class="form-control" placeholder="e.g. Community Acquired Pneumonia, Inpatient Medical Ward">
            </div>

            <div class="col-12">
              <label class="form-label small fw-semibold text-dark">Remarks / Special Notes</label>
              <textarea name="remarks" rows="2" class="form-control" placeholder="Specific laboratory, pharmacy, or operative procedure covered..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light py-2">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="ph-bold ph-check me-1"></i> Save &amp; Activate GL
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal 2: Interactive Malasakit Bill Waterfall Calculator -->
<div class="modal fade" id="waterfallCalcModal" tabindex="-1" aria-labelledby="calcModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold" id="calcModalLabel">
          <i class="ph-bold ph-calculator me-1"></i> Philippine Public Hospital Bill Waterfall Simulation
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <p class="text-muted small mb-3">
          Simulates the mandatory legal order of hospital deductions: <strong>Statutory Discounts (RA 9994/10754) &rarr; PhilHealth Case Rates (RA 11223) &rarr; NBB Policy &rarr; Malasakit Guarantee Letters</strong>.
        </p>

        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label small fw-bold">Select Patient Profile</label>
            <select id="simPatient" class="form-select form-select-sm">
              @foreach($patients as $pt)
                <option value="{{ $pt->id }}">
                  {{ $pt->full_name }} (MRN: {{ $pt->patient_id_number }})
                  @if($pt->is_nbb) [NBB Covered] @endif
                  @if($pt->discount_category) [{{ $pt->discount_category }}] @endif
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label small fw-bold">Gross Hospital Bill (₱)</label>
            <input type="number" step="100" id="simGross" class="form-control form-control-sm font-monospace" value="125000">
          </div>
          <div class="col-md-3">
            <label class="form-label small fw-bold">PhilHealth Case Rate (₱)</label>
            <input type="number" step="100" id="simPhilhealth" class="form-control form-control-sm font-monospace" value="38000">
          </div>
        </div>

        <button type="button" id="btnRunSim" class="btn btn-primary btn-sm w-100 mb-3">
          <i class="ph-bold ph-play me-1"></i> Calculate Legal Deduction Waterfall
        </button>

        <!-- Simulation Output Table -->
        <div id="simResult" class="p-3 bg-light rounded-3 border" style="display: none;">
          <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">
            <i class="ph-bold ph-receipt me-1 text-primary"></i> Patient Statement Deduction Breakdown
          </h6>
          <div class="d-flex justify-content-between py-1 border-bottom">
            <span>Gross Hospital Incurred Charges:</span>
            <strong class="font-monospace" id="outGross">₱ 0.00</strong>
          </div>
          <div class="d-flex justify-content-between py-1 border-bottom text-danger">
            <span id="outDiscountLabel">Less: Statutory Senior/PWD Discount:</span>
            <strong class="font-monospace" id="outDiscount">− ₱ 0.00</strong>
          </div>
          <div class="d-flex justify-content-between py-1 border-bottom text-primary">
            <span>Less: PhilHealth Case Rate (RA 11223):</span>
            <strong class="font-monospace" id="outPhilhealth">− ₱ 0.00</strong>
          </div>
          <div class="d-flex justify-content-between py-1 border-bottom text-success" id="rowNbb" style="display: none;">
            <span>Less: No Balance Billing (NBB Policy Subsidy):</span>
            <strong class="font-monospace" id="outNbb">− ₱ 0.00</strong>
          </div>
          <div class="d-flex justify-content-between py-1 border-bottom text-info">
            <span>Less: Malasakit Guarantee Letters (PCSO/DSWD/MAIP):</span>
            <strong class="font-monospace" id="outGls">− ₱ 0.00</strong>
          </div>
          <div class="d-flex justify-content-between pt-3 fs-5 fw-bold text-dark">
            <span>Final Patient Out-of-Pocket Balance:</span>
            <span class="font-monospace text-danger" id="outNet">₱ 0.00</span>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light py-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('btnRunSim');
  if (!btn) return;

  btn.addEventListener('click', async () => {
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Calculating...';

    const patientId = document.getElementById('simPatient').value;
    const gross = document.getElementById('simGross').value;
    const philhealth = document.getElementById('simPhilhealth').value;

    try {
      const resp = await fetch('{{ route("ar.malasakit.calculate") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          patient_account_id: patientId,
          gross_amount: gross,
          philhealth_amount: philhealth
        })
      });

      const res = await resp.json();
      const wf = res.waterfall;

      document.getElementById('simResult').style.display = 'block';
      document.getElementById('outGross').innerText = '₱ ' + Number(wf.gross_total).toLocaleString('en-US', {minimumFractionDigits: 2});
      
      const discTotal = Number(wf.vat_exempt_relief) + Number(wf.statutory_discount);
      document.getElementById('outDiscountLabel').innerText = 'Less: ' + (wf.discount_rule_applied !== 'NONE' ? wf.discount_rule_applied : 'Statutory Discount');
      document.getElementById('outDiscount').innerText = '− ₱ ' + discTotal.toLocaleString('en-US', {minimumFractionDigits: 2});
      document.getElementById('outPhilhealth').innerText = '− ₱ ' + Number(wf.philhealth_deduction).toLocaleString('en-US', {minimumFractionDigits: 2});

      // NBB row
      const rowNbb = document.getElementById('rowNbb');
      if (wf.is_nbb_covered && Number(wf.nbb_subsidy_amount) > 0) {
        rowNbb.style.display = 'flex';
        document.getElementById('outNbb').innerText = '− ₱ ' + Number(wf.nbb_subsidy_amount).toLocaleString('en-US', {minimumFractionDigits: 2});
      } else {
        rowNbb.style.display = 'none';
      }

      // GLs
      const glTotal = (wf.guarantee_letters_applied || []).reduce((acc, g) => acc + Number(g.amount), 0);
      document.getElementById('outGls').innerText = '− ₱ ' + glTotal.toLocaleString('en-US', {minimumFractionDigits: 2});

      // Net
      const net = Number(wf.net_patient_payable);
      const outNet = document.getElementById('outNet');
      outNet.innerText = '₱ ' + net.toLocaleString('en-US', {minimumFractionDigits: 2});
      if (net === 0) {
        outNet.className = 'font-monospace text-success fw-bold';
        outNet.innerText = '₱ 0.00 (FULLY SUBSIDIZED / ZERO BALANCE)';
      } else {
        outNet.className = 'font-monospace text-danger fw-bold';
      }
    } catch (err) {
      alert('Error computing waterfall: ' + err.message);
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="ph-bold ph-play me-1"></i> Calculate Legal Deduction Waterfall';
    }
  });
});
</script>
@endsection
