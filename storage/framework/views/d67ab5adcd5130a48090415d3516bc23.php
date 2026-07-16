<?php $__env->startSection('title', 'Contacts Management'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/src/table/datatable/datatables.css')); ?>">
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/css/light/table/datatable/dt-global_style.css')); ?>">
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/css/dark/table/datatable/dt-global_style.css')); ?>">
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/src/sweetalerts2/sweetalerts2.css')); ?>">
<style>
    .stat-card {
        border-radius: 10px;
        padding: 20px 24px;
        border: 1px solid var(--card-border-color);
        background: var(--card-bg);
        transition: box-shadow 0.2s, transform 0.2s;
    }
    .stat-card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .stat-card .stat-value {
        font-size: 32px;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 4px;
    }
    .stat-card .stat-label {
        font-size: 13px;
        color: var(--text-muted);
        font-weight: 500;
    }
    .stat-card .stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .filter-section {
        background: var(--card-bg);
        border-radius: 10px;
        padding: 20px 24px;
        margin-bottom: 20px;
        border: 1px solid var(--card-border-color);
    }
    .filter-section .form-label {
        font-weight: 600;
        font-size: 13px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }
    .page-header-btn .btn {
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 18px;
        border-radius: 8px;
        font-size: 14px;
    }
    .table thead th {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        color: var(--text-muted);
        border-bottom: 2px solid var(--card-border-color);
        padding: 12px 16px;
        white-space: nowrap;
    }
    .table tbody td {
        padding: 12px 16px;
        vertical-align: middle;
        font-size: 14px;
    }
    .badge-status-active {
        background: rgba(0, 168, 107, 0.12);
        color: #00a86b;
        font-weight: 600;
        font-size: 11px;
        padding: 4px 10px;
        border-radius: 20px;
        border: 1px solid rgba(0, 168, 107, 0.2);
    }
    .badge-status-inactive {
        background: rgba(231, 76, 60, 0.10);
        color: #e74c3c;
        font-weight: 600;
        font-size: 11px;
        padding: 4px 10px;
        border-radius: 20px;
        border: 1px solid rgba(231, 76, 60, 0.2);
    }
    .wa-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #25D366;
        font-weight: 500;
        font-size: 13px;
        text-decoration: none;
        transition: opacity 0.2s;
    }
    .wa-link:hover { opacity: 0.75; color: #25D366; }
    .btn-action {
        width: 30px;
        height: 30px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.15s;
    }
    .btn-action:hover { transform: scale(1.1); }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
    }
    .empty-state svg {
        opacity: 0.3;
        margin-bottom: 12px;
    }
    div.dataTables_wrapper div.dataTables_filter {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-bottom: 1rem;
    }
    div.dataTables_wrapper div.dataTables_filter input {
        margin-left: 10px;
        border-radius: 8px;
        padding: 8px 14px;
        font-size: 13px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">

        
        <div class="row layout-top-spacing mb-4 align-items-center">
            <div class="col-md-6">
                <h3 class="m-0 fw-bold">Contacts</h3>
                <p class="text-muted mb-0" style="font-size:13px;">Kelola daftar kontak WhatsApp</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0 page-header-btn d-flex justify-content-md-end gap-2 flex-wrap">
                <a href="<?php echo e(route('contacts.import.form')); ?>" class="btn btn-outline-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-upload"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    Import
                </a>
                <a href="<?php echo e(route('contacts.create')); ?>" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user-plus"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                    Add Contact
                </a>
            </div>
        </div>

        
        <div class="row mb-4">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background:rgba(113,106,202,0.12);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#716aca" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <div>
                        <div class="stat-value" style="color:#716aca;"><?php echo e(number_format($stats['total'])); ?></div>
                        <div class="stat-label">Total Contacts</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background:rgba(0,168,107,0.12);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#00a86b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                    <div>
                        <div class="stat-value" style="color:#00a86b;"><?php echo e(number_format($stats['active'])); ?></div>
                        <div class="stat-label">Active</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background:rgba(231,76,60,0.10);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                    </div>
                    <div>
                        <div class="stat-value" style="color:#e74c3c;"><?php echo e(number_format($stats['inactive'])); ?></div>
                        <div class="stat-label">Inactive</div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="filter-section">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label for="statusFilter" class="form-label">Status</label>
                    <select class="form-select" id="statusFilter" style="border-radius:8px;">
                        <option value="">Semua Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <button type="button" class="btn btn-outline-secondary w-100" id="resetFilters" style="border-radius:8px; height:42px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw me-1"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                        Reset
                    </button>
                </div>
            </div>
        </div>

        
        <div class="widget-content widget-content-area br-8">
            <div class="table-responsive">
                <table id="contacts-table" class="table dt-table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th>Name</th>
                            <th>WhatsApp</th>
                            <th>Position</th>
                            <th>Notes</th>
                            <th>Status</th>
                            <th class="text-center" width="160px">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('plugins/src/table/datatable/datatables.js')); ?>"></script>
