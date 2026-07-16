@extends('layouts.app')
@section('title', 'Edit Evaluasi On Board')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="{{ asset('plugins/src/sweetalerts2/sweetalerts2.css') }}">
<style>
    .form-section-header {
        display: flex; align-items: center; gap: 0.75rem;
        padding-bottom: 0.75rem; border-bottom: 2px solid #e5e7eb; margin-bottom: 1.5rem;
    }
    .step-circle {
        display: flex; align-items: center; justify-content: center;
        width: 32px; height: 32px; flex-shrink: 0;
        border-radius: 50%; background-color: #dbeafe; color: #1d4ed8;
        font-weight: 700; font-size: 0.9rem;
    }
    /* ── CRITERIA CARD ─────────────────────────────────────── */
    .criteria-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
        padding: 1rem 1.25rem; margin-bottom: 0.75rem; transition: box-shadow 0.15s ease;
    }
    .criteria-card:hover { box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
    .criteria-card.scored-kurang { border-left: 4px solid #ef4444; }
    .criteria-card.scored-cukup  { border-left: 4px solid #f59e0b; }
    .criteria-card.scored-baik   { border-left: 4px solid #22c55e; }
    .criteria-no {
        display: inline-flex; align-items: center; justify-content: center;
        width: 26px; height: 26px; border-radius: 50%;
        background: #1e3a5f; color: #fff; font-size: 0.75rem; font-weight: 700; flex-shrink: 0;
    }
    /* ── SCORE BUTTONS ─────────────────────────────────────── */
    .score-btn-group { display: flex; gap: 8px; flex-wrap: wrap; }
    .score-btn-group input[type="radio"] { display: none; }
    .score-btn-group label {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 80px; height: 38px; border-radius: 8px;
        border: 2px solid #cbd5e1; cursor: pointer;
        font-size: 0.82rem; font-weight: 700; color: #64748b;
        transition: all 0.15s ease; user-select: none; padding: 0 12px; gap: 5px;
    }
    .score-btn-group input[type="radio"]:checked + label { border-color: transparent; color: #fff; }
    .score-btn-group input[value="1"]:checked + label { background: #ef4444; }
    .score-btn-group input[value="2"]:checked + label { background: #f59e0b; }
    .score-btn-group input[value="3"]:checked + label { background: #22c55e; }
    .score-btn-group label:hover { border-color: #94a3b8; background: #f8fafc; }
    /* ── SIDEBAR SCORE ─────────────────────────────────────── */
    #total-score-display { font-size: 2.5rem; font-weight: 800; color: #1e3a5f; line-height: 1; }
    .score-category-label {
        display: inline-block; padding: 4px 16px;
        border-radius: 20px; font-size: 0.85rem; font-weight: 700;
    }
    .cat-kurang { background: #fee2e2; color: #dc2626; }
    .cat-cukup  { background: #fef3c7; color: #d97706; }
    .cat-baik   { background: #dcfce7; color: #16a34a; }
    .cat-none   { background: #f1f5f9; color: #94a3b8; }
    /* ── DIGITAL SIGNATURE ─────────────────────────────────── */
    .dig-sig-preview {
        border: 1.5px solid #c7d2fe; border-radius: 10px;
        padding: 14px 18px;
        background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 100%);
        position: relative; display: inline-block; min-width: 240px; max-width: 100%;
    }
    .dig-sig-preview::before {
        content: '';
        position: absolute; top: 0; left: 0; right: 0; bottom: 0; border-radius: 9px;
        background: repeating-linear-gradient(
            45deg, transparent, transparent 4px,
            rgba(99,102,241,0.04) 4px, rgba(99,102,241,0.04) 5px
        );
        pointer-events: none;
    }
    .dig-sig-name {
        font-family: 'Georgia', serif; font-size: 1.1rem; font-weight: 700;
        color: #1e3a5f; letter-spacing: -0.01em; margin-bottom: 3px; position: relative;
    }
    .dig-sig-pos {
        font-size: 0.76rem; color: #6366f1; font-weight: 600;
        margin-bottom: 5px; position: relative;
    }
    .dig-sig-date {
        font-size: 0.73rem; color: #64748b; font-weight: 500; position: relative;
    }
    .dig-sig-stamp {
        display: inline-flex; align-items: center; gap: 4px;
        margin-top: 9px; padding: 3px 10px;
        background: #e0e7ff; color: #4338ca;
        border-radius: 20px; font-size: 0.69rem; font-weight: 700;
        border: 1px solid #c7d2fe; position: relative;
    }
    @media (min-width: 992px) { .sticky-sidebar { position: sticky; top: 1.5rem; } }
</style>
@endpush

@section('content')
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">
            <div class="col-12">

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                    <div>
                        <h3 class="fw-bold mb-0">Edit Evaluasi On Board</h3>
                        <p class="text-muted mb-0 small">
                            Kru: <strong>{{ $evaluation->crew_name }}</strong> &mdash;
                            {{ $evaluation->vessel->name ?? '-' }} &mdash;
                            {{ \Carbon\Carbon::parse($evaluation->evaluated_date)->format('d M Y') }}
                        </p>
                    </div>
                    <a href="{{ route('hsse-evaluation.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                @if($evaluation->status === 'submitted')
                <div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    Evaluasi ini sudah <strong>disubmit</strong>. Hanya super-admin dan HSSE yang dapat mengedit.
                </div>
                @endif

                <form id="evalForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" id="form-action" value="submit">

                    <div class="row g-4">

                        {{-- ══ KIRI ══════════════════════════════ --}}
                        <div class="col-lg-8">
                            <div class="d-flex flex-column gap-4">

                                {{-- SECTION 1: Identitas --}}
                                <div class="card shadow-sm border-0 br-8">
                                    <div class="card-body p-4">
                                        <div class="form-section-header">
                                            <div class="step-circle">1</div>
                                            <h5 class="mb-0 fw-bold">Identitas Kru &amp; Kapal</h5>
                                        </div>
                                        <div class="row g-3">

                                            {{-- Perusahaan (read-only) --}}
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Perusahaan</label>
                                                <input type="text" class="form-control bg-light"
                                                    value="{{ $evaluation->company->name ?? 'N/A' }}" readonly>
                                                <input type="hidden" name="company_id" value="{{ $evaluation->company_id }}">
                                            </div>

                                            {{-- Kapal (read-only) --}}
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Kapal</label>
                                                <input type="text" class="form-control bg-light"
                                                    value="{{ $evaluation->vessel->name ?? 'N/A' }}" readonly>
                                                <input type="hidden" name="vessel_id" value="{{ $evaluation->vessel_id }}">
                                            </div>

                                            {{-- Nama Kru --}}
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">
                                                    Nama Kru <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control" name="crew_name"
                                                    required value="{{ $evaluation->crew_name }}">
                                            </div>

                                            {{-- Jabatan --}}
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Jabatan</label>
                                                <input type="text" class="form-control" name="crew_position"
                                                    value="{{ $evaluation->crew_position }}" placeholder="Jabatan kru">
                                            </div>

                                            {{-- Tanggal --}}
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">
                                                    Tanggal Evaluasi <span class="text-danger">*</span>
                                                </label>
                                                <input type="date" class="form-control" name="evaluated_date"
                                                    id="edit_date"
                                                    required
                                                    value="{{ \Carbon\Carbon::parse($evaluation->evaluated_date)->format('Y-m-d') }}">
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                {{-- SECTION 2: Kriteria Penilaian --}}
                                <div class="card shadow-sm border-0 br-8">
                                    <div class="card-body p-4">
                                        <div class="form-section-header">
                                            <div class="step-circle">2</div>
                                            <div class="flex-grow-1">
                                                <h5 class="mb-0 fw-bold">Kriteria Penilaian</h5>
                                                <small class="text-muted">
                                                    <span class="badge bg-danger">1 = Kurang</span>
                                                    <span class="badge bg-warning text-dark ms-1">2 = Cukup</span>
                                                    <span class="badge bg-success ms-1">3 = Baik</span>
                                                </small>
                                            </div>
                                        </div>

                                        {{-- Progress --}}
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <small class="text-muted fw-bold">Progress Pengisian</small>
                                                <small class="text-muted" id="criteria-progress-text">
                                                    0 / {{ $criteria->count() }} kriteria
                                                </small>
                                            </div>
                                            <div class="bg-light rounded" style="height:6px;">
                                                <div id="criteria-progress-bar" style="width:0%; height:6px; border-radius:99px;
                                                    background:linear-gradient(90deg,#1d4ed8,#22c55e); transition:width 0.3s ease;">
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Criteria Cards --}}
                                        @foreach($criteria as $c)
                                        @php
                                            $existingScore = $evaluation->scores->firstWhere('criteria_id', $c->id);
                                            $scoreVal      = $existingScore?->score;
                                            $ketVal        = $existingScore?->keterangan ?? '';
                                            $borderClass   = match((string)$scoreVal) {
                                                '1' => 'scored-kurang',
                                                '2' => 'scored-cukup',
                                                '3' => 'scored-baik',
                                                default => ''
                                            };
                                        @endphp
                                        <div class="criteria-card {{ $borderClass }}" id="card-{{ $c->id }}">
                                            <div class="d-flex align-items-start gap-3 mb-3">
                                                <div class="criteria-no">{{ $c->order_no }}</div>
                                                <div class="flex-grow-1" style="font-size:.9rem;color:#1e293b;line-height:1.5;">
                                                    {{ $c->aspect }}
                                                </div>
                                            </div>
                                            <div class="score-btn-group mb-2">
                                                @foreach([1 => 'Kurang', 2 => 'Cukup', 3 => 'Baik'] as $val => $label)
                                                <input type="radio"
                                                    id="s_{{ $c->id }}_{{ $val }}"
                                                    name="scores[{{ $c->id }}]"
                                                    value="{{ $val }}"
                                                    class="score-input"
                                                    data-criteria="{{ $c->id }}"
                                                    {{ (int)$scoreVal === $val ? 'checked' : '' }}>
                                                <label for="s_{{ $c->id }}_{{ $val }}">
                                                    <span style="font-size:1rem;font-weight:800;">{{ $val }}</span> {{ $label }}
                                                </label>
                                                @endforeach
                                            </div>
                                            <input type="text" class="form-control form-control-sm"
                                                name="keterangan[{{ $c->id }}]"
                                                placeholder="Keterangan (opsional)..."
                                                value="{{ $ketVal }}">
                                        </div>
                                        @endforeach

                                        <div class="p-3 rounded mt-2" style="background:#f8fafc;border:1px solid #e2e8f0;">
                                            <div class="fw-bold small text-muted mb-1">Keterangan Skor Total:</div>
                                            <div class="d-flex gap-3 flex-wrap">
                                                <span class="small"><span class="badge bg-danger">5–8</span> Kurang</span>
                                                <span class="small"><span class="badge bg-warning text-dark">9–11</span> Cukup</span>
                                                <span class="small"><span class="badge bg-success">12–15</span> Baik</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- SECTION 3: Catatan & Assessor --}}
                                <div class="card shadow-sm border-0 br-8">
                                    <div class="card-body p-4">
                                        <div class="form-section-header">
                                            <div class="step-circle">3</div>
                                            <h5 class="mb-0 fw-bold">Catatan &amp; Assessor</h5>
                                        </div>

                                        {{-- Catatan --}}
                                        <div class="mb-4">
                                            <label class="form-label fw-bold">Catatan / Rekomendasi</label>
                                            <textarea class="form-control" name="notes" rows="3"
                                                placeholder="Tuliskan catatan atau rekomendasi...">{{ $evaluation->notes }}</textarea>
                                        </div>

                                        {{-- Assessor --}}
                                        <div class="row g-3 mb-4">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">
                                                    Nama Assessor <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control" name="assessor_name"
                                                    id="edit_assessor_name"
                                                    required value="{{ $evaluation->assessor_name }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Jabatan Assessor</label>
                                                <input type="text" class="form-control" name="assessor_position"
                                                    id="edit_assessor_position"
                                                    value="{{ $evaluation->assessor_position }}"
                                                    placeholder="Contoh: HSSE Officer">
                                            </div>
                                        </div>

                                        {{-- Digital Signature Preview --}}
                                        <div class="pt-3 border-top">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <i class="bi bi-pen-fill text-primary"></i>
                                                <span class="fw-bold small text-muted text-uppercase" style="letter-spacing:.05em;">
                                                    Tanda Tangan Digital
                                                </span>
                                            </div>
                                            <div class="dig-sig-preview">
                                                <div class="dig-sig-name" id="sig-name-preview">{{ $evaluation->assessor_name }}</div>
                                                <div class="dig-sig-pos" id="sig-pos-preview"
                                                    style="{{ $evaluation->assessor_position ? '' : 'display:none;' }}">
                                                    {{ $evaluation->assessor_position }}
                                                </div>
                                                <div class="dig-sig-date" id="sig-date-preview">
                                                    <i class="bi bi-calendar3 me-1"></i>
                                                    {{ \Carbon\Carbon::parse($evaluation->evaluated_date)->translatedFormat('d M Y') }}
                                                </div>
                                                <div class="dig-sig-stamp">
                                                    <i class="bi bi-patch-check-fill"></i> Digital Signature
                                                </div>
                                            </div>
                                            <div class="small text-muted mt-2">
                                                <i class="bi bi-info-circle me-1"></i>
                                                Tanda tangan dibuat otomatis dari nama assessor, jabatan, dan tanggal evaluasi.
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- ══ SIDEBAR ═════════════════════════════ --}}
                        <div class="col-lg-4">
                            <div class="sticky-sidebar d-flex flex-column gap-4">

                                {{-- Score Preview --}}
                                <div class="card shadow-sm border-0 br-8"
                                    style="border-left:4px solid #1e3a5f !important;">
                                    <div class="card-body p-4 text-center">
                                        <div class="text-muted small fw-bold mb-2 text-uppercase">Total Score</div>
                                        <div id="total-score-display">{{ $evaluation->total_score ?? '—' }}</div>
                                        <div class="mt-2">
                                            @php
                                                $sc       = $evaluation->score_category;
                                                $catClass = match($sc) {
                                                    'baik'   => 'cat-baik',
                                                    'cukup'  => 'cat-cukup',
                                                    'kurang' => 'cat-kurang',
                                                    default  => 'cat-none'
                                                };
                                                $catLabel = $sc ? ucfirst($sc) : 'Belum dinilai';
                                            @endphp
                                            <span class="score-category-label {{ $catClass }}"
                                                id="score-category-badge">{{ $catLabel }}</span>
                                        </div>
                                        <div class="mt-3 pt-3 border-top">
                                            <div class="row g-2">
                                                <div class="col-4">
                                                    <div class="p-2 rounded text-center" style="background:#fee2e2;">
                                                        <div class="small fw-bold text-danger">Kurang</div>
                                                        <div class="small text-muted">5–8</div>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="p-2 rounded text-center" style="background:#fef3c7;">
                                                        <div class="small fw-bold text-warning">Cukup</div>
                                                        <div class="small text-muted">9–11</div>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="p-2 rounded text-center" style="background:#dcfce7;">
                                                        <div class="small fw-bold text-success">Baik</div>
                                                        <div class="small text-muted">12–15</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Simpan --}}
                                <div class="card shadow-sm border-0 br-8">
                                    <div class="card-body p-4">
                                        <h6 class="fw-bold mb-1">Simpan Perubahan</h6>
                                        <p class="text-muted small mb-3">Update data evaluasi atau ubah kembali ke draft.</p>
                                        <div class="d-grid gap-2">
                                            <button type="button" class="btn btn-primary fw-bold py-2" id="btn-submit">
                                                <i class="bi bi-send-check me-1"></i> Update &amp; Submit
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary py-2" id="btn-draft">
                                                <i class="bi bi-save me-1"></i> Simpan sebagai Draft
                                            </button>
                                            <a href="{{ route('hsse-evaluation.index') }}"
                                                class="btn btn-light text-muted py-2">Batal</a>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>{{-- /row --}}
                </form>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/src/sweetalerts2/sweetalerts2.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const Toast = Swal.mixin({
        toast: true, position: 'top-end', showConfirmButton: false,
        timer: 3500, timerProgressBar: true
    });

    const totalCriteria = {{ $criteria->count() }};

    /* ── Digital Signature Live Preview ─────────────────────── */
    function updateSigPreview() {
        const name    = document.getElementById('edit_assessor_name')?.value?.trim() || '—';
        const pos     = document.getElementById('edit_assessor_position')?.value?.trim() || '';
        const dateVal = document.getElementById('edit_date')?.value;
        let dateStr   = '—';
        if (dateVal) {
            const d = new Date(dateVal);
            dateStr = d.toLocaleDateString('id-ID', { day:'2-digit', month:'long', year:'numeric' });
        }
        const nameEl = document.getElementById('sig-name-preview');
        const posEl  = document.getElementById('sig-pos-preview');
        const dateEl = document.getElementById('sig-date-preview');
        if (nameEl) nameEl.textContent = name;
        if (posEl) {
            if (pos) { posEl.textContent = pos; posEl.style.display = 'block'; }
            else     { posEl.textContent = '';  posEl.style.display = 'none';  }
        }
        if (dateEl) dateEl.innerHTML = '<i class="bi bi-calendar3 me-1"></i>' + dateStr;
    }

    document.getElementById('edit_assessor_name')?.addEventListener('input',  updateSigPreview);
    document.getElementById('edit_assessor_position')?.addEventListener('input', updateSigPreview);
    document.getElementById('edit_date')?.addEventListener('change', updateSigPreview);

    /* ── Score Update ────────────────────────────────────────── */
    function updateScore() {
        const checked = document.querySelectorAll('.score-input:checked');
        let total = 0;
        checked.forEach(r => total += parseInt(r.value));

        const display = document.getElementById('total-score-display');
        const badge   = document.getElementById('score-category-badge');
        const bar     = document.getElementById('criteria-progress-bar');
        const text    = document.getElementById('criteria-progress-text');

        const pct    = Math.round((checked.length / totalCriteria) * 100);
        bar.style.width  = pct + '%';
        text.textContent = checked.length + ' / ' + totalCriteria + ' kriteria';

        document.querySelectorAll('.score-input').forEach(input => {
            if (!input.checked) return;
            const card = document.getElementById('card-' + input.dataset.criteria);
            if (!card) return;
            card.classList.remove('scored-kurang', 'scored-cukup', 'scored-baik');
            const map = { '1': 'scored-kurang', '2': 'scored-cukup', '3': 'scored-baik' };
            card.classList.add(map[input.value] || '');
        });

        if (checked.length < totalCriteria) {
            display.textContent = total > 0 ? total : '—';
            badge.className     = 'score-category-label cat-none';
            badge.textContent   = 'Belum lengkap';
            return;
        }

        display.textContent = total;
        if (total <= 8) {
            badge.className   = 'score-category-label cat-kurang';
            badge.textContent = 'Kurang';
        } else if (total <= 11) {
            badge.className   = 'score-category-label cat-cukup';
            badge.textContent = 'Cukup';
        } else {
            badge.className   = 'score-category-label cat-baik';
            badge.textContent = 'Baik';
        }
    }

    document.querySelectorAll('.score-input').forEach(r => r.addEventListener('change', updateScore));
    updateScore();

    /* ── Form Submit ─────────────────────────────────────────── */
    function submitForm(action) {
        document.getElementById('form-action').value = action;

        const checked = document.querySelectorAll('.score-input:checked').length;
        if (checked < totalCriteria) {
            Swal.fire('Penilaian Belum Lengkap', 'Mohon isi semua ' + totalCriteria + ' kriteria.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Mohon Tunggu',
            html: action === 'submit' ? 'Menyimpan perubahan...' : 'Menyimpan draft...',
            allowOutsideClick: false, showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        const formData = new FormData(document.getElementById('evalForm'));

        fetch('{{ route("hsse-evaluation.update", $evaluation->id) }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        })
        .then(r => r.json())
        .then(res => {
            Swal.close();
            if (res.success) {
                Toast.fire({ icon: 'success', title: res.message });
                setTimeout(() => window.location.href = res.redirect, 1000);
            } else {
                Swal.fire('Error', res.message || 'Terjadi kesalahan.', 'error');
            }
        })
        .catch(() => {
            Swal.close();
            Swal.fire('Error', 'Gagal menghubungi server.', 'error');
        });
    }

    document.getElementById('btn-submit').addEventListener('click', () => submitForm('submit'));
    document.getElementById('btn-draft').addEventListener('click',  () => submitForm('draft'));
});
</script>
@endpush
