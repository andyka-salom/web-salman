@extends('layouts.app')
@section('title', 'Company Profile')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
<style>
    .company-logo-container {
        position: relative;
        width: 150px;
        height: 150px;
        margin: 0 auto;
    }
    .company-logo {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 8px;
        border: 2px solid #e0e6ed;
        background: #fff;
        padding: 5px;
        transition: all 0.3s ease;
    }
    .logo-overlay {
        position: absolute;
        bottom: -10px;
        right: -10px;
        background: #4361ee;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 2px solid #fff;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .logo-overlay:hover { background: #1b2e4b; }
    .logo-overlay svg { color: #fff; width: 20px; height: 20px; }
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">

        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                        <h4 class="fw-bold mb-0">Edit Company Profile</h4>
                        <div>
                            @if($company->is_active)
                                <span class="badge badge-light-success">Active Profile</span>
                            @else
                                <span class="badge badge-light-danger">Inactive Profile</span>
                            @endif
                        </div>
                    </div>

                    {{-- Link route disesuaikan dengan standar resource atau update profile --}}
                    <form id="company-profile-form" enctype="multipart/form-data">
                        @csrf
                        @method('PUT') {{-- Biasanya update menggunakan PUT/PATCH --}}

                        <div class="row mb-4">
                            <div class="col-md-4 text-center border-end">
                                <label class="form-label fw-bold d-block mb-3">Company Logo</label>
                                <div class="company-logo-container mb-3">
                                    <img id="logoPreview"
                                         src="{{ $company->logo ? Storage::url($company->logo) : asset('assets/img/company-placeholder.png') }}"
                                         class="company-logo shadow-sm" alt="Company Logo">
                                    <label for="logoInput" class="logo-overlay" data-bs-toggle="tooltip" title="Change Logo">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-camera"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                                    </label>
                                    <input type="file" id="logoInput" name="logo" class="d-none" accept="image/jpeg,image/png,image/jpg,image/svg+xml">
                                </div>
                                <p class="text-muted small">Max file size: 2MB.<br>Format: JPG, PNG, SVG</p>
                                <div id="logo-error" class="text-danger small mt-1"></div>
                            </div>

                            <div class="col-md-8">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Company Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name" value="{{ old('name', $company->name) }}" placeholder="Enter company name" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Address <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="address" rows="5" placeholder="Full address..." required>{{ old('address', $company->address) }}</textarea>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                {{-- Jika admin ingin mengubah status aktif melalui profile --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ $company->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="is_active">Active Status</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-5">
                            <div class="col-12 text-end border-top pt-4">
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary me-2">Back to Dashboard</a>
                                <button type="submit" class="btn btn-primary" id="btnSave">
                                    <i class="fas fa-save me-1"></i> Save Profile Changes
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

    // Logo Preview
    $('#logoInput').on('change', function(){
        const file = this.files[0];
        if (file){
            if (file.size > 2048000) {
                Toast.fire({ icon: 'error', title: 'File size must not exceed 2MB' });
                $(this).val('');
                return;
            }
            let reader = new FileReader();
            reader.onload = function(event){
                $('#logoPreview').attr('src', event.target.result);
            }
            reader.readAsDataURL(file);
        }
    });

    // Handle Form Submit
    $('#company-profile-form').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);
        // Karena kita pakai @method('PUT'), tambahkan manual ke FormData
        formData.append('_method', 'PUT');

        // Bersihkan state error
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#logo-error').text('');

        Swal.fire({
            title: 'Please Wait',
            html: 'Updating company profile...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            // Sesuaikan URL route dengan ID company Anda
            url: "{{ route('companies.update', $company->id) }}",
            type: 'POST', // Tetap POST karena FormData, tapi didalamnya ada _method PUT
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                Swal.close();
                Toast.fire({
                    icon: 'success',
                    title: res.message || 'Profile updated successfully'
                });
            },
            error: function(xhr) {
                Swal.close();

                if(xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        if(field === 'logo') {
                            $('#logo-error').text(messages[0]);
                        } else {
                            const input = $(`[name="${field}"]`);
                            input.addClass('is-invalid');
                            input.siblings('.invalid-feedback').text(messages[0]);
                        }
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please check the form for errors.',
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Something went wrong.',
                        confirmButtonText: 'OK'
                    });
                }
            }
        });
    });
});
</script>
@endpush
