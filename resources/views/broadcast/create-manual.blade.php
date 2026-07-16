@extends('layouts.app')

@section('title', 'Broadcast Manual')

@push('styles')
    {{-- FilePond CSS --}}
    <link rel="stylesheet" href="{{ asset('plugins/src/filepond/filepond.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/src/filepond/FilePondPluginImagePreview.min.css') }}">
    <link href="{{ asset('plugins/css/light/filepond/custom-filepond.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('plugins/css/dark/filepond/custom-filepond.css') }}" rel="stylesheet" type="text/css" />

    <style>
        .form-card {
            background: var(--card-bg);
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            border: 1px solid var(--card-border-color);
        }
        .guide-card {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 20px;
            border: 1px dashed #cbd5e1;
        }
        .guide-card h6 {
            color: #3b3f5c;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .guide-list li {
            margin-bottom: 8px;
            font-size: 13px;
            color: #515365;
        }
        .guide-code {
            background: #fff;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
            font-weight: bold;
            color: #e7515a;
            border: 1px solid #e2e8f0;
        }

        /* Penyesuaian FilePond agar serasi */
        .filepond--root {
            margin-bottom: 0;
        }
        .filepond--panel-root {
            border-radius: 8px;
            background-color: #f1f2f3;
            border: 1px dashed #cbd5e1;
        }
    </style>
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">

        <div class="row layout-top-spacing mb-4">
            <div class="col-12">
                <h3 class="m-0 fw-bold">Buat Broadcast Manual</h3>
                <p class="text-muted">Kirim pesan ke daftar nomor spesifik tanpa menyimpan kontak.</p>
            </div>
        </div>

        <form action="{{ route('broadcast.send.manual') }}" method="POST" enctype="multipart/form-data" id="broadcastForm">
            @csrf

            <div class="row">
                {{-- Kolom Kiri: Formulir --}}
                <div class="col-xl-8 col-lg-8 col-md-12 mb-4">
                    <div class="widget-content widget-content-area form-card">

                        <h5 class="mb-4 pb-2 border-bottom fw-bold text-primary">Detail Pengiriman</h5>

                        {{-- Judul --}}
                        <div class="mb-4">
                            <label for="title" class="form-label fw-bold">Judul Broadcast <small class="text-muted fw-normal">(Opsional - Internal Arsip)</small></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" placeholder="Contoh: Info Promo Desember">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Nomor Telepon --}}
                        <div class="mb-4">
                            <label for="phone_numbers" class="form-label fw-bold">Daftar Nomor Telepon <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('phone_numbers') is-invalid @enderror" id="phone_numbers" name="phone_numbers" rows="5" placeholder="081234567890&#10;628123456789&#10;089876543210" required>{{ old('phone_numbers') }}</textarea>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Pisahkan dengan baris baru (Enter) atau koma.</small>
                                <small class="text-muted" id="numberCount">0 nomor terdeteksi</small>
                            </div>
                            @error('phone_numbers') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        {{-- Pesan --}}
                        <div class="mb-4">
                            <label for="message" class="form-label fw-bold">Isi Pesan <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="8" placeholder="Tulis pesan Anda di sini..." required>{{ old('message') }}</textarea>
                            @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Media & Jadwal --}}
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label fw-bold">Lampiran Media <small class="text-muted">(Opsional, Max 10MB)</small></label>

                                {{-- FilePond Input --}}
                                <div class="multiple-file-upload">
                                    <input type="file"
                                        class="filepond file-upload-multiple"
                                        name="media_file"
                                        data-allow-reorder="true"
                                        data-max-file-size="10MB"
                                        data-max-files="1">
                                </div>
                                @error('media_file') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="scheduled_at" class="form-label fw-bold">Jadwalkan Pengiriman <small class="text-muted">(Opsional)</small></label>
                                <input type="datetime-local" class="form-control @error('scheduled_at') is-invalid @enderror" id="scheduled_at" name="scheduled_at" value="{{ old('scheduled_at') }}">
                                <div class="form-text text-muted small mt-1">Biarkan kosong untuk kirim <strong>Segera</strong>.</div>
                                @error('scheduled_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('broadcast.index') }}" class="btn btn-outline-secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left me-1"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                Kembali
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-4" id="submitBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-send me-2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                                <span id="btnText">Kirim Broadcast</span>
                            </button>
                        </div>

                    </div>
                </div>

                {{-- Kolom Kanan: Panduan --}}
                <div class="col-xl-4 col-lg-4 col-md-12">
                    <div class="guide-card mb-4">
                        <h6><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info me-2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg> Petunjuk Format Nomor</h6>
                        <ul class="guide-list ps-3 mb-0">
                            <li>Gunakan format internasional (628...) atau lokal (08...). Sistem akan otomatis menyesuaikan.</li>
                            <li>Pisahkan setiap nomor dengan <strong>baris baru (Enter)</strong> atau <strong>koma (,)</strong>.</li>
                            <li>Pastikan nomor terdaftar di WhatsApp.</li>
                        </ul>
                    </div>

                    <div class="guide-card">
                        <h6><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-3 me-2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg> Format Teks WhatsApp</h6>
                        <ul class="guide-list ps-3 mb-0">
                            <li><strong>Tebal:</strong> Apit dengan bintang <br><span class="guide-code">*Teks Tebal*</span></li>
                            <li><em>Miring:</em> Apit dengan underscore <br><span class="guide-code">_Teks Miring_</span></li>
                            <li><strike>Coret:</strike> Apit dengan tilde <br><span class="guide-code">~Teks Coret~</span></li>
                            <li><code>Monospace:</code> Apit dengan 3 backtick <br><span class="guide-code">```Kode```</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection

@push('scripts')
{{-- FilePond Scripts --}}
<script src="{{ asset('plugins/src/filepond/filepond.min.js') }}"></script>
<script src="{{ asset('plugins/src/filepond/FilePondPluginFileValidateType.min.js') }}"></script>
<script src="{{ asset('plugins/src/filepond/FilePondPluginImageExifOrientation.min.js') }}"></script>
<script src="{{ asset('plugins/src/filepond/FilePondPluginImagePreview.min.js') }}"></script>
<script src="{{ asset('plugins/src/filepond/FilePondPluginImageCrop.min.js') }}"></script>
<script src="{{ asset('plugins/src/filepond/FilePondPluginImageResize.min.js') }}"></script>
<script src="{{ asset('plugins/src/filepond/FilePondPluginImageTransform.min.js') }}"></script>
<script src="{{ asset('plugins/src/filepond/filepondPluginFileValidateSize.min.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // --- 1. Init FilePond ---
        FilePond.registerPlugin(
            FilePondPluginImagePreview,
            FilePondPluginImageExifOrientation,
            FilePondPluginFileValidateSize,
            FilePondPluginFileValidateType
        );

        FilePond.create(
            document.querySelector('.file-upload-multiple'),
            {
                // storeAsFile: true SANGAT PENTING agar file dikirim sebagai input file biasa
                storeAsFile: true,
                acceptedFileTypes: ['image/*', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'video/*'],
                labelIdle: 'Drag & Drop file or <span class="filepond--label-action">Browse</span>',
                maxFileSize: '10MB',
                maxFiles: 1 // Batasi 1 file saja untuk broadcast manual (sesuai kebutuhan)
            }
        );

        // --- 2. Hitung jumlah nomor ---
        const phoneInput = document.getElementById('phone_numbers');
        const countDisplay = document.getElementById('numberCount');

        phoneInput.addEventListener('input', function() {
            const text = this.value;
            const numbers = text.split(/[\n,]+/).filter(n => n.trim().length > 0);
            countDisplay.textContent = numbers.length + " nomor terdeteksi";
        });

        // --- 3. Validasi minimal waktu jadwal ---
        const dateInput = document.getElementById('scheduled_at');
        if(dateInput) {
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            dateInput.min = now.toISOString().slice(0,16);
        }

        // --- 4. Loading State saat Submit ---
        const form = document.getElementById('broadcastForm');
        const btn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');

        form.addEventListener('submit', function() {
            btn.disabled = true;
            btnText.textContent = "Memproses...";
        });
    });
</script>
@endpush
