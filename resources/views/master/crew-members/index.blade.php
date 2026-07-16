{{-- resources/views/crew-members/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Crew Members Management')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/table/datatable/datatables.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/css/light/table/datatable/dt-global_style.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/css/dark/table/datatable/dt-global_style.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
<style>
    .filter-section {
        background: var(--card-bg);
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 0 15px 1px rgba(113, 106, 202, 0.05);
    }
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">

        {{-- Filters Section --}}
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="filter-section">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label for="statusFilter" class="form-label">Status Filter</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label for="vesselFilter" class="form-label">Vessel Filter</label>
                        <select class="form-select" id="vesselFilter">
                            <option value="">All Vessels</option>
                            @foreach($vessels as $vessel)
                                <option value="{{ $vessel->id }}">{{ $vessel->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label for="positionFilter" class="form-label">Position Filter</label>
                        <select class="form-select" id="positionFilter">
                            <option value="">All Positions</option>
                            <option value="Captain">Captain</option>
                            <option value="Chief Officer">Chief Officer</option>
                            <option value="Chief Engineer">Chief Engineer</option>
                            <option value="Bosun">Bosun</option>
                            <option value="AB (Able Seaman)">AB (Able Seaman)</option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label for="genderFilter" class="form-label">Gender Filter</label>
                        <select class="form-select" id="genderFilter">
                            <option value="">All Genders</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <button type="button" class="btn btn-secondary" id="resetFilters">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw">
                                <polyline points="23 4 23 10 17 10"></polyline>
                                <polyline points="1 20 1 14 7 14"></polyline>
                                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                            </svg>
                            <span class="btn-text-inner ms-2">Reset Filters</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- DataTable Section --}}
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="table-responsive">
                    <table id="crew-members-table" class="table dt-table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>NIK</th>
                                <th>Name</th>
                                <th>Vessel</th>
                                <th>Position</th>
                                <th>Gender</th>
                                <th>Age</th>
                                <th>Phone</th>
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
    });

    const table = $('#crew-members-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('crew-members.index') }}",
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.vessel = $('#vesselFilter').val();
                d.position = $('#positionFilter').val();
                d.gender = $('#genderFilter').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'nik', name: 'nik' },
            { data: 'name', name: 'name' },
            { data: 'vessel_name', name: 'vessel.name' },
            { data: 'position', name: 'position' },
            { data: 'gender_badge', name: 'gender' },
            { data: 'age_display', name: 'age' },
            { data: 'phone', name: 'phone' },
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
            sSearchPlaceholder: "Search..",
            sLengthMenu: "Results :  _MENU_",
        },
        stripeClasses: [],
        lengthMenu: [7, 10, 20, 50],
        pageLength: 10,
        order: [[2, 'asc']]
    });

    var addButton = $('<a href="{{ route('crew-members.create') }}" class="btn btn-primary" style="margin-left: 10px;">+ Add Crew Member</a>');
    $('div.dataTables_filter').append(addButton);

    $('#statusFilter, #vesselFilter, #positionFilter, #genderFilter').on('change', function() {
        table.draw();
    });

    $('#resetFilters').on('click', function() {
        $('#statusFilter').val('');
        $('#vesselFilter').val('');
        $('#positionFilter').val('');
        $('#genderFilter').val('');
        table.draw();
    });

    $(document).on('click', '.delete-crew', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        const crewName = $(this).data('name');

        Swal.fire({
            title: 'Are you sure?',
            text: "You will delete crew member: " + crewName,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-outline-danger ms-1'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            table.draw();
                            Toast.fire({ icon: 'success', title: response.message });
                        }
                    },
                    error: function(xhr) {
                        Toast.fire({ icon: 'error', title: xhr.responseJSON.message });
                    }
                });
            }
        });
    });

    $(document).on('click', '.toggle-status', function(e) {
        e.preventDefault();
        const url = $(this).data('url');

        $.ajax({
            url: url,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    Toast.fire({ icon: 'success', title: response.message });
                    table.draw();
                }
            }
        });
    });

});
</script>
@endpush
