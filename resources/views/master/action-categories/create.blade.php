@extends('layouts.app')

@section('title', isset($actionCategory) ? 'Edit Category' : 'Create Category')

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
                        @if(isset($actionCategory))
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="mb-4 fw-bold">{{ isset($actionCategory) ? 'Edit Category' : 'Create Category' }}</h5>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="code" class="form-label fw-bold">Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="code" name="code"
                                               value="{{ old('code', $actionCategory->code ?? '') }}"
                                               placeholder="e.g. AC-001" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="name" class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name"
                                               value="{{ old('name', $actionCategory->name ?? '') }}"
                                               placeholder="Enter category name" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="duration_range" class="form-label fw-bold">Duration Range <span class="text-danger">*</span></label>
                                        <select class="form-select" id="duration_range" name="duration_range" required>
                                            <option value="" disabled {{ !isset($actionCategory) ? 'selected' : '' }}>Select duration range</option>
                                            @foreach($durationRanges as $range)
                                                <option value="{{ $range }}"
                                                        {{ old('duration_range', $actionCategory->duration_range ?? '') == $range ? 'selected' : '' }}>
                                                    {{ $range }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="priority" class="form-label fw-bold">Priority <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="priority" name="priority"
                                               value="{{ old('priority', $actionCategory->priority ?? '0') }}"
                                               min="0" required>
                                        <small class="text-muted">Lower number = Higher priority</small>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                                   {{ old('is_active', $actionCategory->is_active ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="is_active">Active Status</label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-end border-top pt-4">
                                <a href="{{ route('action-categories.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> {{ isset($actionCategory) ? 'Update Category' : 'Save Category' }}
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

    // 2. Submit Form
    $('#categoryForm').on('submit', function(e) {
        e.preventDefault();

        // Bersihkan state error sebelumnya
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        const formData = new FormData(this);
        const isEdit = @json(isset($actionCategory));
        const url = @json(isset($actionCategory) ? route('action-categories.update', $actionCategory->id) : route('action-categories.store'));

        // 3. Tampilkan Loading Alert
        Swal.fire({
            title: 'Mohon Tunggu',
            html: isEdit ? 'Sedang memperbarui kategori...' : 'Sedang menyimpan kategori...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // 4. Proses AJAX
        $.ajax({
            url: url,
            type: 'POST', // Laravel akan membaca PUT dari @method('PUT') di FormData
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                Swal.close(); // Tutup loading

                if (res.success) {
                    Toast.fire({ icon: 'success', title: res.message });
                    // Redirect setelah delay 1 detik
                    setTimeout(() => {
                        window.location.href = res.redirect;
                    }, 1000);
                }
            },
            error: function(xhr) {
                Swal.close(); // Tutup loading

                if (xhr.status === 422) {
                    // Penanganan Error Validasi Laravel
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        const input = $(`[name="${field}"]`);
                        input.addClass('is-invalid');
                        // Masukkan pesan error ke div .invalid-feedback setelah input
                        input.siblings('.invalid-feedback').text(messages[0]);
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        text: 'Silakan periksa kembali isian formulir Anda.',
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
