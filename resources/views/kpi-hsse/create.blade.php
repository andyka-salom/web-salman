{{-- resources/views/kpi-hsse/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Buat Laporan KPI HSSE')

@push('styles')
<style>
/* ── Page layout ─────────────────────────────────────────────────── */
.create-wrap { max-width: 860px; margin: 0 auto; }

/* ── Step indicator ──────────────────────────────────────────────── */
.step-bar { display:flex; align-items:center; gap:0; margin-bottom:2rem; }
.step-item { display:flex; align-items:center; gap:8px; flex:1; }
.step-circle {
    width:32px; height:32px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-weight:700; font-size:.8rem; flex-shrink:0;
    border:2px solid;
}
.step-circle.active   { background:#2563eb; border-color:#2563eb; color:#fff; }
.step-circle.inactive { background:#f1f5f9; border-color:#cbd5e1; color:#94a3b8; }
.step-label { font-size:.78rem; font-weight:600; }
.step-label.active   { color:#2563eb; }
.step-label.inactive { color:#94a3b8; }
.step-line { flex:1; height:2px; background:#e2e8f0; margin:0 8px; }
.step-line.done { background:#2563eb; }

/* ── Section card ────────────────────────────────────────────────── */
.section-card {
    background:#fff; border-radius:12px;
    border:1px solid #e5e7eb;
    box-shadow:0 1px 4px rgba(0,0,0,.06);
    margin-bottom:1.25rem; overflow:hidden;
}
.section-card-header {
    background:#f8fafc; border-bottom:1px solid #e5e7eb;
    padding:.75rem 1.25rem;
    display:flex; align-items:center; gap:10px;
}
.section-card-header .hdr-icon {
    width:32px; height:32px; border-radius:8px;
    display:flex; align-items:center; justify-content:center;
    font-size:1rem; flex-shrink:0;
}
.section-card-header .hdr-title {
    font-weight:700; font-size:.9rem; color:#1e293b; margin:0;
}
.section-card-header .hdr-sub {
    font-size:.75rem; color:#64748b; margin:0;
}
.section-card-body { padding:1.25rem; }

/* ── Period selector ─────────────────────────────────────────────── */
.period-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
@media(max-width:576px){ .period-grid { grid-template-columns:1fr; } }

.period-select-wrap { position:relative; }
.period-select-wrap select {
    width:100%; appearance:none; -webkit-appearance:none;
    border:1.5px solid #e2e8f0; border-radius:8px;
    padding:.65rem 2.5rem .65rem .85rem;
    font-size:.88rem; font-weight:600; color:#1e293b;
    background:#fff; cursor:pointer; outline:none;
    transition:border-color .15s, box-shadow .15s;
}
.period-select-wrap select:focus {
    border-color:#2563eb;
    box-shadow:0 0 0 3px rgba(37,99,235,.12);
}
.period-select-wrap::after {
    content:"▾"; position:absolute; right:.75rem;
    top:50%; transform:translateY(-50%);
    color:#64748b; pointer-events:none; font-size:.85rem;
}
.period-label {
    display:block; font-size:.75rem; font-weight:700;
    color:#475569; margin-bottom:.4rem; text-transform:uppercase;
    letter-spacing:.4px;
}

/* ── Vessel table ────────────────────────────────────────────────── */
.vessel-table {
    width:100%; border-collapse:collapse;
    font-size:.84rem;
}
.vessel-table th {
    background:#f1f5f9; color:#475569;
    font-size:.72rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.4px;
    padding:.5rem .75rem; border:1px solid #e2e8f0;
    white-space:nowrap;
}
.vessel-table td {
    border:1px solid #e2e8f0; padding:0; background:#fff;
    vertical-align:middle;
}
.vessel-table input {
    width:100%; border:none; outline:none;
    padding:.55rem .75rem; font-size:.84rem;
    background:transparent; color:#1e293b;
    transition:background .15s;
}
.vessel-table input:focus { background:#fefce8; }
.vessel-table input::placeholder { color:#94a3b8; }
.vessel-table tr:hover td { background:#f8faff; }
.vessel-table tr:hover td:has(input:focus) { background:#fefce8; }

/* No. cell */
.td-no-vessel {
    width:36px; text-align:center; background:#f8fafc !important;
    font-size:.75rem; font-weight:700; color:#94a3b8;
    padding:.55rem .4rem;
}
/* Del cell */
.td-del-vessel {
    width:36px; text-align:center; background:#f8fafc !important;
    padding:.4rem;
}
.btn-del-v {
    width:22px; height:22px; border-radius:50%;
    background:#fee2e2; border:none; color:#dc2626;
    font-size:12px; cursor:pointer;
    display:inline-flex; align-items:center; justify-content:center;
    transition:background .15s;
}
.btn-del-v:hover { background:#fca5a5; }

/* Add vessel button */
.btn-add-v {
    display:inline-flex; align-items:center; gap:6px;
    padding:.45rem 1rem; font-size:.8rem; font-weight:600;
    background:#eff6ff; border:1.5px dashed #3b82f6;
    border-radius:8px; color:#1d4ed8; cursor:pointer;
    transition:background .15s, border-color .15s;
    margin-top:.75rem;
}
.btn-add-v:hover { background:#dbeafe; border-color:#1d4ed8; }

/* ── Info box ────────────────────────────────────────────────────── */
.info-flow {
    display:flex; gap:.5rem; align-items:flex-start;
    background:#f0f9ff; border:1px solid #bae6fd;
    border-radius:10px; padding:.85rem 1rem;
    margin-bottom:1.25rem; font-size:.82rem; color:#0369a1;
}
.info-flow .info-icon { font-size:1.1rem; flex-shrink:0; margin-top:1px; }
.info-flow ol { margin:0; padding-left:1.2rem; line-height:1.9; }
.info-flow ol li strong { color:#0c4a6e; }

/* ── Submit area ─────────────────────────────────────────────────── */
.submit-area {
    background:#fff; border-radius:12px;
    border:1px solid #e5e7eb;
    box-shadow:0 1px 4px rgba(0,0,0,.06);
    padding:1.25rem; display:flex;
    align-items:center; justify-content:space-between;
    gap:1rem; flex-wrap:wrap;
}
.submit-hint { font-size:.79rem; color:#64748b; max-width:460px; line-height:1.5; }
.submit-hint strong { color:#334155; }

/* ── Validation feedback ─────────────────────────────────────────── */
.field-required { color:#ef4444; margin-left:2px; }
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
<div class="middle-content container-xxl p-0">
<div class="row layout-top-spacing">
<div class="col-12">
<div class="create-wrap">

{{-- ── Header ──────────────────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
    <div>
        <h2 class="fw-bold mb-1">Buat Laporan KPI HSSE</h2>
        <p class="text-muted mb-0" style="font-size:.85rem;">
            <span style="background:#dbeafe;color:#1e40af;border-radius:4px;padding:2px 8px;font-weight:600;font-size:.75rem;">
                {{ $user->company->name ?? '-' }}
            </span>
        </p>
    </div>
    <a href="{{ route('kpi-hsse.index') }}"
       class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        ← Kembali
    </a>
</div>

{{-- ── Step bar ─────────────────────────────────────────────────── --}}
<div class="step-bar mb-4">
    <div class="step-item">
        <div class="step-circle active">1</div>
        <div>
            <div class="step-label active">Periode &amp; Kapal</div>
        </div>
    </div>
    <div class="step-line done"></div>
    <div class="step-item">
        <div class="step-circle inactive">2</div>
        <div>
            <div class="step-label inactive">Isi Data KPI</div>
        </div>
    </div>
    <div class="step-line"></div>
    <div class="step-item">
        <div class="step-circle inactive">3</div>
        <div>
            <div class="step-label inactive">Upload Lampiran</div>
        </div>
    </div>
    <div class="step-line"></div>
    <div class="step-item">
        <div class="step-circle inactive">4</div>
        <div>
            <div class="step-label inactive">Submit ke HSSE</div>
        </div>
    </div>
</div>

{{-- ── Alerts ───────────────────────────────────────────────────── --}}
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-3">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ── Info flow ────────────────────────────────────────────────── --}}
<div class="info-flow">
    <div class="info-icon">📋</div>
    <ol>
        <li><strong>Pilih Periode &amp; isi data Kapal/Unit</strong> di halaman ini</li>
        <li>Klik <strong>Simpan Draft</strong> — sistem akan membuat laporan</li>
        <li>Di halaman <strong>Edit</strong>: isi ∑ Jumlah, Keterangan, upload Lampiran</li>
        <li><strong>Submit</strong> ke HSSE setelah semua lengkap</li>
    </ol>
</div>

<form method="POST" action="{{ route('kpi-hsse.store') }}" id="form-create">
@csrf

{{-- ── Section 1: Periode ───────────────────────────────────────── --}}
<div class="section-card">
    <div class="section-card-header">
        <div class="hdr-icon" style="background:#dbeafe;">📅</div>
        <div>
            <p class="hdr-title">Periode Laporan <span class="field-required">*</span></p>
            <p class="hdr-sub">Pilih bulan dan tahun laporan KPI</p>
        </div>
    </div>
    <div class="section-card-body">
        <div class="period-grid">
            <div>
                <label class="period-label">Bulan &amp; Tahun <span class="field-required">*</span></label>
                <div class="period-select-wrap">
                    <select name="period" required id="sel-period">
                        <option value="">— Pilih periode —</option>
                        @forelse($availableMonths as $mo)
                            <option value="{{ $mo['value'] }}"
                                {{ old('period') == $mo['value'] ? 'selected' : '' }}>
                                {{ $mo['label'] }}
                            </option>
                        @empty
                            <option value="" disabled>Semua periode sudah ada laporan</option>
                        @endforelse
                    </select>
                </div>
                @if($availableMonths->isEmpty())
                <p class="text-warning mt-2 mb-0" style="font-size:.78rem;">
                    ⚠ Semua periode (24 bulan terakhir) sudah memiliki laporan.
                </p>
                @endif
            </div>
            <div style="display:flex;align-items:flex-end;">
                <div class="p-3 rounded-3 w-100"
                     style="background:#f8fafc;border:1px dashed #cbd5e1;font-size:.8rem;color:#64748b;line-height:1.6;">
                    <div style="font-weight:700;color:#334155;margin-bottom:4px;">ℹ Catatan</div>
                    Periode yang sudah memiliki laporan tidak ditampilkan.
                    Laporan mencakup <strong>24 bulan ke belakang</strong>.
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Section 2: Kapal / Unit ─────────────────────────────────── --}}
<div class="section-card">
    <div class="section-card-header">
        <div class="hdr-icon" style="background:#dcfce7;">🚢</div>
        <div>
            <p class="hdr-title">Kapal / Unit &amp; Kontrak <span class="field-required">*</span></p>
            <p class="hdr-sub">
                Satu kontrak bisa mencakup beberapa kapal — isi nama lengkap di kolom Nama Kapal / Unit
            </p>
        </div>
    </div>
    <div class="section-card-body">

        <div style="overflow-x:auto;">
        <table class="vessel-table" id="vessel-table">
            <thead>
                <tr>
                    <th style="width:36px;">No</th>
                    <th style="min-width:180px;">No. Kontrak</th>
                    <th style="width:160px;">Akhir Kontrak</th>
                    <th style="width:90px;">JML <span style="font-weight:400;font-size:.65rem;display:block;">(optional)</span></th>
                    <th>Nama Kapal / Unit <span class="field-required">*</span>
                        <span style="font-weight:400;font-size:.69rem;color:#94a3b8;">
                            (boleh isi lebih dari 1 nama)
                        </span>
                    </th>
                    <th style="width:36px;"></th>
                </tr>
            </thead>
            <tbody id="vessel-tbody">
                <tr id="vrow-0">
                    <td class="td-no-vessel">1</td>
                    <td>
                        <input type="text"
                               name="vessels[0][contract_number]"
                               value="{{ old('vessels.0.contract_number') }}"
                               placeholder="Contoh: PHM/2025/001">
                    </td>
                    <td>
                        <input type="date"
                               name="vessels[0][contract_end_date]"
                               value="{{ old('vessels.0.contract_end_date') }}">
                    </td>
                    <td>
                        <input type="text"
                               name="vessels[0][vessel_count]"
                               value="{{ old('vessels.0.vessel_count') }}"
                               placeholder="1 unit">
                    </td>
                    <td>
                        <input type="text"
                               name="vessels[0][vessel_name]"
                               value="{{ old('vessels.0.vessel_name') }}"
                               placeholder="Contoh: KM Harapan Jaya / Marine Crew Boat 01"
                               required>
                    </td>
                    <td class="td-del-vessel">
                        <button type="button" class="btn-del-v d-none"
                                onclick="delVessel(0)" title="Hapus baris">×</button>
                    </td>
                </tr>
            </tbody>
        </table>
        </div>

        <button type="button" class="btn-add-v" onclick="addVessel()">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah Baris Kontrak / Kapal
        </button>

        <p class="text-muted mt-2 mb-0" style="font-size:.75rem;">
            💡 Jika 1 kontrak mencakup beberapa kapal, tuliskan semua nama di kolom
            <em>Nama Kapal / Unit</em> (misal: "KM A, KM B, KM C")
        </p>
    </div>
</div>

{{-- ── Submit area ──────────────────────────────────────────────── --}}
<div class="submit-area">
    <div class="submit-hint">
        <strong>Langkah selanjutnya:</strong> Setelah klik Simpan, Anda akan diarahkan ke
        halaman <strong>Edit KPI</strong> untuk mengisi ∑ Jumlah, Keterangan, dan upload Lampiran.
    </div>
    <button type="submit" class="btn btn-primary fw-bold px-4 rounded-pill"
            style="min-width:200px;" id="btn-submit">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15"
             viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             class="me-1">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
            <polyline points="17 21 17 13 7 13 7 21"/>
        </svg>
        Simpan Draft &amp; Lanjut Isi KPI
    </button>
</div>

</form>

</div>{{-- /create-wrap --}}
</div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
let _vi = 1;

function addVessel() {
    const tbody = document.getElementById('vessel-tbody');
    const idx   = _vi++;
    const n     = tbody.querySelectorAll('tr').length + 1;
    const tr    = document.createElement('tr');
    tr.id       = 'vrow-' + idx;
    tr.innerHTML = `
        <td class="td-no-vessel">${n}</td>
        <td>
            <input type="text" name="vessels[${idx}][contract_number]"
                   placeholder="No. kontrak">
        </td>
        <td>
            <input type="date" name="vessels[${idx}][contract_end_date]">
        </td>
        <td>
            <input type="text" name="vessels[${idx}][vessel_count]"
                   placeholder="1 unit">
        </td>
        <td>
            <input type="text" name="vessels[${idx}][vessel_name]"
                   placeholder="Nama kapal / unit" required>
        </td>
        <td class="td-del-vessel">
            <button type="button" class="btn-del-v"
                    onclick="delVessel(${idx})" title="Hapus baris">×</button>
        </td>`;
    tbody.appendChild(tr);
    refreshDelButtons();
    // Focus new row
    tr.querySelector('input[type="text"]')?.focus();
}

function delVessel(idx) {
    const rows = document.querySelectorAll('#vessel-tbody tr');
    if (rows.length <= 1) { alert('Minimal 1 kapal / unit.'); return; }
    document.getElementById('vrow-' + idx)?.remove();
    refreshDelButtons();
    refreshRowNumbers();
}

function refreshDelButtons() {
    const rows = document.querySelectorAll('#vessel-tbody tr');
    rows.forEach(r => {
        const btn = r.querySelector('.btn-del-v');
        if (btn) btn.classList.toggle('d-none', rows.length <= 1);
    });
}

function refreshRowNumbers() {
    document.querySelectorAll('#vessel-tbody tr').forEach((r, i) => {
        const no = r.querySelector('.td-no-vessel');
        if (no) no.textContent = i + 1;
    });
}

// Prevent double submit
document.getElementById('form-create')?.addEventListener('submit', function() {
    const btn = document.getElementById('btn-submit');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-1" role="status"></span>
            Menyimpan...`;
    }
});
</script>
@endpush
