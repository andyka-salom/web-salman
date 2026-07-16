


<?php $__env->startSection('title', 'Campaign Salman Reports'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/src/table/datatable/datatables.css')); ?>">
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/css/light/table/datatable/dt-global_style.css')); ?>">
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/css/dark/table/datatable/dt-global_style.css')); ?>">
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/src/sweetalerts2/sweetalerts2.css')); ?>">
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
        box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
        transition: all 0.15s ease;
    }
    .page-action-buttons .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
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
    /* Loading overlay */
    #export-loading-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }
    #export-loading-overlay.show { display: flex; }
    .export-spinner-box {
        background: #fff;
        border-radius: 12px;
        padding: 36px 48px;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        min-width: 300px;
    }
    .export-spinner-box p { margin: 14px 0 4px; font-weight: 700; color: #1a1a2e; font-size: 15px; }
    .export-spinner-box small { color: #888; font-size: 12px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">

        
        <div class="row layout-top-spacing mb-4 align-items-center">
            <div class="col-md-6">
                <h3 class="m-0 fw-bold">Campaign Salman Reports</h3>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <div class="page-action-buttons d-flex justify-content-md-end gap-2 flex-wrap">
                    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'super-admin|hsse')): ?>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportZipModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line>
                        </svg>
                        Export ZIP
                    </button>
                    <button type="button" class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#exportExcelModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        Export Excel
                    </button>
                    <?php endif; ?>
                    <a href="<?php echo e(route('campaign-salman.create')); ?>" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        New Report
                    </a>
                </div>
            </div>
        </div>

        
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
                    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'super-admin|hsse')): ?>
                    <div class="col-lg-3 col-md-6">
                        <label for="company_id" class="form-label">Company</label>
                        <select class="form-select" id="company_id">
                            <option value="">All Companies</option>
                            <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label for="entity_function_id" class="form-label">Entity Function</label>
                        <select class="form-select" id="entity_function_id">
                            <option value="">All Functions</option>
                            <?php $__currentLoopData = $entityFunctions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ef): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($ef->id); ?>"><?php echo e($ef->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <?php else: ?>
                    <div class="col-lg-6 d-none d-lg-block"></div>
                    <?php endif; ?>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="button" class="btn btn-outline-secondary" id="resetFilters">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2">
                                <polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                            </svg>
                            Reset Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="table-responsive">
                    <table id="campaign-table" class="table dt-table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Date</th>
                                <th>Theme</th>
                                <th>Company</th>
                                <th>Speaker</th>
                                <th>Entity Function</th>
                                <th>Created By</th>
                                <th class="no-content text-center" width="15%">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>




<?php if (\Illuminate\Support\Facades\Blade::check('role', 'super-admin|hsse')): ?>


