@extends('layouts.app')

@section('title', 'Import Contacts')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
<style>
    .import-card {
        background: var(--card-bg);
        border-radius: 12px;
        border: 1px solid var(--card-border-color);
        overflow: hidden;
    }
    .import-card-header {
        padding: 22px 28px;
        border-bottom: 1px solid var(--card-border-color);
    }
    .import-card-body { padding: 28px; }
    .upload-zone {
        border: 2px dashed var(--card-border-color);
        border-radius: 12px;
        padding: 48px 24px;
        text-align: center;
        cursor: pointer;
        transition: background 0.2s, border-color 0.2s;
        background: transparent;
        position: relative;
    }
    .upload-zone:hover,
    .upload-zone.drag-over {
        background: rgba(113,106,202,0.04);
        border-color: #716aca;
    }
    .upload-zone.has-file {
        border-color: #00a86b;
        background: rgba(0,168,107,0.04);
    }
    .upload-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: rgba(113,106,202,0.10);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        color: #716aca;
        transition: background 0.2s;
    }
    .upload-zone.has-file .upload-icon {
        background: rgba(0,168,107,0.12);
        color: #00a86b;
    }
    .upload-zone .file-name {
        font-weight: 600;
        color: #716aca;
        font-size: 14px;
    }
    .upload-zone.has-file .file-name { color: #00a86b; }
    .format-table th {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        background: var(--table-header-bg, rgba(0,0,0,0.03));
        padding: 10px 14px;
    }
    .format-table td {
        font-size: 13px;
        padding: 9px 14px;
        vertical-align: middle;
    }
    .guide-card {
        background: var(--card-bg);
        border-radius: 12px;
        border: 1px solid var(--card-border-color);
        overflow: hidden;
    }
    .guide-card-header {
        padding: 18px 22px;
        border-bottom: 1px solid var(--card-border-color);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .guide-card-header h5 {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: var(--text-muted);
        margin: 0;
    }
    .guide-card-body { padding: 20px 22px; }
    .code-preview {
        background: var(--pre-bg, rgba(0,0,0,0.04));
        border-radius: 8px;
        padding: 14px 16px;
        font-size: 12px;
        overflow-x: auto;
        border: 1px solid var(--card-border-color);
    }
    .code-preview pre { margin: 0; }
    .badge-required {
        background: rgba(231,76,60,0.10);
        color: #e74c3c;
        font-size: 11px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 4px;
    }
    .badge-optional {
        background: rgba(0,0,0,0.06);
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 4px;
    }
    .import-progress { display: none; }
    .progress-bar-animated { animation: progress-bar-stripes 0.75s linear infinite; }
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">

            {{-- Back --}}
            <div class="col-12 mb-3">
                <a href="{{ route('contacts.index') }}" class="text-muted text-decoration-none d-inline-flex align-items-center gap-1" style="font-size:13px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    Kembali ke Contacts
                </a>
            </div>

            {{-- Import Form --}}
            <div class="col-xl-7 col-lg-8 col-sm-12 layout-spacing">
                <div class="import-card shadow-sm">
                    <div class="import-card-header">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width:42px;height:42px;border-radius:10px;background:rgba(0,168,107,0.12);display:flex;align-items:center;justify-content:center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00a86b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0">Import Contacts</h5>
                                <p class="text-muted mb-0" style="font-size:12px;">Upload Excel (.xlsx / .xls) atau CSV. Duplikat dilewati otomatis.</p>
                            </div>
                        </div>
                    </div>

                    <div class="import-card-body">
                        <form id="importForm">
                            @csrf

                            {{-- Drop Zone --}}
                            <div class="upload-zone mb-4" id="dropZone">
                                <div class="upload-icon" id="uploadIcon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-upload-cloud"><polyline points="16 16 12 12 8 16"></polyline><line x1="12" y1="12" x2="12" y2="21"></line><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"></path></svg>
                                </div>
                                <p class="fw-bold mb-1" style="font-size:15px;">Drag & drop file di sini</p>
                                <p class="text-muted mb-3" style="font-size:13px;">atau <span style="color:#716aca;font-weight:600;">klik untuk pilih file</span></p>
                                <p class="text-muted mb-3" style="font-size:12px;">Format: .xlsx, .xls, .csv — Maks. 5 MB</p>
                                <p class="file-name mb-0" id="selectedFileName">Belum ada file dipilih</p>
                                <input type="file" id="fileInput" name="file" accept=".xlsx,.xls,.csv" class="d-none">
                            </div>

                            {{-- Progress --}}
                            <div class="import-progress mb-4" id="importProgress">
                                <p class="text-muted mb-2" style="font-size:13px;">Memproses file...</p>
                                <div class="progress" style="height:6px;border-radius:4px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width:100%"></div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('contacts.template.download') }}"
                                   class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"
                                   style="border-radius:8px; font-size:13px; font-weight:600;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                    Download Template
                                </a>
                                <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2" id="importBtn" style="border-radius:8px; font-size:13px; font-weight:600; min-width:150px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-upload"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                    <span id="importBtnText">Import Contacts</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Format Guide --}}
            <div class="col-xl-5 col-lg-4 col-sm-12 layout-spacing">
                <div class="guide-card">
                    <div class="guide-card-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <h5>Panduan Format</h5>
                    </div>
                    <div class="guide-card-body">
                        <p class="text-muted mb-3" style="font-size:12px;">Baris pertama harus berupa <strong>header</strong>. Header yang didukung:</p>

                        <div class="table-responsive mb-4">
                            <table class="table table-bordered format-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Header</th>
                                        <th>Alias</th>
                                        <th>Wajib?</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>name</code></td>
                                        <td><code>nama</code></td>
                                        <td><span class="badge-required">Wajib</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>whatsapp_number</code></td>
                                        <td><code>nomor_whatsapp</code>, <code>no_wa</code></td>
                                        <td><span class="badge-required">Wajib</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>position</code></td>
                                        <td><code>jabatan</code></td>
                                        <td><span class="badge-optional">Opsional</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>notes</code></td>
                                        <td><code>keterangan</code></td>
                                        <td><span class="badge-optional">Opsional</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <p class="fw-bold mb-2" style="font-size:12px;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);">Contoh Data CSV</p>
                        <div class="code-preview mb-4">
                            <pre>name,whatsapp_number,position,notes
