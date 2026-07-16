@extends('layouts.app')
@section('title', 'Edit Crew Assessment')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<style>
/* ── Global Layout & Forms ─────────────────────────────────────────── */
.ca-card {
    background: var(--card-bg, #ffffff);
    border: 1px solid var(--card-border-color, #e2e8f0);
    border-radius: 16px;
    padding: 2.25rem 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.02), 0 2px 8px -1px rgba(0, 0, 0, 0.02);
    transition: transform 0.22s ease, box-shadow 0.22s ease;
}
.ca-card:hover {
    box-shadow: 0 12px 28px -5px rgba(0, 0, 0, 0.04), 0 8px 16px -6px rgba(0, 0, 0, 0.02);
}
.ca-card-header {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    margin-bottom: 2rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid var(--card-border-color, #f1f5f9);
}
.ca-step {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: #ffffff;
    font-size: .95rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(59, 130, 246, 0.25);
}
.ca-card-header h6 {
    margin: 0;
    font-weight: 800;
    font-size: 1.15rem;
    color: var(--text-color, #0f172a);
    letter-spacing: -0.02em;
}
.ca-card-header small {
    color: #64748b;
    font-size: .83rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 2px;
}

/* ── Form Controls & Select2 ─────────────────────────────────────────── */
.form-label {
    font-size: .78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #475569;
    margin-bottom: .6rem;
    display: block;
}
.form-control, .form-select, .select2-container--default .select2-selection--single {
    height: 46px !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 10px !important;
    font-size: 0.92rem !important;
    padding: 0.45rem 0.95rem !important;
    background-color: var(--card-bg, #ffffff) !important;
    color: var(--text-color, #1e293b) !important;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    display: flex;
    align-items: center;
}
.form-control::placeholder {
    color: #94a3b8;
}
.form-control:focus, .form-select:focus,
.select2-container--default.select2-container--focus .select2-selection--single,
.select2-container--default.select2-container--open .select2-selection--single {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12) !important;
    outline: none !important;
}

/* ── Select2 Custom Styling ──────────────────────────────────────────── */
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 34px !important;
    padding-left: 0 !important;
    color: var(--text-color, #1e293b) !important;
    font-weight: 500;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 44px !important;
    right: 8px !important;
}
.select2-dropdown {
    border: 1px solid #cbd5e1 !important;
    border-radius: 10px !important;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.06), 0 8px 10px -6px rgba(0,0,0,0.03) !important;
    overflow: hidden;
    z-index: 9999;
}
.select2-results__option {
    padding: 10px 14px !important;
    font-size: 0.9rem !important;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #3b82f6 !important;
}

/* Styling Select2 inside input-group */
.input-group {
    border-radius: 10px;
    box-shadow: none;
}
.input-group-text {
    background-color: #f8fafc;
    border: 1px solid #cbd5e1;
    color: #64748b;
    padding: 0.375rem 1rem;
    font-size: 1rem;
    border-radius: 10px 0 0 10px !important;
}
.input-group .form-control, .input-group .form-select {
    border-radius: 0 10px 10px 0 !important;
    border-left: 0 !important;
}
.input-group .select2-container--default {
    flex: 1 1 auto;
    width: auto !important;
}
.input-group .select2-container--default .select2-selection--single {
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
    border-left: 0 !important;
}

/* ── Attachments & Dropzone ──────────────────────────────────────────── */
.att-ex, .att-new {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    margin-bottom: 0.85rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.01);
    transition: border-color .2s;
}
.att-ex:hover {
    border-color: #cbd5e1;
}
.att-new {
    background: #f0fdf4;
    border-color: #bbf7d0;
}
.att-new:hover {
    border-color: #86efac;
}
.drop-zone {
    border: 2px dashed #3b82f6;
    border-radius: 12px;
    padding: 3rem 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    background: #f8fafc;
}
.drop-zone:hover, .drop-zone.dragover {
    border-color: #1d4ed8;
    background: #eff6ff;
}
.drop-zone i {
    font-size: 2.5rem;
    color: #3b82f6;
    margin-bottom: 0.5rem;
    display: inline-block;
    transition: transform 0.2s;
}
.drop-zone:hover i {
    transform: translateY(-4px);
}
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
<div class="middle-content container-xxl p-0">

    <div class="row layout-top-spacing mb-4 align-items-center">
        <div class="col-md-8">
            <h3 class="m-0 fw-bold">Edit Crew Assessment</h3>
            <p class="text-muted mb-0 small">
                {{ $crewAssessment->crewMember?->name ?? '—' }}
                &mdash; {{ $crewAssessment->assessment_date->format('d M Y') }}
            </p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('crew-assessment.show', $crewAssessment->id) }}"
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger mb-3">
        <strong>Terdapat kesalahan:</strong>
        <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('crew-assessment.update', $crewAssessment->id) }}"
          method="POST" enctype="multipart/form-data" id="editForm">
        @csrf
        @method('PUT')

        {{-- ═══ SEKSI 1: Kru, Kapal & Perusahaan ═══════════════════════════ --}}
        <div class="ca-card">
            <div class="ca-card-header">
                <div class="ca-step">1</div>
                <div>
                    <h6>Data Kru, Kapal &amp; Perusahaan</h6>
                    <small>
                        <i class="bi bi-lock-fill text-warning me-1"></i>Wajib dipilih dari sistem
                    </small>
                </div>
            </div>

            {{-- Perusahaan & Kapal --}}
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">
                        Perusahaan <span class="text-danger">*</span>
                        <small class="text-muted fw-normal">(untuk relasi assessment)</small>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="bi bi-building" style="font-size:.85rem;color:#6b7280;"></i>
                        </span>
                        <select class="form-select border-start-0 @error('company_id') is-invalid @enderror"
                                name="company_id" id="company_id" required>
                            <option value="">— Pilih Perusahaan —</option>
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}"
                                    {{ old('company_id', $crewAssessment->company_id) == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                            @if(empty($crewAssessment->company_id) && !empty($crewAssessment->company_name_text))
                                <option value="{{ $crewAssessment->company_name_text }}" selected>
                                    {{ $crewAssessment->company_name_text }} (Belum Terhubung)
                                </option>
                            @endif
                        </select>
                        @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">
                        Kapal <span class="text-danger">*</span>
                        <small class="text-muted fw-normal">(pilih perusahaan dulu)</small>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="bi bi-ship" style="font-size:.85rem;color:#6b7280;"></i>
                        </span>
                        <select class="form-select border-start-0 @error('vessel_id') is-invalid @enderror"
                                name="vessel_id" id="vessel_id" required>
                            <option value="">— Pilih Kapal —</option>
                            @foreach($vessels as $v)
                                <option value="{{ $v->id }}"
                                    {{ old('vessel_id', $crewAssessment->vessel_id) == $v->id ? 'selected' : '' }}>
                                    {{ $v->name }}
                                </option>
                            @endforeach
                            @if(empty($crewAssessment->vessel_id) && !empty($crewAssessment->vessel_name_text))
                                <option value="{{ $crewAssessment->vessel_name_text }}" selected>
                                    {{ $crewAssessment->vessel_name_text }} (Belum Terhubung)
                                </option>
                            @endif
                        </select>
                        @error('vessel_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Crew search box --}}
            <div class="crew-search-box">
                <p class="sec-sublabel mb-2">
                    <i class="bi bi-person-search me-1"></i>Cari Kru
                    <span class="text-danger ms-1">*</span>
                </p>

                <div class="crew-select2-wrap">
                    <select class="@error('crew_member_id') is-invalid @enderror"
                            name="crew_member_id" id="crew_member_id"
                            required style="width:100%">
                        @if($crewAssessment->crewMember)
                            <option value="{{ $crewAssessment->crewMember->id }}" selected>
                                {{ $crewAssessment->crewMember->name }}{{ $crewAssessment->crewMember->nik ? ' — '.$crewAssessment->crewMember->nik : '' }}
                            </option>
                        @elseif($crewAssessment->crew_name_text)
                            <option value="{{ $crewAssessment->crew_name_text }}" selected>
                                {{ $crewAssessment->crew_name_text }} (Belum Terhubung)
                            </option>
                        @endif
                    </select>
                    @error('crew_member_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="form-text mt-1" style="font-size:.76rem;">
                    <i class="bi bi-lightbulb text-warning me-1"></i>
                    Ketik nama atau NIK — tidak perlu pilih perusahaan/kapal dulu.
                    Menampilkan semua kru aktif.
                </div>

                {{-- Crew info card --}}
                <div id="crewInfoBoxWrap" {{ $crewAssessment->crewMember || $crewAssessment->crew_name_text ? '' : 'style=display:none' }}>
                    <div class="crew-info-card">
                        <div class="crew-info-avatar" id="cbAvatar">
                            @if($crewAssessment->crewMember)
                                {{ strtoupper(substr($crewAssessment->crewMember->name, 0, 2)) }}
                            @elseif($crewAssessment->crew_name_text)
                                {{ strtoupper(substr($crewAssessment->crew_name_text, 0, 2)) }}
                            @else
                                ?
                            @endif
                        </div>
                        <div class="flex-grow-1" style="min-width:0;">
                            <div class="crew-info-name" id="cbName">{{ $crewAssessment->crewMember?->name ?? $crewAssessment->crew_name_text ?? '—' }}</div>
                            <div class="crew-info-tags">
                                <span class="citag citag-pos" id="cbPosTag" {{ $crewAssessment->crewMember?->position ? '' : 'style=display:none' }}>
                                    <i class="bi bi-briefcase-fill" style="font-size:.6rem;"></i>
                                    <span id="cbPos">{{ $crewAssessment->crewMember?->position ?? '' }}</span>
                                </span>
                                <span class="citag citag-nik" id="cbNikTag" {{ $crewAssessment->crewMember?->nik ? '' : 'style=display:none' }}>
                                    <i class="bi bi-person-badge-fill" style="font-size:.6rem;"></i>
                                    <span id="cbNik">{{ $crewAssessment->crewMember?->nik ?? '' }}</span>
                                </span>
                                <span class="citag citag-co" id="cbCoTag" {{ $crewAssessment->company || $crewAssessment->company_name_text ? '' : 'style=display:none' }}>
                                    <i class="bi bi-building-fill" style="font-size:.6rem;"></i>
                                    <span id="cbCompany">{{ $crewAssessment->company?->name ?? $crewAssessment->company_name_text ?? '' }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ SEKSI 2: Sertifikat & COC ═════════════════════════════════ --}}
        <div class="ca-card">
            <div class="ca-card-header">
                <div class="ca-step">2</div>
                <div>
                    <h6>Sertifikat &amp; COC</h6>
                    <small>No. Sertifikat (Kol. B) berbeda dari COC (Kol. E)</small>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">
                        No. Sertifikat
                        <span class="text-muted fw-normal">(Kol. B — opsional)</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="bi bi-card-text" style="font-size:.85rem;color:#6b7280;"></i>
                        </span>
                        <input type="text"
                               class="form-control border-start-0 @error('certificate_number') is-invalid @enderror"
                               name="certificate_number" value="{{ old('certificate_number', $crewAssessment->certificate_number) }}"
                               placeholder="Nomor sertifikat (jika ada)" maxlength="100">
                        @error('certificate_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">
                        COC
                        <span class="text-muted fw-normal">(Kol. E — cth: ANT IV M)</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="bi bi-award" style="font-size:.85rem;color:#6b7280;"></i>
                        </span>
                        <input type="text"
                               class="form-control border-start-0 @error('coc') is-invalid @enderror"
                               name="coc" id="coc_field" value="{{ old('coc', $crewAssessment->coc) }}"
                               placeholder="cth: ANT IV M, ATT V M, ANT.V" maxlength="100">
                        @error('coc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-text">Otomatis terisi dari jabatan kru, bisa diubah.</div>
                </div>
            </div>
        </div>

        {{-- ═══ SEKSI 3: Jabatan ═══════════════════════════════════════════ --}}
        <div class="ca-card">
            <div class="ca-card-header">
                <div class="ca-step">3</div>
                <div>
                    <h6>Jabatan</h6>
                    <small>Kol. F (jabatan diusulkan) &amp; Kol. G (tipe)</small>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Jabatan Diusulkan <span class="text-muted fw-normal">(Kol. F)</span></label>
                    <select class="form-select select2-tags @error('position_proposed') is-invalid @enderror"
                            name="position_proposed" id="position_proposed">
                        <option value="">— Pilih atau Ketik —</option>
                        @foreach(\App\Models\CrewAssessment::POSITIONS as $k => $v)
                            <option value="{{ $k }}" {{ old('position_proposed', $crewAssessment->position_proposed) == $k ? 'selected' : '' }}>
                                {{ $v }}
                            </option>
                        @endforeach
                        @if($crewAssessment->position_proposed && !array_key_exists($crewAssessment->position_proposed, \App\Models\CrewAssessment::POSITIONS))
                            <option value="{{ $crewAssessment->position_proposed }}" selected>{{ $crewAssessment->position_proposed }}</option>
                        @endif
                    </select>
                    @error('position_proposed')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tipe <span class="text-muted fw-normal">(Kol. G)</span></label>
                    <select class="form-select select2-tags @error('position_type') is-invalid @enderror"
                            name="position_type" id="position_type">
                        <option value="">— Pilih atau Ketik —</option>
                        @foreach(\App\Models\CrewAssessment::POSITION_TYPES as $k => $v)
                            <option value="{{ $k }}" {{ old('position_type', $crewAssessment->position_type) == $k ? 'selected' : '' }}>
                                {{ $v }}
                            </option>
                        @endforeach
                        @if($crewAssessment->position_type && !array_key_exists($crewAssessment->position_type, \App\Models\CrewAssessment::POSITION_TYPES))
                            <option value="{{ $crewAssessment->position_type }}" selected>{{ $crewAssessment->position_type }}</option>
                        @endif
                    </select>
                    @error('position_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- ═══ SEKSI 4: Pengalaman ════════════════════════════════════════ --}}
        <div class="ca-card">
            <div class="ca-card-header">
                <div class="ca-step">4</div>
                <div>
                    <h6>Pengalaman</h6>
                    <small>Kol. I (Pertamina), Kol. J (Master), Kol. K (Diluar)</small>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Pertamina <span class="text-muted fw-normal">(Kol. I)</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0"
                              style="font-size:.7rem;font-weight:700;color:#3b82f6;min-width:40px;justify-content:center;">PT</span>
                        <input type="text" class="form-control border-start-0"
                               name="experience_pertamina" value="{{ old('experience_pertamina', $crewAssessment->experience_pertamina) }}"
                               placeholder="cth: 2 thn 3 bln">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Master <span class="text-muted fw-normal">(Kol. J)</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0"
                              style="font-size:.7rem;font-weight:700;color:#8b5cf6;min-width:40px;justify-content:center;">MS</span>
                        <input type="text" class="form-control border-start-0"
                               name="experience_master" value="{{ old('experience_master', $crewAssessment->experience_master) }}"
                               placeholder="cth: 1 thn">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Diluar <span class="text-muted fw-normal">(Kol. K)</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0"
                              style="font-size:.7rem;font-weight:700;color:#6b7280;min-width:40px;justify-content:center;">DL</span>
                        <input type="text" class="form-control border-start-0"
                               name="experience_outside" value="{{ old('experience_outside', $crewAssessment->experience_outside) }}"
                               placeholder="cth: 5 thn">
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ SEKSI 5: Assessment ════════════════════════════════════════ --}}
        <div class="ca-card">
            <div class="ca-card-header">
                <div class="ca-step">5</div>
                <div>
                    <h6>Detail Assessment</h6>
                    <small>MEV (L), Tanggal &amp; Lokasi (M), Assessor MAR/HSE/FMC (N/O/P)</small>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">MEV Type <span class="text-muted fw-normal">(Kol. L)</span></label>
                    <select class="form-select select2-tags @error('mev_type') is-invalid @enderror"
                            name="mev_type" id="mev_type">
                        <option value="">— Pilih —</option>
                        @foreach(\App\Models\CrewAssessment::MEV_TYPES as $k => $v)
                            <option value="{{ $k }}" {{ old('mev_type', $crewAssessment->mev_type) == $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                        @if($crewAssessment->mev_type && !array_key_exists($crewAssessment->mev_type, \App\Models\CrewAssessment::MEV_TYPES))
                            <option value="{{ $crewAssessment->mev_type }}" selected>{{ $crewAssessment->mev_type }}</option>
                        @endif
                    </select>
                    @error('mev_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">
                        Tanggal Assessment <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="bi bi-calendar3" style="font-size:.85rem;color:#6b7280;"></i>
                        </span>
                        <input type="text"
                               class="form-control border-start-0 flatpickr @error('assessment_date') is-invalid @enderror"
                               name="assessment_date" value="{{ old('assessment_date', $crewAssessment->assessment_date?->format('Y-m-d')) }}"
                               required placeholder="YYYY-MM-DD">
                        @error('assessment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Lokasi</label>
                    <select class="form-select select2-tags @error('assessment_location') is-invalid @enderror"
                            name="assessment_location" id="assessment_location">
                        <option value="">— Pilih —</option>
                        @foreach(\App\Models\CrewAssessment::LOCATIONS as $k => $v)
                            <option value="{{ $k }}" {{ old('assessment_location', $crewAssessment->assessment_location) == $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                        @if($crewAssessment->assessment_location && !array_key_exists($crewAssessment->assessment_location, \App\Models\CrewAssessment::LOCATIONS))
                            <option value="{{ $crewAssessment->assessment_location }}" selected>{{ $crewAssessment->assessment_location }}</option>
                        @endif
                    </select>
                    @error('assessment_location')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3"></div>

                {{-- Assessor sub-section --}}
                <div class="col-12">
                    <p class="sec-sublabel mb-2">
                        <i class="bi bi-person-check me-1"></i>Assessor
                    </p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">MAR <span class="text-muted fw-normal">(Kol. N)</span></label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0"
                                      style="background:#eff6ff;font-size:.7rem;font-weight:700;color:#1d4ed8;min-width:44px;justify-content:center;">MAR</span>
                                <input type="text" class="form-control border-start-0"
                                       name="assessor_mar" value="{{ old('assessor_mar', $crewAssessment->assessor_mar) }}"
                                       placeholder="Nama assessor MAR">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">HSE <span class="text-muted fw-normal">(Kol. O)</span></label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0"
                                      style="background:#f0fdf4;font-size:.7rem;font-weight:700;color:#166534;min-width:44px;justify-content:center;">HSE</span>
                                <input type="text" class="form-control border-start-0"
                                       name="assessor_hse" value="{{ old('assessor_hse', $crewAssessment->assessor_hse) }}"
                                       placeholder="Nama assessor HSE">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">FMC <span class="text-muted fw-normal">(Kol. P)</span></label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0"
                                      style="background:#fefce8;font-size:.7rem;font-weight:700;color:#854d0e;min-width:44px;justify-content:center;">FMC</span>
                                <input type="text" class="form-control border-start-0"
                                       name="assessor_fmc" value="{{ old('assessor_fmc', $crewAssessment->assessor_fmc) }}"
                                       placeholder="Nama assessor FMC">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ SEKSI 6: Hasil & Keterangan ════════════════════════════════ --}}
        <div class="ca-card">
            <div class="ca-card-header">
                <div class="ca-step">6</div>
                <div>
                    <h6>Hasil &amp; Keterangan</h6>
                    <small>HASIL (Kol. Q) &amp; KETERANGAN (Kol. R)</small>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">HASIL <span class="text-muted fw-normal">(Kol. Q)</span></label>
                    <select class="form-select @error('result') is-invalid @enderror"
                            name="result" id="result_field">
                        <option value="">— Pilih —</option>
                        @foreach(\App\Models\CrewAssessment::RESULTS as $k => $v)
                            <option value="{{ $k }}" {{ old('result', $crewAssessment->result) == $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                    @error('result')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">KETERANGAN <span class="text-muted fw-normal">(Kol. R)</span></label>
                    <input type="text" class="form-control" name="notes"
                           value="{{ old('notes', $crewAssessment->notes) }}"
                           placeholder="cth: hasil nunggu video aktivitas manuver"
                           maxlength="2000">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status Record</label>
                    <select class="form-select" name="status" required>
                        <option value="active"  {{ old('status', $crewAssessment->status) === 'active'  ? 'selected' : '' }}>Active</option>
                        <option value="expired" {{ old('status', $crewAssessment->status) === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="revoked" {{ old('status', $crewAssessment->status) === 'revoked' ? 'selected' : '' }}>Revoked</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Berlaku Dari</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="bi bi-calendar-check" style="font-size:.85rem;color:#6b7280;"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 flatpickr"
                               name="valid_from" value="{{ old('valid_from', $crewAssessment->valid_from?->format('Y-m-d')) }}"
                               placeholder="YYYY-MM-DD">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Berlaku Hingga</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="bi bi-calendar-x" style="font-size:.85rem;color:#6b7280;"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 flatpickr"
                               name="valid_until" value="{{ old('valid_until', $crewAssessment->valid_until?->format('Y-m-d')) }}"
                               placeholder="YYYY-MM-DD">
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ SEKSI 7: Lampiran ══════════════════════════════════════════ --}}
        <div class="ca-card">
            <div class="ca-card-header">
                <div class="ca-step">7</div>
                <div>
                    <h6>Lampiran File</h6>
                    <small>PDF, Word, Excel, Gambar — maks. <strong>2 MB</strong>/file, maks. 10 file</small>
                </div>
            </div>

            {{-- Existing attachments --}}
            @if($crewAssessment->attachments->count())
                <p class="sec-sublabel mb-3"><i class="bi bi-paperclip me-1"></i>Lampiran Saat Ini</p>
                @foreach($crewAssessment->attachments as $att)
                    <div class="att-ex">
                        <i class="{{ $att->icon_class }} fs-4 flex-shrink-0"></i>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-semibold text-truncate" style="font-size:.85rem;">{{ $att->original_name }}</div>
                            <div class="text-muted" style="font-size:.72rem">
                                {{ $att->size_human }}
                                @if($att->label) · {{ $att->label }} @endif
                            </div>
                        </div>
                        <div class="form-check form-switch ms-2 flex-shrink-0">
                            <input class="form-check-input" type="checkbox"
                                   name="delete_attachment_ids[]" value="{{ $att->id }}"
                                   id="del_{{ $att->id }}">
                            <label class="form-check-label text-danger" style="font-size:.78rem;" for="del_{{ $att->id }}">Hapus</label>
                        </div>
                    </div>
                @endforeach
                <hr class="my-4" style="border-top:1px solid #e2e8f0;">
            @endif

            <p class="sec-sublabel mb-3"><i class="bi bi-cloud-arrow-up me-1"></i>Tambah Lampiran Baru</p>
            <div class="drop-zone" id="dropZone">
                <i class="bi bi-cloud-arrow-up"></i>
                <p class="mb-1 mt-2 fw-semibold" style="font-size:.9rem;">
                    Drag &amp; drop atau klik untuk pilih file
                </p>
                <p class="text-muted mb-0" style="font-size:.78rem;">
                    PDF, DOC, DOCX, XLS, XLSX, JPG, PNG &mdash; maks. 2 MB/file
                </p>
                <input type="file" id="fileInput" multiple
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" style="display:none">
            </div>
            <div id="newAttachList" class="mt-3"></div>
        </div>


        <div class="d-flex gap-2 justify-content-end mb-5">
            <a href="{{ route('crew-assessment.show', $crewAssessment->id) }}"
               class="btn btn-outline-secondary px-4">Batal</a>
            <button type="submit" class="btn btn-primary px-5">
                <i class="bi bi-save me-1"></i> Simpan Perubahan
            </button>
        </div>
    </form>

</div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(function ($) {
    'use strict';

    const MAX_KB      = 2 * 1024 * 1024; // 2 MB
    const ALLOWED_EXT = ['pdf','doc','docx','xls','xlsx','jpg','jpeg','png'];

    flatpickr('.flatpickr', { dateFormat: 'Y-m-d', allowInput: true });

    $('#company_id,#vessel_id,#position_proposed,#position_type,#mev_type,#assessment_location').select2({
        tags: true,
        placeholder: '-- Pilih atau Ketik Baru --',
        allowClear: true,
        width: '100%',
    });

    // ── Company → Vessels (jika user ganti perusahaan assessment) ─────────────
    $('#company_id').on('change', function () {
        const id = $(this).val();
        const $v = $('#vessel_id');
        $v.html('<option value="">-- Memuat... --</option>');
        if (!id) { $v.html('<option value="">-- Pilih Perusahaan dulu --</option>'); return; }

        // Jika bukan UUID (free text baru), langsung kosongkan kapal dan ijinkan input baru
        const isUuid = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(id);
        if (!isUuid) {
            $v.html('<option value="">-- Ketik nama kapal baru --</option>');
            return;
        }

        $.ajax({
            url:  '{{ route("crew-assessment.ajax.vessels") }}',
            data: { company_id: id },
            success: data => {
                let h = '<option value="">-- Pilih Kapal --</option>';
                data.forEach(v => h += `<option value="${v.id}">${v.name}</option>`);
                $v.html(data.length ? h : '<option value="">-- Tidak ada kapal --</option>');
            },
            error: () => $v.html('<option value="">-- Gagal memuat --</option>'),
        });
    });

    // ── Crew Search via Select2 AJAX — lintas perusahaan & vessel ─────────────
    $('#crew_member_id').select2({
        tags: true,
        placeholder: '-- Ketik nama atau NIK kru --',
        allowClear: true,
        minimumInputLength: 1,
        width: '100%',
        language: {
            inputTooShort: () => 'Ketik minimal 1 karakter untuk mencari...',
            searching:     () => 'Mencari...',
            noResults:     () => 'Kru tidak ditemukan.',
        },
        ajax: {
            url:      '{{ route("crew-assessment.ajax.search-crew") }}',
            dataType: 'json',
            delay:    300,
            data:     params => ({ q: params.term }),
            processResults: data => ({ results: data.results }),
            cache:    true,
        },
        templateResult: function (item) {
            if (item.loading) return item.text;
            return $(`<div>
                <strong>${item.name || item.text}</strong>
                ${item.nik ? '<small class="text-muted"> — NIK: ' + item.nik + '</small>' : ''}
                <br>
                <small class="text-info">${item.position || ''}</small>
                ${item.company ? '<small class="text-muted float-end">' + item.company + '</small>' : ''}
            </div>`);
        },
        templateSelection: item => item.name || item.text,
    });

    $('#crew_member_id').on('select2:select', function (e) {
        const d = e.params.data;
        $('#cbName').text(d.name || d.text || '—');
        $('#cbPos').text(d.position || '—');
        $('#cbNik').text(d.nik || '—');
        $('#cbCompany').text(d.company || '—');
        $('#crewInfoBoxWrap').show();
    });

    $('#crew_member_id').on('select2:clear', function () {
        $('#crewInfoBoxWrap').hide();
    });

    // ── New file upload ───────────────────────────────────────────────────────
    const dz  = document.getElementById('dropZone');
    const fi  = document.getElementById('fileInput');
    const nl  = document.getElementById('newAttachList');
    let files = [];

    dz.addEventListener('click',     () => fi.click());
    dz.addEventListener('dragover',  e  => { e.preventDefault(); dz.classList.add('dragover'); });
    dz.addEventListener('dragleave', ()  => dz.classList.remove('dragover'));
    dz.addEventListener('drop',      e  => {
        e.preventDefault();
        dz.classList.remove('dragover');
        addFiles([...e.dataTransfer.files]);
    });
    fi.addEventListener('change', () => { addFiles([...fi.files]); fi.value = ''; });

    function addFiles(newFiles) {
        newFiles.forEach(f => {
            const ext = f.name.split('.').pop().toLowerCase();
            if (!ALLOWED_EXT.includes(ext)) {
                Swal.fire({ icon: 'warning', title: 'Format tidak didukung', text: f.name });
                return;
            }
            if (f.size > MAX_KB) {
                Swal.fire({ icon: 'warning', title: 'File terlalu besar', text: `${f.name} melebihi 2 MB.` });
                return;
            }
            files.push(f);
        });
        renderFiles();
    }

    function renderFiles() {
        nl.innerHTML = '';
        document.querySelectorAll('.js-nf').forEach(e => e.remove());

        files.forEach((f, i) => {
            const ext = f.name.split('.').pop().toLowerCase();
            const ic  = ext === 'pdf' ? 'bi-file-earmark-pdf text-danger'
                      : ['doc','docx'].includes(ext) ? 'bi-file-earmark-word text-primary'
                      : ['xls','xlsx'].includes(ext) ? 'bi-file-earmark-excel text-success'
                      : 'bi-file-earmark-image text-info';
            const sz  = f.size >= 1048576
                      ? (f.size / 1048576).toFixed(2) + ' MB'
                      : Math.round(f.size / 1024) + ' KB';

            const d = document.createElement('div');
            d.className = 'att-new';
            d.innerHTML = `
                <i class="bi ${ic} fs-4 flex-shrink-0"></i>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-semibold text-truncate small">${f.name}</div>
                    <div class="text-muted" style="font-size:.72rem">${sz}</div>
                </div>
                <input type="text" name="new_attachment_labels[]"
                       class="form-control form-control-sm" style="max-width:180px"
                       placeholder="Label (opsional)">
                <button type="button" class="btn btn-sm btn-outline-danger" data-idx="${i}">
                    <i class="bi bi-x"></i>
                </button>`;
            nl.appendChild(d);

            const dt  = new DataTransfer();
            dt.items.add(f);
            const inp = document.createElement('input');
            inp.type  = 'file';
            inp.name  = 'new_attachments[]';
            inp.classList.add('js-nf');
            inp.style.display = 'none';
            inp.files = dt.files;
            document.getElementById('editForm').appendChild(inp);
        });
    }

    nl.addEventListener('click', e => {
        const b = e.target.closest('button[data-idx]');
        if (b) { files.splice(parseInt(b.dataset.idx), 1); renderFiles(); }
    });

}(jQuery));
</script>
@endpush