<div class="modal fade" id="exportZipModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2 text-success">
                        <polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line>
                    </svg>
                    Export ZIP (PDF)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning d-flex gap-2 py-2 mb-3 align-items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0 mt-1">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <div>
                        <strong>Batas Maksimal: 7 Hari &amp; 50 Laporan</strong><br>
                        <small class="text-muted">Export ZIP memuat PDF beserta gambar. Untuk rentang lebih lama, gunakan <strong>Export Excel</strong>.</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Date Range <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="date" id="zip_start_date" class="form-control">
                        <span class="input-group-text bg-light">to</span>
                        <input type="date" id="zip_end_date" class="form-control">
                    </div>
                    <div id="zip_date_error" class="text-danger small mt-1" style="display:none;">
                        ⚠ Rentang tidak boleh lebih dari 7 hari.
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Filter by Company <small class="text-muted fw-normal">(Optional)</small></label>
                    <select id="zip_company_id" class="form-select">
                        <option value="">All Companies</option>
                        <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Filter by Entity Function <small class="text-muted fw-normal">(Optional)</small></label>
                    <select id="zip_entity_function_id" class="form-select">
                        <option value="">All Functions</option>
                        <?php $__currentLoopData = $entityFunctions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ef): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($ef->id); ?>"><?php echo e($ef->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="btn_download_zip" class="btn btn-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                        <polyline points="8 17 12 21 16 17"></polyline><line x1="12" y1="12" x2="12" y2="21"></line><path d="M20.88 18.09A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.29"></path>
                    </svg>
                    Download ZIP
                </button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="exportExcelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2 text-info">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    Export Excel Summary
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info d-flex gap-2 py-2 mb-3 align-items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0 mt-1">
                        <circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    <div>
                        <strong>Data Summary Saja (Tanpa Foto/Lampiran)</strong><br>
                        <small class="text-muted">Berisi: Tanggal, Tema, Perusahaan, Entity Function, Pemateri, Lokasi, Entitas, Jumlah Peserta, Dibuat Oleh. Mendukung rentang hingga 1 tahun.</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Date Range <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="date" id="excel_start_date" class="form-control">
                        <span class="input-group-text bg-light">to</span>
                        <input type="date" id="excel_end_date" class="form-control">
                    </div>
                    <div id="excel_date_error" class="text-danger small mt-1" style="display:none;">
                        ⚠ Rentang tidak boleh lebih dari 1 tahun (365 hari).
                    </div>
                    <small class="text-muted">Maksimal 1 tahun per export.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Filter by Company <small class="text-muted fw-normal">(Optional)</small></label>
                    <select id="excel_company_id" class="form-select">
                        <option value="">All Companies</option>
                        <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Filter by Entity Function <small class="text-muted fw-normal">(Optional)</small></label>
                    <select id="excel_entity_function_id" class="form-select">
                        <option value="">All Functions</option>
                        <?php $__currentLoopData = $entityFunctions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ef): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($ef->id); ?>"><?php echo e($ef->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="btn_download_excel" class="btn btn-info text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                        <polyline points="8 17 12 21 16 17"></polyline><line x1="12" y1="12" x2="12" y2="21"></line><path d="M20.88 18.09A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.29"></path>
                    </svg>
                    Download Excel
                </button>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>


<div id="export-loading-overlay">
    <div class="export-spinner-box">
        <div class="spinner-border text-primary" role="status" style="width:3rem;height:3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p id="loading-text">Sedang memproses...</p>
        <small id="loading-subtext">Mohon tunggu, jangan tutup halaman ini.</small>

        
        <div id="zip-progress-wrapper" style="display:none; margin-top:16px; width:100%;">
            <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                <small style="color:#555; font-weight:600;">Memproses PDF...</small>
                <small id="zip-progress-label" style="color:#1F4E79; font-weight:700;">0 / 0</small>
            </div>
            <div style="background:#e9ecef; border-radius:99px; height:10px; overflow:hidden;">
                <div id="zip-progress-bar"
                     style="height:100%; width:0%; background:linear-gradient(90deg,#1F4E79,#2E86C1); border-radius:99px; transition:width 0.4s ease;">
                </div>
            </div>
            <small id="zip-progress-detail" style="color:#888; font-size:11px; display:block; margin-top:6px;">
                Setiap PDF diproses satu per satu...
            </small>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('plugins/src/table/datatable/datatables.js')); ?>"></script>
