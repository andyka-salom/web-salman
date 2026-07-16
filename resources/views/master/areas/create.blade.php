@extends('layouts.app')

@section('title', isset($area) ? 'Edit Area' : 'Create Area')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/tomSelect/tom-select.default.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
<style>
    /* Styling khusus agar TomSelect terlihat merah saat validasi gagal */
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

                    <form id="areaForm">
                        @csrf
                        @if(isset($area))
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="mb-4 fw-bold">{{ isset($area) ? 'Edit Area' : 'Create Area' }}</h5>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="code" class="form-label fw-bold">Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="code" name="code"
                                               value="{{ old('code', $area->code ?? '') }}"
                                               placeholder="e.g. A-001" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="company_id" class="form-label fw-bold">Company</label>
                                        <select class="form-select" id="company_id" name="company_id">
                                            <option value="">Select Company</option>
                                            @foreach($companies as $company)
                                                <option value="{{ $company->id }}"
                                                    {{ (old('company_id', $area->company_id ?? '') == $company->id) ? 'selected' : '' }}>
                                                    {{ $company->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="name" class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name"
                                               value="{{ old('name', $area->name ?? '') }}"
                                               placeholder="Enter area name" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                                   {{ old('is_active', $area->is_active ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="is_active">Active Status</label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-end border-top pt-4">
                                <a href="{{ route('areas.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> {{ isset($area) ? 'Update Area' : 'Save Area' }}
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
<script>
$(document).ready(function() {
    // Inisialisasi TomSelect
    var companySelect = new TomSelect('#company_id', {
        placeholder: 'Select Company',
        allowEmptyOption: true
    });

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    $('#areaForm').on('submit', function(e) {
        e.preventDefault();

        // Bersihkan error lama
        $('.is-invalid').removeClass('is-invalid');
        $('.ts-wrapper').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        const formData = new FormData(this);
        const isEdit = @json(isset($area));
        const url = @json(isset($area) ? route('areas.update', $area->id) : route('areas.store'));

        // Tampilkan Loading Alert
        Swal.fire({
            title: 'Mohon Tunggu',
            html: isEdit ? 'Sedang memperbarui area...' : 'Sedang menyimpan area...',
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

                        // Khusus untuk Company ID (TomSelect)
                        if (field === 'company_id') {
                            $(companySelect.control).parent().addClass('is-invalid');
                            $(companySelect.control).parent().siblings('.invalid-feedback').text(messages[0]).show();
                        } else {
                            input.addClass('is-invalid');
                            input.siblings('.invalid-feedback').text(messages[0]);
                        }
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        text: 'Silakan periksa inputan Anda.',
                        confirmButtonText: 'OK'
                    });
                } else {
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
});
</script>
@endpush
