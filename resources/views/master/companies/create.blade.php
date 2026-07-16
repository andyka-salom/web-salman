@extends('layouts.app')

@section('title', isset($company) ? 'Edit Company' : 'Create Company')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
<style>
    .logo-preview-container {
        border: 2px dashed #e0e6ed;
        border-radius: 8px;
        padding: 20px;
        background: #fbfbfb;
    }
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">

            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 shadow-sm">

                    <form id="companyForm" enctype="multipart/form-data">
                        @csrf
                        @if(isset($company))
                            @method('PUT')
                        @endif

                        <div class="row">
                            {{-- Company Information --}}
                            <div class="col-md-8">
                                <h5 class="mb-4 fw-bold">Company Information</h5>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="name" class="form-label fw-bold">Company Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name"
                                               value="{{ old('name', $company->name ?? '') }}"
                                               placeholder="Enter company name" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="address" class="form-label fw-bold">Address <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="address" name="address" rows="3"
                                                  placeholder="Enter company address" required>{{ old('address', $company->address ?? '') }}</textarea>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                                   {{ old('is_active', $company->is_active ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="is_active">Active Status</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Logo Upload --}}
                            <div class="col-md-4">
                                <h5 class="mb-4 fw-bold">Company Logo</h5>

                                <div class="logo-preview-container text-center mb-3">
                                    {{-- Container untuk logo saat ini (Edit Mode) --}}
                                    <div id="currentLogoContainer" class="{{ (isset($company) && $company->logo) ? '' : 'd-none' }}">
                                        <div class="position-relative d-inline-block">
                                            <img src="{{ (isset($company) && $company->logo) ? Storage::url($company->logo) : '' }}"
                                                 class="img-fluid rounded shadow-sm"
                                                 style="max-width: 200px; max-height: 200px;"
                                                 id="currentLogoImg">
                                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" id="removeLogo" style="border-radius: 50%;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Container untuk preview logo baru yang dipilih --}}
                                    <div id="logoPreview" class="d-none">
                                        <img src="" class="img-fluid rounded shadow-sm" style="max-width: 200px; max-height: 200px;">
                                        <p class="text-muted mt-2 small">New Logo Preview</p>
                                    </div>

                                    {{-- Placeholder jika tidak ada gambar --}}
                                    <div id="logoPlaceholder" class="{{ (isset($company) && $company->logo) ? 'd-none' : '' }}">
                                        <i class="fas fa-image fa-4x text-light"></i>
                                        <p class="text-muted mt-2">No logo uploaded</p>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="logo" class="form-label fw-bold">Upload Logo</label>
                                    <input type="file" class="form-control" id="logo" name="logo"
                                           accept="image/jpeg,image/png,image/jpg,image/svg+xml">
                                    <small class="text-muted d-block mt-1">Max: 2MB (JPEG, PNG, JPG, SVG)</small>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="row mt-4">
                            <div class="col-12 text-end border-top pt-4">
                                <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> {{ isset($company) ? 'Update Company' : 'Save Company' }}
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
<script src="{{ asset('plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>
<script>
$(document).ready(function() {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    // Logo Preview Logic
    $('#logo').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 2048000) {
                Toast.fire({ icon: 'error', title: 'File size must not exceed 2MB' });
                $(this).val('');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                $('#logoPreview img').attr('src', e.target.result);
                $('#logoPreview').removeClass('d-none');
                $('#currentLogoContainer').addClass('d-none');
                $('#logoPlaceholder').addClass('d-none');
            };
            reader.readAsDataURL(file);
        }
    });

    // Remove Current Logo
    $('#removeLogo').on('click', function() {
        $('#currentLogoContainer').addClass('d-none');
        $('#logoPlaceholder').removeClass('d-none');
        $('#logo').val('');
        $('#logoPreview').addClass('d-none');
    });

    // Form Submission
    $('#companyForm').on('submit', function(e) {
        e.preventDefault();

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        const formData = new FormData(this);
        const isEdit = @json(isset($company));
        const url = @json(isset($company) ? route('companies.update', $company->id) : route('companies.store'));

        Swal.fire({
            title: 'Mohon Tunggu',
            html: isEdit ? 'Sedang memperbarui data perusahaan...' : 'Sedang menyimpan data perusahaan...',
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
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(messages[0]);
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        text: 'Silakan periksa kembali inputan Anda.',
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Kesalahan Sistem',
                        text: xhr.responseJSON?.message || 'Server Error',
                    });
                }
            }
        });
    });
});
</script>
@endpush