<script src="<?php echo e(asset('plugins/src/sweetalerts2/sweetalerts2.min.js')); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
(function ($) {
    'use strict';

    // Toast
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
    var table = $('#campaign-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?php echo e(route("campaign-salman.index")); ?>',
            data: function (d) {
                d.start_date         = $('#start_date').val();
                d.end_date           = $('#end_date').val();
                d.company_id         = $('#company_id').val();
                d.entity_function_id = $('#entity_function_id').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex',          name: 'DT_RowIndex',                   orderable: false, searchable: false },
            { data: 'tanggal',              name: 'tanggal' },
            { data: 'tema',                 name: 'tema' },
            { data: 'company_name',         name: 'company.name' },
            { data: 'pemateri',             name: 'pemateri' },
            { data: 'entity_function_name', name: 'creator.entityFunction.name' },
            { data: 'creator_name',         name: 'creator.name' },
            { data: 'action',               name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        dom: '<"dt--top-section"<"row"<"col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center"l><"col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3"f>>><"table-responsive"tr><"dt--bottom-section d-sm-flex justify-content-sm-between text-center"<"dt--pages-count mb-sm-0 mb-3"i><"dt--pagination"p>>',
        oLanguage: {
            oPaginate: {
                sPrevious: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
                sNext: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>'
            },
            sInfo: 'Showing page _PAGE_ of _PAGES_',
            sSearch: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
            sSearchPlaceholder: 'Search report...',
            sLengthMenu: 'Results :  _MENU_'
        },
        stripeClasses: [],
        lengthMenu: [7, 10, 20, 50],
        pageLength: 10,
        order: [[1, 'desc']]
    });

    // Filter
    $('#start_date, #end_date, #company_id, #entity_function_id').on('change', function () {
        table.draw();
    });
    $('#resetFilters').on('click', function () {
        $('#start_date, #end_date').val('');
        $('#company_id, #entity_function_id').val('');
        table.draw();
    });

    // Delete
    $(document).on('click', '.delete-record', function (e) {
        e.preventDefault();
        var url  = $(this).data('url');
        var tema = $(this).closest('tr').find('td:eq(2)').text();
        Swal.fire({
            title: 'Delete Report?',
            text: 'Are you sure you want to delete: ' + tema + '?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e7515a',
            cancelButtonColor: '#3b3f5c',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: url, type: 'DELETE',
                    data: { _token: '<?php echo e(csrf_token()); ?>' },
                    success: function (r) {
                        if (r.success) { table.draw(); Toast.fire({ icon: 'success', title: r.message }); }
                        else { Toast.fire({ icon: 'error', title: r.message }); }
                    },
                    error: function () { Toast.fire({ icon: 'error', title: 'Error deleting record' }); }
                });
            }
        });
    });

    // ================================================================
    // EXPORT
    // ================================================================

    function diffDays(s, e) {
        return (new Date(e) - new Date(s)) / 86400000;
    }

    function checkZipDates() {
        var s = $('#zip_start_date').val(), e = $('#zip_end_date').val();
        if (!s || !e) { $('#zip_date_error').hide(); $('#btn_download_zip').prop('disabled', false); return true; }
        var ok = diffDays(s, e) >= 0 && diffDays(s, e) <= 7;
        $('#zip_date_error').toggle(!ok);
        $('#btn_download_zip').prop('disabled', !ok);
        return ok;
    }

    function checkExcelDates() {
        var s = $('#excel_start_date').val(), e = $('#excel_end_date').val();
        if (!s || !e) { $('#excel_date_error').hide(); $('#btn_download_excel').prop('disabled', false); return true; }
        var ok = diffDays(s, e) >= 0 && diffDays(s, e) <= 365;
        $('#excel_date_error').toggle(!ok);
        $('#btn_download_excel').prop('disabled', !ok);
        return ok;
    }

    $('#zip_start_date, #zip_end_date').on('change', checkZipDates);
    $('#excel_start_date, #excel_end_date').on('change', checkExcelDates);

    // Overlay
    function showOverlay(text, subtext) {
        $('#loading-text').text(text);
        $('#loading-subtext').text(subtext);
        $('#zip-progress-wrapper').hide();
        $('#export-loading-overlay').addClass('show');
    }
    function hideOverlay() {
        $('#export-loading-overlay').removeClass('show');
        $('#zip-progress-wrapper').hide();
        clearZipTimer();
    }

    // ZIP timer
    var zipTimerInterval = null;
    function clearZipTimer() {
        if (zipTimerInterval) { clearInterval(zipTimerInterval); zipTimerInterval = null; }
    }
    function startZipTimer(totalReports) {
        var estSeconds = Math.max(10, totalReports * 8);
        var elapsed = 0;
        $('#zip-progress-wrapper').show();
        $('#zip-progress-bar').css('width', '0%');
        $('#zip-progress-label').text('0 / ' + totalReports + ' laporan');
        $('#zip-progress-detail').text('Estimasi ~' + estSeconds + ' detik untuk ' + totalReports + ' laporan...');
        zipTimerInterval = setInterval(function () {
            elapsed++;
            var pct = Math.min(92, Math.round((elapsed / estSeconds) * 100));
            $('#zip-progress-bar').css('width', pct + '%');
            var remaining = Math.max(0, estSeconds - elapsed);
            if (remaining > 0) {
                $('#zip-progress-detail').text('Mohon tunggu... estimasi sisa ~' + remaining + ' detik');
            } else {
                $('#zip-progress-detail').text('Hampir selesai, menyusun ZIP...');
            }
        }, 1000);
    }
    function finishZipTimer() {
        clearZipTimer();
        $('#zip-progress-bar').css('width', '100%');
        $('#zip-progress-label').text('Selesai!');
        $('#zip-progress-detail').text('File ZIP siap didownload...');
    }

    // Download helper (untuk Excel)
    function fetchDownload(url, payload, fallbackFilename, loadingText, loadingSubText) {
        showOverlay(loadingText, loadingSubText);
        $('#exportZipModal, #exportExcelModal').modal('hide');

        var fd = new FormData();
        fd.append('_token', '<?php echo e(csrf_token()); ?>');
        Object.keys(payload).forEach(function (k) {
            if (payload[k] !== null && payload[k] !== undefined && payload[k] !== '') {
                fd.append(k, payload[k]);
            }
        });

        fetch(url, { method: 'POST', body: fd })
            .then(function (response) {
                var ct = response.headers.get('Content-Type') || '';
                if (ct.indexOf('application/json') !== -1) {
                    return response.json().then(function (json) {
                        throw new Error(json.message || 'Server error');
                    });
                }
                if (!response.ok) { throw new Error('Server error ' + response.status); }
                var disp = response.headers.get('Content-Disposition') || '';
                var m = disp.match(/filename[^;=]*=([^;\r\n]+)/);
                if (m && m[1]) { fallbackFilename = m[1].trim(); }
                return response.blob();
            })
            .then(function (blob) {
                if (blob.size === 0) { throw new Error('File kosong.'); }
                var blobUrl = window.URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.style.display = 'none'; a.href = blobUrl; a.download = fallbackFilename;
                document.body.appendChild(a); a.click();
                setTimeout(function () { window.URL.revokeObjectURL(blobUrl); a.remove(); }, 200);
                hideOverlay();
                Toast.fire({ icon: 'success', title: 'Download berhasil!' });
            })
            .catch(function (err) {
                hideOverlay();
                console.error('[Export]', err);
                Toast.fire({ icon: 'error', title: err.message || 'Gagal export.' });
            });
    }

    // ----------------------------------------------------------------
    // ZIP: 2 langkah — check dulu, lalu download
    // ----------------------------------------------------------------
    $('#btn_download_zip').on('click', function () {
        var s = $('#zip_start_date').val(), e = $('#zip_end_date').val();
        if (!s || !e) { Toast.fire({ icon: 'warning', title: 'Pilih rentang tanggal.' }); return; }
        if (!checkZipDates()) { return; }

        var payload = {
            start_date:         s,
            end_date:           e,
            company_id:         $('#zip_company_id').val(),
            entity_function_id: $('#zip_entity_function_id').val()
        };

        var $btn = $('#btn_download_zip');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Memeriksa...');

        var fd = new FormData();
        fd.append('_token', '<?php echo e(csrf_token()); ?>');
        Object.keys(payload).forEach(function (k) { if (payload[k]) fd.append(k, payload[k]); });

        var ICON_DOWNLOAD = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polyline points="8 17 12 21 16 17"></polyline><line x1="12" y1="12" x2="12" y2="21"></line><path d="M20.88 18.09A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.29"></path></svg> Download ZIP';

        fetch('<?php echo e(route("campaign-salman.export-zip-check")); ?>', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                $btn.prop('disabled', false).html(ICON_DOWNLOAD);

                if (!data.ok) {
                    Toast.fire({ icon: 'error', title: data.message || 'Gagal.' });
                    return;
                }

                var count = data.count;
                $('#exportZipModal').modal('hide');
                showOverlay('Sedang menghasilkan ' + count + ' PDF...', 'Jangan tutup halaman ini.');
                startZipTimer(count);

                var fd2 = new FormData();
                fd2.append('_token', '<?php echo e(csrf_token()); ?>');
                Object.keys(payload).forEach(function (k) { if (payload[k]) fd2.append(k, payload[k]); });

                fetch('<?php echo e(route("campaign-salman.export-zip")); ?>', { method: 'POST', body: fd2 })
                    .then(function (response) {
                        var ct = response.headers.get('Content-Type') || '';
                        if (ct.indexOf('application/json') !== -1) {
                            return response.json().then(function (json) {
                                throw new Error(json.message || 'Server error');
                            });
                        }
                        if (!response.ok) { throw new Error('Server error ' + response.status); }
                        var disp = response.headers.get('Content-Disposition') || '';
                        var m = disp.match(/filename[^;=]*=([^;\r\n]+)/);
                        var fname = 'Laporan_Salman_' + s.replace(/-/g, '') + '-' + e.replace(/-/g, '') + '.zip';
                        if (m && m[1]) { fname = m[1].trim(); }
                        return response.blob().then(function (blob) { return { blob: blob, fname: fname }; });
                    })
                    .then(function (result) {
                        if (result.blob.size === 0) { throw new Error('ZIP kosong.'); }
                        finishZipTimer();
                        setTimeout(function () {
                            var blobUrl = window.URL.createObjectURL(result.blob);
                            var a = document.createElement('a');
                            a.style.display = 'none'; a.href = blobUrl; a.download = result.fname;
                            document.body.appendChild(a); a.click();
                            setTimeout(function () { window.URL.revokeObjectURL(blobUrl); a.remove(); }, 200);
                            hideOverlay();
                            Toast.fire({ icon: 'success', title: 'ZIP berhasil didownload!' });
                        }, 800);
                    })
                    .catch(function (err) {
                        hideOverlay();
                        console.error('[ZIP]', err);
                        Toast.fire({ icon: 'error', title: err.message || 'Gagal download ZIP.' });
                    });
            })
            .catch(function () {
                $btn.prop('disabled', false).html(ICON_DOWNLOAD);
                Toast.fire({ icon: 'error', title: 'Gagal memeriksa data. Coba lagi.' });
            });
    });

    // ----------------------------------------------------------------
    // Excel download
    // ----------------------------------------------------------------
    $('#btn_download_excel').on('click', function () {
        var s = $('#excel_start_date').val(), e = $('#excel_end_date').val();
        if (!s || !e) { Toast.fire({ icon: 'warning', title: 'Pilih rentang tanggal.' }); return; }
        if (!checkExcelDates()) { return; }

        fetchDownload(
            '<?php echo e(route("campaign-salman.export-excel")); ?>',
            {
                start_date:         s,
                end_date:           e,
                company_id:         $('#excel_company_id').val(),
                entity_function_id: $('#excel_entity_function_id').val()
            },
            'Summary_CampaignSalman_' + s.replace(/-/g, '') + '-' + e.replace(/-/g, '') + '.xlsx',
            'Sedang membuat file Excel...',
            'Mohon tunggu sebentar.'
        );
    });

}(jQuery));
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/features/campaign-salman/index.blade.php ENDPATH**/ ?>