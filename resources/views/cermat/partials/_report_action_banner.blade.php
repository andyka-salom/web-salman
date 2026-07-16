{{--
|--------------------------------------------------------------------------
| Report Action Banner Partial
|--------------------------------------------------------------------------
|
| Partial ini menampilkan banner notifikasi yang menonjol di bagian atas
| halaman detail laporan. Banner ini memberitahu pengguna tentang tindakan
| selanjutnya yang diperlukan berdasarkan status laporan saat ini.
|
| Variabel yang dibutuhkan:
|   - $report: Instance model CermatReport.
|
--}}

@php
    // Ambil pengguna yang sedang login untuk perbandingan otorisasi.
    $user = Auth::user();
@endphp

@switch($report->status)

    {{-- KASUS 1: Laporan Menunggu Persetujuan Supervisor --}}
    @case(\App\Models\CermatReport::STATUS_AWAITING_APPROVAL)
        {{-- Tampilkan banner ini HANYA jika pengguna yang login adalah supervisor yang ditugaskan. --}}
        @if ($report->line_supervisor_id === $user->id)
        <div class="card shadow-sm border-0 bg-primary-subtle">
            <div class="card-body d-flex align-items-center flex-wrap gap-3 p-3">
                <div class="flex-grow-1">
                    <h6 class="mb-0 text-primary-emphasis"><i class="bi bi-exclamation-diamond-fill me-2"></i>Tindakan Diperlukan</h6>
                    <small>Laporan ini memerlukan persetujuan Anda untuk melanjutkan proses.</small>
                </div>
                <div class="d-flex gap-2">
                     <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approvalModal" data-decision="approve"><i class="bi bi-check-circle-fill me-1"></i> Setujui</button>
                     <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#approvalModal" data-decision="reject"><i class="bi bi-x-circle-fill me-1"></i> Tolak</button>
                </div>
            </div>
        </div>
        @endif
        @break

    {{-- KASUS 2: Laporan Menunggu Review dari HSSE --}}
    @case(\App\Models\CermatReport::STATUS_AWAITING_HSSE_REVIEW)
        {{-- Idealnya, tombol ini dilindungi oleh permission, contoh: @can('review cermat report') --}}
        <div class="card shadow-sm border-0 bg-primary-subtle">
            <div class="card-body d-flex align-items-center flex-wrap gap-3 p-3">
                 <div class="flex-grow-1">
                    <h6 class="mb-0 text-primary-emphasis"><i class="bi bi-exclamation-diamond-fill me-2"></i>Tindakan Diperlukan</h6>
                    <small>Laporan ini memerlukan review dan klasifikasi dari tim HSSE.</small>
                </div>
                <a href="{{ route('cermat.reports.review', $report) }}" class="btn btn-primary"><i class="bi bi-journal-check me-1"></i> Lakukan Review</a>
            </div>
        </div>
        {{-- @endcan --}}
        @break

    {{-- KASUS 3: Laporan dalam Tahap Tindakan Perbaikan --}}
    @case(\App\Models\CermatReport::STATUS_AWAITING_RECOMMENDATION)
    @case(\App\Models\CermatReport::STATUS_ACTION_IN_PROGRESS)
        <div class="card shadow-sm border-0 bg-primary-subtle">
            <div class="card-body d-flex align-items-center flex-wrap gap-3 p-3">
                <div class="flex-grow-1">
                    <h6 class="mb-0 text-primary-emphasis"><i class="bi bi-exclamation-diamond-fill me-2"></i>Tindakan Diperlukan</h6>
                    <small>Laporan ini memerlukan penambahan atau penyelesaian tindakan perbaikan.</small>
                </div>
                <a href="{{ route('cermat.reports.action', $report) }}" class="btn btn-primary"><i class="bi bi-card-checklist me-1"></i> Kelola Tindakan</a>
            </div>
        </div>
        @break

    {{-- KASUS 4: Laporan Siap untuk Ditutup (Semua Tindakan Selesai) --}}
    @case(\App\Models\CermatReport::STATUS_AWAITING_CLOSEOUT)
        {{-- Idealnya, tombol ini dilindungi oleh permission, contoh: @can('close cermat report') --}}
        <div class="card shadow-sm border-0 bg-primary-subtle">
            <div class="card-body d-flex align-items-center flex-wrap gap-3 p-3">
                <div class="flex-grow-1">
                    <h6 class="mb-0 text-primary-emphasis"><i class="bi bi-exclamation-diamond-fill me-2"></i>Tindakan Diperlukan</h6>
                    <small>Semua tindakan perbaikan telah tuntas. Laporan ini siap untuk ditutup.</small>
                </div>
                <a href="{{ route('cermat.reports.close', $report) }}" class="btn btn-primary"><i class="bi bi-lock-fill me-1"></i> Tutup Laporan</a>
            </div>
        </div>
        {{-- @endcan --}}
        @break

@endswitch
