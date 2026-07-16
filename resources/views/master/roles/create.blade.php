@extends('layouts.app')
@section('title', isset($role) ? 'Edit Role' : 'Create Role')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
<style>
    /* Styling Card */
    .permission-card {
        border: 1px solid #e0e6ed;
        border-radius: 8px;
        background: #fff;
        height: 100%;
        transition: all 0.3s ease;
    }
    .permission-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border-color: #4361ee;
    }

    /* Header Card */
    .permission-header {
        background: #fbfcff;
        padding: 10px 15px;
        border-bottom: 1px solid #e0e6ed;
        display: flex;
        align-items: center;
    }

    /* Agar area klik checkbox judul lebih luas */
    .permission-header .form-check {
        margin-bottom: 0;
        width: 100%;
    }

    .group-label {
        font-weight: 700;
        text-transform: uppercase;
        font-size: 13px;
        color: #3b3f5c;
        cursor: pointer;
        user-select: none;
    }

    /* Body Card */
    .permission-body {
        padding: 15px;
    }
    .permission-item {
        margin-bottom: 8px;
    }
    .permission-item:last-child {
        margin-bottom: 0;
    }
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 shadow-sm">
                    <form id="roleForm">
                        @csrf
                        @if(isset($role)) @method('PUT') @endif

                        <!-- HEADER & ACTIONS -->
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 pb-3 border-bottom">
                            <h4 class="fw-bold mb-3 mb-md-0">{{ isset($role) ? 'Edit Role' : 'Create Role' }}</h4>
                            <div class="d-flex gap-2">
                                <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Back</a>
                                <button type="submit" class="btn btn-primary" id="btnSaveHeader">
                                    <i class="fas fa-save me-1"></i> Save
                                </button>
                            </div>
                        </div>

                        <!-- INPUT NAMA & SEARCH -->
                        <div class="row mb-4">
                            <div class="col-md-5">
                                <label class="form-label fw-bold">Role Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name"
                                       value="{{ old('name', $role->name ?? '') }}" required placeholder="e.g. Supervisor">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-7 d-flex align-items-end justify-content-md-end mt-3 mt-md-0 gap-2">
                                <input type="text" class="form-control" id="permissionSearch" placeholder="Cari permission..." style="max-width: 250px;">
                                <button type="button" class="btn btn-sm btn-info text-white" id="selectAll">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-dark" id="deselectAll">Deselect All</button>
                            </div>
                        </div>

                        <!-- LIST PERMISSION -->
                        <div class="row g-4" id="permissionsContainer">
                            @foreach($permissions as $group => $perms)
                                <div class="col-md-6 col-lg-4 permission-group-wrapper" data-group-name="{{ strtolower($group) }}">
                                    <div class="permission-card">

                                        <!-- Header Group -->
                                        <div class="permission-header">
                                            @if(count($perms) > 1)
                                                <!-- Jika Item > 1, Tampilkan Checkbox 'Pilih Semua' -->
                                                <div class="form-check">
                                                    <input class="form-check-input group-checkbox" type="checkbox"
                                                           id="group_{{ $group }}"
                                                           data-group="{{ $group }}">
                                                    <label class="form-check-label group-label" for="group_{{ $group }}">
                                                        {{ ucfirst($group) }} <span class="text-muted fw-normal" style="font-size: 11px;">(All)</span>
                                                    </label>
                                                </div>
                                            @else
                                                <!-- Jika Item cuma 1, Hanya Tampilkan Judul (Tanpa Checkbox) -->
                                                <span class="group-label ps-1">{{ ucfirst($group) }}</span>
                                            @endif
                                        </div>

                                        <!-- Isi Permission -->
                                        <div class="permission-body">
                                            @foreach($perms as $perm)
                                                <div class="form-check permission-item">
                                                    <input class="form-check-input perm-check group-{{ $group }}"
                                                           type="checkbox"
                                                           name="permissions[]"
                                                           value="{{ $perm->name }}"
                                                           id="perm_{{ $perm->id }}"
                                                           {{ isset($rolePermissions) && in_array($perm->name, $rolePermissions) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm_{{ $perm->id }}">
                                                        {{ str_replace($group.'.', '', $perm->name) }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>

                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row mt-5">
                            <div class="col-12 text-end border-top pt-4">
                                <a href="{{ route('roles.index') }}" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="btnSaveFooter">Save Role</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>
<script>
    $(document).ready(function() {

        // Define Toast Mixin untuk notifikasi top-end
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        // 1. Logic Checkbox Header (Select All per Group)
        $('.group-checkbox').change(function() {
            let group = $(this).data('group');
            let isChecked = $(this).is(':checked');
            // Centang semua anak buahnya
            $(`.group-${group}`).prop('checked', isChecked);
        });

        // 2. Logic Checkbox Anak (Update Header jika semua anak dicentang)
        $('.perm-check').change(function() {
            // Ambil nama group dari class (misal: group-users)
            let groupClass = $(this).attr('class').split(' ').find(c => c.startsWith('group-'));
            let groupName = groupClass.replace('group-', '');

            // Cek apakah semua anak dalam grup ini sudah dicentang
            let totalInGroup = $(`.${groupClass}`).length;
            let totalChecked = $(`.${groupClass}:checked`).length;

            // Update checkbox header (jika ada)
            $(`#group_${groupName}`).prop('checked', totalInGroup === totalChecked);
        });

        // 3. Global Select All / Deselect All
        $('#selectAll').click(function() {
            $('.perm-check:visible').prop('checked', true);
            $('.group-checkbox:visible').prop('checked', true);
        });

        $('#deselectAll').click(function() {
            $('.perm-check:visible').prop('checked', false);
            $('.group-checkbox:visible').prop('checked', false);
        });

        // 4. Search Function (Real-time)
        $('#permissionSearch').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('.permission-group-wrapper').each(function() {
                var groupWrapper = $(this);
                var groupName = groupWrapper.data('group-name');
                var hasVisiblePerms = false;

                // Cek item di dalam grup
                groupWrapper.find('.permission-item').each(function() {
                    var label = $(this).find('label').text().toLowerCase();
                    if (label.indexOf(value) > -1 || groupName.indexOf(value) > -1) {
                        $(this).show();
                        hasVisiblePerms = true;
                    } else {
                        $(this).hide();
                    }
                });

                // Tampilkan/Sembunyikan card grup
                hasVisiblePerms ? groupWrapper.fadeIn(100) : groupWrapper.hide();
            });
        });

        // 5. Submit Form dengan SweetAlert Loading
        $('#roleForm').on('submit', function(e) {
            e.preventDefault();
            $('.is-invalid').removeClass('is-invalid'); // Bersihkan feedback lama

            var formData = new FormData(this);
            var url = @json(isset($role) ? route('roles.update', $role->id) : route('roles.store'));
            var isEdit = @json(isset($role));

            // Tampilkan loading alert
            Swal.fire({
                title: 'Mohon Tunggu',
                html: isEdit ? 'Sedang mengupdate role...' : 'Sedang menyimpan role...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    // Tutup loading
                    Swal.close();

                    if(res.success) {
                        // Tampilkan success toast
                        Toast.fire({ icon: 'success', title: res.message });
                        setTimeout(() => { window.location.href = res.redirect; }, 1000);
                    }
                },
                error: function(xhr) {
                    // Tutup loading
                    Swal.close();

                    if(xhr.status === 422) {
                        // Handle validation errors
                        const errors = xhr.responseJSON.errors;
                        if (errors.name) {
                            $('[name="name"]').addClass('is-invalid').next('.invalid-feedback').text(errors.name[0]);
                        }

                        // Error alert untuk validation
                        Swal.fire({
                            icon: 'error',
                            title: 'Validasi Gagal',
                            text: 'Silakan periksa input Anda',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        // Error alert untuk server error
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            text: xhr.responseJSON?.message || 'Server Error',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        });

        // Trigger perubahan awal (untuk Edit Mode agar header checkbox menyesuaikan)
        $('.perm-check').trigger('change');
    });
</script>
@endpush
