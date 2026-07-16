@extends('layouts.app')

@section('title', isset($contact) ? 'Edit Contact' : 'Create Contact')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
<style>
    .form-card {
        background: var(--card-bg);
        border-radius: 12px;
        border: 1px solid var(--card-border-color);
        overflow: hidden;
    }
    .form-card-header {
        padding: 24px 28px;
        border-bottom: 1px solid var(--card-border-color);
        background: var(--card-bg);
    }
    .form-card-body {
        padding: 28px;
    }
    .form-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: 6px;
    }
    .form-control, .form-select {
        border-radius: 8px;
        font-size: 14px;
        padding: 10px 14px;
        border-color: var(--card-border-color);
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .form-control:focus, .form-select:focus {
        border-color: #716aca;
        box-shadow: 0 0 0 3px rgba(113,106,202,0.12);
    }
    .form-control.is-invalid {
        border-color: #e74c3c;
        box-shadow: none;
    }
    .form-control.is-invalid:focus {
        box-shadow: 0 0 0 3px rgba(231,76,60,0.12);
    }
    .invalid-feedback {
        font-size: 12px;
        margin-top: 5px;
    }
    .input-group .input-group-text {
        border-radius: 8px 0 0 8px;
        background: var(--input-bg, #f8f9fa);
        border-color: var(--card-border-color);
        padding: 0 14px;
    }
    .input-group .form-control {
        border-radius: 0 8px 8px 0;
    }
    .form-check-switch-label {
        font-size: 14px;
        font-weight: 500;
    }
    .form-check-input:checked {
        background-color: #00a86b;
        border-color: #00a86b;
    }
    .char-count {
        font-size: 11px;
        color: var(--text-muted);
        text-align: right;
        margin-top: 4px;
    }
    .char-count.near-limit { color: #e67e22; }
    .char-count.at-limit   { color: #e74c3c; }
    .section-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--text-muted);
        margin-bottom: 16px;
        padding-bottom: 10px;
        border-bottom: 1px dashed var(--card-border-color);
    }
    #submitBtn .spinner-border { width: 14px; height: 14px; border-width: 2px; }
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">

            <div class="col-xl-8 col-lg-10 col-sm-12 mx-auto layout-spacing">

                {{-- Back link --}}
                <div class="mb-3">
                    <a href="{{ route('contacts.index') }}" class="text-muted text-decoration-none d-inline-flex align-items-center gap-1" style="font-size:13px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                        Kembali ke Contacts
                    </a>
                </div>

                <div class="form-card shadow-sm">
                    {{-- Header --}}
                    <div class="form-card-header d-flex align-items-center gap-3">
                        <div style="width:40px;height:40px;border-radius:10px;background:rgba(113,106,202,0.12);display:flex;align-items:center;justify-content:center;">
                            @if(isset($contact))
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#716aca" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#716aca" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                            @endif
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0">{{ isset($contact) ? 'Edit Contact' : 'Tambah Kontak Baru' }}</h5>
                            <p class="text-muted mb-0" style="font-size:12px;">
                                {{ isset($contact) ? 'Perbarui informasi kontak ' . $contact->name : 'Isi form di bawah untuk menambah kontak' }}
                            </p>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="form-card-body">
                        <form id="contactForm" novalidate>
                            @csrf
                            @if(isset($contact))
                                <input type="hidden" name="_method" value="PUT">
                            @endif

                            {{-- Section: Informasi Utama --}}
                            <p class="section-title">Informasi Utama</p>

                            <div class="row">
                                {{-- Name --}}
                                <div class="col-md-6 mb-4">
                                    <label for="name" class="form-label">
                                        Nama Lengkap <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control"
                                           id="name"
                                           name="name"
                                           value="{{ old('name', $contact->name ?? '') }}"
                                           placeholder="Masukkan nama lengkap"
                                           maxlength="255"
                                           autocomplete="off"
                                           required>
                                    <div class="invalid-feedback" id="name_error"></div>
                                </div>

                                {{-- WhatsApp Number --}}
                                <div class="col-md-6 mb-4">
                                    <label for="whatsapp_number" class="form-label">
                                        Nomor WhatsApp <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#25D366" viewBox="0 0 16 16">
                                                <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
                                            </svg>
                                        </span>
                                        <input type="text"
                                               class="form-control"
                                               id="whatsapp_number"
                                               name="whatsapp_number"
                                               value="{{ old('whatsapp_number', $contact->whatsapp_number ?? '') }}"
                                               placeholder="08123456789 atau 628123456789"
                                               maxlength="20"
                                               required>
                                    </div>
                                    <div class="invalid-feedback d-block" id="whatsapp_number_error" style="display:none!important;"></div>
                                    <small class="text-muted d-block mt-1" style="font-size:11px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                        Diawali <code>0</code> otomatis diubah ke <code>62</code>
                                    </small>
                                </div>
                            </div>

                            {{-- Section: Detail --}}
                            <p class="section-title mt-2">Detail Tambahan</p>

                            <div class="row">
                                {{-- Position --}}
                                <div class="col-md-6 mb-4">
                                    <label for="position" class="form-label">Jabatan / Posisi</label>
                                    <input type="text"
                                           class="form-control"
                                           id="position"
                                           name="position"
                                           value="{{ old('position', $contact->position ?? '') }}"
                                           placeholder="cth: Manager, Supervisor, Teknisi"
                                           maxlength="255">
                                    <div class="invalid-feedback" id="position_error"></div>
                                </div>

                                {{-- Active Status --}}
                                <div class="col-md-6 mb-4 d-flex align-items-center">
                                    <div>
                                        <label class="form-label d-block">Status Kontak</label>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       role="switch"
                                                       id="is_active"
                                                       name="is_active"
                                                       value="1"
                                                       {{ old('is_active', $contact->is_active ?? true) ? 'checked' : '' }}
                                                       style="width:2.4em;height:1.3em;cursor:pointer;">
                                                <label class="form-check-label form-check-switch-label" for="is_active" id="statusLabel">
                                                    {{ old('is_active', $contact->is_active ?? true) ? 'Active' : 'Inactive' }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Notes --}}
                                <div class="col-md-12 mb-2">
                                    <label for="notes" class="form-label">Keterangan / Notes</label>
                                    <textarea class="form-control"
                                              id="notes"
                                              name="notes"
                                              rows="4"
                                              maxlength="2000"
                                              placeholder="Tambahkan catatan tentang kontak ini (opsional)...">{{ old('notes', $contact->notes ?? '') }}</textarea>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <div class="invalid-feedback d-block" id="notes_error" style="display:none!important;"></div>
                                        <div class="char-count ms-auto" id="notesCharCount">0 / 2000</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="d-flex justify-content-between align-items-center mt-4 pt-4" style="border-top:1px solid var(--card-border-color);">
                                <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary" style="border-radius:8px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                    Batal
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn" style="border-radius:8px; min-width:140px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-save me-1"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                    <span id="submitText">{{ isset($contact) ? 'Update Contact' : 'Simpan Contact' }}</span>
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>
<script>
$(document).ready(function () {

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
    });

    // Character counter for notes
    function updateCharCount() {
        const len  = $('#notes').val().length;
        const max  = 2000;
        const el   = $('#notesCharCount');
        el.text(len + ' / ' + max);
        el.removeClass('near-limit at-limit');
        if (len >= max)           el.addClass('at-limit');
        else if (len >= max * 0.85) el.addClass('near-limit');
    }
    $('#notes').on('input', updateCharCount);
    updateCharCount();

    // Status label toggle
    $('#is_active').on('change', function () {
        $('#statusLabel').text(this.checked ? 'Active' : 'Inactive');
    });

    // Clear a field's error state
    function clearError(field) {
        $(`[name="${field}"]`).removeClass('is-invalid');
        $(`#${field}_error`).text('').hide();
    }
    $('input, textarea').on('input change', function () {
        clearError($(this).attr('name'));
    });

    // Show validation error
    function showError(field, message) {
        const input = $(`[name="${field}"]`);
        input.addClass('is-invalid');
        // handle input-group
        if (input.closest('.input-group').length) {
            input.closest('.input-group').find('.form-control').addClass('is-invalid');
        }
        $(`#${field}_error`).text(message).css('display', 'block');
    }

    $('#contactForm').on('submit', function (e) {
        e.preventDefault();

        // Clear all errors
        $('.is-invalid').removeClass('is-invalid');
        $('[id$="_error"]').text('').hide();

        const isEdit = @json(isset($contact));
        const url = isEdit
            ? @json(isset($contact) ? route('contacts.update', $contact->id ?? 0) : '#')
            : "{{ route('contacts.store') }}";

        const formData = new FormData(this);

        // Button loading state
        const btn = $('#submitBtn');
        btn.prop('disabled', true);
        btn.html(`<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...`);

        $.ajax({
            url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.success) {
                    Toast.fire({ icon: 'success', title: res.message || 'Berhasil disimpan.' });
                    setTimeout(() => {
                        window.location.href = res.redirect || "{{ route('contacts.index') }}";
                    }, 1000);
                } else {
                    btn.prop('disabled', false);
                    btn.html(`<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-save me-1"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg><span id="submitText">{{ isset($contact) ? 'Update Contact' : 'Simpan Contact' }}</span>`);
                    Toast.fire({ icon: 'error', title: res.message || 'Gagal menyimpan.' });
                }
            },
            error: function (xhr) {
                btn.prop('disabled', false);
                btn.html(`<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-save me-1"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg><span id="submitText">{{ isset($contact) ? 'Update Contact' : 'Simpan Contact' }}</span>`);

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON?.errors || {};
                    $.each(errors, function (field, messages) {
                        showError(field, messages[0]);
                    });
                    // Scroll to first error
                    const firstErr = $('.is-invalid').first();
                    if (firstErr.length) {
                        $('html, body').animate({ scrollTop: firstErr.offset().top - 120 }, 300);
                    }
                } else {
                    const msg = xhr.responseJSON?.message || 'Terjadi kesalahan server.';
                    Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonText: 'OK' });
                }
            },
        });
    });

});
</script>
@endpush
