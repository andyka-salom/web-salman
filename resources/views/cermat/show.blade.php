@extends('layouts.app')

@section('title', 'Detail Laporan #' . $report->report_number)

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="{{ asset('src/plugins/src/animate/animate.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('src/assets/css/light/components/modal.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('src/assets/css/dark/components/modal.css') }}" rel="stylesheet" type="text/css" />

<style>
    /* Styling dasar template */
    .detail-card {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
        border: none;
        border-radius: 8px;
    }
    .detail-card .card-header {
        background-color: var(--card-bg);
        border-bottom: 1px solid var(--bs-border-color);
    }
    .card-title i { color: var(--bs-primary); }

    /* Detail Teks */
    .detail-label {
        font-size: 0.8rem;
        color: var(--text-muted);
        display: block;
        margin-bottom: 0.25rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .detail-value {
        font-size: 1rem;
        color: var(--text-color);
        font-weight: 600;
    }
    .detail-value.long-text {
        white-space: pre-wrap;
        font-weight: 400;
        line-height: 1.6;
    }
    .detail-value .badge { font-size: 0.9rem; font-weight: 500; }

    /* Attachment Gallery */
    .attachment-gallery .attachment-item {
        display: block;
        overflow: hidden;
        border-radius: .5rem;
        position: relative;
        aspect-ratio: 1 / 1;
        background-color: var(--bs-light);
        cursor: pointer;
    }
    .attachment-gallery .attachment-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .3s ease, filter .3s ease;
    }
    .attachment-gallery .attachment-item:hover img { transform: scale(1.1); filter: brightness(0.7); }
    .attachment-gallery .attachment-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.4);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity .3s ease;
    }
    .attachment-gallery .attachment-item:hover .attachment-overlay { opacity: 1; }

    /* Action Items Accordion */
    .accordion-item-completed { opacity: 0.8; }
    .accordion-item-completed .accordion-button { background-color: var(--bs-light); }
    .completion-notes {
        margin-top: 0.75rem;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        border-left: 4px solid var(--bs-success);
        background-color: var(--bs-success-bg-subtle);
        font-style: italic;
    }
    .proof-gallery { display: flex; flex-wrap: wrap; gap: 0.75rem; }
    .proof-gallery .proof-item {
        width: 70px;
        height: 70px;
        border-radius: .5rem;
        overflow: hidden;
        border: 1px solid var(--bs-border-color);
        transition: transform .2s ease, box-shadow .2s ease;
        cursor: pointer;
    }
    .proof-gallery .proof-item:hover { transform: scale(1.05); box-shadow: 0 4px 10px rgba(0,0,0,.1); }
    .proof-gallery .proof-item img { width: 100%; height: 100%; object-fit: cover; }

    /* Modal Approval */
    .hover-bg-light:hover { background-color: var(--bs-light) !important; transition: background-color 0.2s ease; }
</style>
@endpush

@section('content')
@php
    use App\Models\CermatReport;
    use App\Models\ActionItem;
    use Illuminate\Support\Str;
    use Illuminate\Support\Arr;

    $statusClass = $report->getStatusBadgeClass();
    $user = Auth::user();
    $isRejected = $report->supervisor_status === CermatReport::SUPERVISOR_STATUS_REJECTED;