John Doe,08123456789,Manager,Kontak utama
Jane Smith,6281234567890,Supervisor,</pre>
                        </div>

                        <div class="p-3 rounded" style="background:rgba(113,106,202,0.06);border:1px solid rgba(113,106,202,0.15);">
                            <p class="mb-1" style="font-size:12px;font-weight:700;color:#716aca;">💡 Tips</p>
                            <ul class="mb-0 ps-3" style="font-size:12px;color:var(--text-muted);line-height:1.8;">
                                <li>Nomor diawali <code>0</code> otomatis diubah ke <code>62</code></li>
                                <li>Duplikat (nama + nomor sama) dilewati</li>
                                <li>Baris dengan nama/nomor kosong dilewati</li>
                                <li>File maksimal <strong>5 MB</strong></li>
                            </ul>
                        </div>
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
        toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, timerProgressBar: true,
    });

    const dropZone  = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const fileLabel = document.getElementById('selectedFileName');

    dropZone.addEventListener('click', () => fileInput.click());

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            setFile(e.dataTransfer.files[0]);
        }
    });

    fileInput.addEventListener('change', function () {
        if (this.files[0]) setFile(this.files[0]);
    });

    function setFile(file) {
        fileLabel.textContent = file.name;
        dropZone.classList.add('has-file');
        // Validate size
        if (file.size > 5 * 1024 * 1024) {
            Toast.fire({ icon: 'warning', title: 'File terlalu besar (maks. 5 MB).' });
            resetFile();
        }
    }

    function resetFile() {
        fileLabel.textContent = 'Belum ada file dipilih';
        dropZone.classList.remove('has-file');
        fileInput.value = '';
    }

    $('#importForm').on('submit', function (e) {
        e.preventDefault();

        if (!fileInput.files.length) {
            Toast.fire({ icon: 'warning', title: 'Pilih file terlebih dahulu.' });
            return;
        }

        // UI loading
        $('#importBtn').prop('disabled', true);
        $('#importBtnText').text('Mengimpor...');
        $('#importProgress').show();

        const formData = new FormData(this);

        $.ajax({
            url: "{{ route('contacts.import') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                $('#importProgress').hide();
                $('#importBtn').prop('disabled', false);
                $('#importBtnText').text('Import Contacts');

                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Import Selesai',
                        html: `
                            <div style="text-align:center;padding:8px 0;">
                                <div style="display:flex;justify-content:center;gap:32px;margin-bottom:8px;">
                                    <div>
                                        <div style="font-size:28px;font-weight:700;color:#00a86b;">${res.imported}</div>
                                        <div style="font-size:12px;color:#888;">Berhasil</div>
                                    </div>
                                    <div>
                                        <div style="font-size:28px;font-weight:700;color:#e74c3c;">${res.skipped}</div>
                                        <div style="font-size:12px;color:#888;">Dilewati</div>
                                    </div>
                                </div>
                            </div>`,
                        confirmButtonText: 'Lihat Contacts',
                        confirmButtonColor: '#716aca',
                    }).then(() => {
                        window.location.href = "{{ route('contacts.index') }}";
                    });
                }
            },
            error: function (xhr) {
                $('#importProgress').hide();
                $('#importBtn').prop('disabled', false);
                $('#importBtnText').text('Import Contacts');

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON?.errors ?? {};
                    const errText = Object.values(errors).flat().join('<br>');
                    Swal.fire({ icon: 'error', title: 'Validasi Gagal', html: errText });
                } else {
                    const msg = xhr.responseJSON?.message ?? 'Import gagal. Coba lagi.';
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                }
            },
        });
    });

});
</script>
@endpush
