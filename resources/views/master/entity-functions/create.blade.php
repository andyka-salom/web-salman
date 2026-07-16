@extends('layouts.app')

@section('title', isset($entityFunction) ? 'Edit Function' : 'Create Function')

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

                    <form id="functionForm">
                        @csrf
                        @if(isset($entityFunction)) @method('PUT') @endif

                        {{-- Header --}}
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                            <h4 class="fw-bold mb-0">{{ isset($entityFunction) ? 'Edit Entity Function' : 'Create New Function' }}</h4>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="code" value="{{ old('code', $entityFunction->code ?? '') }}" required placeholder="e.g. HR-001">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="{{ old('name', $entityFunction->name ?? '') }}" required placeholder="e.g. Human Resources">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Parent Function</label>
                                <select class="form-select" id="parent_id" name="parent_id">
                                    <option value="">No Parent (Root Level)</option>
                                    @foreach($parents as $p)
                                        <option value="{{ $p->id }}" {{ (old('parent_id', $entityFunction->parent_id ?? '') == $p->id) ? 'selected' : '' }}>
                                            {{ str_repeat('— ', $p->level) }} {{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Leaving empty creates a Root function.</small>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Description</label>
                                <textarea class="form-control" name="description" rows="1" placeholder="Short description...">{{ old('description', $entityFunction->description ?? '') }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row mb-3 mt-4">
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $entityFunction->is_active ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="is_active">Active Status</label>
                                </div>
                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="row mt-4">
                            <div class="col-12 text-end border-top pt-4">
                                <a href="{{ route('entity-functions.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> {{ isset($entityFunction) ? 'Update Function' : 'Save Function' }}
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
    // 1. Inisialisasi TomSelect
    var tsParent = new TomSelect('#parent_id', {
        allowEmptyOption: true,
        placeholder: "Select Parent Function"
    });

    // 2. Inisialisasi Toast
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    // 3. Handle Form Submit
    $('#functionForm').on('submit', function(e) {
        e.preventDefault();

        // Bersihkan state error sebelumnya
        $('.is-invalid').removeClass('is-invalid');
        $('.ts-wrapper').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        const formData = new FormData(this);
        const isEdit = @json(isset($entityFunction));
        const url = @json(isset($entityFunction) ? route('entity-functions.update', $entityFunction->id ?? 0) : route('entity-functions.store'));

        // 4. Tampilkan Loading Alert
        Swal.fire({
            title: 'Mohon Tunggu',
            html: isEdit ? 'Sedang memperbarui data fungsi...' : 'Sedang menyimpan fungsi baru...',
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
                        if (field === 'parent_id') {
                            $(tsParent.control).parent().addClass('is-invalid');
                            $(tsParent.control).parent().siblings('.invalid-feedback').text(messages[0]).show();
                        } else {
                            const input = $(`[name="${field}"]`);
                            input.addClass('is-invalid');
                            input.siblings('.invalid-feedback').text(messages[0]);
                        }
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
