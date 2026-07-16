@extends('layouts.app')
@section('title', 'HSSE On Board Evaluation')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/table/datatable/datatables.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/css/light/table/datatable/dt-global_style.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/css/dark/table/datatable/dt-global_style.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .filter-section {
        background: var(--card-bg);
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 24px;
        border: 1px solid var(--card-border-color);
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }
    .filter-section .form-label {
        font-weight: 700;
        font-size: 14px;
        color: var(--text-color);
        margin-bottom: 8px;
    }
    .page-action-buttons .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        padding: 10px 20px;
        box-shadow: 0 4px 6px rgba(50,50,93,0.11), 0 1px 3px rgba(0,0,0,0.08);
        transition: all 0.15s ease;
    }
    .page-action-buttons .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 7px 14px rgba(50,50,93,0.1), 0 3px 6px rgba(0,0,0,0.08);
    }
    div.dataTables_wrapper div.dataTables_filter {
        text-align: right;
        margin-bottom: 1rem;
    }
    div.dataTables_wrapper div.dataTables_filter input {
        margin-left: 0.5em;
        display: inline-block;
        width: auto;
        border-radius: 6px;
        padding: 8px 12px;
        border: 1px solid #e0e6ed;
    }
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">

        {{-- ── Header & Tombol Aksi ────────────────────────────── --}}
        <div class="row layout-top-spacing mb-4 align-items-center">
            <div class="col-md-6">
                <h3 class="m-0 fw-bold">HSSE On Board Evaluation</h3>
                <p class="text-muted mb-0 small">Daftar seluruh evaluasi kompetensi kru kapal.</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <div class="page-action-buttons d-flex justify-content-md-end gap-2 flex-wrap">

                    {{-- Dashboard: semua role --}}
                    <a href="{{ route('hsse-evaluation.dashboard') }}" class="btn btn-outline-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        Dashboard
                    </a>

                    {{-- Export Excel: semua role (koordinator di-scope ke company sendiri) --}}
                    <button type="button" class="btn btn-success" id="btnExport">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="12" y1="18" x2="12" y2="12"></line>
                            <polyline points="9 15 12 18 15 15"></polyline>
                        </svg>
                        Export Excel
                    </button>

                    {{-- Evaluasi Baru: HANYA super-admin & hsse --}}
                    @if($canManage)
                    <a href="{{ route('hsse-evaluation.create') }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Evaluasi Baru
                    </a>
                    @endif

                </div>
            </div>
        </div>

        {{-- ── Filter ───────────────────────────────────────────── --}}
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="filter-section">
                <div class="row g-3">

                    <div class="col-lg-3 col-md-6">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="text" class="form-control flatpickr" id="start_date" placeholder="YYYY-MM-DD">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="text" class="form-control flatpickr" id="end_date" placeholder="YYYY-MM-DD">
                    </div>

                    {{-- Filter Company & Vessel: hanya super-admin & hsse --}}
                    @if($canManage)
                    <div class="col-lg-2 col-md-6">
                        <label for="filter_company" class="form-label">Company</label>
                        <select class="form-select" id="filter_company">
                            <option value="">All Companies</option>
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label for="filter_vessel" class="form-label">Vessel</label>
                        <select class="form-select" id="filter_vessel">
                            <option value="">All Vessels</option>
                            @foreach($vessels as $v)
                                <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    {{-- Koordinator: filter vessel dari perusahaan sendiri --}}
                    <div class="col-lg-3 col-md-6">
                        <label for="filter_vessel" class="form-label">Vessel</label>
                        <select class="form-select" id="filter_vessel">
                            <option value="">All Vessels</option>
                            @foreach($vessels as $v)
                                <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="col-lg-2 col-md-6">
                        <label for="filter_status" class="form-label">Status</label>
                        <select class="form-select" id="filter_status">
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="submitted">Submitted</option>
                        </select>
                    </div>

                </div>
                <div class="row mt-3 align-items-center">
                    <div class="col-auto">
                        <button type="button" class="btn btn-outline-secondary" id="resetFilters">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round" class="me-2">
                                <polyline points="23 4 23 10 17 10"></polyline>
                                <polyline points="1 20 1 14 7 14"></polyline>
                                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                            </svg>
                            Reset Filters
                        </button>
                    </div>
                    <div class="col-auto">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Export Excel: pilih <strong>Start Date &amp; End Date</strong> (maks. 7 hari)
                            lalu klik <strong>Export Excel</strong>.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── DataTable ────────────────────────────────────────── --}}
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="table-responsive">
                    <table id="eval-table" class="table dt-table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Tanggal</th>
                                <th>Nama Kru</th>
                                <th>Jabatan</th>
                                <th>Kapal</th>
                                <th>Company</th>
                                <th class="text-center">Score</th>
                                <th class="text-center">Kategori</th>
                                <th class="text-center">Status</th>
                                <th>Assessor</th>
                                <th class="text-center no-content" width="12%">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/src/table/datatable/datatables.js') }}"></script>
