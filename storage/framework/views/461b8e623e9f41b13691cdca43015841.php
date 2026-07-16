<?php $__env->startSection('title', 'Security Event Categories Management'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/src/table/datatable/datatables.css')); ?>">
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/css/light/table/datatable/dt-global_style.css')); ?>">
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/css/dark/table/datatable/dt-global_style.css')); ?>">
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/src/sweetalerts2/sweetalerts2.css')); ?>">
<style>
    /* Styling Filter Section */
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

    /* Perbaikan posisi Search Box DataTable */
    div.dataTables_wrapper div.dataTables_filter {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-bottom: 1rem;
    }
    div.dataTables_wrapper div.dataTables_filter input {
        margin-left: 10px;
        border-radius: 6px;
        padding: 8px 12px;
    }

    /* Tombol Aksi di Header */
    .page-title-action .btn {
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">

        
        <div class="row layout-top-spacing mb-4 align-items-center">
            <div class="col-md-6">
                <h3 class="m-0 fw-bold">Security Event Categories</h3>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0 page-title-action">
                <a href="<?php echo e(route('security-event-categories.create')); ?>" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Add Category
                </a>
            </div>
        </div>

        
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="filter-section">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-6 col-md-6">
                        <label for="statusFilter" class="form-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-toggle-right me-1"><rect x="1" y="5" width="22" height="14" rx="7" ry="7"></rect><circle cx="16" cy="12" r="3"></circle></svg>
                            Status Filter
                        </label>
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="col-lg-6 col-md-6">
                        <button type="button" class="btn btn-outline-secondary w-100" id="resetFilters" style="height: 46px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw me-2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                            Reset Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="table-responsive">
                    <table id="categories-table" class="table dt-table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th class="no-content text-center" width="20%">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('plugins/src/table/datatable/datatables.js')); ?>"></script>
<script src="<?php echo e(asset('plugins/src/sweetalerts2/sweetalerts2.min.js')); ?>"></script>
<script>
$(document).ready(function() {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    const table = $('#categories-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?php echo e(route('security-event-categories.index')); ?>",
            data: function(d) {
                d.status = $('#statusFilter').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code', name: 'code' },
            { data: 'name', name: 'name' },
            { data: 'description_short', name: 'description' },
            { data: 'status', name: 'is_active', className: 'text-center' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
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
            sSearchPlaceholder: "Search...",
            sLengthMenu: "Results :  _MENU_",
        },
        stripeClasses: [],
        lengthMenu: [7, 10, 20, 50],
        pageLength: 10,
        order: [[2, 'asc']]
    });

    // Filter Logic
    $('#statusFilter').on('change', function() { table.draw(); });
    $('#resetFilters').on('click', function() {
        $('#statusFilter').val('');
        table.draw();
    });

    // Delete Handler
    $(document).on('click', '.delete-category', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        const categoryName = $(this).closest('tr').find('td:eq(2)').text();

        Swal.fire({
            title: 'Delete Category?',
            text: "Are you sure you want to delete category: " + categoryName + "?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-outline-secondary ms-1' },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: { _token: '<?php echo e(csrf_token()); ?>' },
                    success: function(response) {
                        if (response.success) {
                            table.draw();
                            Toast.fire({ icon: 'success', title: response.message });
                        }
                    },
                    error: function() { Toast.fire({ icon: 'error', title: 'Error deleting record' }); }
                });
            }
        });
    });

    // Toggle Status
    $(document).on('click', '.toggle-status', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        $.ajax({
            url: url,
            type: 'POST',
            data: { _token: '<?php echo e(csrf_token()); ?>' },
            success: function(response) {
                if (response.success) {
                    Toast.fire({ icon: 'success', title: response.message });
                    table.draw();
                } else {
                    Toast.fire({ icon: 'error', title: response.message });
                }
            },
            error: function() { Toast.fire({ icon: 'error', title: 'Error updating status' }); }
        });
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/master/security-event-categories/index.blade.php ENDPATH**/ ?>