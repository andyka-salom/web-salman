@extends('layouts.app')

@section('title', 'Monitoring Action Items')

@push('styles')
    <link rel="stylesheet" href="{{ asset('plugins/src/table/datatable/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/css/light/table/datatable/dt-global_style.css') }}">
    <style>
        .stat-card { transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-3px); }
        .progress-thin { height: 6px; }
        /* Styling untuk Modal Foto */
        .photo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px; margin-top: 10px; }
        .photo-item { width: 100%; height: 100px; object-fit: cover; border-radius: 6px; cursor: pointer; border: 1px solid #ddd; }
        .photo-item:hover { opacity: 0.8; }
    </style>
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">

        {{-- HEADER & FILTER --}}
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="fw-bold mb-3 text-primary">Monitoring Action Items</h4>
                    <form action="{{ route('cermat.action-monitoring.index') }}" method="GET">
                        <div class="row g-3">
                            <div class="col-md-3 col-lg-2">
                                <label class="form-label small fw-bold">Perusahaan</label>
                                <select name="company_id" class="form-select">
                                    <option value="">Semua Perusahaan</option>
                                    @foreach($companies as $comp)
                                        <option value="{{ $comp->id }}" {{ request('company_id') == $comp->id ? 'selected' : '' }}>
                                            {{ $comp->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-lg-2">
                                <label class="form-label small fw-bold">Fungsi / Departemen</label>
                                <select name="entity_function_id" class="form-select">
                                    <option value="">Semua Fungsi</option>
                                    @foreach($entityFunctions as $func)
                                        <option value="{{ $func->id }}" {{ request('entity_function_id') == $func->id ? 'selected' : '' }}>
                                            {{ $func->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-lg-2">
                                <label class="form-label small fw-bold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="do" {{ request('status') == 'do' ? 'selected' : '' }}>To Do</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cannot_do" {{ request('status') == 'cannot_do' ? 'selected' : '' }}>Cannot Do</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-lg-4">
                                <label class="form-label small fw-bold">Tanggal Pembuatan</label>
                                <div class="input-group">
                                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                                    <span class="input-group-text">s/d</span>
                                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-12 col-lg-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 fw-bold">
                                    <i class="bi bi-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- STATISTIC CARDS --}}
        <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 mb-4">
            <div class="card bg-primary text-white stat-card h-100">
                <div class="card-body">
                    <h6 class="text-uppercase mb-2 text-white-50 small fw-bold">Total Actions</h6>
                    <h2 class="mb-0 text-white">{{ $stats->total }}</h2>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 mb-4">
            <div class="card bg-white border-left-secondary stat-card h-100 shadow-sm" style="border-left: 4px solid #6c757d;">
                <div class="card-body">
                    <h6 class="text-uppercase mb-2 text-muted small fw-bold">To Do</h6>
                    <h2 class="mb-0 text-secondary">{{ $stats->status_do }}</h2>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 mb-4">
            <div class="card bg-white border-left-info stat-card h-100 shadow-sm" style="border-left: 4px solid #0dcaf0;">
                <div class="card-body">
                    <h6 class="text-uppercase mb-2 text-muted small fw-bold">In Progress</h6>
                    <h2 class="mb-0 text-info">{{ $stats->status_progress }}</h2>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 mb-4">
            <div class="card bg-white border-left-success stat-card h-100 shadow-sm" style="border-left: 4px solid #198754;">
                <div class="card-body">
                    <h6 class="text-uppercase mb-2 text-muted small fw-bold">Completed</h6>
                    <h2 class="mb-0 text-success">{{ $stats->status_completed }}</h2>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 mb-4">
            <div class="card bg-white border-left-danger stat-card h-100 shadow-sm" style="border-left: 4px solid #dc3545;">
                <div class="card-body">
                    <h6 class="text-uppercase mb-2 text-danger small fw-bold">Overdue</h6>
                    <h2 class="mb-0 text-danger">{{ $stats->overdue }}</h2>
                    <small class="text-muted">Target terlewati</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 mb-4">
            <div class="card bg-light stat-card h-100 shadow-sm">
                <div class="card-body">
                    <h6 class="text-uppercase mb-2 text-muted small fw-bold">Completion Rate</h6>
                    @php $rate = $stats->total > 0 ? round(($stats->status_completed / $stats->total) * 100) : 0; @endphp
                    <h2 class="mb-0">{{ $rate }}%</h2>
                    <div class="progress mt-2 progress-thin">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $rate }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABS CONTENT --}}
        <div class="col-12">
            <div class="widget-content widget-content-area br-8 p-0">
                <div class="simple-pill">
                    <ul class="nav nav-pills mb-3 p-3 bg-white rounded shadow-sm" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-entity-tab" data-bs-toggle="pill" data-bs-target="#pills-entity" type="button" role="tab">
                                <i class="bi bi-building me-1"></i> Per Fungsi (PIC)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-company-tab" data-bs-toggle="pill" data-bs-target="#pills-company" type="button" role="tab">
                                <i class="bi bi-buildings me-1"></i> Per Perusahaan
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-list-tab" data-bs-toggle="pill" data-bs-target="#pills-list" type="button" role="tab">
                                <i class="bi bi-list-task me-1"></i> Detail Daftar
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content px-3 pb-3" id="pills-tabContent">

                        {{-- TAB 1: PER ENTITY FUNCTION --}}
                        <div class="tab-pane fade show active" id="pills-entity" role="tabpanel">
                            <div class="table-responsive bg-white rounded shadow-sm p-3">
                                <table class="table table-hover table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fungsi / Departemen</th>
                                            <th class="text-center">Total Actions</th>
                                            <th class="text-center">Completed</th>
                                            <th class="text-center">Overdue</th>
                                            <th class="text-center">% Selesai</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($byEntity as $row)
                                            <tr>
                                                <td class="fw-bold">{{ $row->entity_name ?? 'Tidak Ada Fungsi' }}</td>
                                                <td class="text-center">{{ $row->total }}</td>
                                                <td class="text-center text-success">{{ $row->completed }}</td>
                                                <td class="text-center text-danger">{{ $row->overdue }}</td>
                                                <td class="text-center">
                                                    @php $p = $row->total > 0 ? round(($row->completed / $row->total) * 100) : 0; @endphp
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <span class="me-2 text-xs fw-bold">{{ $p }}%</span>
                                                        <div class="progress progress-thin w-50">
                                                            <div class="progress-bar {{ $p < 50 ? 'bg-warning' : 'bg-success' }}" style="width: {{ $p }}%"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted">Tidak ada data</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- TAB 2: PER PERUSAHAAN --}}
                        <div class="tab-pane fade" id="pills-company" role="tabpanel">
                            <div class="table-responsive bg-white rounded shadow-sm p-3">
                                <table class="table table-hover table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nama Perusahaan</th>
                                            <th class="text-center">Total Actions</th>
                                            <th class="text-center">Completed</th>
                                            <th class="text-center">Overdue</th>
                                            <th class="text-center">% Selesai</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($byCompany as $row)
                                            <tr>
                                                <td class="fw-bold">{{ $row->company_name ?? 'Internal / Tidak Diketahui' }}</td>
                                                <td class="text-center">{{ $row->total }}</td>
                                                <td class="text-center text-success">{{ $row->completed }}</td>
                                                <td class="text-center text-danger">{{ $row->overdue }}</td>
                                                <td class="text-center">
                                                    @php $p = $row->total > 0 ? round(($row->completed / $row->total) * 100) : 0; @endphp
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <span class="me-2 text-xs fw-bold">{{ $p }}%</span>
                                                        <div class="progress progress-thin w-50">
                                                            <div class="progress-bar {{ $p < 50 ? 'bg-warning' : 'bg-success' }}" style="width: {{ $p }}%"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted">Tidak ada data</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- TAB 3: DETAIL LIST (DATATABLES) - DENGAN TOMBOL EXPORT --}}
                        <div class="tab-pane fade" id="pills-list" role="tabpanel">
                            <div class="bg-white rounded shadow-sm p-3">
                                {{-- Header dengan Tombol Export --}}
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                    <div>
                                        <h5 class="mb-1 fw-bold text-primary">
                                            <i class="bi bi-list-task me-2"></i>Detail Daftar Action Items
                                        </h5>
                                        <p class="text-muted small mb-0">Daftar lengkap semua tindakan berdasarkan filter yang dipilih</p>
                                    </div>
                                    <a href="{{ route('cermat.action-monitoring.export', request()->all()) }}"
                                       class="btn btn-success d-flex align-items-center gap-2"
                                       title="Download data ke file Excel">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                                        </svg>
                                        <span class="fw-bold">Export ke Excel</span>
                                    </a>
                                </div>

                                {{-- Tabel DataTables --}}
                                <div class="table-responsive">
                                    <table id="actions-table" class="table dt-table-hover w-100">
                                        <thead class="bg-light">
                                            <tr>
                                                <th style="width: 25%">Deskripsi Tindakan</th>
                                                <th>PIC</th>
                                                <th>Fungsi</th>
                                                <th>Perusahaan</th>
                                                <th>Target</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Foto</th>
                                                <th class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- MODAL DETAIL ACTION --}}
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Detail Tindakan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light-primary border-0 mb-3" role="alert">
                    <small class="text-muted d-block text-uppercase fw-bold">No. Laporan</small>
                    <span id="modal-report-number" class="fw-bold h6 text-primary"></span>
                    <a href="#" id="modal-report-link" target="_blank" class="float-end small text-decoration-none">Buka Laporan <i class="bi bi-box-arrow-up-right"></i></a>
                </div>

                <div class="mb-3">
                    <label class="fw-bold text-muted small">Deskripsi Tindakan</label>
                    <div class="p-3 bg-light rounded" id="modal-desc"></div>
                </div>

                <div class="mb-3">
                    <label class="fw-bold text-muted small">Catatan Penyelesaian</label>
                    <div class="p-3 bg-light rounded" id="modal-notes"></div>
                </div>

                <div class="mb-3">
                    <label class="fw-bold text-muted small">Bukti Foto</label>
                    <div id="modal-photos" class="photo-grid"></div>
                    <div id="modal-no-photos" class="text-muted small fst-italic d-none">Tidak ada foto bukti.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('plugins/src/table/datatable/datatables.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);

        // --- 1. INISIALISASI DATATABLE ---
        let table = $('#actions-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('cermat.action-monitoring.data') }}",
                data: function(d) {
                    d.company_id = urlParams.get('company_id');
                    d.entity_function_id = urlParams.get('entity_function_id');
                    d.status = urlParams.get('status');
                    d.start_date = urlParams.get('start_date');
                    d.end_date = urlParams.get('end_date');
                }
            },
            columns: [
                { data: 'description', name: 'description', render: function(data){
                    return data.length > 60 ? data.substr(0, 60) + '...' : data;
                }},
                { data: 'responsible_name', name: 'responsible.name' },
                { data: 'entity_name', name: 'responsible.entityFunction.name' },
                { data: 'company_name', name: 'responsible.company.name' },
                { data: 'target_date', name: 'current_target_date' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'photos_count', name: 'photos_count', className: 'text-center', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
            ],
            order: [[4, 'asc']], // Sort by Target Date
            dom: "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'l><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'f>>>" +
                "<'table-responsive'tr>" +
                "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count mb-sm-0 mb-3'i><'dt--pagination'p>>",
            oLanguage: {
                oPaginate: {
                    sPrevious: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
                    sNext: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>'
                },
                sInfo: "Showing page _PAGE_ of _PAGES_",
                sSearch: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
                sSearchPlaceholder: "Cari Tindakan...",
                sLengthMenu: "Results : _MENU_",
            },
            stripeClasses: [],
            lengthMenu: [7, 10, 20, 50],
            pageLength: 10
        });

        // --- 2. LOGIKA MODAL DETAIL ---
        const detailModal = new bootstrap.Modal(document.getElementById('detailModal'));

        // Menggunakan Event Delegation karena tombol render dinamis
        document.querySelector('#actions-table tbody').addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-view-detail');
            if (btn) {
                const data = JSON.parse(btn.getAttribute('data-json'));

                // Isi Konten Modal
                document.getElementById('modal-report-number').textContent = data.report_number;
                document.getElementById('modal-report-link').href = data.report_url;
                document.getElementById('modal-desc').innerText = data.description || '-';
                document.getElementById('modal-notes').innerText = data.completion_notes || '-';

                // Render Foto
                const photoContainer = document.getElementById('modal-photos');
                const noPhotoMsg = document.getElementById('modal-no-photos');
                photoContainer.innerHTML = '';

                if (data.photos && data.photos.length > 0) {
                    noPhotoMsg.classList.add('d-none');
                    data.photos.forEach(url => {
                        const img = document.createElement('img');
                        img.src = url;
                        img.className = 'photo-item';
                        img.onclick = () => window.open(url, '_blank');
                        photoContainer.appendChild(img);
                    });
                } else {
                    noPhotoMsg.classList.remove('d-none');
                }

                detailModal.show();
            }
        });
    });
</script>
@endpush
