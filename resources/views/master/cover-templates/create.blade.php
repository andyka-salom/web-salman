@extends('layouts.app')

@section('title', isset($coverTemplate) ? 'Edit Template' : 'Create Template')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
<style>
    .card { border: 1px solid #e0e6ed; box-shadow: none; }
    .img-preview-container {
        background: #f8f9fa;
        border: 1px dashed #ced4da;
        border-radius: 6px;
        padding: 10px;
    }
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 shadow-sm">

                    <form id="templateForm" enctype="multipart/form-data">
                        @csrf
                        @if(isset($coverTemplate)) @method('PUT') @endif

                        {{-- Header --}}
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                            <h4 class="fw-bold mb-0">{{ isset($coverTemplate) ? 'Edit Template' : 'Create New Template' }}</h4>
                        </div>

                        <div class="row">
                            <div class="col-lg-7">
                                <h5 class="mb-4 fw-bold text-primary">Template Information</h5>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Template Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name', $coverTemplate->name ?? '') }}" required placeholder="e.g. Standard Corporate">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Description</label>
                                    <textarea class="form-control" name="description" rows="5" placeholder="Short description about this template...">{{ old('description', $coverTemplate->description ?? '') }}</textarea>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $coverTemplate->is_active ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="is_active">Active Status</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <h5 class="mb-4 fw-bold text-primary">Design Assets</h5>

                                {{-- Cover Image --}}
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <label class="form-label fw-bold">Cover Image (Front) <span class="text-danger">*</span></label>
                                        @if(isset($coverTemplate) && $coverTemplate->cover_image_path)
                                            <div class="mb-2 text-center img-preview-container">
                                                <img src="{{ $coverTemplate->cover_url }}" class="img-fluid rounded" style="max-height: 150px;">
                                                <p class="small text-muted mt-1 mb-0">Current Front Cover</p>
                                            </div>
                                        @endif
                                        <input type="file" class="form-control" id="cover_image" name="cover_image" accept="image/*" {{ isset($coverTemplate) ? '' : 'required' }}>
                                        <small class="text-muted d-block mt-1">A4 Ratio recommended. Max 2MB.</small>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                {{-- Page Image --}}
                                <div class="card">
                                    <div class="card-body">
                                        <label class="form-label fw-bold">Page Background (Content) <span class="text-danger">*</span></label>
                                        @if(isset($coverTemplate) && $coverTemplate->page_image_path)
                                            <div class="mb-2 text-center img-preview-container">
                                                <img src="{{ $coverTemplate->page_url }}" class="img-fluid rounded" style="max-height: 150px;">
                                                <p class="small text-muted mt-1 mb-0">Current Page Background</p>
                                            </div>
                                        @endif
                                        <input type="file" class="form-control" id="page_image" name="page_image" accept="image/*" {{ isset($coverTemplate) ? '' : 'required' }}>
                                        <small class="text-muted d-block mt-1">Background for internal pages. Max 2MB.</small>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="row mt-5">
                            <div class="col-12 text-end border-top pt-4">
                                <a href="{{ route('cover-templates.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> {{ isset($coverTemplate) ? 'Update Template' : 'Save Template' }}
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

    $('#templateForm').on('submit', function(e) {
        e.preventDefault();

        // 1. Bersihkan Error
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        const formData = new FormData(this);
        const isEdit = @json(isset($coverTemplate));
        const url = @json(isset($coverTemplate) ? route('cover-templates.update', $coverTemplate->id ?? 0) : route('cover-templates.store'));

        // 2. Tampilkan Loading Alert
        Swal.fire({
            title: 'Mohon Tunggu',
            html: isEdit ? 'Sedang memperbarui template...' : 'Sedang menyimpan template baru...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // 3. Eksekusi AJAX
        $.ajax({
            url: url,
            type: 'POST', // Menggunakan POST karena FormData membawa file
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
                        if (input.length) {
                            input.addClass('is-invalid');
                            // Cari invalid-feedback setelah input atau setelah tag small
                            let feedback = input.siblings('.invalid-feedback');
                            feedback.text(messages[0]);
                        }
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        text: 'Beberapa aset design atau informasi masih belum sesuai.',
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
