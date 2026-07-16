@extends('layouts.app')

@section('title', isset($unsafeCondition) ? 'Edit Unsafe Condition' : 'Create Unsafe Condition')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">

            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 shadow-sm">

                    <form id="conditionForm">
                        @csrf
                        @if(isset($unsafeCondition))
                            @method('PUT')
                        @endif

                        {{-- Header --}}
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                            <h4 class="fw-bold mb-0">{{ isset($unsafeCondition) ? 'Edit Unsafe Condition' : 'Create New Unsafe Condition' }}</h4>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="code" class="form-label fw-bold">Code <span class="text-danger">*</span></label>
                                        <input type="text"
                                               class="form-control"
                                               id="code"
                                               name="code"
                                               value="{{ old('code', $unsafeCondition->code ?? '') }}"
                                               placeholder="e.g. UC-001"
                                               required>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    @if(isset($unsafeCondition))
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Usage Count</label>
                                        <input type="text" class="form-control bg-light" value="{{ $unsafeCondition->usage_count }}" readonly>
                                    </div>
                                    @endif
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="description" class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                        <textarea class="form-control"
                                                  id="description"
                                                  name="description"
                                                  rows="3"
                                                  placeholder="Enter condition description"
                                                  required>{{ old('description', $unsafeCondition->description ?? '') }}</textarea>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="is_active"
                                                   name="is_active"
                                                   value="1"
                                                   {{ old('is_active', $unsafeCondition->is_active ?? true) ? 'checked' : '' }}>
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
                                <a href="{{ route('unsafe-conditions.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> {{ isset($unsafeCondition) ? 'Update Condition' : 'Save Condition' }}
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

    // 2. Handle Form Submit
    $('#conditionForm').on('submit', function(e) {
        e.preventDefault();

        // Bersihkan error validasi sebelumnya
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        const formData = new FormData(this);
        const isEdit = @json(isset($unsafeCondition));
        const url = @json(isset($unsafeCondition) ? route('unsafe-conditions.update', $unsafeCondition->id ?? 0) : route('unsafe-conditions.store'));

        // 3. Tampilkan Loading Alert
        Swal.fire({
            title: 'Mohon Tunggu',
            html: isEdit ? 'Sedang memperbarui data kondisi...' : 'Sedang menyimpan data kondisi...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // 4. Proses AJAX
        $.ajax({
            url: url,
            type: 'POST', // Laravel membaca PUT dari @method('PUT') dalam FormData
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                Swal.close();

                if (res.success) {
                    Toast.fire({ icon: 'success', title: res.message });

                    // Redirect setelah delay singkat
                    setTimeout(() => {
                        window.location.href = res.redirect;
                    }, 1000);
                }
            },
            error: function(xhr) {
                Swal.close();

                if (xhr.status === 422) {
                    // Penanganan Error Validasi
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
                    // Penanganan Error Server Umum
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
