@extends('layouts.app')

@section('title', 'Detail Broadcast')

@push('styles')
{{-- Menggunakan Font Bootstrap Icons --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
{{-- SweetAlert Styles --}}
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">

<style>
    /* Styling Status Badge agar mirip dengan design system */
    .badge-status {
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .badge-completed { background: #e0e7ff; color: #4361ee; border: 1px solid #c7d2fe; } /* Primary Light */
    .badge-sent { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; } /* Green Light */
    .badge-failed { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; } /* Red Light */
    .badge-processing { background: #ffedd5; color: #9a3412; border: 1px solid #fed7aa; } /* Orange Light */
    .badge-pending { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; } /* Gray Light */

    /* Info Box styling (Kotak Data) */
    .info-group {
        margin-bottom: 1.5rem;
    }
    .info-label {
        font-size: 12px;
        color: #888ea8;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }
    .info-value {
        font-size: 15px;
        color: #3b3f5c;
        font-weight: 600;
    }

    /* Message Box yang lebih clean */
    .message-content-box {
        background: #fbfcff;
        border: 1px solid #e0e6ed;
        border-radius: 8px;
        padding: 20px;
        font-size: 14px;
        line-height: 1.6;
        color: #3b3f5c;
        white-space: pre-wrap;
    }

    /* File Attachment Card */
    .attachment-card {
        border: 1px solid #e0e6ed;
        border-radius: 8px;
        padding: 12px 15px;
        display: flex;
        align-items: center;
        background: #fff;
        transition: all 0.2s;
        text-decoration: none;
    }
    .attachment-card:hover {
        border-color: #4361ee;
        background: #f0f5ff;
        color: #4361ee;
    }

    /* Stats Box Minimalis */
    .stat-box-minimal {
        background: #fff;
        border: 1px solid #e0e6ed;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
    }
    .stat-box-minimal .num {
        font-size: 24px;
        font-weight: 700;
        display: block;
        margin-bottom: 2px;
    }
    .stat-box-minimal .txt {
        font-size: 12px;
        color: #888ea8;
        font-weight: 600;
        text-transform: uppercase;
    }

    /* Table styling tweaks */
    .table-hover tbody tr:hover {
        background-color: #fbfcff;
    }
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">

        {{-- Header Halaman (Sama dengan Create) --}}
        <div class="row layout-top-spacing mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="m-0 fw-bold">Detail Broadcast</h3>
                    <p class="text-muted">Informasi lengkap status pengiriman pesan.</p>
                </div>
                <a href="{{ route('broadcast.history') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row">
            {{-- Kolom Kiri: Informasi Utama --}}
            <div class="col-xl-8 col-lg-8 col-md-12 mb-4">
                <div class="widget-content widget-content-area br-8 p-4 h-100">
                    <h5 class="mb-4 fw-bold text-primary border-bottom pb-2">Informasi Pesan</h5>

                    {{-- Judul & Status --}}
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <div class="info-label">Judul Broadcast</div>
                            <div class="fs-5 fw-bold text-dark">{{ $broadcast->title ?? '(Tanpa Judul)' }}</div>
                        </div>
                        <div>
                            @php
                                $statusClass = match($broadcast->status) {
                                    'completed' => 'badge-completed', // Custom Blue
                                    'sent', 'delivered' => 'badge-sent', // Custom Green
                                    'failed', 'cancelled' => 'badge-failed', // Custom Red
                                    'processing', 'sending' => 'badge-processing', // Custom Orange
                                    default => 'badge-pending'
                                };

                                $statusIcon = match($broadcast->status) {
                                    'completed', 'sent' => 'bi-check-circle-fill',
                                    'failed' => 'bi-x-circle-fill',
                                    'processing' => 'bi-arrow-repeat',
                                    'scheduled' => 'bi-alarm',
                                    default => 'bi-hourglass-split'
                                };
                            @endphp
                            <span class="badge-status {{ $statusClass }}">
                                <i class="bi {{ $statusIcon }}"></i>
                                {{ ucfirst($broadcast->status) }}
                            </span>
                        </div>
                    </div>

                    {{-- Grid Informasi Dasar --}}
                    <div class="row mb-2">
                        <div class="col-md-6 info-group">
                            <div class="info-label">Dibuat Oleh</div>
                            <div class="info-value">
                                <i class="bi bi-person-circle text-muted me-1"></i> {{ $broadcast->creator->name }}
                            </div>
                        </div>
                        <div class="col-md-6 info-group">
                            <div class="info-label">Waktu Dibuat</div>
                            <div class="info-value">
                                <i class="bi bi-calendar3 text-muted me-1"></i> {{ $broadcast->created_at->format('d M Y, H:i') }}
                            </div>
                        </div>
                        <div class="col-md-6 info-group">
                            <div class="info-label">Tipe Target</div>
                            <div class="info-value text-capitalize">
                                <i class="bi bi-bullseye text-muted me-1"></i> {{ str_replace('_', ' ', $broadcast->target_type) }}
                            </div>
                        </div>
                        @if ($broadcast->scheduled_at)
                        <div class="col-md-6 info-group">
                            <div class="info-label">Jadwal Pengiriman</div>
                            <div class="info-value text-primary">
                                <i class="bi bi-clock-history me-1"></i> {{ $broadcast->scheduled_at->format('d M Y, H:i') }}
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Error Alert jika ada --}}
                    @if ($broadcast->error_message)
                        <div class="alert alert-light-danger border-danger mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>System Error:</strong> {{ $broadcast->error_message }}
                        </div>
                    @endif

                    {{-- Isi Pesan --}}
                    <div class="mb-4">
                        <div class="info-label mb-2">Isi Pesan</div>
                        <div class="message-content-box">
                            {{ $broadcast->message }}
                        </div>
                    </div>

                    {{-- Lampiran --}}
                    @if ($broadcast->media_url)
                        <div class="mb-2">
                            <div class="info-label mb-2">Lampiran Media</div>
                            <a href="{{ $broadcast->media_url }}" target="_blank" class="attachment-card w-100 w-md-50">
                                <i class="bi bi-file-earmark-richtext fs-3 me-3 text-primary"></i>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="fw-bold text-truncate">{{ $broadcast->media_filename ?? 'File Lampiran' }}</div>
                                    <small class="text-muted">{{ strtoupper($broadcast->media_type ?? 'FILE') }}</small>
                                </div>
                                <i class="bi bi-download"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Kolom Kanan: Statistik & Aksi --}}
            <div class="col-xl-4 col-lg-4 col-md-12 mb-4">

                {{-- Card Statistik --}}
                <div class="widget-content widget-content-area br-8 p-4 mb-4">
                    <h5 class="mb-4 fw-bold text-primary border-bottom pb-2">Statistik</h5>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="stat-box-minimal">
                                <span class="num text-primary">{{ $recipientStats['total'] }}</span>
                                <span class="txt">Total</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box-minimal">
                                <span class="num text-warning">{{ $recipientStats['pending'] }}</span>
                                <span class="txt">Pending</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box-minimal">
                                <span class="num text-success">{{ $recipientStats['successful'] }}</span>
                                <span class="txt">Berhasil</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box-minimal">
                                <span class="num text-danger">{{ $recipientStats['failed'] }}</span>
                                <span class="txt">Gagal</span>
                            </div>
                        </div>
                    </div>

                    @if ($recipientStats['total'] > 0)
                        @php $successRate = round(($recipientStats['successful'] / $recipientStats['total']) * 100, 1); @endphp
                        <div class="mt-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small fw-bold text-muted">Tingkat Keberhasilan</span>
                                <span class="small fw-bold text-success">{{ $successRate }}%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $successRate }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Card Aksi --}}
                <div class="widget-content widget-content-area br-8 p-4">
                    <h5 class="mb-3 fw-bold text-dark">Tindakan</h5>

                    <div class="d-grid gap-2">
                        @if ($recipientStats['failed'] > 0)
                            <form action="{{ route('broadcast.retry', $broadcast->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-warning w-100 text-white" onclick="return confirm('Kirim ulang pesan ke {{ $recipientStats['failed'] }} penerima yang gagal?')">
                                    <i class="bi bi-arrow-clockwise me-2"></i> Retry Gagal ({{ $recipientStats['failed'] }})
                                </button>
                            </form>
                        @endif

                        <button type="button" id="btn-delete" class="btn btn-outline-danger w-100">
                            <i class="bi bi-trash me-2"></i> Hapus Data
                        </button>

                        <form id="delete-form" action="{{ route('broadcast.destroy', $broadcast->id) }}" method="POST" class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel Penerima --}}
        <div class="row layout-spacing">
            <div class="col-12">
                <div class="widget-content widget-content-area br-8 p-4">
                    <h5 class="mb-4 fw-bold text-primary border-bottom pb-2">Detail Penerima</h5>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th class="fw-bold">Nama Penerima</th>
                                    <th class="fw-bold">Nomor Telepon</th>
                                    <th class="fw-bold">Tipe</th>
                                    <th class="fw-bold text-center">Status</th>
                                    <th class="fw-bold">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($broadcast->recipients->sortByDesc('updated_at') as $recipient)
                                    <tr>
                                        <td class="fw-bold text-dark">{{ $recipient->recipient_name }}</td>
                                        <td>{{ $recipient->phone_number }}</td>
                                        <td>
                                            <span class="badge badge-light-secondary text-secondary">
                                                {{ ucfirst($recipient->recipient_type) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $rStatus = match ($recipient->status) {
                                                    'read', 'sent', 'delivered' => ['class' => 'text-success', 'icon' => 'bi-check-all'],
                                                    'failed' => ['class' => 'text-danger', 'icon' => 'bi-x'],
                                                    default => ['class' => 'text-warning', 'icon' => 'bi-clock']
                                                };
                                            @endphp
                                            <span class="{{ $rStatus['class'] }} fw-bold" style="font-size: 0.9em;">
                                                <i class="bi {{ $rStatus['icon'] }}"></i> {{ ucfirst($recipient->status) }}
                                            </span>
                                        </td>
                                        <td class="text-muted small">
                                            {{ $recipient->error_message ? Str::limit($recipient->error_message, 50) : '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                            Belum ada data penerima.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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
    document.addEventListener('DOMContentLoaded', function() {
        const deleteBtn = document.getElementById('btn-delete');
        const deleteForm = document.getElementById('delete-form');

        if(deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Hapus Broadcast?',
                    text: "Data broadcast dan riwayat pengiriman akan dihapus permanen.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e7515a', // Warna merah template
                    cancelButtonColor: '#3b3f5c', // Warna gelap template
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    padding: '2em'
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteForm.submit();
                    }
                });
            });
        }
    });
</script>
@endpush
