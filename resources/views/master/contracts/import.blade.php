@extends('layouts.app')

@section('title', 'Import Contracts')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
<style>
    .import-info {
        background: #f8f9fa;
        border: 1px dashed #ced4da;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
    }
    #errorList li {
        margin-left: -15px;
    }
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        {{-- Assume layout handles breadcrumbs inclusion --}}

        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8">

                    <h5 class="mb-4">Import Contracts Data</h5>

                    <form id="importForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="file" class="form-label">Upload Excel File (.xlsx, .xls, .csv) <span class="text-danger">*</span></label>
                            <input type="file"
                                   class="form-control"
                                   id="file"
                                   name="file"
                                   accept=".xlsx, .xls, .csv"
                                   required>
                            <small class="text-muted">Maksimal ukuran file: 5MB.</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="import-info">
                            <h6>Panduan Import:</h6>
                            <p class="small mb-2">Pastikan baris header pada file Excel Anda menggunakan nama kolom berikut (Wajib huruf kecil dan tanpa spasi):</p>
                            <ul class="small">
                                <li>**nama_kontraktor** (Wajib, Unik)</li>
                                <li>**nomor_sap** (Wajib, Unik)</li>
                                <li>email_kantor (Opsional, Unik)</li>
                                <li>nomor_telepon_kantor (Opsional)</li>
                                <li>alamat_kantor (Opsional)</li>
                            </ul>
                            <p class="small text-danger mb-0">Kesalahan validasi (data duplikat atau kolom wajib kosong) akan diuraikan di bawah jika proses import gagal.</p>
                        </div>


                        <div id="errorDetails" class="mt-4 alert alert-danger d-none">
                            <strong>Import Gagal!</strong>
                            <p class="mt-2 mb-0">Terdapat kesalahan validasi pada data:</p>
                            <ul id="errorList" class="mt-2 mb-0">
                                {{-- Errors will be appended here --}}
                            </ul>
                        </div>


                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-upload"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                <span class="btn-text-inner ms-2">Start Import</span>
                            </button>
                            <a href="{{ route('contracts.index') }}" class="btn btn-secondary">Cancel</a>
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
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    $('#importForm').on('submit', function(e) {
        e.preventDefault();

        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Importing...');

        $('#errorDetails').addClass('d-none');
        $('.is-invalid').removeClass('is-invalid');

        const formData = new FormData(this);

        $.ajax({
            url: "{{ route('contracts.import') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Import Success',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    });

                    setTimeout(() => {
                        window.location.href = response.redirect;
                    }, 1500);
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalText);

                if (xhr.status === 422) {
                    const response = xhr.responseJSON;

                    if (response.error_type === 'validation' && response.errors) {
                        $('#errorList').empty();
                        response.errors.forEach(err => {
                            $('#errorList').append(`<li>${err}</li>`);
                        });
                        $('#errorDetails').removeClass('d-none');
                    } else if (response.errors && response.errors.file) {
                        $('#file').addClass('is-invalid');
                        $('#file').next('.invalid-feedback').text(response.errors.file[0]);
                        Toast.fire({ icon: 'error', title: 'File validation failed.' });
                    }

                } else {
                    const response = xhr.responseJSON;
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'An unexpected error occurred during import.'
                    });
                }
            }
        });
    });
});
</script>
@endpush
