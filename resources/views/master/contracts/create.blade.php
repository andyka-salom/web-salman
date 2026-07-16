@extends('layouts.app')

@section('title', isset($contract) ? 'Edit Contract' : 'Create Contract')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">

        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 shadow-sm">

                    <form id="contractForm">
                        @csrf
                        @if(isset($contract))
                            @method('PUT')
                        @endif

                        {{-- Header --}}
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                            <h4 class="fw-bold mb-0">{{ isset($contract) ? 'Edit Contract' : 'Create New Contract' }}</h4>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nama_kontraktor" class="form-label fw-bold">Nama Kontraktor <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control"
                                       id="nama_kontraktor"
                                       name="nama_kontraktor"
                                       value="{{ old('nama_kontraktor', $contract->nama_kontraktor ?? '') }}"
                                       placeholder="Nama Lengkap Perusahaan"
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="sap_no" class="form-label fw-bold">Nomor SAP <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control"
                                       id="sap_no"
                                       name="sap_no"
                                       value="{{ old('sap_no', $contract->sap_no ?? '') }}"
                                       placeholder="Contoh: 12345678"
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="alamat_email" class="form-label fw-bold">Email Kantor</label>
                                <input type="email"
                                       class="form-control"
                                       id="alamat_email"
                                       name="alamat_email"
                                       value="{{ old('alamat_email', $contract->alamat_email ?? '') }}"
                                       placeholder="kantor@kontraktor.com">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="no_tlp_kantor" class="form-label fw-bold">Nomor Telepon Kantor</label>
                                <input type="text"
                                       class="form-control"
                                       id="no_tlp_kantor"
                                       name="no_tlp_kantor"
                                       value="{{ old('no_tlp_kantor', $contract->no_tlp_kantor ?? '') }}"
                                       placeholder="+62 123 456 789">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="alamat_kantor" class="form-label fw-bold">Alamat Kantor</label>
                                <textarea class="form-control"
                                          id="alamat_kantor"
                                          name="alamat_kantor"
                                          rows="3"
                                          placeholder="Masukkan alamat lengkap kantor">{{ old('alamat_kantor', $contract->alamat_kantor ?? '') }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="row mt-4">
                            <div class="col-12 text-end border-top pt-4">
                                <a href="{{ route('contracts.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> {{ isset($contract) ? 'Update Contract' : 'Save Contract' }}
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
    // 1. Init Toast
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    // 2. Handle Form Submit
    $('#contractForm').on('submit', function(e) {
        e.preventDefault();

        // Bersihkan error lama
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        const formData = new FormData(this);
        const isEdit = @json(isset($contract));
        const url = @json(isset($contract) ? route('contracts.update', $contract->id ?? 0) : route('contracts.store'));

        // 3. Tampilkan Loading Alert
        Swal.fire({
            title: 'Mohon Tunggu',
            html: isEdit ? 'Sedang memperbarui data kontrak...' : 'Sedang menyimpan data kontrak...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: url,
            type: 'POST', // Laravel akan membaca PUT dari @method('PUT') di FormData
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
