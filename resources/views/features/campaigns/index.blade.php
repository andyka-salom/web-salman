@extends('layouts.app')

@section('title', 'Campaign Management')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/table/datatable/datatables.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/css/light/table/datatable/dt-global_style.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/css/dark/table/datatable/dt-global_style.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
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
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">

        {{-- Page Header --}}
        <div class="row layout-top-spacing mb-4 align-items-center">
            <div class="col-md-6">
                <h3 class="m-0 fw-bold">Campaign Management</h3>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0 page-title-action">
                <a href="{{ route('campaigns.create') }}" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    New Campaign
                </a>
            </div>
        </div>

        {{-- Filters Section --}}
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="filter-section">
                <div class="row g-3 align-items-end">
                    {{-- Status Filter --}}
                    <div class="col-lg-4 col-md-4">
                        <label for="statusFilter" class="form-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-toggle-right me-1"><rect x="1" y="5" width="22" height="14" rx="7" ry="7"></rect><circle cx="16" cy="12" r="3"></circle></svg>
                            Status
                        </label>
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="1">Published</option>
                            <option value="0">Draft</option>
                        </select>
                    </div>

                    {{-- Category Filter --}}
                    <div class="col-lg-4 col-md-4">
                        <label for="categoryFilter" class="form-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-grid me-1"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                            Category
                        </label>
                        <select class="form-select" id="categoryFilter">
                            <option value="">All Categories</option>
                            <option value="Safety">Safety</option>
                            <option value="Health">Health</option>
                            <option value="Environment">Environment</option>
                            <option value="Operations">Operations</option>
                            <option value="General">General</option>
                        </select>
                    </div>

                    {{-- Reset Button --}}
                    <div class="col-lg-4 col-md-4">
                        <button type="button" class="btn btn-outline-secondary w-100" id="resetFilters" style="height: 46px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw me-2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                            Reset Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- DataTable Section --}}
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="table-responsive">
                    <table id="campaigns-table" class="table dt-table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Views</th>
                                <th>Published At</th>
                                <th>Creator</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Data loaded via AJAX --}}
                        </tbody>
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

    const table = $('#campaigns-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('campaigns.index') }}",
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.category = $('#categoryFilter').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'title', name: 'title' },
            { data: 'category', name: 'category' },
            { data: 'view_count', name: 'view_count' },
            { data: 'published_at', name: 'published_at' },
            { data: 'creator_name', name: 'creator.name' },
            { data: 'status', name: 'is_published', className: 'text-center' },
            { data: 'action', orderable: false, searchable: false, className: 'text-center' }
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
        order: [[4, 'desc']]
    });

    // Filter event listeners
    $('#statusFilter, #categoryFilter').on('change', function() {
        table.draw();
    });

    // Reset Filters
    $('#resetFilters').on('click', function() {
        $('#statusFilter').val('');
        $('#categoryFilter').val('');
        table.draw();
    });

    // Delete Record
    $(document).on('click', '.delete-record', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        const title = $(this).closest('tr').find('td:eq(1)').text();

        Swal.fire({
            title: 'Delete Campaign?',
            text: "Are you sure you want to delete: " + title + "?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-outline-secondary ms-1'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {_token: '{{ csrf_token() }}'},
                    success: function(res) {
                        if(res.success) {
                            table.draw();
                            Toast.fire({ icon: 'success', title: res.message });
                        } else {
                            Toast.fire({ icon: 'error', title: res.message });
                        }
                    },
                    error: function(xhr) {
                        Toast.fire({ icon: 'error', title: 'Something went wrong' });
                    }
                });
            }
        });
    });

    // Toggle Publish / Feature
    $(document).on('click', '.toggle-action', function(e) {
        e.preventDefault();
        const url = $(this).data('url');

        $.post(url, {_token: '{{ csrf_token() }}'}, function(res) {
            if(res.success) {
                table.draw();
                Toast.fire({ icon: 'success', title: res.message });
            } else {
                Toast.fire({ icon: 'error', title: 'Failed to update status' });
            }
        }).fail(function() {
            Toast.fire({ icon: 'error', title: 'Error occurred' });
        });
    });
});
</script>
@endpush