@endphp
    <div class="layout-px-spacing">
        <div class="middle-content container-xxl p-0">

            {{-- HEADER HALAMAN DENGAN TOMBOL AKSI --}}
            <div class="row layout-top-spacing">
                <div class="col-12">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                        <div>
                            <h2 class="mb-0 fw-bold d-flex align-items-center gap-2">
                                Detail Laporan
                                <span class="badge bg-{{ $statusClass }} align-middle fs-6 fw-normal">{{ Str::of($report->status)->replace('_', ' ')->title() }}</span>
                            </h2>
                            <p class="mb-0 text-muted">#{{ $report->report_number }}</p>
                        </div>
                        <div class="d-flex gap-2">
                            {{-- Tombol Edit --}}
                            @if (
                                !$report->isConsideredFinished() &&
                                (
                                    $report->canBeEdited() ||
                                    $report->isSupervisor(Auth::id())
                                )
                            )
                            <a href="{{ route('cermat.reports.edit', $report) }}" class="btn btn-outline-{{ $isRejected ? 'warning' : 'secondary' }} rounded-pill px-3">
                                <i class="bi bi-pencil-fill me-1"></i> {{ $isRejected ? 'Revisi Laporan' : 'Edit Laporan' }}
                            </a>
                            @endif

                            {{-- Tombol Resubmit --}}
                            @if ($isRejected && $report->isReporter($user->id))
                                <form action="{{ route('cermat.reports.resubmit', $report) }}" method="POST" id="resubmit-form-{{ $report->id }}" class="d-inline">
                                    @csrf
                                    <button type="button" id="btn-resubmit-{{ $report->id }}" class="btn btn-success rounded-pill px-3">
                                        <i class="bi bi-arrow-clockwise me-1"></i> Ajukan Ulang
                                    </button>
                                </form>
                            @endif
                            {{-- Edit Button (if user can edit) --}}
                            @if($report->canUserEdit())
                            <a href="{{ route('cermat.reports.edit', $report) }}" class="btn btn-warning">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                                Edit Laporan
                            </a>
                            @endif
                            @role('super-admin')
            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                </svg>
                Hapus Permanen
            </button>
            @endrole

                            <a href="{{ route('cermat.reports.index') }}" class="btn btn-secondary rounded-pill px-3">
                                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                            </a>

                            <a href="{{ route('cermat.reports.downloadPdf', $report) }}" class="btn btn-dark rounded-pill px-3" target="_blank">
                                <i class="bi bi-file-pdf me-1"></i> Download PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Alert Messages --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row g-4 layout-spacing">
                {{-- KOLOM KIRI (UTAMA) --}}
                <div class="col-lg-8">
                    <div class="d-flex flex-column gap-4">

                    {{--
                        [MODIFIKASI] BANNER SUPERVISOR DIPINDAHKAN KE SINI
                        Agar tidak tergantung pada status global (IN_REVIEW / ACTION_IN_PROGRESS)
                    --}}
                    @if (
                        $report->isSupervisor($user->id) &&
                        $report->supervisor_status === CermatReport::SUPERVISOR_STATUS_AWAITING &&
                        !$report->isConsideredFinished()
                    )
                        <div class="card detail-card border-0 bg-primary-subtle">
                            <div class="card-body d-flex align-items-center flex-wrap gap-2 p-3">
                                <div class="me-auto">
                                    <p class="mb-0 fw-bold text-primary-emphasis">
                                        <i class="bi bi-exclamation-diamond-fill me-2"></i>Keputusan Supervisor Diperlukan
                                    </p>
                                    <small class="text-primary-emphasis opacity-75">
                                        Persetujuan Anda diperlukan untuk validasi administratif laporan ini.
                                    </small>
                                </div>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#approvalModal">
                                    <i class="bi bi-check2-square me-1"></i> Tindak Lanjuti
                                </button>
                            </div>
                        </div>
                    @endif
                    {{-- [AKHIR MODIFIKASI BANNER SUPERVISOR] --}}


                    {{-- BLOCK STATUS LAINNYA --}}
                    @switch($report->status)

                        @case(CermatReport::STATUS_IN_REVIEW)
                            {{-- Banner HSSE Review --}}
                            @role('hsse')
                            @if ($report->hsse_status !== CermatReport::HSSE_STATUS_RECOMMENDED)
                            <div class="card detail-card border-0 bg-info-subtle mt-2">
                                <div class="card-body d-flex align-items-center flex-wrap gap-2 p-3">
                                    <p class="mb-0 fw-bold text-info-emphasis me-3">
                                        <i class="bi bi-info-circle-fill"></i> Laporan ini memerlukan Review & Rekomendasi HSSE.
                                    </p>
                                    <a href="{{ route('cermat.reports.review', $report) }}" class="btn btn-info">
                                        <i class="bi bi-journal-check me-1"></i> Review & Tindakan
                                    </a>
                                </div>
                            </div>
                            @endif
                            @endrole
                            @break

                        @case(CermatReport::STATUS_ACTION_IN_PROGRESS)
                            @role('hsse')
                            <div class="card detail-card border-0 bg-warning-subtle">
                                <div class="card-body d-flex align-items-center flex-wrap gap-2 p-3">
                                    <p class="mb-0 fw-bold text-warning-emphasis me-3">
                                        <i class="bi bi-tools"></i> Tindakan perbaikan sedang berlangsung.
                                    </p>
                                    <a href="{{ route('cermat.reports.review', $report) }}" class="btn btn-warning text-dark">
                                        <i class="bi bi-card-checklist me-1"></i> Kelola Tindakan
                                    </a>
                                </div>
                            </div>
                            @endrole
                            @break

                        {{-- STATUS: SIAP UNTUK DITUTUP --}}
                        @case(CermatReport::STATUS_AWAITING_CLOSEOUT)
                        @role('hsse')
                            <div class="card detail-card border-0 bg-primary-subtle">
                                <div class="card-body d-flex align-items-center flex-wrap gap-2 p-3">
                                    <p class="mb-0 fw-bold text-primary-emphasis me-3">
                                        <i class="bi bi-exclamation-diamond-fill"></i> Laporan ini siap untuk ditutup:
                                    </p>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#closeReportModal">
                                        <i class="bi bi-lock-fill me-1"></i> Tutup Laporan
                                    </button>
                                </div>
                            </div>
                        @endrole
                        @break

                        {{-- STATUS: NO ACTION --}}
                        @case(CermatReport::STATUS_NO_ACTION_REQUIRED)
                            <div class="card detail-card border-0 bg-info-subtle">
                                <div class="card-body p-3">
                                    <p class="mb-0 fw-bold text-info-emphasis">
                                        <i class="bi bi-info-circle-fill me-1"></i>
                                        Laporan ini telah ditinjau dan tidak memerlukan tindakan lebih lanjut.
                                    </p>
                                </div>
                            </div>
                            @break

                        {{-- STATUS: DITUTUP --}}
                        @case(CermatReport::STATUS_CLOSED)
                            <div class="card detail-card border-0 bg-success-subtle">
                                <div class="card-body p-3">
                                    <p class="mb-0 fw-bold text-success-emphasis">
                                        <i class="bi bi-check-circle-fill me-1"></i>
                                        Laporan ini telah selesai dan ditutup pada {{ $report->close_out_at?->format('d M Y') ?? '' }}.
                                    </p>
                                </div>
                            </div>
                            @break
                    @endswitch

                        {{-- Menampilkan alasan penolakan supervisor --}}
                        @if($isRejected && $report->rejection_reason)
                        <div class="card detail-card border-start border-4 border-danger">
                            <div class="card-header py-3">
                                <h5 class="mb-0 card-title fw-bold text-danger">
                                    <i class="bi bi-journal-x-fill me-2"></i>
                                    Alasan Penolakan Supervisor
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="detail-value long-text fst-italic">"{{ $report->rejection_reason }}"</p>
                                <p class="text-muted small mb-0 mt-2">
                                    <i class="bi bi-clock me-1"></i> Ditolak pada: {{ $report->supervisor_rejected_at?->format('d M Y, H:i') ?? '-' }}
                                </p>
                            </div>
                        </div>
                        @elseif($report->status === CermatReport::STATUS_NO_ACTION_REQUIRED && $report->supervisor_notes)
                        <div class="card detail-card border-start border-4 border-info">
                            <div class="card-header py-3">
                                <h5 class="mb-0 card-title fw-bold text-info">
                                    <i class="bi bi-info-circle-fill me-2"></i>
                                    Catatan Keputusan Supervisor
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="detail-value long-text fst-italic">"{{ $report->supervisor_notes }}"</p>
                                <p class="text-muted small mb-0 mt-2">
                                    <i class="bi bi-clock me-1"></i> Diputuskan pada: {{ $report->supervisor_approved_at?->format('d M Y, H:i') ?? '-' }}
                                </p>
                            </div>
                        </div>
                        @endif

                        <!-- CARD DETAIL UTAMA -->
                        <div class="card detail-card widget-content widget-content-area br-8">
                            <div class="card-header py-3">
                                <h5 class="mb-0 card-title fw-bold"><i class="bi bi-info-circle-fill me-2"></i>Informasi Utama Laporan</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <span class="detail-label">Uraian Kejadian / Temuan</span>
                                        <p class="detail-value long-text">{{ $report->details }}</p>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <span class="detail-label">Tanggal & Waktu Kejadian</span>
                                        <p class="detail-value">{{ $report->report_datetime->format('d M Y, H:i') }}</p>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <span class="detail-label">Area</span>
                                        <p class="detail-value">{{ $report->area->name ?? '-' }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <span class="detail-label">Detail Lokasi Spesifik</span>
                                        <p class="detail-value">{{ $report->location_details ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CARD ANALISIS & KLASIFIKASI -->
                        <div class="card detail-card widget-content widget-content-area br-8">
                            <div class="card-header py-3">
                                <h5 class="mb-0 card-title fw-bold"><i class="bi bi-clipboard-data-fill me-2"></i>Analisis & Klasifikasi Pelapor</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <span class="detail-label">Unsafe Act(s)</span>
                                        <div class="detail-value">
                                            @forelse($report->unsafeActs as $act)
                                                <span class="badge bg-warning text-dark me-1 mb-1">{{ $act->description }}</span>
                                            @empty
                                                <span class="text-muted fst-italic small">Tidak ada</span>
                                            @endforelse
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <span class="detail-label">Unsafe Condition(s)</span>
                                        <div class="detail-value">
                                            @forelse($report->unsafeConditions as $condition)
                                                <span class="badge bg-danger text-white me-1 mb-1">{{ $condition->description }}</span>
                                            @empty
                                                <span class="text-muted fst-italic small">Tidak ada</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- HSSE CLASSIFICATION & REVIEW --}}
                        @if($report->hsse_reviewed_at)
                        <div class="card detail-card widget-content widget-content-area br-8">
                            <div class="card-header py-3">
                                <h5 class="mb-0 card-title fw-bold"><i class="bi bi-person-workspace me-2"></i>Klasifikasi & Review HSSE</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-md-4 col-6">
                                        <span class="detail-label">Tipe Kejadian</span>
                                        <p class="detail-value">{{ Str::of($report->event_type)->title() }}</p>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <span class="detail-label">Klasifikasi</span>
                                        <p class="detail-value">{{ Str::of($report->classification)->replace('_', ' ')->title() }}</p>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <span class="detail-label">Sirkulasi Terbatas</span>
                                        <p class="detail-value">{!! $report->is_limited_circulation ? '<span class="badge bg-danger">Ya</span>' : '<span class="badge bg-secondary">Tidak</span>' !!}</p>
                                    </div>
                                    @if($report->synergi_register_no)
                                    <div class="col-md-4 col-6">
                                        <span class="detail-label">P-horse  </span>
                                        <p class="detail-value">{{ $report->synergi_register_no }}</p>
                                    </div>
                                    @endif
                                    <div class="col-md-4 col-6">
                                        <span class="detail-label">Direview oleh</span>
                                        <p class="detail-value">{{ $report->hsseOfficer->name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <span class="detail-label">Tanggal Review</span>
                                        <p class="detail-value">{{ $report->hsse_reviewed_at?->format('d M Y, H:i') ?? '-' }}</p>
                                    </div>

                                    @if($report->short_term_mitigation)
                                    <div class="col-12">
                                        <span class="detail-label">Mitigasi Jangka Pendek (HSSE)</span>
                                        <p class="detail-value long-text">{{ $report->short_term_mitigation }}</p>
                                    </div>
                                    @endif

                                    @if($report->cost_to_business_actual || $report->cost_to_business_potential)
                                    <div class="col-12 border-top pt-3">
                                        <h6 class="mb-2">Dampak Finansial (USD)</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <span class="detail-label">Kerugian Aktual</span>
                                                <p class="detail-value">{{ number_format($report->cost_to_business_actual ?? 0, 2) }}</p>
                                            </div>
                                            <div class="col-md-4">
                                                <span class="detail-label">Potensi Kerugian</span>
                                                <p class="detail-value">{{ number_format($report->cost_to_business_potential ?? 0, 2) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- CARD PENILAIAN RISIKO -->
                        @if($report->riskAssessments->isNotEmpty())
                        @php
                            $initialRisks = $report->riskAssessments->where('type', 'initial');
                            $residualRisks = $report->riskAssessments->where('type', 'residual');
                        @endphp
                        <div class="card detail-card widget-content widget-content-area br-8">
                            <div class="card-header py-3">
                                <h5 class="mb-0 card-title fw-bold"><i class="bi bi-shield-shaded me-2"></i>Penilaian Risiko</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    @if($initialRisks->isNotEmpty())
                                    <div class="col-lg-6">
                                        <h6 class="mb-3">Initial Risk</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Kategori Dampak</th>
                                                        <th class="text-center">Aktual</th>
                                                        <th class="text-center">Potensial</th>
                                                        <th class="text-center">Likelihood</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($initialRisks as $risk)
                                                    <tr>
                                                        <td>{{ Str::of($risk->consequence_category)->replace('_', ' ')->title() }}</td>
                                                        <td class="text-center">{{ $risk->real_severity ?? '-' }}</td>
                                                        <td class="text-center">{{ $risk->potential_severity ?? '-' }}</td>
                                                        <td class="text-center">{{ $risk->likelihood ?? '-' }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    @endif

                                    @if($residualRisks->isNotEmpty())
                                    <div class="col-lg-6">
                                        <h6 class="mb-3">Residual Risk</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Kategori Dampak</th>
                                                        <th class="text-center">Potensial</th>
                                                        <th class="text-center">Likelihood</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($residualRisks as $risk)
                                                    <tr>
                                                        <td>{{ Str::of($risk->consequence_category)->replace('_', ' ')->title() }}</td>
                                                        <td class="text-center">{{ $risk->potential_severity ?? '-' }}</td>
                                                        <td class="text-center">{{ $risk->likelihood ?? '-' }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- CARD TINDAK LANJUT & OTORISASI -->
                        <div class="card detail-card widget-content widget-content-area br-8">
                            <div class="card-header py-3">
                                <h5 class="mb-0 card-title fw-bold"><i class="bi bi-shield-fill-check me-2"></i>Tindak Lanjut & Otorisasi</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <span class="detail-label">Tindakan Langsung yang Diambil</span>
                                        <p class="detail-value long-text">{{ $report->immediate_action_taken }}</p>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <span class="detail-label">Pelapor</span>
                                        <p class="detail-value">{{ $report->reporter->name ?? '-' }}</p>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <span class="detail-label">Atasan Langsung</span>
                                        <p class="detail-value">{{ $report->lineSupervisor->name ?? '-' }}</p>
                                    </div>
                                    <div class="col-md-2 col-6">
                                        <span class="detail-label">STOP Job?</span>
                                        <p class="detail-value">{!! $report->stop_card_issued ? '<span class="badge bg-success">Ya</span>' : '<span class="badge bg-secondary">Tidak</span>' !!}</p>
                                    </div>
                                    @if($report->suggestion_for_improvement)
                                    <div class="col-12">
                                        <span class="detail-label">Saran Perbaikan Pelapor</span>
                                        <p class="detail-value long-text">{{ $report->suggestion_for_improvement }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- CARD PROGRES TINDAKAN PERBAIKAN -->
                        @php
                            $totalActions = $report->actionItems->count();
                            $completedCount = 0;
                            $progressPercentage = 0;
                            if ($totalActions > 0) {
                                $completedCount = $report->actionItems->whereIn('status', [ActionItem::STATUS_COMPLETED, ActionItem::STATUS_CANT_DO])->count();
                                $progressPercentage = ($completedCount / $totalActions) * 100;
                            }
                        @endphp
                        <div class="card detail-card widget-content widget-content-area br-8">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap">
                                <h5 class="mb-0 card-title fw-bold"><i class="bi bi-kanban-fill me-2"></i>Progres Tindakan Perbaikan</h5>
                                @if($report->hsse_reviewed_at)
                                <a href="{{ route('cermat.reports.review', $report) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    <i class="bi bi-card-checklist me-1"></i> Kelola Tindakan
                                </a>
                                @endif
                            </div>
                            <div class="card-body">
                                @if($totalActions > 0)
                                    {{-- Progress Bar --}}
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold">Progres Penyelesaian</span>
                                            <span class="fw-bold">{{ $completedCount }} / {{ $totalActions }} Tuntas</span>
                                        </div>
                                        <div class="progress" role="progressbar" aria-valuenow="{{ $progressPercentage }}" aria-valuemin="0" aria-valuemax="100" style="height: 1.25rem;">
                                            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated fw-bold" style="width: {{ $progressPercentage }}%">{{ round($progressPercentage) }}%</div>
                                        </div>
                                    </div>

                                    {{-- Accordion untuk Daftar Tindakan --}}
                                    <div class="accordion" id="actionItemsAccordion">
                                        @foreach($report->actionItems as $item)
                                            @php
                                                $isCompleted = in_array($item->status, [ActionItem::STATUS_COMPLETED, ActionItem::STATUS_CANT_DO]);
                                                $statusConfig = match($item->status) {
                                                    ActionItem::STATUS_DO => ['class' => 'secondary', 'icon' => 'clipboard'],
                                                    ActionItem::STATUS_IN_PROGRESS => ['class' => 'info', 'icon' => 'arrow-repeat'],
                                                    ActionItem::STATUS_COMPLETED => ['class' => 'success', 'icon' => 'check2-circle-fill'],
                                                    ActionItem::STATUS_CANT_DO => ['class' => 'danger', 'icon' => 'x-circle-fill'],
                                                    default => ['class' => 'secondary', 'icon' => 'question-circle'],
                                                };
                                            @endphp
                                            <div class="accordion-item @if($isCompleted) accordion-item-completed @endif">
                                                <h2 class="accordion-header" id="heading-{{ $item->id }}">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $item->id }}" aria-expanded="false" aria-controls="collapse-{{ $item->id }}">
                                                        <div class="d-flex w-100 align-items-center pe-2">
                                                            <i class="bi bi-{{$statusConfig['icon']}} fs-5 me-3 text-{{$statusConfig['class']}} status-icon"></i>
                                                            <span class="fw-bold flex-grow-1">{{ $item->description }}</span>
                                                            <span class="badge bg-{{$statusConfig['class']}}-subtle text-{{$statusConfig['class']}}-emphasis border border-{{$statusConfig['class']}}-subtle rounded-pill ms-3">{{ Str::of($item->status)->replace('_', ' ')->title() }}</span>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="collapse-{{ $item->id }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $item->id }}" data-bs-parent="#actionItemsAccordion">
                                                    <div class="accordion-body">
                                                        <div class="d-flex flex-wrap gap-4 mb-3 pb-3 border-bottom">
                                                            <div>
                                                                <div class="detail-label">Penanggung Jawab</div>
                                                                <div class="detail-value"><i class="bi bi-person-fill me-1"></i>{{ $item->responsible->name ?? 'N/A' }}</div>
                                                            </div>
                                                            <div>
                                                                <div class="detail-label">Kategori Aksi</div>
                                                                <div class="detail-value">{{ $item->actionCategory->name ?? 'N/A' }}</div>
                                                            </div>
                                                            <div>
                                                                <div class="detail-label">Target Awal</div>
                                                                <div class="detail-value"><i class="bi bi-calendar-event me-1"></i>{{ $item->target_date->isoFormat('DD MMMM Y') }}</div>
                                                            </div>
                                                            @if($item->target_date->isPast() && !$isCompleted)
                                                                <div>
                                                                    <div class="detail-label">Keterangan</div>
                                                                    <div class="detail-value text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>Terlambat</div>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        @if($isCompleted && ($item->completion_notes || $item->photos->isNotEmpty()))
                                                            <div class="d-flex flex-column gap-3">
                                                                @if($item->completion_notes)
                                                                    <div>
                                                                        <div class="detail-label">Catatan Penyelesaian</div>
                                                                        <div class="completion-notes">"{{ $item->completion_notes }}"</div>
                                                                    </div>
                                                                @endif
                                                                @if($item->photos->isNotEmpty())
                                                                    <div>
                                                                        <div class="detail-label">Bukti Foto</div>
                                                                        <div class="proof-gallery">
                                                                            @foreach($item->photos as $photo)
                                                                            <div class="proof-item attachment-item" data-bs-toggle="modal" data-bs-target="#imageModal" data-img-src="{{ asset('storage/' . $photo->file_path) }}" data-img-title="Bukti Tindakan: {{ Str::limit($item->description, 40) }}">
                                                                                <img src="{{ asset('storage/' . $photo->file_path) }}" alt="Bukti Foto">
                                                                            </div>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <p class="text-muted fst-italic small">Belum ada detail penyelesaian.</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                @else
                                    <div class="text-center text-muted p-4 bg-light rounded">
                                        <i class="bi bi-clipboard-x fs-2"></i>
                                        <p class="mb-1 mt-2 fw-bold">Belum Ada Tindakan Perbaikan</p>
                                        <p class="small mb-0">Tindakan perbaikan akan muncul di sini setelah HSSE merekomendasikannya.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- CARD CLOSE OUT INFO (JIKA SUDAH DITUTUP) --}}
                        @if($report->status === CermatReport::STATUS_CLOSED && $report->close_out_description)
                        <div class="card detail-card border-start border-4 border-success widget-content widget-content-area br-8">
                            <div class="card-header py-3">
                                <h5 class="mb-0 card-title fw-bold text-success">
                                    <i class="bi bi-lock-fill me-2"></i>Informasi Penutupan
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <span class="detail-label">Ditutup oleh</span>
                                        <p class="detail-value">{{ $report->closeOutPerformer->name ?? '-' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="detail-label">Tanggal Penutupan</span>
                                        <p class="detail-value">{{ $report->close_out_at?->format('d M Y, H:i') ?? '-' }}</p>
                                    </div>
                                    <div class="col-12">
                                        <span class="detail-label">Deskripsi Penutupan</span>
                                        <p class="detail-value long-text">{{ $report->close_out_description }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>

                {{-- KOLOM KANAN (SIDEBAR) --}}
                <div class="col-lg-4">
                    <div class="position-sticky d-flex flex-column gap-4" style="top: 2rem;">
                        <!-- GALERI LAMPIRAN -->
                        <div class="card widget-content widget-content-area br-8">
                            <div class="card-header py-3">
                                <h5 class="mb-0 card-title fw-bold"><i class="bi bi-images me-2"></i>Galeri Lampiran</h5>
                            </div>
                            <div class="card-body">
                                @if($report->attachments->isNotEmpty())
                                <div class="row g-2 attachment-gallery">
                                    @foreach($report->attachments as $attachment)
                                    <div class="col-4">
                                        <a href="#" class="attachment-item" data-bs-toggle="modal" data-bs-target="#imageModal" data-img-src="{{ asset('storage/' . $attachment->file_path) }}" data-img-title="{{ $attachment->file_name }}">
                                            <img src="{{ asset('storage/' . $attachment->file_path) }}" alt="{{ $attachment->file_name }}">
                                            <div class="attachment-overlay"><i class="bi bi-arrows-fullscreen fs-4"></i></div>
                                        </a>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <div class="text-center text-muted p-3">
                                    <i class="bi bi-paperclip fs-2"></i>
                                    <p class="mb-0 mt-2">Tidak ada lampiran.</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- RIWAYAT / LOG PERUBAHAN -->
                        <div class="card widget-content widget-content-area br-8">
                            <div class="card-header py-3">
                                <h5 class="mb-0 card-title fw-bold"><i class="bi bi-clock-history me-2"></i>Riwayat Perubahan</h5>
                            </div>
                            <div class="list-group list-group-flush" style="max-height: 450px; overflow-y: auto;">
                                @forelse($audits as $audit)
                                <div class="list-group-item p-3">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div>
                                            <p class="mb-1 fw-bold">{{ $audit->user->name ?? 'Sistem' }}</p>
                                            <small class="text-muted d-block">
                                                @if($audit->event === 'created')
                                                    Membuat laporan ini.
                                                @elseif($audit->event === 'updated')
                                                    Memperbarui laporan.
                                                @else
                                                    Melakukan aksi: <span class="badge bg-light text-dark fw-normal">{{ $audit->event }}</span>
                                                @endif
                                            </small>
                                        </div>
                                        <small class="text-muted text-nowrap ms-3">{{ $audit->created_at->diffForHumans() }}</small>
                                    </div>

                                    @if ($audit->event === 'updated' && count($audit->getModified()))
                                    <div class="mt-2 ps-2 border-start border-2 border-secondary-subtle">
                                        <ul class="list-unstyled mb-0" style="font-size: 0.8rem;">
                                            @foreach($audit->getModified() as $attribute => $modified)
                                            @php
                                                $formatValue = function ($value) {
                                                    if (is_null($value)) return '<em>(kosong)</em>';
                                                    if (is_bool($value)) return $value ? '<span class="badge bg-success-subtle text-success-emphasis">Ya</span>' : '<span class="badge bg-danger-subtle text-danger-emphasis">Tidak</span>';
                                                    if (is_array($value)) return '<pre class="mb-0"><code>' . e(json_encode($value, JSON_PRETTY_PRINT)) . '</code></pre>';
                                                    return e(Str::limit($value, 80));
                                                };
                                            @endphp
                                            <li class="mb-1">
                                                <strong class="fw-semibold">{{ Str::of($attribute)->replace('_', ' ')->title() }}</strong> diubah dari
                                                <span class="text-danger">{!! $formatValue(Arr::get($modified, 'old')) !!}</span> menjadi
                                                <span class="text-success fw-bold">{!! $formatValue(Arr::get($modified, 'new')) !!}</span>.
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                </div>
                                @empty
                                <div class="list-group-item text-center text-muted p-4">
                                    <p class="mb-0">Belum ada riwayat perubahan.</p>
                                </div>
                                @endforelse
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

{{-- MODAL IMAGE VIEWER --}}
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Lihat Lampiran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid" alt="Preview">
            </div>
        </div>
    </div>
</div>
{{-- Delete Confirmation Modal --}}
@role('super-admin')
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold" id="deleteModalLabel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    Konfirmasi Penghapusan Permanen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger mb-3" role="alert">
                    <strong>PERINGATAN!</strong> Tindakan ini tidak dapat dibatalkan.
                </div>
                <p class="mb-3">
                    Anda akan <strong>menghapus permanen</strong> laporan berikut:
                </p>
                <div class="card bg-light">
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="fw-bold" style="width: 40%">No. Laporan:</td>
                                <td>{{ $report->report_number }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Pelapor:</td>
                                <td>{{ $report->reporter->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Tanggal:</td>
                                <td>{{ $report->report_datetime->format('d M Y, H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Area:</td>
                                <td>{{ $report->area->name ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-muted small">
                    Data yang akan dihapus meliputi: laporan utama, lampiran file, foto tindakan, riwayat audit, dan semua data terkait.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    Batal
                </button>
                <form id="deleteForm" action="{{ route('cermat.reports.destroy', $report) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" id="confirmDeleteBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                        Ya, Hapus Permanen
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endrole
{{-- MODAL UNTUK KEPUTUSAN SUPERVISOR (Hanya jika status SPV AWAITING) --}}
@if($report->isSupervisor(Auth::id()) && $report->supervisor_status === CermatReport::SUPERVISOR_STATUS_AWAITING)
<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('cermat.reports.submitApproval', $report) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="approvalModalLabel">Keputusan Laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Pilih tindakan yang akan diambil untuk laporan ini:</p>

                    @if ($errors->any())
                        {{-- Logic untuk memunculkan error setelah redirect back --}}
                        @if(old('decision') && (old('decision') === 'reject' || old('decision') === 'no_action'))
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    @endif

                    <div class="mb-3">
                        {{-- OPSI 1: SETUJUI --}}
                        <div class="form-check p-3 border rounded mb-3 hover-bg-light" style="cursor: pointer;">
                            <input class="form-check-input" type="radio" name="decision" id="decisionApprove"
                                   value="approve" required {{ old('decision') == 'approve' ? 'checked' : '' }}>
                            <label class="form-check-label w-100" for="decisionApprove" style="cursor: pointer;">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
                                    <div>
                                        <strong class="d-block">Setujui & Lanjutkan</strong>
                                        <small class="text-muted">Laporan akan diteruskan ke HSSE untuk review lebih lanjut.</small>
                                    </div>
                                </div>
                            </label>
                        </div>

                        {{-- OPSI 2: TIDAK PERLU TINDAKAN --}}
                        <div class="form-check p-3 border rounded mb-3 hover-bg-light" style="cursor: pointer;">
                            <input class="form-check-input" type="radio" name="decision" id="decisionNoAction"
                                   value="no_action" {{ old('decision') == 'no_action' ? 'checked' : '' }}>
                            <label class="form-check-label w-100" for="decisionNoAction" style="cursor: pointer;">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-info-circle-fill text-info fs-4 me-3"></i>
                                    <div>
                                        <strong class="d-block">Tidak Perlu Tindakan</strong>
                                        <small class="text-muted">Laporan diterima, tetapi tidak memerlukan tindakan perbaikan lebih lanjut.</small>
                                    </div>
                                </div>
                            </label>
                        </div>

                        {{-- OPSI 3: TOLAK --}}
                        <div class="form-check p-3 border rounded hover-bg-light" style="cursor: pointer;">
                            <input class="form-check-input" type="radio" name="decision" id="decisionReject"
                                   value="reject" {{ old('decision') == 'reject' ? 'checked' : '' }}>
                            <label class="form-check-label w-100" for="decisionReject" style="cursor: pointer;">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-x-circle-fill text-danger fs-4 me-3"></i>
                                    <div>
                                        <strong class="d-block">Tolak Laporan</strong>
                                        <small class="text-muted">Laporan tidak valid. Reporter dapat merevisi dan mengajukan ulang.</small>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- TEXTAREA CATATAN --}}
                    <div class="mb-3">
                        <label for="supervisor_notes" class="form-label fw-semibold" id="notesLabel">
                            Catatan (Opsional)
                        </label>
                        <textarea class="form-control @error('supervisor_notes') is-invalid @enderror"
                                  id="supervisor_notes"
                                  name="supervisor_notes"
                                  rows="4"
                                  placeholder="Berikan alasan atau detail tambahan...">{{ old('supervisor_notes') }}</textarea>
                        <small class="form-text text-muted" id="notesHint">
                            Catatan ini akan terlihat oleh reporter dan tim terkait.
                        </small>
                        @error('supervisor_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send-fill me-1"></i> Kirim Keputusan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- MODAL UNTUK CLOSE OUT LAPORAN --}}
@if($report->status === CermatReport::STATUS_AWAITING_CLOSEOUT)
<div class="modal fade" id="closeReportModal" tabindex="-1" aria-labelledby="closeReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('cermat.reports.submitClose', $report) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="closeReportModalLabel">
                        <i class="bi bi-lock-fill me-2"></i>Tutup Laporan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Semua tindakan perbaikan telah selesai. Anda dapat menutup laporan ini sekarang.
                    </div>

                    @if ($errors->any() && old('close_out_description'))
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="close_out_description" class="form-label fw-semibold">
                            Deskripsi Penutupan <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control @error('close_out_description') is-invalid @enderror"
                                  id="close_out_description"
                                  name="close_out_description"
                                  rows="4"
                                  required
                                  placeholder="Berikan ringkasan penyelesaian dan konfirmasi bahwa semua tindakan telah selesai...">{{ old('close_out_description') }}</textarea>
                        <small class="form-text text-muted">
                            Jelaskan bahwa laporan ini telah diselesaikan dengan baik.
                        </small>
                        @error('close_out_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-lock-fill me-1"></i> Tutup Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    @role('super-admin')
function confirmDelete() {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

// Handle form submission via AJAX for better UX
document.getElementById('deleteForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const btn = document.getElementById('confirmDeleteBtn');
    const originalBtnHtml = btn.innerHTML;

    // Disable button and show loading
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menghapus...';

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            _method: 'DELETE'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            const alertHtml = `
                <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <strong>Berhasil!</strong><span class="ms-1">${data.message}</span>
                    </div>
                </div>
            `;

            // Redirect to index page after short delay
            setTimeout(() => {
                window.location.href = '{{ route("cermat.reports.index") }}';
            }, 1000);
        } else {
            throw new Error(data.message || 'Terjadi kesalahan');
        }
    })
    .catch(error => {
        // Show error message
        btn.disabled = false;
        btn.innerHTML = originalBtnHtml;

        alert('Gagal menghapus laporan: ' + error.message);
        console.error('Delete error:', error);
    });
});
@endrole
    // Image Modal Handler
    document.addEventListener('DOMContentLoaded', function() {
        // --- Image Modal ---
        const imageModal = document.getElementById('imageModal');
        if (imageModal) {
            imageModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const imgSrc = button.getAttribute('data-img-src');
                const imgTitle = button.getAttribute('data-img-title');

                const modalImage = imageModal.querySelector('#modalImage');
                const modalTitle = imageModal.querySelector('#imageModalLabel');

                modalImage.src = imgSrc;
                modalTitle.textContent = imgTitle || 'Lihat Lampiran';
            });
        }

        // --- Approval Modal - Dynamic Notes Label & Hint ---
        const approvalModal = document.getElementById('approvalModal');
        if (approvalModal) {
            const radioButtons = approvalModal.querySelectorAll('input[name="decision"]');
            const notesLabel = document.getElementById('notesLabel');
            const notesHint = document.getElementById('notesHint');
            const notesTextarea = document.getElementById('supervisor_notes');

            const updateNotesState = (decisionValue) => {
                if (decisionValue === 'reject') {
                    notesLabel.innerHTML = 'Alasan Penolakan <span class="text-danger">*</span>';
                    notesTextarea.required = true;
                    notesTextarea.placeholder = 'Jelaskan alasan penolakan dan apa yang perlu diperbaiki...';
                    notesHint.textContent = 'Catatan wajib diisi untuk penolakan. Berikan panduan yang jelas untuk perbaikan.';
                } else if (decisionValue === 'no_action') {
                    notesLabel.innerHTML = 'Catatan Keputusan <span class="text-danger">*</span>';
                    notesTextarea.required = true;
                    notesTextarea.placeholder = 'Jelaskan mengapa laporan ini tidak memerlukan tindakan lanjut...';
                    notesHint.textContent = 'Catatan wajib diisi untuk keputusan "Tidak Perlu Tindakan Lanjut".';
                } else {
                    notesLabel.innerHTML = 'Catatan (Opsional)';
                    notesTextarea.required = false;
                    notesTextarea.placeholder = 'Berikan komentar atau instruksi tambahan jika diperlukan...';
                    notesHint.textContent = 'Catatan ini akan terlihat oleh reporter dan tim terkait.';
                }
            };

            radioButtons.forEach(radio => {
                radio.addEventListener('change', function() {
                    updateNotesState(this.value);
                });
            });

            // Trigger change event on page load if there's an old value or initialize default
            const checkedRadio = approvalModal.querySelector('input[name="decision"]:checked');
            if (checkedRadio) {
                updateNotesState(checkedRadio.value);
            } else {
                 // Initialize default state ('approve') if no decision selected
                 updateNotesState('approve');
            }
        }

        // --- Auto-reopen modals if there are validation errors ---
        @if($errors->any() && old('decision'))
            const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
            modal.show();
        @endif

        @if($errors->any() && old('close_out_description'))
            const closeModal = new bootstrap.Modal(document.getElementById('closeReportModal'));
            closeModal.show();
        @endif

        // --- Resubmit Confirmation ---
        const resubmitButton = document.getElementById('btn-resubmit-{{ $report->id }}');

        if (resubmitButton) {
            resubmitButton.addEventListener('click', function (e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Ajukan Ulang Laporan?',
                    text: "Pastikan Anda telah merevisi laporan sesuai alasan penolakan. Laporan akan dikirim kembali untuk review.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Ajukan Ulang!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        const resubmitForm = document.getElementById('resubmit-form-{{ $report->id }}');
                        if(resubmitForm) {
                            resubmitButton.innerHTML = `
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Mengajukan...
                            `;
                            resubmitButton.disabled = true;

                            resubmitForm.submit();
                        }
                    }
                });
            });
        }
    });

</script>
@endpush

@endsection
