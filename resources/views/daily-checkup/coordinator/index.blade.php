@extends('layouts.app')

@section('title', 'Daily Checkup - Coordinator')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.1);
}

.progress {
    background-color: #e9ecef;
}

.badge {
    font-weight: 500;
}

.card.border-2 {
    border-width: 2px !important;
}

.badge-critical {
    background-color: #dc3545 !important;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Daily Checkup Management</h2>
            <p class="text-muted mb-0">Monitor dan verifikasi pemeriksaan kesehatan crew di kapal-kapal Anda</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            @if($allStats['pending'] > 0)
            <button type="button" class="btn btn-dark" onclick="verifyAllVessels()">
                <i class="fas fa-check-double me-2"></i>Verify All Vessels ({{ $allStats['pending'] }})
            </button>
            @endif

            <input type="date" id="dateFilter" class="form-control" value="{{ $selectedDate }}" max="{{ date('Y-m-d') }}" style="width: auto;">
        </div>
    </div>

    {{-- Summary Stats --}}
    @if(isset($allStats))
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border">
                <div class="card-body">
                    <div class="text-muted small mb-1">Total Checkup</div>
                    <div class="fs-4 fw-bold">{{ $allStats['total'] }}</div>
                    <small class="text-muted">Semua Kapal</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border border-warning">
                <div class="card-body">
                    <div class="text-muted small mb-1">Pending Review</div>
                    <div class="fs-4 fw-bold text-warning">{{ $allStats['pending'] }}</div>
                    <small class="text-muted">Perlu Verifikasi</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border border-info">
                <div class="card-body">
                    <div class="text-muted small mb-1">Reviewed</div>
                    <div class="fs-4 fw-bold text-info">{{ $allStats['reviewed'] }}</div>
                    <small class="text-muted">Sudah Diverifikasi</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border border-success">
                <div class="card-body">
                    <div class="text-muted small mb-1">Validated</div>
                    <div class="fs-4 fw-bold text-success">{{ $allStats['validated'] }}</div>
                    <small class="text-muted">Sudah Divalidasi</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border border-warning">
                <div class="card-body">
                    <div class="text-muted small mb-1">Health Issues</div>
                    <div class="fs-4 fw-bold text-warning">{{ $allStats['warnings'] }}</div>
                    <small class="text-muted">Perlu Perhatian</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border border-danger">
                <div class="card-body">
                    <div class="text-muted small mb-1">
                        <i class="fas fa-exclamation-triangle me-1"></i>Critical
                    </div>
                    <div class="fs-4 fw-bold text-danger">{{ $allStats['critical'] ?? 0 }}</div>
                    <small class="text-muted">Kondisi Kritis</small>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Vessels Grid --}}
    <div class="row g-4">
        @forelse($vessels as $vessel)
            @php
                $stats = $vesselStats[$vessel->id] ?? [];
                $completionRate = $stats['completion_rate'] ?? 0;
                $hasWarnings = ($stats['warnings'] ?? 0) > 0;
                $hasCritical = ($stats['critical'] ?? 0) > 0;
                $canVerify = ($stats['pending'] ?? 0) > 0;

                // MCU stats per vessel
                $mcuExpiredCount = \App\Models\CrewMember::whereHas('vesselAssignments', fn($q) =>
                    $q->where('vessel_id', $vessel->id)->where('is_active', true)->whereNull('unassigned_at')
                )->where('is_active', true)->mcuExpired()->count();

                $mcuExpiringSoonCount = \App\Models\CrewMember::whereHas('vesselAssignments', fn($q) =>
                    $q->where('vessel_id', $vessel->id)->where('is_active', true)->whereNull('unassigned_at')
                )->where('is_active', true)->mcuExpiringSoon()->count();

                // Health category counts - unfit (P5) and limited (P3/P4)
                $crewUnfit = \App\Models\CrewMember::whereHas('vesselAssignments', fn($q) =>
                    $q->where('vessel_id', $vessel->id)->where('is_active', true)->whereNull('unassigned_at')
                )->where('is_active', true)->where('health_category', 'P5')->count();

                $crewLimited = \App\Models\CrewMember::whereHas('vesselAssignments', fn($q) =>
                    $q->where('vessel_id', $vessel->id)->where('is_active', true)->whereNull('unassigned_at')
                )->where('is_active', true)->whereIn('health_category', ['P3', 'P4'])->count();
            @endphp

            <div class="col-md-6 col-lg-4">
                <div class="card border h-100 {{ $hasCritical ? 'border-danger border-2' : ($hasWarnings ? 'border-warning border-2' : '') }}">
                    <div class="card-body">
                        {{-- Vessel Header --}}
                        <div class="d-flex align-items-start mb-3">
                            <div class="flex-shrink-0">
                                <div class="rounded-circle bg-light p-3">
                                    <i class="fas fa-ship text-secondary fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">{{ $vessel->name }}</h5>
                                <p class="text-muted small mb-0">{{ $vessel->company->name }}</p>
                            </div>
                            @if($hasCritical)
                                <span class="text-danger fs-4" title="Ada crew dengan kondisi kritis yang memerlukan perhatian segera">
                                    <i class="fas fa-exclamation-circle"></i>
                                </span>
                            @elseif($hasWarnings)
                                <span class="text-warning fs-4" title="Ada crew dengan kondisi kesehatan yang perlu perhatian">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </span>
                            @endif
                        </div>

                        {{-- Progress Bar --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-muted">Completion Progress</span>
                                <span class="small fw-semibold">{{ $completionRate }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-dark"
                                     role="progressbar"
                                     style="width: {{ $completionRate }}%"
                                     aria-valuenow="{{ $completionRate }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                </div>
                            </div>
                            @if($completionRate < 100)
                                <div class="small text-muted mt-1">
                                    {{ $stats['not_checked'] ?? 0 }} crew belum diperiksa
                                </div>
                            @endif
                        </div>

                        {{-- Statistics --}}
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="border rounded p-2 text-center">
                                    <div class="small text-muted">Total Crew</div>
                                    <div class="fs-5 fw-bold">{{ $stats['crew_total'] ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 text-center">
                                    <div class="small text-muted">Completed</div>
                                    <div class="fs-5 fw-bold">{{ $stats['completed'] ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 text-center">
                                    <div class="small text-muted">Pending Review</div>
                                    <div class="fs-5 fw-bold">{{ $stats['pending'] ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 text-center {{ $hasCritical ? 'bg-danger-subtle border-danger' : ($hasWarnings ? 'bg-warning-subtle' : '') }}">
                                    <div class="small text-muted d-flex align-items-center justify-content-center gap-1">
                                        Health Status
                                        @if($hasCritical)
                                            <i class="fas fa-exclamation-circle text-danger small"></i>
                                        @elseif($hasWarnings)
                                            <i class="fas fa-exclamation-triangle text-warning small"></i>
                                        @endif
                                    </div>
                                    <div class="fs-5 fw-bold {{ $hasCritical ? 'text-danger' : ($hasWarnings ? 'text-warning' : '') }}">
                                        {{ $stats['warnings'] ?? 0 }}
                                        @if($hasCritical)
                                            <div class="small text-danger">{{ $stats['critical'] ?? 0 }} Kritis</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Status Summary --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="small text-muted">Pending Review</span>
                                <span class="badge bg-warning text-dark">{{ $stats['pending'] ?? 0 }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="small text-muted">Reviewed</span>
                                <span class="badge bg-info text-dark">{{ $stats['reviewed'] ?? 0 }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="small text-muted">Validated</span>
                                <span class="badge bg-success">{{ $stats['validated'] ?? 0 }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-2">
                                <span class="small text-muted">Not Checked</span>
                                <span class="badge bg-light text-dark">{{ $stats['not_checked'] ?? 0 }}</span>
                            </div>
                        </div>

                        {{-- MCU & Health Category Warning Summary --}}
                        @if($mcuExpiredCount > 0 || $mcuExpiringSoonCount > 0 || $crewUnfit > 0 || $crewLimited > 0)
                        <div class="mb-3 pt-2 border-top">
                            <div class="small fw-semibold text-muted mb-2">
                                <i class="fas fa-id-card me-1"></i>MCU & Health Category
                            </div>
                            <div class="d-flex flex-wrap gap-1">
                                @if($mcuExpiredCount > 0)
                                    <span class="badge bg-danger" title="Crew dengan MCU expired">
                                        <i class="fas fa-times-circle me-1"></i>{{ $mcuExpiredCount }} MCU Expired
                                    </span>
                                @endif
                                @if($mcuExpiringSoonCount > 0)
                                    <span class="badge bg-warning text-dark" title="Crew dengan MCU akan segera expired (≤30 hari)">
                                        <i class="fas fa-exclamation-circle me-1"></i>{{ $mcuExpiringSoonCount }} MCU Expiring
                                    </span>
                                @endif
                                @if($crewUnfit > 0)
                                    <span class="badge bg-danger" title="Crew dengan kategori P5 - Unfit for sea service">
                                        <i class="fas fa-user-slash me-1"></i>{{ $crewUnfit }} P5 Unfit
                                    </span>
                                @endif
                                @if($crewLimited > 0)
                                    <span class="badge bg-warning text-dark" title="Crew dengan kategori P3/P4 - Fit with limitations">
                                        <i class="fas fa-user-clock me-1"></i>{{ $crewLimited }} P3/P4 Limited
                                    </span>
                                @endif
                                @if($mcuExpiredCount === 0 && $mcuExpiringSoonCount === 0 && $crewUnfit === 0 && $crewLimited === 0)
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-check-circle text-success me-1"></i>MCU & Cat. OK
                                    </span>
                                @endif
                            </div>
                        </div>
                        @else
                        <div class="mb-3 pt-2 border-top">
                            <div class="small fw-semibold text-muted mb-2">
                                <i class="fas fa-id-card me-1"></i>MCU & Health Category
                            </div>
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-check-circle text-success me-1"></i>Semua OK
                            </span>
                        </div>
                        @endif

                        {{-- Action Buttons --}}
                        <div class="d-grid gap-2">
                            <a href="{{ route('daily-checkup.coordinator.input', $vessel) }}?date={{ $selectedDate }}"
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-plus-circle me-2"></i>Input Checkup
                            </a>

                            <a href="{{ route('daily-checkup.vessel.show', $vessel) }}?date={{ $selectedDate }}"
                               class="btn btn-outline-dark btn-sm">
                                <i class="fas fa-eye me-2"></i>View Details
                            </a>

                            @if($canVerify)
                                <button type="button"
                                        class="btn btn-dark btn-sm"
                                        onclick="verifyVessel('{{ $vessel->id }}', '{{ $vessel->name }}', {{ $stats['pending'] }})">
                                    <i class="fas fa-check-circle me-2"></i>Verify Crew Input ({{ $stats['pending'] }})
                                </button>
                            @else
                                <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
                                    <i class="fas fa-check-circle me-2"></i>All Verified
                                </button>
                            @endif
                        </div>

                        {{-- Coordinator Info --}}
                        @if($vessel->coordinator)
                        <div class="mt-3 pt-3 border-top">
                            <div class="small text-muted">
                                <i class="fas fa-user me-1"></i>
                                Coordinator: {{ $vessel->coordinator->name }}
                            </div>
                        </div>
                        @endif

                        {{-- Critical / Warning Notice --}}
                        @if($hasCritical)
                        <div class="mt-3 pt-3 border-top border-danger">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fas fa-exclamation-circle text-danger mt-1"></i>
                                <div class="small">
                                    <strong class="text-danger">{{ $stats['critical'] }} crew dalam kondisi kritis!</strong><br>
                                    <span class="text-muted">Memerlukan tindakan segera</span>
                                </div>
                            </div>
                        </div>
                        @elseif($hasWarnings)
                        <div class="mt-3 pt-3 border-top border-warning">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fas fa-exclamation-triangle text-warning mt-1"></i>
                                <div class="small text-muted">
                                    {{ $stats['warnings'] }} crew memiliki kondisi kesehatan yang memerlukan perhatian
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-ship text-muted" style="font-size: 4rem;"></i>
                        <p class="text-muted mt-3 mb-0">Tidak ada vessel yang ditemukan</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.getElementById('dateFilter').addEventListener('change', function() {
    window.location.href = `{{ route('daily-checkup.index') }}?date=${this.value}`;
});

function verifyAllVessels() {
    const pendingCount = {{ $allStats['pending'] }};

    if (pendingCount === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Tidak ada data',
            text: 'Tidak ada checkup pending untuk diverifikasi',
            confirmButtonColor: '#1b2e4b'
        });
        return;
    }

    Swal.fire({
        title: 'Verifikasi Semua Kapal',
        html: `
        <div class="text-start">
            <div class="alert alert-light border mb-3">
                <i class="fas fa-info-circle me-2"></i>
                Anda akan memverifikasi <strong>${pendingCount} pemeriksaan pending</strong>
                dari <strong>semua kapal</strong> pada tanggal
                <strong>{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}</strong>.
            </div>
            <div class="mb-3">
                <label class="form-label small">Catatan Verifikasi (Opsional)</label>
                <textarea id="verification_notes" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
            </div>
        </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1b2e4b',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-check-circle me-2"></i>Verify All',
        cancelButtonText: 'Batal',
        width: '600px',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const notes = document.getElementById('verification_notes').value;
            return fetch('{{ route("daily-checkup.verify") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    vessel_id: 'all',
                    check_date: '{{ $selectedDate }}',
                    verification_notes: notes
                })
            })
            .then(response => {
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error('Server tidak mengembalikan response JSON');
                }
                return response.json().then(data => {
                    if (!response.ok) throw new Error(data.message || 'Terjadi kesalahan pada server');
                    return data;
                });
            })
            .catch(error => {
                Swal.showValidationMessage(`Gagal memverifikasi: ${error.message}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: result.value.message,
                confirmButtonColor: '#1b2e4b',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => location.reload());
        }
    });
}

function verifyVessel(vesselId, vesselName, pendingCount) {
    Swal.fire({
        title: 'Verifikasi Checkup',
        html: `
        <div class="text-start">
            <div class="alert alert-light border mb-3">
                <i class="fas fa-info-circle me-2"></i>
                Anda akan memverifikasi <strong>${pendingCount} pemeriksaan pending</strong>
                untuk vessel <strong>${vesselName}</strong> pada tanggal
                <strong>{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}</strong>.
            </div>
            <div class="mb-3">
                <label class="form-label small">Catatan Verifikasi (Opsional)</label>
                <textarea id="verification_notes" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
            </div>
        </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1b2e4b',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-check-circle me-2"></i>Verify',
        cancelButtonText: 'Batal',
        width: '600px',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const notes = document.getElementById('verification_notes').value;
            return fetch('{{ route("daily-checkup.verify") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    vessel_id: vesselId,
                    check_date: '{{ $selectedDate }}',
                    verification_notes: notes
                })
            })
            .then(response => {
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error('Server tidak mengembalikan response JSON');
                }
                return response.json().then(data => {
                    if (!response.ok) throw new Error(data.message || 'Terjadi kesalahan pada server');
                    return data;
                });
            })
            .catch(error => {
                Swal.showValidationMessage(`Gagal memverifikasi: ${error.message}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                html: `<strong>${pendingCount} checkup</strong> untuk vessel <strong>${vesselName}</strong> telah diverifikasi`,
                confirmButtonColor: '#1b2e4b',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => location.reload());
        }
    });
}

setInterval(function() {
    location.reload();
}, 300000);

@if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        confirmButtonColor: '#1b2e4b',
        timer: 3000,
        timerProgressBar: true
    });
@endif

@if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '{{ session('error') }}',
        confirmButtonColor: '#1b2e4b'
    });
@endif
</script>
@endpush
