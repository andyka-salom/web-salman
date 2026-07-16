@extends('layouts.app')

@section('title', isset($vessel) ? 'Edit Vessel' : 'Create Vessel')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/tomSelect/tom-select.default.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/flatpickr/flatpickr.css') }}">
<style>
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

                    <form id="vesselForm">
                        @csrf
                        @if(isset($vessel))
                            @method('PUT')
                        @endif

                        {{-- Header --}}
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                            <h4 class="fw-bold mb-0">{{ isset($vessel) ? 'Edit Vessel' : 'New Vessel Information' }}</h4>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3">
                                    {{-- Company Field: Role Based --}}
                                    <div class="col-md-6">
                                        <label for="company_id" class="form-label fw-bold">Company <span class="text-danger">*</span></label>
                                        @hasrole('super-admin')
                                            <select class="form-select" id="company_id" name="company_id" required>
                                                <option value="">Select Company</option>
                                                @foreach($companies as $company)
                                                    <option value="{{ $company->id }}"
                                                        {{ old('company_id', isset($vessel) ? $vessel->company_id : '') == $company->id ? 'selected' : '' }}>
                                                        {{ $company->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="text" class="form-control bg-light" value="{{ auth()->user()->company->name }}" readonly>
                                            <input type="hidden" id="company_id" name="company_id" value="{{ auth()->user()->company_id }}">
                                        @endhasrole
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    {{-- Coordinator Field --}}
                                    <div class="col-md-6">
                                        <label for="coordinator_id" class="form-label fw-bold">Crew Penanggung Jawab DCU<span class="text-danger">*</span></label>
                                        <select class="form-select" id="coordinator_id" name="coordinator_id" required>
                                            <option value="">Select Coordinator</option>
                                            @foreach($coordinators as $coordinator)
                                                <option value="{{ $coordinator->id }}"
                                                    {{ old('coordinator_id', isset($vessel) ? $vessel->coordinator_id : '') == $coordinator->id ? 'selected' : '' }}>
                                                    {{ $coordinator->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label fw-bold">Vessel Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name"
                                               value="{{ old('name', isset($vessel) ? $vessel->name : '') }}"
                                               placeholder="Enter vessel name" required>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="type" class="form-label fw-bold">Vessel Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="type" name="type" required>
                                            <option value="">Select Type</option>
                                            @foreach($vesselTypes as $key => $label)
                                                <option value="{{ $key }}"
                                                    {{ old('type', isset($vessel) ? $vessel->type : '') == $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="valid_until" class="form-label fw-bold">Valid Until</label>
                                        <input type="text" class="form-control" id="valid_until" name="valid_until"
                                               value="{{ old('valid_until', isset($vessel) && $vessel->valid_until ? $vessel->valid_until->format('Y-m-d') : '') }}"
                                               placeholder="Select date">
                                        <small class="text-muted">Leave empty for no expiry date</small>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                                   {{ old('is_active', isset($vessel) ? $vessel->is_active : true) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="is_active">Active Status</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="row mt-4">
                            <div class="col-12 text-end border-top pt-4">
                                <a href="{{ route('vessels.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> {{ isset($vessel) ? 'Update Vessel' : 'Create Vessel' }}
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

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    // --- Init Components ---
    let tsConfig = { placeholder: 'Select Option', allowEmptyOption: false, sortField: 'text' };

    let tsCompany = null;
    if(document.getElementById('company_id') && document.getElementById('company_id').tagName === 'SELECT') {
        tsCompany = new TomSelect('#company_id', tsConfig);
    }

    let tsCoordinator = new TomSelect('#coordinator_id', tsConfig);

    // Type is now REQUIRED - no empty option allowed
    let tsType = new TomSelect('#type', {
        placeholder: 'Select Type',
        allowEmptyOption: false
    });

    flatpickr('#valid_until', { dateFormat: 'Y-m-d', minDate: 'today', allowInput: true });

    // --- Form Submit ---
    $('#vesselForm').on('submit', function(e) {
        e.preventDefault();

        $('.is-invalid').removeClass('is-invalid');
        $('.ts-wrapper').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        const formData = new FormData(this);
        const isEdit = @json(isset($vessel));
        const url = @json(isset($vessel) ? route('vessels.update', $vessel->id ?? 0) : route('vessels.store'));

        Swal.fire({
            title: 'Mohon Tunggu',
            html: isEdit ? 'Sedang memperbarui data kapal...' : 'Sedang menyimpan data kapal...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => { Swal.showLoading(); }
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

                        if (['company_id', 'coordinator_id', 'type'].includes(field)) {
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
                        text: 'Silakan periksa kembali isian Anda.',
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
