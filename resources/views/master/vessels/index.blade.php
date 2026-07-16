@extends('layouts.app')

@section('title', 'Vessels & Crew Management')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/table/datatable/datatables.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/css/light/table/datatable/dt-global_style.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/css/dark/table/datatable/dt-global_style.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
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

    .stats-card {
        background: var(--card-bg);
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        border: 1px solid var(--card-border-color);
        transition: transform 0.2s;
    }
    .stats-card:hover {
        transform: translateY(-2px);
    }
    .stats-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

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
                <h3 class="m-0 fw-bold">Vessels / Unit & Crew Management</h3>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0 page-title-action">
                <a href="{{ route('vessels.create') }}" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Add Vessel / Unit
                </a>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="row layout-spacing">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-anchor"><circle cx="12" cy="5" r="3"></circle><line x1="12" y1="22" x2="12" y2="8"></line><path d="M5 12H2a10 10 0 0 0 20 0h-3"></path></svg>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0 text-muted">Total Vessels / Unit</h6>
                            <h3 class="mb-0 fw-bold text-primary">{{ $stats['total_vessels'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-success bg-opacity-10 text-success">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0 text-muted">Active Vessels / Unit</h6>
                            <h3 class="mb-0 fw-bold text-success">{{ $stats['active_vessels'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-info bg-opacity-10 text-info">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0 text-muted">Total Crew</h6>
                            <h3 class="mb-0 fw-bold text-info">{{ $stats['total_crew'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-triangle"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0 text-muted">Expiring Soon</h6>
                            <h3 class="mb-0 fw-bold text-warning">{{ $stats['expiring_soon'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters Section --}}
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="filter-section">
                <div class="row g-3 align-items-end">
                    {{-- Status Filter --}}
                    <div class="col-lg-3 col-md-6">
                        <label for="statusFilter" class="form-label">Status Filter</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    {{-- Company Filter: ONLY FOR SUPER ADMIN --}}
                    @hasrole('super-admin')
                    <div class="col-lg-3 col-md-6">
                        <label for="companyFilter" class="form-label">Company Filter</label>
                        <select class="form-select" id="companyFilter">
                            <option value="">All Companies</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endhasrole

                    {{-- Type Filter --}}
                    <div class="col-lg-3 col-md-6">
                        <label for="typeFilter" class="form-label">Type Filter</label>
                        <select class="form-select" id="typeFilter">
                            <option value="">All Types</option>
                            <option value="Cargo">Cargo</option>
                            <option value="Tanker">Tanker</option>
                            <option value="Container">Container</option>
                            <option value="Passenger">Passenger</option>
                            <option value="Tug Boat">Tug Boat</option>
                            <option value="Barge">Barge</option>
                            <option value="Supply Vessel">Supply Vessel</option>
                        </select>
                    </div>

                    {{-- Reset Button --}}
                    <div class="col-lg-3 col-md-6">
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
                    <table id="vessels-table" class="table dt-table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Name</th>
                                <th>Company</th>
                                <th>Type</th>
                                <th>Crew DCU</th>
                                <th>Validity</th>
                                <th>Crew</th>
                                <th>Status</th>
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

    const table = $('#vessels-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('vessels.index') }}",
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.company = $('#companyFilter').length ? $('#companyFilter').val() : '';
                d.type = $('#typeFilter').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'company_name', name: 'company.name' },
            { data: 'type_badge', name: 'type' },
            { data: 'coordinator_name', name: 'coordinator.name' },
            { data: 'validity_status', name: 'valid_until' },
            { data: 'crew_count', name: 'crew_count', orderable: false, searchable: false },
            { data: 'status', name: 'is_active' },
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
        pageLength: 10
    });

    // Filter Listeners
    $('#statusFilter, #companyFilter, #typeFilter').on('change', function() { table.draw(); });

    $('#resetFilters').on('click', function() {
        $('#statusFilter').val('');
        $('#typeFilter').val('');
        if($('#companyFilter').length) { $('#companyFilter').val(''); }
        table.draw();
    });

    // Toggle Status
    $(document).on('click', '.toggle-status', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).data('url'),
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    Toast.fire({ icon: 'success', title: response.message });
                    table.ajax.reload(null, false);
                }
            },
            error: function() { Toast.fire({ icon: 'error', title: 'Failed to update status' }); }
        });
    });

    // Delete Action
    $(document).on('click', '.delete-vessel', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        const name = $(this).data('name');

        Swal.fire({
            title: 'Delete Vessel?',
            text: "Are you sure you want to delete: " + name + "?",
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
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            Toast.fire({ icon: 'success', title: response.message });
                            table.draw();
                        }
                    },
                    error: function(xhr) {
                        Toast.fire({ icon: 'error', title: xhr.responseJSON.message || 'Error deleting vessel' });
                    }
                });
            }
        });
    });
});
</script>
@endpush
