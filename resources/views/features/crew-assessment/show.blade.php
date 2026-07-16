@extends('layouts.app')
@section('title', 'Detail Crew Assessment')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
<style>
/* ── Global Layout & Forms ─────────────────────────────────────────── */
.ca-card {
    background: var(--card-bg, #ffffff);
    border: 1px solid var(--card-border-color, #e2e8f0);
    border-radius: 16px;
    padding: 2.25rem 2rem;
    margin-bottom: 1.75rem;
    box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.02), 0 2px 8px -1px rgba(0, 0, 0, 0.02);
}
.dl {
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #64748b;
    margin-bottom: .45rem;
}
.dv {
    font-size: .95rem;
    color: var(--text-color, #1e293b);
    font-weight: 600;
}
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1.5rem 1.25rem;
}
.att-card {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    margin-bottom: 0.75rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.01);
    transition: all .2s;
}
.att-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}
.stat-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: .85rem;
    font-weight: 700;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}
.profile-banner {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 1.5rem 2rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 1.75rem;
}
.profile-avatar {
    width: 54px;
    height: 54px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: #ffffff;
    font-size: 1.2rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(59, 130, 246, 0.2);
}
.profile-name {
    font-size: 1.2rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
    margin: 0 0 2px 0;
}
.profile-subtitle {
    color: #64748b;
    font-size: .85rem;
    font-weight: 500;
}
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
<div class="middle-content container-xxl p-0">

    {{-- Header --}}
    <div class="row layout-top-spacing mb-4 align-items-center">
        <div class="col-md-8">
            <h3 class="m-0 fw-bold">Detail Crew Assessment</h3>
            <p class="text-muted mb-0 small">
                Lihat informasi lengkap riwayat dan berkas assessment crew.
            </p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end flex-wrap">
            <a href="{{ route('crew-assessment.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            @can('manage crew assessment')
            <a href="{{ route('crew-assessment.edit', $crewAssessment->id) }}" class="btn btn-warning btn-sm px-3 text-dark fw-bold">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            @endcan
        </div>
    </div>

    {{-- Profile Banner --}}
    <div class="profile-banner">
        <div class="profile-avatar">
            {{ $crewAssessment->crewMember ? strtoupper(substr($crewAssessment->crewMember->name, 0, 2)) : ($crewAssessment->crew_name_text ? strtoupper(substr($crewAssessment->crew_name_text, 0, 2)) : '?') }}
        </div>
        <div>
            <h4 class="profile-name">
                {{ $crewAssessment->crewMember?->name ?? $crewAssessment->crew_name_text ?? '—' }}
                @if(empty($crewAssessment->crew_member_id) && !empty($crewAssessment->crew_name_text))
                    <span class="badge bg-warning text-dark ms-2" style="font-size:0.7rem; font-weight:600; text-transform:none; padding:4px 8px; border-radius:12px;">Belum Terhubung</span>
                @endif
            </h4>
            <div class="profile-subtitle d-flex align-items-center gap-2 flex-wrap mt-1">
                <span class="badge bg-primary px-3 py-1 text-white" style="border-radius:20px; font-weight:600; font-size:.75rem;">
                    {{ $crewAssessment->coc ?? $crewAssessment->crewMember?->position ?? 'No COC' }}
                </span>
                <span class="text-muted">|</span>
                <span>NIK: <strong>{{ $crewAssessment->crewMember?->nik ?? '—' }}</strong></span>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- ── LEFT COLUMN ───────────────────────────────── --}}
        <div class="col-xl-8">

            {{-- Hasil banner --}}
            @if($crewAssessment->result)
            @php
                $resultBg = match($crewAssessment->result) {
                    'Lulus'       => 'linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%)',
                    'Pending'     => 'linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%)',
                    'Tidak Lulus' => 'linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%)',
                    default       => 'linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%)',
                };
                $resultBorder = match($crewAssessment->result) {
                    'Lulus'       => '#bbf7d0',
                    'Pending'     => '#fde68a',
                    'Tidak Lulus' => '#fecaca',
                    default       => '#e2e8f0',
                };
                $resultColor = match($crewAssessment->result) {
                    'Lulus'       => '#15803d',
                    'Pending'     => '#b45309',
                    'Tidak Lulus' => '#b91c1c',
                    default       => '#475569',
                };
                $resultIcon = match($crewAssessment->result) {
                    'Lulus'       => 'bi-patch-check-fill',
                    'Pending'     => 'bi-hourglass-split',
                    'Tidak Lulus' => 'bi-x-circle-fill',
                    default       => 'bi-question-circle',
                };
            @endphp
            <div class="d-flex align-items-center gap-3 mb-4 p-4 rounded-3"
                 style="background:{{ $resultBg }}; border: 1px solid {{ $resultBorder }}; color: {{ $resultColor }}; box-shadow: 0 4px 12px rgba(0,0,0,0.01);">
                <i class="bi {{ $resultIcon }} fs-1"></i>
                <div>
                    <div class="fw-extrabold fs-5 text-uppercase" style="letter-spacing:0.02em;">HASIL: {{ $crewAssessment->result }}</div>
                    @if($crewAssessment->notes)
                        <div class="mt-1 opacity-90 fw-medium" style="font-size:.9rem">{{ $crewAssessment->notes }}</div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Data Kru --}}
            <div class="ca-card">
                <h6 class="fw-bold mb-4 pb-2 border-bottom" style="color:var(--text-color, #0f172a);">
                    <i class="bi bi-person-circle me-2 text-primary"></i> Data Kru &amp; Dokumen
                </h6>
                <div class="info-grid">
                    <div>
                        <div class="dl">Nama Lengkap</div>
                        <div class="dv text-primary fw-bold">{{ $crewAssessment->crewMember?->name ?? $crewAssessment->crew_name_text ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="dl">NIK / Kode Pelaut</div>
                        <div class="dv">{{ $crewAssessment->crewMember?->nik ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="dl">Jabatan Asal (Sistem)</div>
                        <div class="dv">{{ $crewAssessment->crewMember?->position ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="dl">No. Sertifikat</div>
                        <div class="dv">{{ $crewAssessment->certificate_number ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="dl">COC Pelaut</div>
                        <div class="dv">{{ $crewAssessment->coc ?? $crewAssessment->crewMember?->position ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="dl">Tipe Integrasi</div>
                        <div class="dv">
                            @if($crewAssessment->crew_member_id)
                                <span class="badge bg-success px-2.5 py-1 text-white" style="font-size:.72rem;">Sistem (Terhubung)</span>
                            @else
                                <span class="badge bg-warning px-2.5 py-1 text-dark" style="font-size:.72rem;">Manual (Belum Terhubung)</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Jabatan & Pengalaman --}}
            <div class="ca-card">
                <h6 class="fw-bold mb-4 pb-2 border-bottom" style="color:var(--text-color, #0f172a);">
                    <i class="bi bi-person-badge me-2 text-primary"></i> Jabatan Diusulkan &amp; Pengalaman
                </h6>
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="dl">Jabatan Diusulkan</div>
                        <div class="dv text-primary fw-bold">{{ $crewAssessment->position_proposed ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="dl">Tipe Jabatan</div>
                        <div class="dv">{{ $crewAssessment->position_type ?? '—' }}</div>
                    </div>
                </div>
                <div class="info-grid">
                    <div>
                        <div class="dl">Pengalaman Pertamina</div>
                        <div class="dv">{{ $crewAssessment->experience_pertamina ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="dl">Pengalaman Master</div>
                        <div class="dv">{{ $crewAssessment->experience_master ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="dl">Pengalaman Luar</div>
                        <div class="dv">{{ $crewAssessment->experience_outside ?? '—' }}</div>
                    </div>
                </div>
            </div>

            {{-- Detail Assessment --}}
            <div class="ca-card">
                <h6 class="fw-bold mb-4 pb-2 border-bottom" style="color:var(--text-color, #0f172a);">
                    <i class="bi bi-journal-check me-2 text-primary"></i> Rincian Assessment
                </h6>
                <div class="info-grid mb-4">
                    <div>
                        <div class="dl">Tipe MEV</div>
                        <div class="dv">{{ $crewAssessment->mev_type ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="dl">Tanggal Assessment</div>
                        <div class="dv text-dark">{{ $crewAssessment->assessment_date->format('d M Y') }}</div>
                    </div>
                    <div>
                        <div class="dl">Lokasi</div>
                        <div class="dv">{{ $crewAssessment->assessment_location ?? '—' }}</div>
                    </div>
                </div>

                <p class="sec-sublabel mb-3 mt-4"><i class="bi bi-people me-1"></i>Daftar Assessor</p>
                <div class="info-grid">
                    <div>
                        <div class="dl">Assessor MAR</div>
                        <div class="dv"><span class="badge bg-light text-dark border px-3 py-1.5 fw-semibold">{{ $crewAssessment->assessor_mar ?? '—' }}</span></div>
                    </div>
                    <div>
                        <div class="dl">Assessor HSE</div>
                        <div class="dv"><span class="badge bg-light text-dark border px-3 py-1.5 fw-semibold">{{ $crewAssessment->assessor_hse ?? '—' }}</span></div>
                    </div>
                    <div>
                        <div class="dl">Assessor FMC</div>
                        <div class="dv"><span class="badge bg-light text-dark border px-3 py-1.5 fw-semibold">{{ $crewAssessment->assessor_fmc ?? '—' }}</span></div>
                    </div>
                </div>

                @if($crewAssessment->valid_from || $crewAssessment->valid_until)
                <p class="sec-sublabel mb-3 mt-4"><i class="bi bi-calendar-range me-1"></i>Masa Berlaku</p>
                <div class="info-grid">
                    <div>
                        <div class="dl">Berlaku Mulai</div>
                        <div class="dv text-success">{{ $crewAssessment->valid_from?->format('d M Y') ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="dl">Berlaku Hingga</div>
                        <div class="dv text-danger">{{ $crewAssessment->valid_until?->format('d M Y') ?? '—' }}</div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Lampiran --}}
            <div class="ca-card">
                <h6 class="fw-bold mb-4 pb-2 border-bottom" style="color:var(--text-color, #0f172a);">
                    <i class="bi bi-paperclip me-2 text-primary"></i> Lampiran Berkas
                    <span class="badge bg-secondary ms-2" style="font-size:.78rem; font-weight:600;">{{ $crewAssessment->attachments->count() }}</span>
                </h6>
                @forelse($crewAssessment->attachments as $att)
                <div class="att-card">
                    <i class="{{ $att->icon_class }} fs-2 flex-shrink-0" style="color:#4f46e5;"></i>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-bold text-truncate text-dark" style="font-size:.88rem;">{{ $att->original_name }}</div>
                        <div class="text-muted mt-1" style="font-size:.72rem">
                            {{ $att->size_human }}
                            @if($att->label) · <span class="badge bg-light border text-dark">{{ $att->label }}</span>@endif
                            · Uploaded: {{ $att->created_at->format('d M Y') }}
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('crew-assessment.attachment.download', $att->id) }}"
                           class="btn btn-sm btn-outline-primary" style="border-radius:20px;" title="Download File">
                            <i class="bi bi-download"></i>
                        </a>
                        @can('manage crew assessment')
                        <button class="btn btn-sm btn-outline-danger delete-attachment" style="border-radius:20px;"
                                data-id="{{ $att->id }}" data-name="{{ $att->original_name }}"
                                title="Hapus Permanen">
                            <i class="bi bi-trash"></i>
                        </button>
                        @endcan
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-folder-x fs-2 d-block mb-2 opacity-55"></i>
                    <p class="small mb-0">Belum ada lampiran file yang diupload.</p>
                </div>
                @endforelse
            </div>

        </div>

        {{-- ── RIGHT COLUMN ──────────────────────────────── --}}
        <div class="col-xl-4">

            {{-- Kapal & Perusahaan --}}
            <div class="ca-card">
                <h6 class="fw-bold mb-4 pb-2 border-bottom" style="color:var(--text-color, #0f172a);">
                    <i class="bi bi-ship me-2 text-primary"></i> Kapal &amp; Perusahaan
                </h6>
                <div class="mb-4">
                    <div class="dl">Perusahaan (Assessment)</div>
                    <div class="dv text-primary fw-bold" style="font-size:1.05rem;">
                        {{ $crewAssessment->company?->name ?? $crewAssessment->company_name_text ?? '—' }}
                        @if(empty($crewAssessment->company_id) && !empty($crewAssessment->company_name_text))
                            <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem; font-weight:600; text-transform:none; padding:2px 6px; border-radius:8px;">Belum Terhubung</span>
                        @endif
                    </div>
                </div>
                <div class="mb-4">
                    <div class="dl">Nama Kapal</div>
                    <div class="dv" style="font-size:1.05rem;">
                        <i class="bi bi-ship text-muted me-1"></i> {{ $crewAssessment->vessel?->name ?? $crewAssessment->vessel_name_text ?? '—' }}
                        @if(empty($crewAssessment->vessel_id) && !empty($crewAssessment->vessel_name_text))
                            <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem; font-weight:600; text-transform:none; padding:2px 6px; border-radius:8px;">Belum Terhubung</span>
                        @endif
                    </div>
                </div>
                @if($crewAssessment->crewMember)
                <div>
                    <div class="dl">Status Kru Terkini</div>
                    <div class="dv mt-1.5">
                        @if($crewAssessment->crewMember->is_active)
                            <span class="badge bg-success px-3 py-1.5 text-white" style="border-radius:20px; font-weight:600; font-size:.78rem;">Aktif</span>
                        @else
                            <span class="badge bg-secondary px-3 py-1.5 text-white" style="border-radius:20px; font-weight:600; font-size:.78rem;">Tidak Aktif</span>
                        @endif
                    </div>
                    <div class="form-text text-muted mt-2.5" style="font-size:.72rem; line-height:1.4;">
                        <i class="bi bi-info-circle-fill text-warning me-1"></i>
                        Informasi perusahaan di atas merekam kondisi saat assessment ini dilaksanakan. Perusahaan asal crew saat ini: <strong>{{ $crewAssessment->crewMember->company?->name ?? '—' }}</strong>.
                    </div>
                </div>
                @endif
            </div>

            {{-- Hasil & Status --}}
            <div class="ca-card">
                <h6 class="fw-bold mb-4 pb-2 border-bottom" style="color:var(--text-color, #0f172a);">
                    <i class="bi bi-award me-2 text-primary"></i> Hasil &amp; Status Record
                </h6>
                <div class="mb-4">
                    <div class="dl">Hasil Akhir</div>
                    <div class="mt-2">
                        @if($crewAssessment->result)
                        <span class="stat-pill bg-{{ $crewAssessment->result_color }} text-white">
                            <i class="bi {{ match($crewAssessment->result) {
                                'Lulus'       => 'bi-check-circle-fill',
                                'Pending'     => 'bi-hourglass-split',
                                'Tidak Lulus' => 'bi-x-circle-fill',
                                default       => 'bi-question-circle'
                            } }}"></i>
                            {{ $crewAssessment->result }}
                        </span>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="dl">Status Record</div>
                    <div class="mt-2">
                        @php
                            $stColor = match($crewAssessment->status) {
                                'active'  => 'success',
                                'expired' => 'warning',
                                'revoked' => 'danger',
                                default   => 'secondary',
                            };
                        @endphp
                        <span class="badge bg-{{ $stColor }} px-3 py-1.5" style="border-radius:20px; font-weight:600; font-size:.78rem;">{{ ucfirst($crewAssessment->status) }}</span>
                    </div>
                </div>
            </div>

            {{-- Metadata --}}
            <div class="ca-card text-muted">
                <h6 class="fw-bold mb-4 pb-2 border-bottom" style="color:var(--text-color, #0f172a);">
                    <i class="bi bi-info-circle me-2 text-primary"></i> Metadata Assessment
                </h6>
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <span class="dl m-0">Diinput Oleh</span>
                    <span class="dv small" style="font-weight:600;">{{ $crewAssessment->creator?->name ?? '—' }}</span>
                </div>
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <span class="dl m-0">Tanggal Input</span>
                    <span class="dv small">{{ $crewAssessment->created_at->format('d M Y H:i') }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="dl m-0">Terakhir Update</span>
                    <span class="dv small">{{ $crewAssessment->updated_at->format('d M Y H:i') }}</span>
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
(function ($) {
    'use strict';
    const Toast = Swal.mixin({
        toast: true, position: 'top-end',
        showConfirmButton: false, timer: 3000, timerProgressBar: true
    });

    $(document).on('click', '.delete-attachment', function () {
        const id   = $(this).data('id');
        const name = $(this).data('name');
        const btn  = $(this);

        Swal.fire({
            title: 'Hapus Lampiran?',
            html: '<strong>' + $('<div>').text(name).html() + '</strong> akan dihapus permanen.',
            icon: 'warning',
            showCancelButton:    true,
            confirmButtonColor:  '#e7515a',
            cancelButtonColor:   '#3b3f5c',
            confirmButtonText:   'Ya, hapus!',
            cancelButtonText:    'Batal'
        }).then(r => {
            if (r.isConfirmed) {
                $.ajax({
                    url:  '/crew-assessment/attachment/' + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: res => {
                        if (res.success) {
                            btn.closest('.att-card').fadeOut(200, function () { $(this).remove(); });
                            Toast.fire({ icon: 'success', title: res.message });
                        }
                    },
                    error: () => Toast.fire({ icon: 'error', title: 'Gagal menghapus lampiran.' })
                });
            }
        });
    });
}(jQuery));
</script>
@endpush
