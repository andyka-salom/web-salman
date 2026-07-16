@extends('layouts.app')

@section('title', 'Crew Management')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/table/datatable/datatables.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/css/light/table/datatable/dt-global_style.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/css/dark/table/datatable/dt-global_style.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
<link href="{{ asset('plugins/css/light/sweetalerts2/custom-sweetalert.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('plugins/css/dark/sweetalerts2/custom-sweetalert.css') }}" rel="stylesheet" type="text/css" />

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
    .stats-card:hover { transform: translateY(-2px); }
    .stats-icon {
        width: 48px; height: 48px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center; font-size: 24px;
    }
    .crew-avatar {
        width: 40px; height: 40px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: bold; color: white; font-size: 14px;
    }
    .page-title-action .btn {
        font-weight: 600; display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
    }
    .is-invalid {
        border-color: #dc3545 !important;
    }
    .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    .mcu-section {
        background: var(--card-bg, #f8f9fa);
        border: 1px solid var(--card-border-color, #dee2e6);
        border-radius: 6px;
        padding: 14px 16px;
        margin-bottom: 1rem;
    }
    .mcu-section-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-muted, #6c757d);
        margin-bottom: 12px;
    }
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">

        {{-- Page Header --}}
        <div class="row layout-top-spacing mb-4 align-items-center">
            <div class="col-md-6">
                <h3 class="m-0 fw-bold">Crew Management</h3>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0 page-title-action">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crewModal" id="addCrewBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                    Add Crew Member
                </button>
            </div>
        </div>

        {{-- Filter Section --}}
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="filter-section">
                <div class="row g-3">
                    @hasrole('super-admin')
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Company Filter</label>
                        <select class="form-select" id="companyFilter">
                            <option value="">All Companies</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endhasrole

                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Vessel Filter</label>
                        <select class="form-select" id="vesselFilter">
                            <option value="">All Vessels</option>
                            @foreach($vessels as $vessel)
                                <option value="{{ $vessel->id }}">{{ $vessel->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Position</label>
                        <select class="form-select" id="positionFilter">
                            <option value="">All Positions</option>
                            @foreach($positions as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">MCU Status</label>
                        <select class="form-select" id="mcuStatusFilter">
                            <option value="">All MCU</option>
                            <option value="valid">Valid</option>
                            <option value="expiring_soon">Expiring Soon</option>
                            <option value="expired">Expired</option>
                            <option value="none">No MCU</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Health Category</label>
                        <select class="form-select" id="healthCategoryFilter">
                            <option value="">All Categories</option>
                            @foreach($healthCategories as $code => $label)
                                <option value="{{ $code }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-secondary w-100" id="resetFilters" style="height: 46px;">
                            <i data-feather="refresh-cw" class="me-2"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- DataTable Section --}}
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <table id="crew-table" class="table dt-table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Crew Name</th>
                            <th>NIK</th>
                            <th>Current Vessel</th>
                            @if(Auth::user()->hasRole('super-admin'))
                            <th>Company</th>
                            @endif
                            <th>Position</th>
                            <th>MCU Valid Until</th>
                            <th>Health Cat.</th>
                            <th>Status</th>
                            <th class="no-content text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>
</div>

{{-- Add/Edit Crew Modal --}}
<div class="modal fade" id="crewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="crewModalTitle">Add Crew Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="crewForm">
                @csrf
                <input type="hidden" id="crewId" name="crew_id">
                <div class="modal-body">

                    {{-- Company (super-admin only) --}}
                    @if(Auth::user()->hasRole('super-admin'))
                    <div class="mb-3">
                        <label class="form-label fw-bold">Company <span class="text-danger">*</span></label>
                        <select class="form-select" id="modal_company_id" name="company_id" required>
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    @endif

                    {{-- Name & NIK --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="modal_name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">NIK <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nik" id="modal_nik" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    {{-- Position & Phone --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Position</label>
                            <select class="form-select" name="position" id="modal_position">
                                <option value="">Select Position</option>
                                @foreach($positions as $code => $name)
                                    <option value="{{ $code }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Phone</label>
                            <input type="text" class="form-control" name="phone" id="modal_phone">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    {{-- MCU Section --}}
                    <div class="mcu-section">
                        <div class="mcu-section-title">
                            <i data-feather="activity" style="width:13px;height:13px;vertical-align:-1px;" class="me-1"></i>
                            Medical Check-Up (MCU)
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-0">
                                <label class="form-label fw-bold">MCU Valid Until <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="mcu_valid_until" id="modal_mcu_valid_until" required>
                                <div class="invalid-feedback"></div>
                                <small class="text-muted">Tanggal akhir masa aktif hasil MCU</small>
                            </div>
                            <div class="col-md-6 mb-0">
                                <label class="form-label fw-bold">Health Category <span class="text-danger">*</span></label>
                                <select class="form-select" name="health_category" id="modal_health_category" required>
                                    <option value="">— Select Category —</option>
                                    @foreach($healthCategories as $code => $label)
                                        <option value="{{ $code }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                                <small class="text-muted">Kategori kesehatan hasil MCU</small>
                            </div>
                        </div>
                    </div>

                    {{-- Active Status --}}
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="modal_is_active" name="is_active" value="1" checked>
                        <label class="form-check-label fw-bold" for="modal_is_active">Active Status</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Assign to Vessel Modal --}}
<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5>Assign Crew to Vessel</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="assignForm">
                @csrf
                <input type="hidden" id="assignCrewId">
                <div class="modal-body">
                    <p>Assign <strong id="assignCrewName" class="text-primary"></strong> to:</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Vessel *</label>
                        <select class="form-select" id="assign_vessel_id" name="vessel_id" required>
                            <option value="">Select Vessel</option>
                            @foreach($vessels as $vessel)
                                <option value="{{ $vessel->id }}">{{ $vessel->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Assign Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Transfer Crew Modal --}}
<div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5>Transfer Crew Member</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="transferForm">
                @csrf
                <input type="hidden" id="transferCrewId">
                <div class="modal-body">
                    <div class="alert alert-info">
                        Transfer <strong id="transferCrewName"></strong> from <strong id="currentVesselName" class="text-warning"></strong> to:
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">New Vessel *</label>
                        <select class="form-select" id="new_vessel_id" name="new_vessel_id" required>
                            <option value="">Select Vessel</option>
                            @foreach($vessels as $vessel)
                                <option value="{{ $vessel->id }}">{{ $vessel->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Transfer Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
@if(!isset($featherLoaded))
<script src="https://unpkg.com/feather-icons"></script>
@endif

<script src="{{ asset('plugins/src/table/datatable/datatables.js') }}"></script>
<script src="{{ asset('plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>

<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

function initializePage() {
    if (typeof feather === 'undefined') {
        setTimeout(initializePage, 100);
        return;
    }

    feather.replace();

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    function clearValidationErrors() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('').hide();
    }

    function displayValidationErrors(errors) {
        clearValidationErrors();
        $.each(errors, function(field, messages) {
            const input = $('[name="' + field + '"]');
            input.addClass('is-invalid');
            input.siblings('.invalid-feedback').text(messages[0]).show();
        });
    }

    // ===================================
    // DataTable
    // ===================================
    const table = $('#crew-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('crew.index') }}",
            data: function(d) {
                d.status          = $('#statusFilter').val();
                d.company_id      = $('#companyFilter').val();
                d.vessel_id       = $('#vesselFilter').val();
                d.position        = $('#positionFilter').val();
                d.mcu_status      = $('#mcuStatusFilter').val();
                d.health_category = $('#healthCategoryFilter').val();
            }
        },
        columns: [
            { data: 'name_avatar',   name: 'name' },
            { data: 'nik',           name: 'nik' },
            { data: 'vessel_badge',  name: 'currentVesselAssignment.vessel.name' },
            @if(Auth::user()->hasRole('super-admin'))
            { data: 'company_name',  name: 'company.name' },
            @endif
            { data: 'position_name', name: 'position' },
            { data: 'mcu_badge',     name: 'mcu_valid_until', orderable: true,  searchable: false },
            { data: 'health_badge',  name: 'health_category', orderable: false, searchable: false },
            { data: 'status_badge',  name: 'is_active' },
            { data: 'action',        name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        dom: "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'l><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'f>>>" +
            "<'table-responsive'tr>" +
            "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count mb-sm-0 mb-3'i><'dt--pagination'p>>",
        oLanguage: {
            oPaginate: { sPrevious: '<i data-feather="arrow-left"></i>', sNext: '<i data-feather="arrow-right"></i>' },
            sSearch: '<i data-feather="search"></i>',
            sSearchPlaceholder: "Search...",
            sLengthMenu: "Results :  _MENU_",
        },
        stripeClasses: [],
        lengthMenu: [10, 20, 50],
        pageLength: 10,
        drawCallback: function() { feather.replace(); }
    });

    // ===================================
    // Filters
    // ===================================
    $('#statusFilter, #companyFilter, #vesselFilter, #positionFilter, #mcuStatusFilter, #healthCategoryFilter').on('change', function() {
        table.draw();
    });

    $('#resetFilters').on('click', function() {
        $('#statusFilter, #companyFilter, #vesselFilter, #positionFilter, #mcuStatusFilter, #healthCategoryFilter').val('');
        table.draw();
    });

    // Clear individual field error on interaction
    $('input, select, textarea').on('change keyup', function() {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').text('').hide();
    });

    // ===================================
    // Add / Edit — Form Submit
    // ===================================
    $('#crewForm').on('submit', function(e) {
        e.preventDefault();
        clearValidationErrors();

        const crewId   = $('#crewId').val();
        const formData = new FormData(this);

        // Ensure is_active value is sent correctly (checkbox)
        if (!$('#modal_is_active').is(':checked')) {
            formData.set('is_active', '0');
        }

        let url, method;
        if (crewId) {
            url    = `/crew/${crewId}`;
            method = 'POST';
            formData.append('_method', 'PUT');
        } else {
            url    = '/crew';
            method = 'POST';
        }

        const $btn = $(this).find('[type="submit"]');
        $btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    $('#crewModal').modal('hide');
                    Toast.fire({ icon: 'success', title: res.message });
                    table.draw();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    displayValidationErrors(xhr.responseJSON.errors);
                    const firstError = Object.values(xhr.responseJSON.errors)[0][0];
                    Toast.fire({ icon: 'error', title: firstError });
                } else {
                    const message = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : 'Error saving data';
                    Toast.fire({ icon: 'error', title: message });
                }
            },
            complete: function() {
                $btn.prop('disabled', false).text('Save Changes');
            }
        });
    });

    // ===================================
    // Edit — Populate modal fields
    // ===================================
    $(document).on('click', '.edit-crew', function() {
        clearValidationErrors();

        try {
            const data = JSON.parse(atob($(this).attr('data-crew')));

            $('#crewId').val(data.id);
            $('#modal_name').val(data.name);
            $('#modal_nik').val(data.nik);
            $('#modal_phone').val(data.phone || '');
            $('#modal_position').val(data.position || '');
            $('#modal_is_active').prop('checked', !!data.is_active);

            // MCU fields (mandatory)
            $('#modal_mcu_valid_until').val(data.mcu_valid_until || '');
            $('#modal_health_category').val(data.health_category || '');

            @if(Auth::user()->hasRole('super-admin'))
                $('#modal_company_id').val(data.company_id);
            @endif

            $('#crewModalTitle').text('Edit Crew Member');
            $('#crewModal').modal('show');
        } catch (error) {
            Toast.fire({ icon: 'error', title: 'Error loading crew data: ' + error.message });
        }
    });

    // ===================================
    // Delete
    // ===================================
    $(document).on('click', '.delete-crew', function() {
        const id = $(this).data('crew-id');
        Swal.fire({
            title: 'Delete Crew?',
            text: 'This action cannot be undone',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete!',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-outline-secondary ms-1'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/crew/${id}`,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                    success: function(res) {
                        table.draw();
                        Toast.fire({ icon: 'success', title: res.message || 'Deleted successfully' });
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'Failed to delete';
                        Toast.fire({ icon: 'error', title: message });
                    }
                });
            }
        });
    });

    // ===================================
    // Assign
    // ===================================
    $(document).on('click', '.assign-crew', function() {
        clearValidationErrors();
        $('#assignCrewId').val($(this).data('crew-id'));
        $('#assignCrewName').text($(this).data('crew-name'));
        $('#assign_vessel_id').val('');
        $('#assignModal').modal('show');
    });

    $('#assignForm').on('submit', function(e) {
        e.preventDefault();
        clearValidationErrors();
        const crewId = $('#assignCrewId').val();

        const $btn = $(this).find('[type="submit"]');
        $btn.prop('disabled', true).text('Assigning...');

        $.ajax({
            url: `/crew/${crewId}/assign`,
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.success) {
                    $('#assignModal').modal('hide');
                    Toast.fire({ icon: 'success', title: res.message });
                    table.draw();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    displayValidationErrors(xhr.responseJSON.errors);
                    const firstError = Object.values(xhr.responseJSON.errors)[0][0];
                    Toast.fire({ icon: 'error', title: firstError });
                } else {
                    const message = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : 'Failed to assign';
                    Toast.fire({ icon: 'error', title: message });
                }
            },
            complete: function() {
                $btn.prop('disabled', false).text('Assign Now');
            }
        });
    });

    // ===================================
    // Transfer
    // ===================================
    $(document).on('click', '.transfer-crew', function() {
        clearValidationErrors();
        $('#transferCrewId').val($(this).data('crew-id'));
        $('#transferCrewName').text($(this).data('crew-name'));
        $('#currentVesselName').text($(this).data('vessel-name'));
        $('#new_vessel_id').val('');
        $('#transferModal').modal('show');
    });

    $('#transferForm').on('submit', function(e) {
        e.preventDefault();
        clearValidationErrors();
        const crewId = $('#transferCrewId').val();

        const $btn = $(this).find('[type="submit"]');
        $btn.prop('disabled', true).text('Transferring...');

        $.ajax({
            url: `/crew/${crewId}/transfer`,
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.success) {
                    $('#transferModal').modal('hide');
                    Toast.fire({ icon: 'success', title: res.message });
                    table.draw();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    displayValidationErrors(xhr.responseJSON.errors);
                    const firstError = Object.values(xhr.responseJSON.errors)[0][0];
                    Toast.fire({ icon: 'error', title: firstError });
                } else {
                    const message = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : 'Failed to transfer';
                    Toast.fire({ icon: 'error', title: message });
                }
            },
            complete: function() {
                $btn.prop('disabled', false).text('Transfer Now');
            }
        });
    });

    // ===================================
    // Unassign
    // ===================================
    $(document).on('click', '.unassign-crew', function() {
        const crewId   = $(this).data('crew-id');
        const crewName = $(this).data('crew-name');

        Swal.fire({
            title: 'Unassign Crew?',
            html: `Unassign <strong>${crewName}</strong> from current vessel?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, unassign!',
            customClass: {
                confirmButton: 'btn btn-warning',
                cancelButton: 'btn btn-outline-secondary ms-1'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/crew/${crewId}/unassign`,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        if (res.success) {
                            Toast.fire({ icon: 'success', title: res.message });
                            table.draw();
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'Failed to unassign';
                        Toast.fire({ icon: 'error', title: message });
                    }
                });
            }
        });
    });

    // ===================================
    // Modal reset on close
    // ===================================
    $('.modal').on('hidden.bs.modal', function() {
        const form = $(this).find('form')[0];
        if (form) form.reset();
        $('#crewId').val('');
        $('#crewModalTitle').text('Add Crew Member');
        clearValidationErrors();
    });

    $('#addCrewBtn').on('click', function() {
        clearValidationErrors();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePage);
} else {
    initializePage();
}
</script>
@endpush