


<?php $__env->startSection('title', 'Daftar Laporan CeRMAT'); ?>

<?php $__env->startPush('styles'); ?>
    
    <link rel="stylesheet" href="<?php echo e(asset('plugins/src/table/datatable/datatables.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('plugins/css/light/table/datatable/dt-global_style.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('plugins/css/dark/table/datatable/dt-global_style.css')); ?>">

    <style>
        /* Tab Navigation Styling */
        .nav-pills .nav-link {
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            white-space: nowrap;
            /* Pastikan flexbox bekerja untuk alignment badge */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Container Tab agar bisa di-scroll horizontal pada layar sangat kecil */
        .nav-scroller {
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 5px;
        }

        /* Custom Status Badges */
        .badge-light-success { background-color: #d1f3d1; color: #0e6245; border: 1px solid #c3e6cb; }
        .badge-light-danger { background-color: #ffe0e0; color: #d93025; border: 1px solid #f5c6cb; }
        .badge-light-warning { background-color: #fff4cc; color: #965a00; border: 1px solid #ffeeba; }
        .badge-light-info { background-color: #d0e9ff; color: #014c8c; border: 1px solid #b8daff; }
        .badge-light-primary { background-color: #e0e7ff; color: #3730a3; border: 1px solid #d6d8db; }
        .badge-light-secondary { background-color: #f1f1f1; color: #6b7280; border: 1px solid #d6d8db; }
        .badge-light-dark { background-color: #e2e8f0; color: #1e293b; border: 1px solid #cbd5e1; }

        /* Table Tweaks */
        table.dataTable thead > tr > th {
            white-space: nowrap;
        }

        /* Mobile Tweaks */
        @media (max-width: 767px) {
            .btn-action-group {
                width: 100%;
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            .btn-action-group .btn {
                width: 100%;
            }
            /* Align tabs left on mobile */
            .nav-pills .nav-link {
                justify-content: flex-start;
            }
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">
        <div class="col-12">

            
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
                <div class="text-center text-md-start">
                    <h2 class="mb-1 fw-bold">Laporan CeRMAT</h2>
                    <p class="text-muted mb-0">Review, kelola, dan lacak semua laporan observasi keselamatan.</p>
                </div>

                
                <div class="btn-action-group d-flex gap-2 align-items-center">
                    
                    <?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'super-admin|hsse|koordinator|rses')): ?>
                    <button type="button" class="btn btn-success rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Export Excel
                    </button>
                    <?php endif; ?>

                    <a href="<?php echo e(route('cermat.reports.create')); ?>" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Buat Laporan
                    </a>
                </div>
            </div>

            
            <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <strong>Berhasil!</strong><span class="ms-1"><?php echo e(session('success')); ?></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                <strong>Gagal!</strong> <?php echo e(session('error')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            
            <div class="widget-content widget-content-area br-8 p-4">

                
                <div class="nav-scroller mb-4">
                    <ul class="nav nav-pills d-flex flex-nowrap flex-md-wrap gap-2" id="cermat-tabs" role="tablist">

                        
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" data-bs-toggle="tab" href="#table-container" role="tab" data-scope="my_reports">
                                Laporan Saya
                            </a>
                        </li>

                        
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#table-container" role="tab" data-scope="verification">
                                <span>Verifikasi (SPV)</span>
                                <?php if(isset($supervisorPendingCount) && $supervisorPendingCount > 0): ?>
                                    <span class="badge bg-danger ms-2 rounded-pill px-2 py-1" style="font-size: 0.75rem;">
                                        <?php echo e($supervisorPendingCount); ?>

                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>

                        
                        <?php if (\Illuminate\Support\Facades\Blade::check('role', 'hsse')): ?>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#table-container" role="tab" data-scope="review">
                                <span>Review (HSSE)</span>
                                <?php if(isset($hssePendingCount) && $hssePendingCount > 0): ?>
                                    <span class="badge bg-danger ms-2 rounded-pill px-2 py-1" style="font-size: 0.75rem;">
                                        <?php echo e($hssePendingCount); ?>

                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php endif; ?>

                        
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#table-container" role="tab" data-scope="action_items">
                                <span>Tugas Tindakan</span>
                                <?php if(isset($myActionCount) && $myActionCount > 0): ?>
                                    <span class="badge bg-warning text-dark ms-2 rounded-pill px-2 py-1" style="font-size: 0.75rem;">
                                        <?php echo e($myActionCount); ?>

                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>

                        
                        <?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'super-admin|hsse|rses|koordinator')): ?>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#table-container" role="tab" data-scope="all">
                                Semua Data
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>

                
                <div id="filter-section" class="mb-4" style="display: none;">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row g-3">
                                
                                <?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'super-admin|hsse|rses')): ?>
                                <div class="col-md-4">
                                    <label for="filter-company" class="form-label fw-semibold">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                                        Perusahaan
                                    </label>
                                    <select id="filter-company" class="form-select">
                                        <option value="">Semua Perusahaan</option>
                                        <?php $__currentLoopData = \App\Models\Company::orderBy('name')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($company->id); ?>"><?php echo e($company->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <?php endif; ?>

                                
                                <div class="col-md-4">
                                    <label for="filter-date-start" class="form-label fw-semibold">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                        Dari Tanggal
                                    </label>
                                    <input type="date" id="filter-date-start" class="form-control">
                                </div>

                                <div class="col-md-4">
                                    <label for="filter-date-end" class="form-label fw-semibold">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                        Sampai Tanggal
                                    </label>
                                    <input type="date" id="filter-date-end" class="form-control">
                                </div>

                                
                                <div class="col-md-4">
                                    <label for="filter-status" class="form-label fw-semibold">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                        Status Laporan
                                    </label>
                                    <select id="filter-status" class="form-select">
                                        <option value="">Semua Status</option>
                                        <option value="draft">Draft</option>
                                        <option value="in_review">In Review</option>
                                        <option value="action_in_progress">Action In Progress</option>
                                        <option value="awaiting_closeout">Awaiting Closeout</option>
                                        <option value="closed">Closed</option>
                                        <option value="no_action_required">No Action Required</option>
                                    </select>
                                </div>

                                
                                <div class="col-md-8 d-flex align-items-end gap-2">
                                    <button type="button" id="btn-apply-filter" class="btn btn-primary px-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        Terapkan Filter
                                    </button>
                                    <button type="button" id="btn-reset-filter" class="btn btn-light px-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polyline points="1 4 1 10 7 10"></polyline><polyline points="23 20 23 14 17 14"></polyline><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"></path></svg>
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="table-responsive">
                    <table id="cermat-reports-table" class="table dt-table-hover w-100">
                        <thead>
                            <tr>
                                <th>No. Laporan</th>
                                <th>Tgl Kejadian</th>
                                <th class="text-center">P-horse</th>
                                <th>Detail Singkat</th>
                                <th>Area</th>
                                <th>Pelapor</th>
                                <th class="text-center">Status</th>
                                <th class="text-center no-sort">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'super-admin|hsse|koordinator|rses')): ?>
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="exportModalLabel">Export Data Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo e(route('cermat.reports.export')); ?>" method="GET">
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Pilih rentang tanggal laporan.
                        <?php if (\Illuminate\Support\Facades\Blade::check('role', 'koordinator')): ?>
                            Data yang diexport terbatas pada perusahaan Anda.
                        <?php else: ?>
                            Data dari seluruh perusahaan akan diexport.
                        <?php endif; ?>
                    </p>

                    <div class="mb-3">
                        <label for="start_date" class="form-label fw-bold">Dari Tanggal</label>
                        <input type="date" class="form-control" name="start_date" id="start_date"
                               value="<?php echo e(now()->startOfMonth()->format('Y-m-d')); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="end_date" class="form-label fw-bold">Sampai Tanggal</label>
                        <input type="date" class="form-control" name="end_date" id="end_date"
                               value="<?php echo e(now()->format('Y-m-d')); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Download Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('plugins/src/table/datatable/datatables.js')); ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // SVG Icons untuk Pagination
        const iconNext = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>';
        const iconPrev = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>';

        let table = $('#cermat-reports-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: {
                url: "<?php echo e(route('cermat.reports.data')); ?>",
                data: function (d) {
                    // Mengirim parameter scope tab yang aktif ke controller
                    d.scope = $('#cermat-tabs .nav-link.active').data('scope');

                    // Mengirim parameter filter jika tab "all" aktif
                    if (d.scope === 'all') {
                        d.company_id = $('#filter-company').val();
                        d.date_start = $('#filter-date-start').val();
                        d.date_end = $('#filter-date-end').val();
                        d.status = $('#filter-status').val();
                    }
                },
                error: function (xhr, error, code) {
                    console.log('DataTables Error:', error);
                }
            },
            columns: [
                { data: 'report_number', name: 'report_number', className: 'fw-bold' },
                { data: 'report_datetime', name: 'report_datetime' },
                { 
                    data: 'synergi_register_no', 
                    name: 'synergi_register_no', 
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (!data) return '<span class="badge badge-light-danger fw-medium">Belum Ada</span>';
                        return `<span class="badge badge-light-success fw-bold">${data}</span>`;
                    }
                },
                {
                    data: 'details',
                    name: 'details',
                    render: function(data, type, row) {
                        if (!data) return '<span class="text-muted">-</span>';
                        let text = $('<div>').text(data).html();
                        if (text.length > 40) {
                            return `<span title="${text}" data-bs-toggle="tooltip">${text.substr(0, 40)}...</span>`;
                        }
                        return text;
                    }
                },
                { data: 'area.name', name: 'area.name', defaultContent: '<span class="text-muted">-</span>' },
                { data: 'reporter.name', name: 'reporter.name', defaultContent: '<span class="text-muted">-</span>' },
                { data: 'status_html', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', className: 'text-center', orderable: false, searchable: false }
            ],
            order: [[1, 'desc']],
            dom: "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'l><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'f>>>" +
                "<'table-responsive'tr>" +
                "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count mb-sm-0 mb-3'i><'dt--pagination'p>>",
            oLanguage: {
                sProcessing: '<div class="spinner-border text-primary align-self-center"></div>',
                sSearch: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
                sSearchPlaceholder: "Cari No. Laporan, P-horse, Area...",
                sLengthMenu: "Tampilkan _MENU_ data",
                sInfo: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                sInfoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                sInfoFiltered: "(disaring dari _MAX_ total entri)",
                sZeroRecords: "Tidak ada data yang cocok ditemukan",
                oPaginate: {
                    sPrevious: iconPrev,
                    sNext: iconNext
                }
            },
            stripeClasses: [],
            lengthMenu: [10, 20, 50, 100],
            pageLength: 10,
            drawCallback: function() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })
            }
        });

        // Event Listener untuk Tab Filter
        const tabEls = document.querySelectorAll('a[data-bs-toggle="tab"]');
        tabEls.forEach(function(tabEl) {
            tabEl.addEventListener('shown.bs.tab', function (event) {
                const scope = $(event.target).data('scope');

                // Show/hide filter section based on scope
                if (scope === 'all') {
                    $('#filter-section').slideDown(300);
                } else {
                    $('#filter-section').slideUp(300);
                }

                table.ajax.reload();
            });
        });

        // Event Listener untuk tombol Apply Filter
        $('#btn-apply-filter').on('click', function() {
            table.ajax.reload();
        });

        // Event Listener untuk tombol Reset Filter
        $('#btn-reset-filter').on('click', function() {
            $('#filter-company').val('');
            $('#filter-date-start').val('');
            $('#filter-date-end').val('');
            $('#filter-status').val('');
            table.ajax.reload();
        });

        // Enter key pada filter input
        $('#filter-date-start, #filter-date-end').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                table.ajax.reload();
            }
        });
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/cermat/index.blade.php ENDPATH**/ ?>