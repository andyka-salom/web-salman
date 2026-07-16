@extends('layouts.app')

@section('title', 'Daily Checkup - ' . $vessel->name)

@push('styles')
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- Font Awesome CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" >
<style>
/* Style minimalis untuk badge */
.badge {
    font-weight: 500;
}
/* Spinner di modal detail */
.spinner-border.text-dark {
    color: #343a40 !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('daily-checkup.index') }}">Daily Checkup</a>
                </li>
                <li class="breadcrumb-item active">{{ $vessel->name }}</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">{{ $vessel->name }}</h2>
                <p class="text-muted mb-0">{{ $vessel->company->name }}</p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <input type="date"
                       id="dateFilter"
                       class="form-control"
                       value="{{ $selectedDate }}"
                       max="{{ date('Y-m-d') }}">
                <button type="button"
                        class="btn btn-outline-dark"
                        data-bs-toggle="modal"
                        data-bs-target="#exportModal">
                    <i class="fas fa-download me-2"></i>Export
                </button>
            </div>
        </div>
    </div>

    {{-- Statistics Cards (Disesuaikan dengan Daily Checkup Monitoring) --}}
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">Total Crew</div>
                    <div class="fs-4 fw-bold">{{ $stats['crew_total'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">Completed</div>
                    <div class="fs-4 fw-bold">{{ $stats['completed'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">Pending</div>
                    <div class="fs-4 fw-bold">{{ $stats['pending'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">Reviewed</div>
                    <div class="fs-4 fw-bold">{{ $stats['reviewed'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">Validated</div>
                    <div class="fs-4 fw-bold">{{ $stats['validated'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">Warnings</div>
                    <div class="fs-4 fw-bold text-danger">{{ $stats['warnings'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Bar (Disesuaikan dengan Daily Checkup Monitoring) --}}
    <div class="card border mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex gap-2">
                    <div class="btn-group" role="group">
                        <a href="{{ route('daily-checkup.vessel.show', $vessel) }}?date={{ $selectedDate }}&filter=all"
                           class="btn btn-sm {{ $filter == 'all' ? 'btn-dark' : 'btn-outline-dark' }}">
                            All ({{ $healthChecks->count() }})
                        </a>
                        <a href="{{ route('daily-checkup.vessel.show', $vessel) }}?date={{ $selectedDate }}&filter=warnings"
                           class="btn btn-sm {{ $filter == 'warnings' ? 'btn-danger' : 'btn-outline-danger' }}">
                            Warnings ({{ $stats['warnings'] ?? 0 }})
                        </a>
                    </div>
                </div>

                @if(auth()->user()->hasRole('koordinator'))
                    @if(($stats['pending'] ?? 0) > 0)
                    <button type="button"
                            class="btn btn-dark"
                            onclick="verifyAll()">
                        <i class="fas fa-check-circle me-2"></i>Verify All Pending ({{ $stats['pending'] }})
                    </button>
                    @endif
                @endif

                @if(auth()->user()->hasRole('ners') || auth()->user()->hasRole('super-admin'))
                    @if($healthChecks->isNotEmpty())
                    <button type="button"
                            class="btn btn-dark"
                            onclick="validateAll()">
                        <i class="fas fa-check-double me-2"></i>Validate All ({{ $healthChecks->count() }})
                    </button>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card border">
        <div class="card-body">
            @if($healthChecks->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list text-muted" style="font-size: 4rem;"></i>
                    <p class="text-muted mt-3 mb-0">
                        @if($filter == 'warnings')
                            Tidak ada data dengan warning
                        @else
                            Belum ada pemeriksaan pada tanggal ini
                        @endif
                    </p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Crew</th>
                                <th class="text-center">Suhu (°C)</th>
                                <th class="text-center">Nadi (bpm)</th>
                                <th class="text-center">Tensi (mmHg)</th>
                                <th class="text-center">SpO2 (%)</th>
                                <th class="text-center">Tests</th>
                                <th class="text-center">Fatigue</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($healthChecks as $index => $check)
                            @php
                                // START: Logic Consistency Fix
                                // We use $check->warnings for specific flags (e.g., vital signs)
                                $warnings = $check->warnings ?? [];

                                // We determine $hasIssues by checking all potential sources of warning
                                $isFatigueWarning = $check->fatigue_level === 'sedang' || $check->fatigue_level === 'berat';
                                $isNapzaWarning = $check->napza_test_result !== 'not_tested' && $check->napza_test_result !== 'negative';
                                $hasIssues = !empty($warnings) || $isFatigueWarning || $isNapzaWarning;
                                // END: Logic Consistency Fix
                            @endphp
                            <tr class="{{ $hasIssues ? 'table-light' : '' }}">
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $check->crewMember->name }}</div>
                                    <div class="small text-muted">{{ $check->crewMember->position }}</div>
                                    <div class="small text-secondary">NIK: {{ $check->crewMember->nik }}</div>
                                </td>

                                {{-- Suhu --}}
                                <td class="text-center small">
                                    @if(isset($warnings['temperature']))
                                        <i class="fas fa-exclamation-triangle text-danger me-1" title="Suhu abnormal"></i>
                                        <span class="fw-bold text-danger">{{ $check->temperature ?? '-' }}</span>
                                    @else
                                        {{ $check->temperature ?? '-' }}
                                    @endif
                                </td>
                                {{-- Nadi --}}
                                <td class="text-center small">
                                    @if(isset($warnings['pulse_rate']))
                                        <i class="fas fa-exclamation-triangle text-danger me-1" title="Nadi abnormal"></i>
                                        <span class="fw-bold text-danger">{{ $check->pulse_rate ?? '-' }}</span>
                                    @else
                                        {{ $check->pulse_rate ?? '-' }}
                                    @endif
                                </td>
                                {{-- Tensi --}}
                                <td class="text-center small">
                                    @if(isset($warnings['blood_pressure']))
                                        <i class="fas fa-exclamation-triangle text-danger me-1" title="Tekanan darah abnormal"></i>
                                        <span class="fw-bold text-danger">{{ $check->blood_pressure ?? '-' }}</span>
                                    @else
                                        {{ $check->blood_pressure ?? '-' }}
                                    @endif
                                </td>
                                {{-- SpO2 --}}
                                <td class="text-center small">
                                    @if(isset($warnings['oxygen_saturation']))
                                        <i class="fas fa-exclamation-triangle text-danger me-1" title="Saturasi oksigen abnormal"></i>
                                        <span class="fw-bold text-danger">{{ $check->oxygen_saturation ?? '-' }}</span>
                                    @else
                                        {{ $check->oxygen_saturation ?? '-' }}
                                    @endif
                                </td>

                                <td class="text-center">
                                    <div class="d-flex flex-column gap-1">
                                        {{-- NAPZA --}}
                                        @if($check->napza_test_result !== 'not_tested')
                                        <span class="badge {{ $check->napza_test_result === 'negative' ? 'bg-light text-dark' : 'bg-dark' }}">
                                            NAPZA: {{ $check->napza_test_result === 'negative' ? 'Negatif' : 'Non-Negatif' }}
                                        </span>
                                        @endif
                                        {{-- Romberg --}}
                                        @if($check->romberg_test_result !== 'not_tested')
                                        <span class="badge {{ $check->romberg_test_result === 'negative' ? 'bg-light text-dark' : 'bg-dark' }}">
                                            Romberg: {{ $check->romberg_test_result === 'negative' ? 'Negatif' : 'Positif' }}
                                        </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    {{-- Fatigue --}}
                                    @if($check->fatigue_level)
                                        <span class="badge {{ $check->fatigue_level === 'ringan' ? 'bg-secondary' : ($check->fatigue_level === 'sedang' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                            {{ ucfirst($check->fatigue_level) }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{-- Status --}}
                                    @if($check->status === 'pending')
                                        <span class="badge bg-secondary">Pending</span>
                                    @elseif($check->status === 'reviewed')
                                        <span class="badge bg-secondary">Reviewed</span>
                                    @elseif($check->status === 'validated')
                                        <span class="badge bg-dark">Validated</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{-- Tombol Detail Teks --}}
                                    <button type="button"
                                            class="btn btn-sm btn-outline-dark"
                                            onclick="viewDetail('{{ $check->id }}')">
                                        Detail
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Detail Modal --}}
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Detail Pemeriksaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-dark" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Export Modal --}}
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Export Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="exportForm" method="POST">
                @csrf
                <input type="hidden" name="vessel_id" value="{{ $vessel->id }}">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $selectedDate }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $selectedDate }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Format</label>
                        <select name="format" class="form-select" id="exportFormat" required>
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-dark">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Verify Modal --}}
@if(auth()->user()->hasRole('koordinator'))
<div class="modal fade" id="verifyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Verify Checkups</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('daily-checkup.verify') }}">
                @csrf
                <input type="hidden" name="vessel_id" value="{{ $vessel->id }}">
                <input type="hidden" name="check_date" value="{{ $selectedDate }}">

                <div class="modal-body">
                    <div class="alert alert-light border mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Anda akan memverifikasi <strong>{{ $stats['pending'] ?? 0 }} pemeriksaan</strong>
                        yang berstatus pending pada tanggal
                        <strong>{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}</strong>.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan Verifikasi (Opsional)</label>
                        <textarea name="verification_notes"
                                  class="form-control"
                                  rows="4"
                                  placeholder="Tambahkan catatan verifikasi jika diperlukan..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-dark">
                        <i class="fas fa-check-circle me-2"></i>Verify
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Validate Modal --}}
@if(auth()->user()->hasRole('ners') || auth()->user()->hasRole('super-admin'))
<div class="modal fade" id="validateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Validate Checkups</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('daily-checkup.validate') }}">
                @csrf
                <input type="hidden" name="vessel_id" value="{{ $vessel->id }}">
                <input type="hidden" name="check_date" value="{{ $selectedDate }}">

                <div class="modal-body">
                    <div class="alert alert-light border mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Anda akan memvalidasi <strong>{{ $healthChecks->count() }} pemeriksaan</strong>
                        pada tanggal <strong>{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}</strong>.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan Validasi (Opsional)</label>
                        <textarea name="validation_notes"
                                  class="form-control"
                                  rows="4"
                                  placeholder="Tambahkan catatan validasi jika diperlukan..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-dark">
                        <i class="fas fa-check-double me-2"></i>Validate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Health thresholds from backend (Needed for Detail Modal logic)
const HEALTH_THRESHOLDS = {
    temperature: { max: 37.5 },
    pulse_rate: { min: 60, max: 120 },
    respiratory_rate: { min: 16, max: 24 },
    blood_pressure_systolic: { min: 90, max: 140 },
    blood_pressure_diastolic: { min: 60, max: 90 },
    blood_sugar: { min: 70, max: 200 },
    oxygen_saturation: { min: 95 }
};

// Date filter
document.getElementById('dateFilter').addEventListener('change', function() {
    window.location.href = `{{ route('daily-checkup.vessel.show', $vessel) }}?date=${this.value}`;
});

// Helper function to check abnormality (Client-side implementation)
function checkAbnormalVital(value, type, data) {
    if (!value) return false;

    switch(type) {
        case 'temperature':
            return value > HEALTH_THRESHOLDS.temperature.max;
        case 'pulse_rate':
            return value < HEALTH_THRESHOLDS.pulse_rate.min || value > HEALTH_THRESHOLDS.pulse_rate.max;
        case 'blood_pressure':
            if (!value.includes('/')) return false;

            const [systolic, diastolic] = value.split('/').map(Number);
            return systolic < HEALTH_THRESHOLDS.blood_pressure_systolic.min ||
                   systolic > HEALTH_THRESHOLDS.blood_pressure_systolic.max ||
                   diastolic < HEALTH_THRESHOLDS.blood_pressure_diastolic.min ||
                   diastolic > HEALTH_THRESHOLDS.blood_pressure_diastolic.max;
        case 'respiratory_rate':
            return value < HEALTH_THRESHOLDS.respiratory_rate.min || value > HEALTH_THRESHOLDS.respiratory_rate.max;
        case 'blood_sugar_level': // Using the key used in the data object
            return value < HEALTH_THRESHOLDS.blood_sugar.min || value > HEALTH_THRESHOLDS.blood_sugar.max;
        case 'oxygen_saturation':
            return value < HEALTH_THRESHOLDS.oxygen_saturation.min;
        default:
            return false;
    }
}


// View detail
function viewDetail(checkupId) {
    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
    modal.show();

    // Reset content to loading spinner
    document.getElementById('detailContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-dark" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;

    fetch(`/daily-checkup/${checkupId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('detailContent').innerHTML = generateDetailHTML(data);
        })
        .catch(error => {
            document.getElementById('detailContent').innerHTML = `
                <div class="alert alert-light border">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Gagal memuat data. Silakan coba lagi.
                </div>
            `;
        });
}

function generateDetailHTML(data) {

    // Client-side warning determination for vital signs in the modal
    let warnings = {};
    if (checkAbnormalVital(data.temperature, 'temperature', data)) warnings.temperature = true;
    if (checkAbnormalVital(data.pulse_rate, 'pulse_rate', data)) warnings.pulse_rate = true;
    if (checkAbnormalVital(data.blood_pressure, 'blood_pressure', data)) warnings.blood_pressure = true;
    if (checkAbnormalVital(data.oxygen_saturation, 'oxygen_saturation', data)) warnings.oxygen_saturation = true;
    if (checkAbnormalVital(data.respiratory_rate, 'respiratory_rate', data)) warnings.respiratory_rate = true;
    if (checkAbnormalVital(data.blood_sugar_level, 'blood_sugar_level', data)) warnings.blood_sugar_level = true;

    // Check if the primary backend flag or our determined vital warnings exist
    const hasWarning = data.has_health_issue || Object.keys(warnings).length > 0;

    // Helper function to show icon if warning exists
    const warningIcon = (key) => warnings[key] ? '<i class="fas fa-exclamation-triangle text-danger me-2"></i>' : '';
    const warningClass = (key) => warnings[key] ? 'text-danger' : '';

    // Helper function untuk status badge netral (sesuai Daily Checkup Monitoring)
    const getStatusBadge = (status) => {
        if (status === 'pending') return '<span class="badge bg-secondary">Pending</span>';
        if (status === 'reviewed') return '<span class="badge bg-secondary">Reviewed</span>';
        if (status === 'validated') return '<span class="badge bg-dark">Validated</span>';
        return '-';
    };

    // Helper untuk badge tes (sesuai Daily Checkup Monitoring)
    const getTestBadge = (result) => {
        if (result === 'negative') return 'bg-light text-dark';
        if (result === 'positive' || result === 'non_negative') return 'bg-dark';
        return 'bg-secondary';
    };

    // Helper untuk badge fatigue (sesuai gaya minimalis)
    const getFatigueBadge = (level) => {
        if (level === 'ringan') return 'bg-secondary';
        if (level === 'sedang') return 'bg-warning text-dark';
        if (level === 'berat') return 'bg-danger';
        return 'bg-secondary';
    };


    return `
        <div class="row g-3">
            <div class="col-12">
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="mb-1">${data.crew_member.name}</h5>
                            <p class="mb-0 text-muted">${data.crew_member.position}</p>
                            <p class="mb-0 text-muted small">Vessel: ${data.crew_member.vessel.name}</p>
                        </div>
                        ${hasWarning ? '<span class="badge bg-dark mt-2"><i class="fas fa-exclamation-triangle me-1"></i>Health Issue Detected</span>' : '<span class="badge bg-light text-dark mt-2">Normal</span>'}
                    </div>
                </div>
            </div>

            <div class="col-12">
                <h6 class="border-bottom pb-2 mb-3">Vital Signs</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="small text-muted">Suhu Tubuh</label>
                        <div class="fw-bold ${warningClass('temperature')}">
                            ${warningIcon('temperature')} ${data.temperature || '-'} °C
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">Frekuensi Nadi</label>
                        <div class="fw-bold ${warningClass('pulse_rate')}">
                            ${warningIcon('pulse_rate')} ${data.pulse_rate || '-'} bpm
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">Tekanan Darah</label>
                        <div class="fw-bold ${warningClass('blood_pressure')}">
                            ${warningIcon('blood_pressure')} ${data.blood_pressure || '-'} mmHg
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">Frekuensi Nafas</label>
                        <div class="fw-bold ${warningClass('respiratory_rate')}">
                            ${warningIcon('respiratory_rate')} ${data.respiratory_rate || '-'} x/min
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">Gula Darah</label>
                        <div class="fw-bold ${warningClass('blood_sugar_level')}">
                            ${warningIcon('blood_sugar_level')} ${data.blood_sugar_level || '-'} mg/dL
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">Saturasi Oksigen</label>
                        <div class="fw-bold ${warningClass('oxygen_saturation')}">
                            ${warningIcon('oxygen_saturation')} ${data.oxygen_saturation || '-'} %
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <h6 class="border-bottom pb-2 mb-3">Health Status</h6>
                <div class="mb-3">
                    <label class="small text-muted">Keluhan Kesehatan</label>
                    <div class="border rounded p-2">${data.illness_complaints || '-'}</div>
                </div>
                <div class="mb-3">
                    <label class="small text-muted">Obat-obatan</label>
                    <div class="border rounded p-2">${data.medications_consumed || '-'}</div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="small text-muted">Tingkat Kelelahan</label>
                        <div class="fw-bold">
                            <span class="badge ${getFatigueBadge(data.fatigue_level)}">
                                ${data.fatigue_level ? data.fatigue_level.charAt(0).toUpperCase() + data.fatigue_level.slice(1) : '-'}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">Area Kerja</label>
                        <div class="fw-bold">${data.work_area || '-'}</div>
                    </div>
                </div>
            </div>

            ${data.napza_test_result !== 'not_tested' || data.romberg_test_result !== 'not_tested' ? `
            <div class="col-12">
                <h6 class="border-bottom pb-2 mb-3">Medical Tests</h6>
                <div class="row g-3">
                    ${data.napza_test_result !== 'not_tested' ? `
                    <div class="col-md-6">
                        <label class="small text-muted">NAPZA Test</label>
                        <div>
                            <span class="badge ${getTestBadge(data.napza_test_result === 'non_negative' ? 'positive' : data.napza_test_result)}">
                                NAPZA: ${data.napza_test_result === 'negative' ? 'Negatif' : 'Non-Negatif'}
                            </span>
                        </div>
                    </div>
                    ` : ''}
                    ${data.romberg_test_result !== 'not_tested' ? `
                    <div class="col-md-6">
                        <label class="small text-muted">Romberg Test</label>
                        <div>
                            <span class="badge ${getTestBadge(data.romberg_test_result === 'positive' ? 'positive' : data.romberg_test_result)}">
                                Romberg: ${data.romberg_test_result === 'negative' ? 'Negatif' : 'Positif'}
                            </span>
                        </div>
                    </div>
                    ` : ''}
                </div>
            </div>
            ` : ''}

            ${data.remarks ? `
            <div class="col-12">
                <h6 class="border-bottom pb-2 mb-3">Remarks</h6>
                <div class="border rounded p-3 bg-light">${data.remarks}</div>
            </div>
            ` : ''}

            <div class="col-12">
                <h6 class="border-bottom pb-2 mb-3">Status & Timeline</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="small text-muted">Status</label>
                        <div>${getStatusBadge(data.status)}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted">Reported By</label>
                        <div class="fw-bold">${data.reporter ? data.reporter.name : '-'}</div>
                        <div class="small text-muted">${new Date(data.checked_at).toLocaleString('id-ID')}</div>
                    </div>
                    ${data.verifier ? `
                    <div class="col-md-4">
                        <label class="small text-muted">Verified By</label>
                        <div class="fw-bold">${data.verifier.name}</div>
                    </div>
                    ` : ''}
                    ${data.validator ? `
                    <div class="col-md-4">
                        <label class="small text-muted">Validated By</label>
                        <div class="fw-bold">${data.validator.name}</div>
                    </div>
                    ` : ''}
                </div>
            </div>
        </div>
    `;
}

// Verify all
function verifyAll() {
    const modal = new bootstrap.Modal(document.getElementById('verifyModal'));
    modal.show();
}

// Validate all
function validateAll() {
    const modal = new bootstrap.Modal(document.getElementById('validateModal'));
    modal.show();
}

// Export
document.getElementById('exportForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const format = document.getElementById('exportFormat').value;
    const action = format === 'excel'
        ? '{{ route("daily-checkup.export.excel") }}'
        : '{{ route("daily-checkup.export.pdf") }}';

    this.action = action;
    this.submit();
});
</script>
@endpush
