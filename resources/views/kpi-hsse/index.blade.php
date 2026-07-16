{{-- resources/views/kpi-hsse/index.blade.php — Styled like Crew Assessment template --}}
@extends('layouts.app')
@section('title', 'KPI HSSE Kontraktor')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/table/datatable/datatables.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/css/light/table/datatable/dt-global_style.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/css/dark/table/datatable/dt-global_style.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
/* ── Filter Card (identik crew assessment) ── */
.filter-card {
    background: var(--card-bg);
    border-radius: 10px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid var(--card-border-color);
}
.filter-card .form-label {
    font-weight: 700;
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--text-color);
    margin-bottom: .35rem;
}

/* ── Status badges ── */
.badge-kpi { border-radius: 20px; padding: 3px 11px; font-size: .72rem; font-weight: 700; display: inline-block; white-space: nowrap; }
.badge-kpi-draft     { background: #f1f5f9; color: #6b7280; border: 1px solid #d6d8db; }
.badge-kpi-submitted { background: #d0e9ff; color: #014c8c; border: 1px solid #b8daff; }
.badge-kpi-validated { background: #d1f3d1; color: #0e6245; border: 1px solid #c3e6cb; }
.badge-kpi-rejected  { background: #ffe0e0; color: #d93025; border: 1px solid #f5c6cb; }

/* ── Score pill ── */
.score-pill {
    display: inline-flex; align-items: center; justify-content: center;
    width: 38px; height: 38px; border-radius: 50%; font-weight: 800;
    font-size: .72rem; border: 3px solid;
}
.score-pill.excellent { border-color: #0e6245; color: #0e6245; background: #d1f3d1; }
.score-pill.good      { border-color: #014c8c; color: #014c8c; background: #d0e9ff; }
.score-pill.fair      { border-color: #965a00; color: #965a00; background: #fff4cc; }
.score-pill.poor      { border-color: #d93025; color: #d93025; background: #ffe0e0; }
.score-pill.none      { border-color: #9ca3af; color: #9ca3af; background: #f1f1f1; }

/* ── Pending badge ── */
.pending-count-badge {
    background: #fff3cd; color: #856404;
    border: 1px solid #ffc107;
    border-radius: 20px; padding: 4px 12px;
    font-size: .75rem; font-weight: 700;
    display: inline-flex; align-items: center; gap: 5px;
}
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
<div class="middle-content container-xxl p-0">

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="row layout-top-spacing mb-4 align-items-center">
        <div class="col-md-6">
            <h3 class="m-0 fw-bold">KPI HSSE Kontraktor</h3>
            <p class="text-muted mb-0 small">
                @if($isKoord) Kelola laporan KPI perusahaan Anda.
                @elseif($isSA) Akses penuh semua laporan. Dapat edit &amp; hapus permanen.
                @else Laporan submitted, waiting review, dan validated.
                @endif
            </p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end gap-2 flex-wrap align-items-center">
            @if($pendingReviewCount > 0 && $isHsse && !$isKoord)
                <span class="pending-count-badge">
                    <i class="bi bi-clock-history"></i>
                    {{ $pendingReviewCount }} menunggu review
                </span>
            @endif
            <a href="{{ route('kpi-hsse.dashboard') }}" class="btn btn-outline-info btn-sm">
                <i class="bi bi-speedometer2 me-1"></i> Dashboard
            </a>
            @if($isKoord || $isSA)
                <a href="{{ route('kpi-hsse.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Buat Laporan
                </a>
            @endif
        </div>
    </div>

    {{-- ── Alerts ───────────────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── Filter Card ──────────────────────────────────────────────────── --}}
    <div class="filter-card">
        <div class="row g-3 align-items-end">
            @if($isHsse)
            <div class="col-lg-3 col-md-4 col-6">
                <label class="form-label">Perusahaan</label>
                <select class="form-select form-select-sm" id="filter_company">
                    <option value="">Semua</option>
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-lg-3 col-md-4 col-6">
                <label class="form-label">Periode</label>
                <select class="form-select form-select-sm" id="filter_period">
                    <option value="">Semua</option>
                    @foreach($periods as $p)
                        <option value="{{ $p->period_month . '-' . $p->period_year }}"
                            {{ request('period') == $p->period_month . '-' . $p->period_year ? 'selected' : '' }}>
                            {{ $p->label ?? $p->period_month . '/' . $p->period_year }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-md-4 col-6">
                <label class="form-label">Status</label>
                <select class="form-select form-select-sm" id="filter_status">
                    <option value="">Semua</option>
                    @if($isSA || $isKoord)
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    @endif
                    <option value="submitted"  {{ request('status') === 'submitted'  ? 'selected' : '' }}>Submitted</option>
                    <option value="validated"  {{ request('status') === 'validated'  ? 'selected' : '' }}>Validated</option>
                    <option value="rejected"   {{ request('status') === 'rejected'   ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="resetFilters">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                </button>
            </div>
        </div>
    </div>

    {{-- ── Table ────────────────────────────────────────────────────────── --}}
    <div class="statbox widget box box-shadow">
    <div class="widget-content widget-content-area">
    <div class="table-responsive">
        <table id="kpiTable" class="table dt-table-hover" style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    @if($isHsse)<th>Perusahaan</th>@endif
                    <th>Periode</th>
                    <th>Kapal / Unit</th>
                    <th>Status</th>
                    <th class="text-center">Score</th>
                    <th class="text-center">Lampiran</th>
                    <th>Update</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($reports as $idx => $r)
                @php
                    $canKoordEdit = $isKoord && in_array($r->status, ['draft', 'rejected']);
                    $canHsseEdit  = ($isHsse || $isSA) && in_array($r->status, ['submitted', 'validated', 'rejected', 'draft']);
                    $canEdit      = $canKoordEdit || $canHsseEdit || $isSA;
                    $sc           = (float) ($r->total_score ?? 0);
                    $scoreClass   = $sc >= 90 ? 'excellent' : ($sc >= 75 ? 'good' : ($sc >= 60 ? 'fair' : 'poor'));
                    $evCount      = $r->details->sum(fn($d) => $d->evidences_count ?? $d->evidences->count());
                @endphp
                <tr>
                    <td>{{ $reports->firstItem() + $loop->index }}</td>

                    @if($isHsse)
                    <td>
                        <span class="fw-semibold" style="font-size:.83rem;">{{ $r->company->name ?? '-' }}</span>
                    </td>
                    @endif

                    <td>
                        <span class="fw-bold">{{ $r->kpiPeriod->label ?? '-' }}</span>
                    </td>

                    <td style="max-width:175px;font-size:.79rem;word-wrap:break-word;white-space:normal;">
                        @foreach($r->vessels->take(2) as $v)
                            <div>
                                {{ $v->vessel_name }}
                                @if($v->vessel_count)
                                    <span class="text-muted" style="font-size:.69rem;">({{ $v->vessel_count }})</span>
                                @endif
                            </div>
                        @endforeach
                        @if($r->vessels->count() > 2)
                            <div class="text-muted" style="font-size:.72rem;">+{{ $r->vessels->count() - 2 }} lainnya</div>
                        @endif
                    </td>

                    <td>
                        <span class="badge-kpi badge-kpi-{{ $r->status }}">{{ ucfirst($r->status) }}</span>
                        @if($r->status === 'submitted')
                            <div style="font-size:.67rem;color:#d97706;margin-top:2px;">⏳ Waiting review</div>
                        @elseif($r->status === 'validated')
                            <div style="font-size:.67rem;color:#16a34a;margin-top:2px;">✓ Tervalidasi</div>
                        @elseif($r->status === 'rejected')
                            <div style="font-size:.67rem;color:#d93025;margin-top:2px;">✗ Ditolak</div>
                        @endif
                    </td>

                    <td class="text-center">
                        @if($r->total_score)
                            <span class="score-pill {{ $scoreClass }}">{{ number_format($sc, 0) }}</span>
                        @else
                            <span class="score-pill none">—</span>
                        @endif
                    </td>

                    <td class="text-center">
                        @if($evCount > 0)
                            <span class="badge bg-info">{{ $evCount }} file</span>
                        @elseif($r->status === 'draft')
                            <span class="badge bg-warning text-dark">Belum</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td style="font-size:.77rem;color:#6b7280;white-space:nowrap;">
                        {{ $r->updated_at->diffForHumans() }}
                    </td>

                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center flex-wrap">
                            <a href="{{ route('kpi-hsse.show', $r) }}"
                               class="btn btn-sm btn-outline-secondary"
                               title="Lihat">
                                <i class="bi bi-eye"></i>
                            </a>

                            @if($canEdit)
                                <a href="{{ route('kpi-hsse.edit', $r) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endif

                            {{-- Export PDF --}}
                            <a href="{{ route('kpi-hsse.export-pdf', $r) }}"
                               class="btn btn-sm btn-outline-danger"
                               title="Export PDF" target="_blank">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>

                            {{-- Export Excel --}}
                            <a href="{{ route('kpi-hsse.export', $r) }}"
                               class="btn btn-sm btn-outline-success"
                               title="Export Excel">
                                <i class="bi bi-file-earmark-spreadsheet"></i>
                            </a>

                            @if($isSA || ($isKoord && $r->status === 'draft'))
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger delete-record"
                                        data-url="{{ route('kpi-hsse.destroy', $r) }}"
                                        data-label="{{ $r->kpiPeriod->label ?? '' }} — {{ $r->company->name ?? '' }}"
                                        title="Hapus">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $isHsse ? 9 : 8 }}" class="text-center text-muted py-5">
                        <div style="font-size:2rem;margin-bottom:.5rem;">📋</div>
                        Belum ada laporan KPI.
                        @if($isKoord || $isSA)
                            <a href="{{ route('kpi-hsse.create') }}" class="d-block mt-2">+ Buat laporan pertama</a>
                        @endif
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    </div>
    </div>

    {{-- pagination bawaan Laravel (hidden — DataTables handle) --}}
    {{-- <div class="mt-3">{{ $reports->links() }}</div> --}}

</div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/src/table/datatable/datatables.js') }}"></script>
<script src="{{ asset('plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>
<script>
(function ($) {
    'use strict';

    const Toast = Swal.mixin({
        toast: true, position: 'top-end',
        showConfirmButton: false, timer: 3500, timerProgressBar: true
    });

    // DataTable — client-side (data sudah di-render oleh Blade)
    const table = $('#kpiTable').DataTable({
        dom: '<"dt--top-section"<"row"<"col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center"l><"col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3"f>>><"table-responsive"tr><"dt--bottom-section d-sm-flex justify-content-sm-between text-center"<"dt--pages-count mb-sm-0 mb-3"i><"dt--pagination"p>>',
        oLanguage: {
            oPaginate: {
                sPrevious: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>',
                sNext:     '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>'
            },
            sInfo: 'Showing _START_–_END_ of _TOTAL_',
            sSearch: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
            sSearchPlaceholder: 'Cari...', sLengthMenu: 'Tampil: _MENU_'
        },
        stripeClasses: [], lengthMenu: [10, 25, 50, 100], pageLength: 25,
        columnDefs: [{ orderable: false, targets: -1 }]
    });

    // ── Custom filter dropdowns ──────────────────────────────────────────
    function applyFilters() {
        const company = $('#filter_company').val()  || '';
        const period  = $('#filter_period').val()   || '';
        const status  = $('#filter_status').val()   || '';

        // Filter via DataTables search (client-side fallback)
        // If server-side is preferred, redirect with query params instead:
        const params = new URLSearchParams(window.location.search);
        if (company) params.set('company_id', company); else params.delete('company_id');
        if (period)  params.set('period', period);      else params.delete('period');
        if (status)  params.set('status', status);      else params.delete('status');
        window.location.href = window.location.pathname + '?' + params.toString();
    }

    $('#filter_company, #filter_period, #filter_status').on('change', applyFilters);

    $('#resetFilters').on('click', function () {
        window.location.href = '{{ route("kpi-hsse.index") }}';
    });

    // ── Delete dengan SweetAlert ─────────────────────────────────────────
    $(document).on('click', '.delete-record', function () {
        const url   = $(this).data('url');
        const label = $(this).data('label');

        Swal.fire({
            title: 'Hapus Laporan?',
            html:  '<strong>' + label + '</strong><br><span class="text-danger" style="font-size:.85rem;">Semua data termasuk lampiran akan dihapus <u>permanen</u> dan tidak dapat dipulihkan.</span>',
            icon:  'warning',
            showCancelButton:    true,
            confirmButtonColor:  '#e7515a',
            cancelButtonColor:   '#3b3f5c',
            confirmButtonText:   'Ya, hapus permanen!',
            cancelButtonText:    'Batal'
        }).then(result => {
            if (result.isConfirmed) {
                // Submit form DELETE via fetch
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: '_method=DELETE'
                })
                .then(res => res.ok ? res : Promise.reject(res))
                .then(() => {
                    Toast.fire({ icon: 'success', title: 'Laporan berhasil dihapus.' });
                    setTimeout(() => window.location.reload(), 1500);
                })
                .catch(() => {
                    Toast.fire({ icon: 'error', title: 'Gagal menghapus laporan.' });
                });
            }
        });
    });

}(jQuery));
</script>
@endpush
