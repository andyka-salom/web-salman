@extends('layouts.app')

@section('title', isset($unsafeAct) ? 'Edit Unsafe Act' : 'Create Unsafe Act')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">

            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 shadow-sm">

                    <form id="unsafeActForm">
                        @csrf
                        @if(isset($unsafeAct))
                            @method('PUT')
                        @endif

                        {{-- Header --}}
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                            <h4 class="fw-bold mb-0">{{ isset($unsafeAct) ? 'Edit Unsafe Act' : 'Create New Unsafe Act' }}</h4>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="code" class="form-label fw-bold">Unsafe Act Code <span class="text-danger">*</span></label>
                                        <input type="text"
                                               class="form-control"
                                               id="code"
                                               name="code"
                                               value="{{ old('code', $unsafeAct->code ?? '') }}"
                                               placeholder="e.g., UA-001"
                                               maxlength="50"
                                               required>
                                        <small class="text-muted">Unique code for this unsafe act (max 50 characters)</small>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="description" class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                        <textarea class="form-control"
                                                  id="description"
                                                  name="description"
                                                  rows="5"
                                                  maxlength="500"
                                                  placeholder="Enter detailed description of the unsafe act"
                                                  required>{{ old('description', $unsafeAct->description ?? '') }}</textarea>
                                        <div class="d-flex justify-content-between mt-1">
                                            <small class="text-muted">Detailed description (max 500 characters)</small>
                                            <small class="text-muted fw-bold" id="charCounter">0/500</small>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                @if(isset($unsafeAct))
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="alert alert-light-info border-0 mb-0 d-flex align-items-center" role="alert">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info me-2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                            <span>Usage Statistics: This unsafe act has been reported <strong>{{ $unsafeAct->usage_count }}</strong> time(s).</span>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="row mb-3 mt-4">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="is_active"
                                                   name="is_active"
                                                   value="1"
                                                   {{ old('is_active', $unsafeAct->is_active ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="is_active">
                                                Active Status
                                            </label>
                                        </div>
                                        <small class="text-muted">Only active unsafe acts can be selected in reports.</small>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="row mt-4">
                            <div class="col-12 text-end border-top pt-4">
                                <a href="{{ route('unsafe-acts.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> {{ isset($unsafeAct) ? 'Update Unsafe Act' : 'Save Unsafe Act' }}
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
        const maxLength = 500;
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
    $('#unsafeActForm').on('submit', function(e) {
        e.preventDefault();

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        const formData = new FormData(this);
        const isEdit = @json(isset($unsafeAct));
        const url = @json(isset($unsafeAct) ? route('unsafe-acts.update', $unsafeAct->id ?? 0) : route('unsafe-acts.store'));

        // Tampilkan Loading Alert
        Swal.fire({
            title: 'Mohon Tunggu',
            html: isEdit ? 'Sedang memperbarui data Unsafe acts...' : 'Sedang menyimpan data tindakan tidak aman...',
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
