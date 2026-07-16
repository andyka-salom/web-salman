@extends('layouts.app')

@section('title', isset($category) ? 'Edit Security Event Category' : 'Create Security Event Category')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">

            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 shadow-sm">

                    <form id="categoryForm">
                        @csrf
                        @if(isset($category))
                            @method('PUT')
                        @endif

                        {{-- Header --}}
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                            <h4 class="fw-bold mb-0">{{ isset($category) ? 'Edit Security Event Category' : 'Create New Security Event Category' }}</h4>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="code" class="form-label fw-bold">Category Code <span class="text-danger">*</span></label>
                                        <input type="text"
                                               class="form-control"
                                               id="code"
                                               name="code"
                                               value="{{ old('code', $category->code ?? '') }}"
                                               placeholder="e.g., SEC-001"
                                               maxlength="50"
                                               required>
                                        <small class="text-muted">Unique code (max 50 characters)</small>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="name" class="form-label fw-bold">Category Name <span class="text-danger">*</span></label>
                                        <input type="text"
                                               class="form-control"
                                               id="name"
                                               name="name"
                                               value="{{ old('name', $category->name ?? '') }}"
                                               placeholder="Enter category name"
                                               maxlength="255"
                                               required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="description" class="form-label fw-bold">Description</label>
                                        <textarea class="form-control"
                                                  id="description"
                                                  name="description"
                                                  rows="4"
                                                  maxlength="1000"
                                                  placeholder="Enter category description (optional)">{{ old('description', $category->description ?? '') }}</textarea>
                                        <div class="d-flex justify-content-between mt-1">
                                            <small class="text-muted">Maximum 1000 characters</small>
                                            <small class="text-muted fw-bold" id="charCounter">0/1000</small>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row mb-3 mt-4">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="is_active"
                                                   name="is_active"
                                                   value="1"
                                                   {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="is_active">
                                                Active Status
                                            </label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="row mt-4">
                            <div class="col-12 text-end border-top pt-4">
                                <a href="{{ route('security-event-categories.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> {{ isset($category) ? 'Update Category' : 'Save Category' }}
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
    // 1. Inisialisasi Toast
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    // 2. Fitur UX (Uppercase & Counter)
    $('#code').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    function updateCharCounter() {
        const maxLength = 1000;
        const currentLength = $('#description').val().length;
        $('#charCounter').text(`${currentLength}/${maxLength}`);

        if (currentLength >= maxLength) {
            $('#charCounter').removeClass('text-muted').addClass('text-danger');
        } else {
            $('#charCounter').removeClass('text-danger').addClass('text-muted');
        }
    }
    updateCharCounter();
    $('#description').on('input', updateCharCounter);

    // 3. Handle Form Submit
    $('#categoryForm').on('submit', function(e) {
        e.preventDefault();

        // Bersihkan state error sebelumnya
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        const formData = new FormData(this);
        const isEdit = @json(isset($category));
        const url = @json(isset($category) ? route('security-event-categories.update', $category->id ?? 0) : route('security-event-categories.store'));

        // Tampilkan Loading Alert
        Swal.fire({
            title: 'Mohon Tunggu',
            html: isEdit ? 'Sedang memperbarui kategori...' : 'Sedang menyimpan kategori...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: url,
            type: 'POST', // Laravel membaca PUT dari spoofing _method di FormData
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
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: xhr.responseJSON?.message || 'Gagal memproses data ke server.',
                        confirmButtonText: 'OK'
                    });
                }
            }
        });
    });
});
</script>
@endpush
