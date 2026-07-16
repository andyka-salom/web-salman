{{--
    Partial: crew-panel-static.blade.php
    Panel kru pertama — di-render server-side.
    PENTING: Jangan pakai TomSelect di sini, JS yang akan init setelah data loaded.

    Variables:
      $criteria  — Collection of HsseCriteria
      $idx       — int (0)
--}}

{{-- Crew Identity Box --}}
<div class="p-3 rounded mb-3" style="background:#f8fafc;border:1px solid #e2e8f0;">
    <div class="row g-3">

        {{-- Kru --}}
        <div class="col-md-6">
            <label class="form-label fw-bold small mb-1">
                Kru <span class="text-danger">*</span>
                <span class="crew-loading-spin spinner-border spinner-border-sm text-primary ms-1 d-none"></span>
            </label>
            {{-- PENTING: tidak ada attribute disabled, tidak ada TomSelect --}}
            <div id="crew-dropdown-wrap-{{ $idx }}">
                <select id="crew-sel-{{ $idx }}" class="form-select">
                    <option value="">— Pilih kapal dahulu —</option>
                </select>
            </div>
            <input type="text"
                id="crew-manual-{{ $idx }}"
                class="form-control d-none crew-manual-input"
                placeholder="Ketik nama kru manual">
            <input type="hidden" id="crew-name-hid-{{ $idx }}" value="">
            <div class="form-check mt-1">
                <input class="form-check-input" type="checkbox"
                    id="chk-manual-{{ $idx }}" data-cidx="{{ $idx }}">
                <label class="form-check-label small text-muted"
                    for="chk-manual-{{ $idx }}">Ketik manual</label>
            </div>
        </div>

        {{-- Jabatan --}}
        <div class="col-md-6">
            <label class="form-label fw-bold small mb-1">Jabatan</label>
            <input type="text" class="form-control crew-pos-field"
                placeholder="Terisi otomatis / ketik manual">
        </div>

        {{-- Catatan --}}
        <div class="col-md-6">
            <label class="form-label fw-bold small mb-1">Catatan / Rekomendasi</label>
            <textarea class="form-control form-control-sm crew-notes" rows="2"
                placeholder="Catatan untuk kru ini..."></textarea>
        </div>
    </div>
</div>

{{-- Progress --}}
<div class="mb-3">
    <div class="d-flex justify-content-between mb-1">
        <small class="text-muted fw-bold">Progress Penilaian</small>
        <small class="text-muted crew-prog-text">0 / {{ $criteria->count() }} kriteria</small>
    </div>
    <div class="bg-light rounded" style="height:6px;">
        <div class="crew-prog-bar"
            style="width:0%;height:6px;border-radius:99px;
                   background:linear-gradient(90deg,#1d4ed8,#22c55e);
                   transition:width .3s ease;"></div>
    </div>
</div>

{{-- Criteria Cards --}}
@foreach($criteria as $c)
<div class="criteria-card" id="ccard-{{ $idx }}-{{ $c->id }}">
    <div class="d-flex align-items-start gap-3 mb-3">
        <div class="criteria-no">{{ $c->order_no }}</div>
        <div class="flex-grow-1" style="font-size:.9rem;color:#1e293b;line-height:1.5;">
            {{ $c->aspect }}
        </div>
    </div>
    <div class="score-btn-group mb-2">
        @foreach([1 => 'Kurang', 2 => 'Cukup', 3 => 'Baik'] as $val => $label)
        <input type="radio"
            id="sc{{ $idx }}_{{ $c->id }}_{{ $val }}"
            name="scores_{{ $idx }}[{{ $c->id }}]"
            value="{{ $val }}"
            class="score-input"
            data-cid="{{ $c->id }}"
            data-cidx="{{ $idx }}">
        <label for="sc{{ $idx }}_{{ $c->id }}_{{ $val }}">
            <span style="font-size:1rem;font-weight:800;">{{ $val }}</span> {{ $label }}
        </label>
        @endforeach
    </div>
    <input type="text"
        class="form-control form-control-sm ket-input"
        data-cid="{{ $c->id }}"
        placeholder="Keterangan (opsional)...">
</div>
@endforeach

{{-- Total Legend --}}
<div class="p-3 rounded mt-2" style="background:#f8fafc;border:1px solid #e2e8f0;">
    <div class="fw-bold small text-muted mb-1">Keterangan Skor Total:</div>
    <div class="d-flex gap-3 flex-wrap">
        <span class="small"><span class="badge bg-danger">5–8</span> Kurang</span>
        <span class="small"><span class="badge bg-warning text-dark">9–11</span> Cukup</span>
        <span class="small"><span class="badge bg-success">12–15</span> Baik</span>
    </div>
</div>

{{-- Save Buttons --}}
<div class="d-flex align-items-center gap-3 mt-3 pt-3 border-top flex-wrap">
    <button type="button" class="btn btn-success btn-save-one" data-cidx="{{ $idx }}">
        <i class="bi bi-person-check me-1"></i> Simpan Kru Ini
    </button>
    <button type="button" class="btn btn-outline-secondary btn-draft-one" data-cidx="{{ $idx }}">
        <i class="bi bi-save me-1"></i> Draft Kru Ini
    </button>
    <span class="saved-badge" id="saved-badge-{{ $idx }}">
        <i class="bi bi-check-circle-fill"></i> Tersimpan
    </span>
</div>