<script src="<?php echo e(asset('plugins/src/sweetalerts2/sweetalerts2.min.js')); ?>"></script>
<script>
$(document).ready(function () {

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    const table = $('#contacts-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?php echo e(route('contacts.index')); ?>",
            data: function (d) {
                d.status = $('#statusFilter').val();
            },
            error: function (xhr) {
                Toast.fire({ icon: 'error', title: 'Gagal memuat data.' });
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'wa_link', name: 'whatsapp_number', orderable: false },
            {
                data: 'position',
                name: 'position',
                render: (d) => d ? `<span>${d}</span>` : '<span class="text-muted fst-italic" style="font-size:12px;">—</span>'
            },
            {
                data: 'notes',
                name: 'notes',
                render: function (data) {
                    if (!data) return '<span class="text-muted fst-italic" style="font-size:12px;">—</span>';
                    const safe = $('<div>').text(data).html();
                    return safe.length > 55 ? `<span title="${safe}">${safe.substring(0, 55)}…</span>` : safe;
                }
            },
            { data: 'status', name: 'is_active', searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
        ],
        dom: "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'l><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'f>>>" +
             "<'table-responsive'tr>" +
             "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count mb-sm-0 mb-3'i><'dt--pagination'p>>",
        oLanguage: {
            oPaginate: {
                sPrevious: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
                sNext:     '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>',
            },
            sInfo:            "Menampilkan halaman _PAGE_ dari _PAGES_",
            sInfoEmpty:       "Tidak ada data",
            sInfoFiltered:    "(difilter dari _MAX_ total)",
            sSearch:          '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
            sSearchPlaceholder: "Cari nama, nomor...",
            sLengthMenu:      "Tampilkan :  _MENU_",
            sZeroRecords:     "Tidak ada kontak yang cocok.",
            sEmptyTable:      "Belum ada kontak.",
            sProcessing:      "Memuat data...",
        },
        stripeClasses: [],
        lengthMenu: [10, 25, 50, 100],
        pageLength: 10,
        order: [[1, 'asc']],
    });

    $('#statusFilter').on('change', function () { table.draw(); });
    $('#resetFilters').on('click', function () {
        $('#statusFilter').val('');
        table.search('').draw();
    });

    // Delete
    $(document).on('click', '.delete-contact', function (e) {
        e.preventDefault();
        const url  = $(this).data('url');
        const name = $(this).data('name');

        Swal.fire({
            title: 'Hapus Kontak?',
            html: `Yakin ingin menghapus <strong>${name}</strong>? Tindakan ini tidak dapat dibatalkan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-outline-secondary' },
            buttonsStyling: false,
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url,
                    type: 'DELETE',
                    data: { _token: '<?php echo e(csrf_token()); ?>' },
                    success: function (res) {
                        if (res.success) {
                            table.draw(false);
                            Toast.fire({ icon: 'success', title: res.message });
                        } else {
                            Toast.fire({ icon: 'error', title: res.message || 'Gagal menghapus.' });
                        }
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus.';
                        Toast.fire({ icon: 'error', title: msg });
                    },
                });
            }
        });
    });

    // Toggle Status
    $(document).on('click', '.toggle-status', function (e) {
        e.preventDefault();
        const url = $(this).data('url');
        const btn = $(this);

        btn.prop('disabled', true);

        $.ajax({
            url,
            type: 'POST',
            data: { _token: '<?php echo e(csrf_token()); ?>' },
            success: function (res) {
                if (res.success) {
                    Toast.fire({ icon: 'success', title: res.message });
                    table.draw(false);
                } else {
                    Toast.fire({ icon: 'error', title: res.message });
                    btn.prop('disabled', false);
                }
            },
            error: function () {
                Toast.fire({ icon: 'error', title: 'Gagal mengubah status.' });
                btn.prop('disabled', false);
            },
        });
    });

});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/master/contacts/index.blade.php ENDPATH**/ ?>