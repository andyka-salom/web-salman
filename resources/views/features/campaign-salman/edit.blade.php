@extends('layouts.app')
@section('title', 'Edit Laporan')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
<style>
    .img-preview-container { position: relative; display: inline-block; margin: 5px; }
    .btn-del-img {
        position: absolute; top: -5px; right: -5px; padding: 2px 6px;
        font-size: 10px; border-radius: 50%; width: 20px; height: 20px;
        display: flex; align-items: center; justify-content: center;
        background: #ef4444; color: #fff; border: none;
    }
    .btn-del-img:hover { background: #dc2626; }
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 shadow-sm">

                    {{-- Header --}}
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom ps-3 pt-3 pe-3">
                        <h4 class="fw-bold mb-0">Edit Laporan: {{ $campaignSalman->tema }}</h4>
                    </div>

                    <form id="editForm" enctype="multipart/form-data" class="p-3">
                        @csrf
                        @method('PUT')

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Tanggal Kegiatan <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="tanggal" required value="{{ $campaignSalman->tanggal->format('Y-m-d') }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Tema / Judul <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="tema" required value="{{ $campaignSalman->tema }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Lokasi <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="lokasi" required value="{{ $campaignSalman->lokasi }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Entitas <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="entitas" required value="{{ $campaignSalman->entitas }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                @hasrole('super-admin|hsse')
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Perusahaan <span class="text-danger">*</span></label>
                                        <select class="form-select" name="company_id" required>
                                            <option value="">Pilih Perusahaan</option>
                                            @foreach($companies as $c)
                                                <option value="{{ $c->id }}" {{ $campaignSalman->company_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                @else
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Perusahaan</label>
                                        <input type="text" class="form-control bg-light" value="{{ $campaignSalman->company->name ?? 'N/A' }}" readonly>
                                        <input type="hidden" name="company_id" value="{{ $campaignSalman->company_id }}">
                                    </div>
                                @endhasrole

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Jumlah Peserta <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="jumlah_peserta" required min="1" value="{{ $campaignSalman->jumlah_peserta }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Pemateri <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="pemateri" required value="{{ $campaignSalman->pemateri }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Template Cover</label>
                                    <select class="form-select" name="cover_template_id">
                                        <option value="">-- Pilih Template --</option>
                                        @foreach($templates as $t)
                                            <option value="{{ $t->id }}"
                                                {{ ($campaignSalman->cover_template_id == $t->id) ||
                                                   (!$campaignSalman->cover_template_id && $defaultTemplate && $t->id == $defaultTemplate->id)
                                                   ? 'selected' : '' }}>
                                                {{ $t->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Ringkasan Kegiatan <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="ringkasan" rows="5" required>{{ $campaignSalman->ringkasan }}</textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        {{-- Image Management --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light border-0 shadow-none">
                                    <div class="card-body">
                                        <label class="fw-bold mb-2">Dokumentasi</label>
                                        @if($campaignSalman->dokumentasi)
                                        <div class="mb-3 border-bottom pb-2" id="dokumentasi-container">
                                            @foreach($campaignSalman->dokumentasi as $path)
                                                <div class="img-preview-container">
                                                    <img src="{{ Storage::url($path) }}" class="rounded border shadow-sm" style="width: 60px; height: 60px; object-fit: cover;">
                                                    <button type="button" class="btn-del-img" onclick="deleteImage('dokumentasi', '{{ $path }}', this)">×</button>
                                                </div>
                                            @endforeach
                                        </div>
                                        @endif
                                        <input type="file" class="form-control" name="dokumentasi[]" multiple accept="image/*">
                                        <small class="text-muted d-block mt-1">Tambah foto baru. Max 5MB per file.</small>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card bg-light border-0 shadow-none">
                                    <div class="card-body">
                                        <label class="fw-bold mb-2">Daftar Hadir</label>
                                        @if($campaignSalman->daftar_hadir)
                                        <div class="mb-3 border-bottom pb-2" id="daftar_hadir-container">
                                            @foreach($campaignSalman->daftar_hadir as $path)
                                                <div class="img-preview-container">
                                                    <img src="{{ Storage::url($path) }}" class="rounded border shadow-sm" style="width: 60px; height: 60px; object-fit: cover;">
                                                    <button type="button" class="btn-del-img" onclick="deleteImage('daftar_hadir', '{{ $path }}', this)">×</button>
                                                </div>
                                            @endforeach
                                        </div>
                                        @endif
                                        <input type="file" class="form-control" name="daftar_hadir[]" multiple accept="image/*">
                                        <small class="text-muted d-block mt-1">Tambah foto baru.</small>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-top pt-4 text-end">
                            <a href="{{ route('campaign-salman.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="btnUpdate">
                                <i class="fas fa-save me-1"></i> Update Laporan
                            </button>
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
        toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true
    });

    // --- Update Logic ---
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Tampilkan Loading Alert
        Swal.fire({
            title: 'Mohon Tunggu',
            html: 'Sedang mengupdate data laporan dan mengunggah aset...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData(this);
        // Method spoofing PUT ditambahkan manual untuk FormData
        formData.append('_method', 'PUT');

        $.ajax({
            url: "{{ route('campaign-salman.update', $campaignSalman->id) }}",
            type: 'POST',
            data: formData,
            processData: false, contentType: false,
            success: function(response) {
                Swal.close();
                if(response.success) {
                    Toast.fire({ icon: 'success', title: response.message });
                    setTimeout(() => { window.location.href = response.redirect; }, 1000);
                }
            },
            error: function(xhr) {
                Swal.close();
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        // Bersihkan dot notation jika ada (e.g dokumentasi.0)
                        const baseField = field.split('.')[0];
                        const input = $(`[name="${baseField}"]`).length ? $(`[name="${baseField}"]`) : $(`[name="${field}"]`);

                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(messages[0]);
                    });

                    Toast.fire({ icon: 'error', title: 'Validasi Gagal. Mohon periksa input Anda.' });
                } else {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Gagal mengupdate laporan.', 'error');
                }
            }
        });
    });

    // --- Delete Image Logic ---
    window.deleteImage = function(type, path, btnElement) {
        Swal.fire({
            title: 'Hapus Gambar?',
            text: "Gambar akan dihapus permanen dari server.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            customClass: { confirmButton: 'btn btn-primary', cancelButton: 'btn btn-outline-danger ms-1' },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('campaign-salman.delete-image', $campaignSalman->id) }}",
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}", type: type, path: path },
                    success: function(res) {
                        if(res.success) {
                            $(btnElement).closest('.img-preview-container').fadeOut(300, function() { $(this).remove(); });
                            Toast.fire({ icon: 'success', title: 'Gambar dihapus.' });
                        }
                    },
                    error: function(xhr) {
                        Toast.fire({ icon: 'error', title: 'Gagal menghapus gambar.' });
                    }
                });
            }
        });
    }
});
</script>
@endpush
