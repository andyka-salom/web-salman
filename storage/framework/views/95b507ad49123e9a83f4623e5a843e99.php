<?php $__env->startSection('title', 'Entity Functions Management'); ?>

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
        padding: 8px 16px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">

        
        <div class="row layout-top-spacing mb-4 align-items-center">
            <div class="col-md-5">
                <h3 class="m-0 fw-bold">Entity Functions</h3>
            </div>
            <div class="col-md-7 text-md-end mt-3 mt-md-0 page-title-action">
                <div class="d-flex justify-content-md-end gap-2 flex-wrap">
                    
                    <a href="<?php echo e(route('entity-functions.export')); ?>" class="btn btn-outline-success">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Export
                    </a>

                    
                    <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#importModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-upload"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                        Import
                    </button>

                    
                    <a href="<?php echo e(route('entity-functions.hierarchy')); ?>" class="btn btn-outline-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-share-2"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
                        Hierarchy
                    </a>

                    
                    <a href="<?php echo e(route('entity-functions.create')); ?>" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Add Function
                    </a>
                </div>
            </div>
        </div>

        
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="filter-section">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Status Filter</label>
                        <select class="form-select" id="statusFilter" style="height: 48px;">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Level Filter</label>
                        <select class="form-select" id="levelFilter" style="height: 48px;">
                            <option value="">All Levels</option>
                            <option value="0">Level 0 (Root)</option>
                            <option value="1">Level 1</option>
                            <option value="2">Level 2</option>
                            <option value="3">Level 3+</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-secondary w-100" id="resetFilters" style="height: 48px;">
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
                    <table id="functions-table" class="table dt-table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">Code</th>
                                <th>Name</th>
                                <th>Parent Function</th>
                                <th width="10%">Level</th>
                                <th width="10%">Status</th>
                                <th class="no-content text-center" width="10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>


<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Entity Functions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-light-info mb-3 p-3 border-0">
                        <p class="mb-2 fw-bold text-info">Instructions:</p>
                        <ul class="mb-0 ps-3 text-muted small">
                            <li>Supported formats: <strong>.xlsx, .xls, .csv</strong></li>
                            <li>Columns required: <strong>Code, Name</strong></li>
                            <li>Optional: <strong>Parent Function Code, Description, Is Active</strong></li>
                            <li>Ensure Parent records are listed before Children.</li>
                        </ul>
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label fw-bold">Choose File</label>
                        <input class="form-control" type="file" id="file" name="file" required accept=".xlsx, .xls, .csv">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="btnImport">Import Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>

<script src="<?php echo e(asset('plugins/src/table/datatable/datatables.js')); ?>"></script>
<script src="<?php echo e(asset('plugins/src/sweetalerts2/sweetalerts2.min.js')); ?>"></script>

<script>
$(document).ready(function() {

    // Define Toast Mixin
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    // 1. Inisialisasi DataTable
    const table = $('#functions-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?php echo e(route('entity-functions.index')); ?>",
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.level = $('#levelFilter').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code', name: 'code' },
            { data: 'name', name: 'name' },
            { data: 'parent_name', name: 'parent.name' },
            { data: 'level', name: 'level' },
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
            sSearch: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
            sSearchPlaceholder: "Search...",
            sLengthMenu: "Results :  _MENU_",
        },
        stripeClasses: [],
        lengthMenu: [7, 10, 20, 50],
        pageLength: 10,
        order: [[2, 'asc']]
    });

    // 2. Event Listener untuk Filter
    $('#statusFilter, #levelFilter').on('change', function() { table.draw(); });

    // 3. Reset Filter
    $('#resetFilters').on('click', function() {
        $('#statusFilter').val('');
        $('#levelFilter').val('');
        table.draw();
    });

    // 4. Handle Delete Record
    $(document).on('click', '.delete-record', function(e) {
        e.preventDefault();
        const url = $(this).data('url');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            customClass: { confirmButton: 'btn btn-primary', cancelButton: 'btn btn-outline-danger ms-1' },
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
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON.message || 'Failed to delete record', 'error');
                    }
                });
            }
        });
    });

    // 5. Handle Toggle Status
    $(document).on('click', '.toggle-status', function(e) {
        e.preventDefault();
        const url = $(this).data('url');

        $.ajax({
            url: url,
            type: 'POST',
            data: { _token: '<?php echo e(csrf_token()); ?>' },
            success: function(response) {
                if (response.success) {
                    table.draw();
                    Toast.fire({ icon: 'success', title: response.message });
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to update status', 'error');
            }
        });
    });

    // 6. Handle Import Form Submit
    $('#importForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var btn = $('#btnImport');
        var originalText = btn.html();

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Importing...');

        $.ajax({
            url: "<?php echo e(route('entity-functions.import')); ?>",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'},
            success: function(response) {
                btn.prop('disabled', false).html(originalText);
                $('#importModal').modal('hide');
                $('#importForm')[0].reset();

                if(response.success) {
                    Toast.fire({ icon: 'success', title: response.message });
                    table.draw();
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(originalText);
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred during import.';
                Swal.fire('Import Failed', msg, 'error');
            }
        });
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/master/entity-functions/index.blade.php ENDPATH**/ ?>