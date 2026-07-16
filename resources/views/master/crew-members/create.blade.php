@extends('layouts.app')

@section('title', isset($crewMember) ? 'Edit Crew Member' : 'Create Crew Member')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/tomSelect/tom-select.default.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/flatpickr/flatpickr.css') }}">
<style>
    /* Styling agar TomSelect terlihat merah saat validasi gagal */
    .ts-wrapper.is-invalid .ts-control {
        border-color: #e7515a !important;
    }
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">

            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 shadow-sm">

                    <form id="crewMemberForm">
                        @csrf
                        @if(isset($crewMember))
                            @method('PUT')
                        @endif

                        {{-- Header --}}
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                            <h4 class="fw-bold mb-0">{{ isset($crewMember) ? 'Edit Crew Member' : 'Create New Crew Member' }}</h4>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="mb-4 fw-bold text-primary">Personal Information</h5>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name"
                                               value="{{ old('name', $crewMember->name ?? '') }}"
                                               placeholder="Enter full name" required>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="nik" class="form-label fw-bold">NIK <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nik" name="nik"
                                               value="{{ old('nik', $crewMember->nik ?? '') }}"
                                               placeholder="Enter NIK" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="vessel_id" class="form-label fw-bold">Vessel</label>
                                        <select id="vessel_id" name="vessel_id">
                                            <option value="">Select Vessel (Optional)</option>
                                            @foreach($vessels as $vessel)
                                                <option value="{{ $vessel->id }}"
                                                    {{ old('vessel_id', $crewMember->vessel_id ?? '') == $vessel->id ? 'selected' : '' }}>
                                                    {{ $vessel->name }} - {{ $vessel->company->name ?? 'N/A' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="position" class="form-label fw-bold">Position</label>
                                        <select id="position" name="position">
                                            <option value="">Select Position</option>
                                            @foreach($positions as $code => $name)
                                                <option value="{{ $code }}"
                                                    {{ old('position', $crewMember->position ?? '') == $code ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="gender" class="form-label fw-bold">Gender</label>
                                        <select id="gender" name="gender">
                                            <option value="">Select Gender</option>
                                            <option value="male" {{ old('gender', $crewMember->gender ?? '') == 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ old('gender', $crewMember->gender ?? '') == 'female' ? 'selected' : '' }}>Female</option>
                                            <option value="other" {{ old('gender', $crewMember->gender ?? '') == 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="birth_date" class="form-label fw-bold">Birth Date</label>
                                        <input type="text" class="form-control" id="birth_date" name="birth_date"
                                               value="{{ old('birth_date', $crewMember->birth_date ? $crewMember->birth_date->format('Y-m-d') : '') }}"
                                               placeholder="Select birth date">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="blood_type" class="form-label fw-bold">Blood Type</label>
                                        <select id="blood_type" name="blood_type">
                                            <option value="">Select Blood Type</option>
                                            @foreach($bloodTypes as $type)
                                                <option value="{{ $type }}"
                                                    {{ old('blood_type', $crewMember->blood_type ?? '') == $type ? 'selected' : '' }}>
                                                    {{ $type }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label fw-bold">Phone Number</label>
                                        <input type="text" class="form-control" id="phone" name="phone"
                                               value="{{ old('phone', $crewMember->phone ?? '') }}"
                                               placeholder="e.g. 08123456789">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="address" class="form-label fw-bold">Address</label>
                                        <textarea class="form-control" id="address" name="address" rows="1"
                                                  placeholder="Enter full address">{{ old('address', $crewMember->address ?? '') }}</textarea>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row mb-3 mt-4">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                                   {{ old('is_active', $crewMember->is_active ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="is_active">Active Status</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="row mt-4">
                            <div class="col-12 text-end border-top pt-4">
                                <a href="{{ route('crew-members.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> {{ isset($crewMember) ? 'Update Crew Member' : 'Save Crew Member' }}
                                </button>
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
<script src="{{ asset('plugins/src/tomselect/tom-select.base.js') }}"></script>
<script src="{{ asset('plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>
<script src="{{ asset('plugins/src/flatpickr/flatpickr.js') }}"></script>
<script>
$(document).ready(function() {
    // 1. Inisialisasi Toast
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    // 2. Init TomSelect Components
    let tsVessel = new TomSelect('#vessel_id', { placeholder: 'Select Vessel', allowEmptyOption: true });
    let tsPosition = new TomSelect('#position', { placeholder: 'Select Position', allowEmptyOption: true });
    let tsGender = new TomSelect('#gender', { placeholder: 'Select Gender', allowEmptyOption: true });
    let tsBlood = new TomSelect('#blood_type', { placeholder: 'Select Blood Type', allowEmptyOption: true });

    // 3. Init Flatpickr
    flatpickr('#birth_date', {
        dateFormat: 'Y-m-d',
        maxDate: 'today',
        allowInput: true
    });

    // 4. Handle Form Submission
    $('#crewMemberForm').on('submit', function(e) {
        e.preventDefault();

        // Bersihkan error lama
        $('.is-invalid').removeClass('is-invalid');
        $('.ts-wrapper').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        const formData = new FormData(this);
        const isEdit = @json(isset($crewMember));
        const url = @json(isset($crewMember) ? route('crew-members.update', $crewMember->id ?? 0) : route('crew-members.store'));

        // Tampilkan Loading Alert
        Swal.fire({
            title: 'Mohon Tunggu',
            html: isEdit ? 'Sedang memperbarui data crew...' : 'Sedang menyimpan data crew baru...',
            allowOutsideClick: false,
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
                Swal.close();
                if (res.success) {
                    Toast.fire({ icon: 'success', title: res.message });
                    setTimeout(() => { window.location.href = res.redirect; }, 1000);
                }
            },
            error: function(xhr) {
                Swal.close();
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        const input = $(`[name="${field}"]`);

                        // Handle TomSelect Visuals
                        const tsFields = ['vessel_id', 'position', 'gender', 'blood_type'];
                        if (tsFields.includes(field)) {
                            $(`#${field}`).siblings('.ts-wrapper').addClass('is-invalid');
                            $(`#${field}`).siblings('.invalid-feedback').text(messages[0]).show();
                        } else {
                            input.addClass('is-invalid');
                            input.siblings('.invalid-feedback').text(messages[0]);
                        }
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        text: 'Silakan periksa kembali inputan Anda.',
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Kesalahan',
                        text: xhr.responseJSON?.message || 'Server Error',
                    });
                }
            }
        });
    });
});
</script>
@endpush
