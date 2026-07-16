@extends('layouts.app')

@section('title', isset($pelanggaran) ? 'Edit Pelanggaran' : 'Create Pelanggaran')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">

            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 shadow-sm">

                    <form id="pelanggaranForm">
                        @csrf
                        @if(isset($pelanggaran))
                            @method('PUT')
                        @endif

                        {{-- Header --}}
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                            <h4 class="fw-bold mb-0">{{ isset($pelanggaran) ? 'Edit Pelanggaran' : 'Create New Pelanggaran' }}</h4>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="sequence_number" class="form-label fw-bold">Sequence Number <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="sequence_number" name="sequence_number"
                                               value="{{ old('sequence_number', $pelanggaran->sequence_number ?? $nextSequence ?? 1) }}"
                                               placeholder="e.g. 1" min="1" required>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="severity" class="form-label fw-bold">Severity <span class="text-danger">*</span></label>
                                        <select class="form-select" id="severity" name="severity" required>
                                            <option value="">Select Severity</option>
                                            @foreach(['low', 'medium', 'high', 'critical'] as $sev)
                                                <option value="{{ $sev }}"
                                                    {{ (old('severity', $pelanggaran->severity ?? '') == $sev) ? 'selected' : '' }}>
                                                    {{ ucfirst($sev) }}
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
                                               value="{{ old('name', $pelanggaran->name ?? '') }}"
                                               placeholder="Enter violation name" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="description" class="form-label fw-bold">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"
                                                  placeholder="Enter description (optional)">{{ old('description', $pelanggaran->description ?? '') }}</textarea>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                                   {{ old('is_active', $pelanggaran->is_active ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="is_active">Active Status</label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="row mt-4">
                            <div class="col-12 text-end border-top pt-4">
                                <a href="{{ route('pelanggaran.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> {{ isset($pelanggaran) ? 'Update Pelanggaran' : 'Save Pelanggaran' }}
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
    $('#pelanggaranForm').on('submit', function(e) {
        e.preventDefault();

        // Bersihkan error validasi sebelumnya
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        const formData = new FormData(this);
        const isEdit = @json(isset($pelanggaran));
        const url = @json(isset($pelanggaran) ? route('pelanggaran.update', $pelanggaran->id ?? 0) : route('pelanggaran.store'));

        // 3. Tampilkan Loading Alert
        Swal.fire({
            title: 'Mohon Tunggu',
            html: isEdit ? 'Sedang memperbarui data pelanggaran...' : 'Sedang menyimpan data pelanggaran...',
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

                    // Redirect setelah delay
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