<script src="{{ asset('plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
(function ($) {
    'use strict';

    var Toast = Swal.mixin({
        toast: true, position: 'top-end', showConfirmButton: false,
        timer: 3500, timerProgressBar: true,
        didOpen: function (t) {
            t.addEventListener('mouseenter', Swal.stopTimer);
            t.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    // Flatpickr
    $('.flatpickr').flatpickr({ dateFormat: 'Y-m-d', allowInput: true });

    // DataTable
    var table = $('#eval-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("hsse-evaluation.index") }}',
            data: function (d) {
                d.start_date = $('#start_date').val();
                d.end_date   = $('#end_date').val();
                d.company_id = $('#filter_company').val() || '';
                d.vessel_id  = $('#filter_vessel').val()  || '';
                d.status     = $('#filter_status').val()  || '';
            }
        },
        columns: [
            { data: 'DT_RowIndex',       name: 'DT_RowIndex',    orderable: false, searchable: false },
            { data: 'evaluated_date',    name: 'evaluated_date' },
            { data: 'crew_name',         name: 'crew_name' },
            { data: 'crew_position',     name: 'crew_position',  defaultContent: '-' },
            { data: 'vessel_name',       name: 'vessel.name' },
            { data: 'company_name',      name: 'company.name' },
            {
                data: 'total_score',
                name: 'total_score',
                className: 'text-center',
                render: function (data) {
                    if (!data) return '<span class="text-muted">—</span>';
                    return '<strong>' + data + '</strong>';
                }
            },
            {
                data: 'score_category',
                name: 'score_category',
                className: 'text-center',
                orderable: false,
                searchable: false,
                render: function (data) {
                    if (!data) return '<span class="text-muted">—</span>';
                    var map   = { baik: 'success', cukup: 'warning', kurang: 'danger' };
                    var color = map[data] || 'secondary';
                    var label = data.charAt(0).toUpperCase() + data.slice(1);
                    return '<span class="badge bg-' + color + '">' + label + '</span>';
                }
            },
            {
                data: 'status',
                name: 'status',
                className: 'text-center',
                render: function (data) {
                    var color = data === 'submitted' ? 'success' : 'secondary';
                    var label = data ? (data.charAt(0).toUpperCase() + data.slice(1)) : '-';
                    return '<span class="badge bg-' + color + '">' + label + '</span>';
                }
            },
            { data: 'assessor_name_col', name: 'assessor_name' },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-center'
            }
        ],
        dom: '<"dt--top-section"<"row"<"col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center"l><"col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3"f>>><"table-responsive"tr><"dt--bottom-section d-sm-flex justify-content-sm-between text-center"<"dt--pages-count mb-sm-0 mb-3"i><"dt--pagination"p>>',
        oLanguage: {
            oPaginate: {
                sPrevious: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
                sNext:     '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>'
            },
            sInfo:            'Showing page _PAGE_ of _PAGES_',
            sSearch:          '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
            sSearchPlaceholder: 'Search...',
            sLengthMenu:      'Results : _MENU_'
        },
        stripeClasses: [],
        lengthMenu: [7, 10, 20, 50],
        pageLength: 10,
        order: [[1, 'desc']]
    });

    // Filter change → reload table
    $('#start_date, #end_date, #filter_company, #filter_vessel, #filter_status').on('change', function () {
        table.draw();
    });

    // Reset filters
    $('#resetFilters').on('click', function () {
        $('#start_date, #end_date').val('');
        $('#filter_company, #filter_vessel, #filter_status').val('');
        table.draw();
    });

    // ── Export Excel ──────────────────────────────────────────
    $('#btnExport').on('click', function () {
        var start = $('#start_date').val();
        var end   = $('#end_date').val();

        if (!start || !end) {
            Swal.fire({
                icon: 'warning',
                title: 'Tanggal Wajib Diisi',
                html: 'Pilih <strong>Start Date</strong> dan <strong>End Date</strong> terlebih dahulu.<br>' +
                      '<small class="text-muted">Maksimal rentang 7 hari.</small>',
                confirmButtonColor: '#1d4ed8'
            });
            return;
        }

        var diffDay = (new Date(end) - new Date(start)) / (1000 * 60 * 60 * 24);
        if (diffDay > 7) {
            Swal.fire({
                icon: 'error',
                title: 'Rentang Terlalu Lama',
                text: 'Maksimal rentang export adalah 7 hari.',
                confirmButtonColor: '#e7515a'
            });
            return;
        }

        var params = new URLSearchParams({
            start_date: start,
            end_date:   end,
            company_id: $('#filter_company').val() || '',
            vessel_id:  $('#filter_vessel').val()  || '',
            status:     $('#filter_status').val()  || '',
        });

        Toast.fire({ icon: 'info', title: 'Memproses export Excel...' });
        window.location.href = '{{ route("hsse-evaluation.export-excel") }}?' + params.toString();
    });

    // ── Delete (hard delete) ──────────────────────────────────
    $(document).on('click', '.delete-eval', function (e) {
        e.preventDefault();
        var url  = $(this).data('url');
        var name = $(this).data('name');

        Swal.fire({
            title: 'Hapus Permanen?',
            html:  'Evaluasi kru <strong>' + name + '</strong> akan dihapus <strong>permanen</strong> ' +
                   'beserta semua data penilaian dan tanda tangan.<br>' +
                   '<small class="text-danger">Tindakan ini tidak dapat dibatalkan.</small>',
            icon:  'warning',
            showCancelButton:    true,
            confirmButtonColor:  '#e7515a',
            cancelButtonColor:   '#3b3f5c',
            confirmButtonText:   'Ya, hapus permanen!',
            cancelButtonText:    'Batal'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url:  url,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (r) {
                        if (r.success) {
                            table.draw();
                            Toast.fire({ icon: 'success', title: r.message });
                        } else {
                            Toast.fire({ icon: 'error', title: r.message });
                        }
                    },
                    error: function () {
                        Toast.fire({ icon: 'error', title: 'Gagal menghapus data.' });
                    }
                });
            }
        });
    });

}(jQuery));
</script>
@endpush
