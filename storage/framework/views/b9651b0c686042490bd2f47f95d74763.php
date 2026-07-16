<?php $__env->startSection('title', 'Contracts Management'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/src/table/datatable/datatables.css')); ?>">
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/css/light/table/datatable/dt-global_style.css')); ?>">
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/css/dark/table/datatable/dt-global_style.css')); ?>">
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/src/sweetalerts2/sweetalerts2.css')); ?>">
<style>
    /* Styling Filter Section untuk DataTable */
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

    /* Styling Tombol Aksi */
    .page-action-buttons .btn {
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
    }
    .page-action-buttons .btn svg {
        width: 18px;
        height: 18px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">

        
        <div class="row layout-top-spacing mb-4 align-items-center">
            <div class="col-md-6">
                <h3 class="m-0 fw-bold">Contracts Management</h3>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <div class="page-action-buttons d-flex justify-content-md-end gap-2 flex-wrap">

                    
                    <div class="btn-group" role="group">
                        <a href="<?php echo e(route('contracts.export')); ?>" class="btn btn-outline-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            Export
                        </a>
                        <a href="<?php echo e(route('contracts.import.form')); ?>" class="btn btn-outline-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-upload"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            Import
                        </a>
                    </div>

                    
                    <a href="<?php echo e(route('contracts.create')); ?>" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Add Contract
                    </a>
                </div>
            </div>
        </div>

        
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="table-responsive">
                    <table id="contracts-table" class="table dt-table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Kontraktor</th>
                                <th>Nomor SAP</th>
                                <th>Email</th>
                                <th>Phone</th>
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

    const table = $('#contracts-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "<?php echo e(route('contracts.index')); ?>",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'nama_kontraktor', name: 'nama_kontraktor' },
            { data: 'sap_no', name: 'sap_no' },
            { data: 'alamat_email', name: 'alamat_email' },
            { data: 'no_tlp_kantor', name: 'no_tlp_kantor' },
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

    // Delete handler
    $(document).on('click', '.delete-contract', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        const contractName = $(this).closest('tr').find('td:eq(1)').text();

        Swal.fire({
            title: 'Delete Contract?',
            text: "Are you sure you want to delete: " + contractName + "?",
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
                        } else {
                            Toast.fire({ icon: 'error', title: response.message });
                        }
                    },
                    error: function() { Toast.fire({ icon: 'error', title: 'Something went wrong' }); }
                });
            }
        });
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/master/contracts/index.blade.php ENDPATH**/ ?>