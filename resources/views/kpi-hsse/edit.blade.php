{{-- resources/views/kpi-hsse/edit.blade.php — Production v4 --}}
{{--
  PERBAIKAN v4:
  1. Input ∑/% dan Keterangan TIDAK memakai form POST biasa.
     Setiap baris disimpan via AJAX (saveRow) saat onblur/onchange.
     → Tidak ada HTML `required` yang memblokir submit ke HSSE.
  2. Notifikasi berhasil disimpan via toast (tidak hilang).
  3. Tombol Submit ke HSSE tetap sebagai form POST biasa (normal).
  4. As-Reported rows ditampilkan tapi tidak wajib diisi (sesuai controller).

  FIX ParseError line 383:
  - Dihapus baris `{{ $isAS?'':'data-item="{{ $item->id }}"' }}` yang
    menyebabkan nested Blade expression (tidak valid).
  - Atribut data-item="{{ $item->id }}" sudah ada di baris berikutnya,
    jadi baris duplikat tersebut tidak diperlukan.
--}}
@extends('layouts.app')
@section('title', 'Edit KPI HSSE — ' . ($kpiReport->kpiPeriod->label ?? ''))

@push('styles')
    <style>
    :root{--navy:#1e3a5f;--blue:#2563eb;--blue2:#1e40af;--lag-bd:#f59e0b;--lead-bd:#3b82f6;--r:10px;}

    /* Status badge */
    .st-badge{display:inline-flex;align-items:center;gap:5px;padding:3px 12px;border-radius:20px;font-size:.73rem;font-weight:700;}
    .st-draft    {background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;}
    .st-submitted{background:#dbeafe;color:#1e40af;border:1px solid #93c5fd;}
    .st-validated{background:#dcfce7;color:#166534;border:1px solid #86efac;}
    .st-rejected {background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;}

    /* Layout */
    .edit-layout{display:grid;grid-template-columns:1fr 280px;gap:1.5rem;align-items:start;}
    @media(max-width:991px){.edit-layout{grid-template-columns:1fr;}}

    /* Table */
    .tbl-card{background:#fff;border-radius:var(--r);border:1px solid #e2e8f0;overflow:hidden;box-shadow:0 1px 8px rgba(0,0,0,.06);}
    .tbl-wrap{overflow-x:auto;}
    .kpi-tbl{width:100%;border-collapse:collapse;font-size:.82rem;min-width:820px;}
    .kpi-tbl th,.kpi-tbl td{border:1px solid #e2e8f0;padding:0;vertical-align:middle;}

    .r-title td{background:var(--navy);color:#fff;font-weight:700;font-size:.92rem;text-align:center;padding:.65rem 1rem;letter-spacing:.6px;}
    .r-company td{background:var(--blue);color:#fff;padding:.4rem .9rem;font-weight:600;font-size:.86rem;}

    .r-vhdr td{background:#dbeafe;color:var(--blue2);font-weight:700;font-size:.7rem;text-transform:uppercase;letter-spacing:.4px;padding:.35rem .7rem;border-color:#bfdbfe;}
    .r-vdata td{background:#fff;padding:2px 4px;border-color:#bfdbfe;}
    .r-vdata input{width:100%;border:none;background:transparent;font-size:.82rem;padding:4px 6px;outline:none;color:#1e293b;}
    .r-vdata input:focus{background:#fefce8;border-radius:3px;}

    .r-chdr th{background:var(--navy);color:#fff;font-size:.68rem;font-weight:700;text-align:center;padding:.5rem .35rem;text-transform:uppercase;letter-spacing:.3px;border-color:var(--blue);white-space:nowrap;}

    .r-sect-lag td{background:linear-gradient(90deg,#fef3c7,#fde68a);color:#78350f;font-weight:800;font-size:.82rem;padding:.5rem .8rem;border-color:var(--lag-bd);}
    .r-sect-lead td{background:linear-gradient(90deg,#dbeafe,#bfdbfe);color:var(--blue2);font-weight:800;font-size:.82rem;padding:.5rem .8rem;border-color:var(--lead-bd);}
    .sect-pill{float:right;font-size:.71rem;background:rgba(0,0,0,.14);padding:1px 9px;border-radius:20px;}

    .r-kpi td{background:#fff;transition:background .12s;}
    .r-kpi:hover td{background:#f8faff;}
    .r-kpi.as-rep td{background:#fafafa;}
    .r-kpi.rej td{background:#fff8f8;}
    .r-kpi.lag-sh td{background:#fffdf0;}

    .td-no{text-align:center;font-weight:700;width:32px;color:#475569;padding:.4rem;background:#f8fafc;border-right:2px solid #e2e8f0;font-size:.78rem;}
    .r-kpi.rej .td-no{border-left:3px solid #ef4444;color:#ef4444;}
    .r-kpi.as-rep .td-no{color:#cbd5e1;}

    /* Item cell */
    .td-item{padding:.45rem .65rem;min-width:190px;max-width:240px;line-height:1.45;}
    .i-name{font-weight:600;color:#0f172a;font-size:.82rem;}
    .i-target{font-size:.67rem;color:#b0b0b0;margin-top:3px;padding-top:3px;border-top:1px dashed #eee;font-style:italic;line-height:1.35;}
    .i-tag{display:inline-flex;align-items:center;gap:3px;margin-top:3px;font-size:.65rem;border-radius:4px;padding:1px 6px;font-weight:700;}
    .i-tag.asrep{background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;}
    .i-tag.rej{background:#fee2e2;color:#b91c1c;}
    .i-tag.ok{background:#dcfce7;color:#166534;}
    .rej-cmt{font-size:.68rem;color:#b91c1c;font-style:italic;margin-top:2px;}

    /* ∑ / % — AUTOSAVE, no required attr */
    .td-jml{padding:3px 4px;width:90px;}
    .jml-inner{display:flex;align-items:center;gap:3px;}
    .jml-inner input{flex:1;min-width:0;border:1.5px solid #e2e8f0;border-radius:6px;padding:5px 4px;
        font-size:.84rem;font-weight:700;text-align:center;background:#fffbeb;outline:none;
        transition:border-color .15s,background .15s;}
    .jml-inner input:focus{border-color:#f59e0b;background:#fefce8;box-shadow:0 0 0 2px rgba(245,158,11,.15);}
    .jml-inner input.saving{border-color:#f59e0b;background:#fefce8;}
    .jml-inner input.saved {border-color:#86efac;background:#f0fdf4;}
    .jml-inner input.err   {border-color:#fca5a5;background:#fff8f8;}
    .r-kpi.as-rep .jml-inner input{background:#f8fafc;color:#94a3b8;}
    .unit-chip{flex-shrink:0;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:4px;padding:2px 5px;font-size:.66rem;font-weight:800;color:#64748b;}

    /* Keterangan — AUTOSAVE */
    .td-ket{padding:3px 4px;min-width:150px;}
    .td-ket textarea{width:100%;border:1.5px solid #e2e8f0;border-radius:6px;padding:4px 6px;
        font-size:.79rem;background:#fffbeb;outline:none;resize:none;transition:border-color .15s;}
    .td-ket textarea:focus{border-color:#f59e0b;background:#fefce8;box-shadow:0 0 0 2px rgba(245,158,11,.15);}
    .td-ket textarea.saved{border-color:#86efac;background:#f0fdf4;}
    .td-ket textarea.err  {border-color:#fca5a5;}

    /* Evidence */
    .td-ev{padding:5px 6px;min-width:120px;vertical-align:top;}
    .ev-row{display:flex;flex-wrap:wrap;gap:3px;margin-bottom:4px;}
    .ev-wrap{position:relative;display:inline-block;}
    .ev-img{width:36px;height:36px;object-fit:cover;border-radius:5px;border:1.5px solid #e2e8f0;cursor:pointer;display:block;transition:transform .15s;}
    .ev-img:hover{transform:scale(1.12);border-color:#6366f1;}
    .ev-pdf-thumb{width:36px;height:36px;border-radius:5px;border:1.5px solid #bfdbfe;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:14px;text-decoration:none;}
    .ev-del{position:absolute;top:-5px;right:-5px;width:14px;height:14px;border-radius:50%;background:#dc2626;color:#fff;font-size:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;border:2px solid #fff;opacity:0;transition:opacity .15s;z-index:4;}
    .ev-wrap:hover .ev-del{opacity:1;}
    .btn-ev-up{display:inline-flex;align-items:center;gap:2px;padding:3px 7px;font-size:.68rem;background:#f0f9ff;border:1px dashed #7dd3fc;border-radius:5px;color:#0369a1;cursor:pointer;white-space:nowrap;}
    .btn-ev-up:hover{background:#e0f2fe;}
    .btn-ev-up.busy{opacity:.55;pointer-events:none;}
    .ev-prog{display:none;height:3px;background:#e2e8f0;border-radius:2px;margin-top:3px;overflow:hidden;}
    .ev-prog.on{display:block;}
    .ev-bar{height:100%;background:#3b82f6;width:0%;transition:width .2s;}
    .ev-err{font-size:.66rem;color:#dc2626;margin-top:2px;display:none;}
    .ev-err.on{display:block;}
    .ev-st-ok {display:inline-block;font-size:.65rem;background:#dcfce7;color:#166534;border:1px solid #86efac;border-radius:4px;padding:1px 5px;font-weight:700;}
    .ev-st-req{display:inline-block;font-size:.65rem;background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;border-radius:4px;padding:1px 5px;font-weight:700;}
    .ev-st-sh {display:inline-block;font-size:.65rem;background:#fef9c3;color:#92400e;border:1px solid #fde68a;border-radius:4px;padding:1px 5px;font-weight:600;}

    /* Nilai — HSSE only */
    .td-nilai{padding:3px 4px;width:72px;}
    .td-nilai input{width:100%;border:1.5px solid #bfdbfe;border-radius:6px;padding:5px 4px;font-size:.84rem;font-weight:700;text-align:center;background:#eff6ff;outline:none;}
    .td-nilai input:focus{border-color:#3b82f6;background:#dbeafe;}
    .td-nilai input:disabled{background:#f1f5f9;color:#94a3b8;border-color:#e2e8f0;}

    /* Bobot / Score */
    .td-bobot{text-align:center;width:52px;font-weight:600;color:#475569;background:#f8fafc;padding:.4rem;font-size:.78rem;}
    .td-score{text-align:center;width:58px;font-weight:700;background:#eff6ff;color:var(--blue2);padding:.4rem;font-size:.82rem;}
    .td-score.s-exc{color:#065f46;background:#dcfce7;}
    .td-score.s-good{color:var(--blue2);background:#dbeafe;}
    .td-score.s-fair{color:#92400e;background:#fef3c7;}
    .td-score.s-poor{color:#991b1b;background:#fee2e2;}
    .r-kpi.as-rep .td-bobot,.r-kpi.as-rep .td-score{background:#f1f5f9;color:#cbd5e1;}

    /* Total */
    .r-total td{background:var(--navy);color:#fff;font-weight:700;padding:.55rem .8rem;font-size:.86rem;}
    .total-chip{display:inline-flex;flex-direction:column;align-items:center;padding:4px 14px;border-radius:6px;}

    /* Sidebar */
    .sb-card{background:#fff;border-radius:var(--r);border:1px solid #e2e8f0;padding:1.1rem;box-shadow:0 1px 5px rgba(0,0,0,.05);}
    .sb-card+.sb-card{margin-top:1rem;}
    .sb-title{font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#94a3b8;margin-bottom:.85rem;}
    .s-ring{width:60px;height:60px;border-radius:50%;border:4px solid;display:flex;flex-direction:column;align-items:center;justify-content:center;font-weight:800;flex-shrink:0;}
    .r-none{border-color:#94a3b8;color:#94a3b8;background:#f1f5f9;}
    .r-exc {border-color:#16a34a;color:#16a34a;background:#dcfce7;}
    .r-good{border-color:var(--blue);color:var(--blue);background:#dbeafe;}
    .r-fair{border-color:#d97706;color:#d97706;background:#fef3c7;}
    .r-poor{border-color:#dc2626;color:#dc2626;background:#fee2e2;}
    .mini-prog{height:4px;border-radius:3px;background:#e2e8f0;overflow:hidden;margin-top:.5rem;}
    .mini-prog-fill{height:100%;border-radius:3px;background:var(--blue);transition:width .4s;}
    .btn-add-v{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;font-size:.71rem;background:#eff6ff;border:1px dashed var(--blue);border-radius:5px;color:var(--blue2);cursor:pointer;white-space:nowrap;}
    .btn-del-v{width:17px;height:17px;border-radius:50%;background:#fee2e2;border:none;color:#dc2626;font-size:10px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;}
    @media(min-width:992px){.sticky-sb{position:sticky;top:1.25rem;}}

    /* Toast notification */
    #kpi-toast{position:fixed;bottom:24px;right:24px;z-index:9999;display:none;
        padding:10px 20px;border-radius:8px;font-size:.83rem;font-weight:600;
        box-shadow:0 4px 20px rgba(0,0,0,.18);animation:slideUp .25s ease;}
    #kpi-toast.ok {background:#dcfce7;color:#166534;border:1px solid #86efac;}
    #kpi-toast.err{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;}
    @keyframes slideUp{from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:translateY(0);}}
    </style>
@endpush

@section('content')
    @php
        $LAG_GROUP = [1, 2, 3, 4, 5];
        $ts = (float) $kpiReport->total_score;
        $ringCls = $ts <= 0 ? 'r-none' : ($ts >= 90 ? 'r-exc' : ($ts >= 75 ? 'r-good' : ($ts >= 60 ? 'r-fair' : 'r-poor')));
        $sections = [
            'lagging' => ['label' => 'SECTION 1 — LAGGING INDICATOR', 'cls' => 'r-sect-lag'],
            'leading' => ['label' => 'SECTION 2 — LEADING INDICATOR', 'cls' => 'r-sect-lead'],
        ];
        $colSpan = ($isHsse || $isSA) ? 9 : 6;
    @endphp

    <div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
    <div class="row layout-top-spacing">
    <div class="col-12">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                <h2 class="fw-bold mb-0">Edit KPI HSSE</h2>
                <span class="st-badge st-{{ $kpiReport->status }}">
                    {{ ['draft' => '📝', 'submitted' => '⏳', 'validated' => '✅', 'rejected' => '❌'][$kpiReport->status] ?? '' }}
                    {{ ucfirst($kpiReport->status) }}
                </span>
            </div>
            <p class="text-muted mb-0" style="font-size:.83rem;">
                <strong>{{ $kpiReport->company->name ?? '-' }}</strong> &bull; {{ $kpiReport->kpiPeriod->label ?? '-' }}
            </p>
        </div>
        <a href="{{ route('kpi-hsse.show', $kpiReport) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">← Lihat Detail</a>
    </div>

    {{-- Flash alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <strong>Validasi gagal:</strong>
            <ul class="mb-0 mt-1 ps-3">@foreach($errors->all() as $e)<li style="font-size:.83rem;">{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($kpiReport->status === 'rejected')
        <div class="alert mb-3 py-2 px-3 d-flex gap-2" style="background:#fff8f8;border:1px solid #fca5a5;border-radius:8px;font-size:.82rem;">
            ⚠️ <div><strong>Laporan ditolak HSSE.</strong> Perbaiki item bertanda merah, lengkapi lampiran, lalu submit ulang.</div>
        </div>
    @endif
    @if($canEditKoord)
        <div class="alert mb-3 py-2 px-3 d-flex gap-2" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;font-size:.79rem;color:#065f46;">
            📎 <div>
                <strong>Data tersimpan otomatis</strong> saat Anda pindah ke kolom berikutnya.
                Lampiran wajib (PDF/JPG/PNG/WEBP ≤2 MB). Lagging No.1–5 cukup 1 lampiran bersama di No.1.
            </div>
        </div>
    @endif
    @if($canEditHsse)
        <div class="alert mb-3 py-2 px-3 d-flex gap-2" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;font-size:.79rem;color:#1e40af;">
            🔵 <div>Mode <strong>Penilaian HSSE</strong>. Isi Nilai (0–100) tiap item scored. Score dihitung otomatis.</div>
        </div>
    @endif

    {{-- ════ TABLE ════ --}}
    <div class="edit-layout">
    <div>
    <div class="tbl-card">
    <div class="tbl-wrap">
    <table class="kpi-tbl">

        <tr class="r-title"><td colspan="{{ $colSpan }}">KPI HSSE — KONTRAKTOR</td></tr>

        <tr class="r-company">
            <td colspan="{{ ($isHsse || $isSA) ? 6 : 3 }}" style="padding:.4rem .9rem;">
                <strong>{{ $kpiReport->company->name ?? '-' }}</strong>
            </td>
            <td colspan="{{ ($isHsse || $isSA) ? 3 : 3 }}" style="padding:.4rem .9rem;text-align:right;font-size:.79rem;opacity:.85;">
                BULAN: <strong>{{ strtoupper($kpiReport->kpiPeriod->label ?? '-') }}</strong>
            </td>
        </tr>

        {{-- Vessel rows --}}
        <tr class="r-vhdr">
            <td colspan="2" style="width:56px;padding:.35rem .6rem;"></td>
            <td style="min-width:135px;padding:.35rem .6rem;">NOMER KONTRAK</td>
            <td style="min-width:115px;padding:.35rem .6rem;">AKHIR KONTRAK</td>
            <td style="min-width:85px;padding:.35rem .6rem;">JML KAPAL</td>
            <td colspan="{{ ($isHsse || $isSA) ? 4 : 1 }}" style="padding:.35rem .6rem;">
                NAMA KAPAL / UNIT
                @if($canEditKoord)
                    &nbsp;<button type="button" class="btn-add-v" onclick="doAddVessel()">+ Tambah</button>
                @endif
            </td>
        </tr>
        <tbody id="vessel-tbody">
        @foreach($kpiReport->vessels->sortBy('sort_order') as $vi => $v)
            <tr class="r-vdata" id="vrow-{{ $v->id }}">
                <td style="width:28px;padding:3px 5px;background:#f0f4ff;text-align:center;font-size:.7rem;color:#94a3b8;">{{ $vi + 1 }}</td>
                <td style="width:28px;padding:3px 5px;background:#f0f4ff;text-align:center;">
                    @if($canEditKoord)
                        <button type="button" class="btn-del-v {{ $kpiReport->vessels->count() <= 1 ? 'd-none' : '' }}"
                                onclick="doDelVessel('{{ $v->id }}','{{ route('kpi-hsse.vessels.destroy', [$kpiReport, $v]) }}')" title="Hapus">×</button>
                    @endif
                </td>
                <td>
                    @if($canEditKoord)
                        <input type="text" id="vc-{{ $v->id }}" value="{{ $v->contract_number }}" placeholder="No. kontrak"
                               onblur="doSaveVessel('{{ $v->id }}','{{ route('kpi-hsse.vessels.update', [$kpiReport, $v]) }}')">
                    @else
                        <span style="padding:4px 6px;font-size:.82rem;display:block;">{{ $v->contract_number ?: '—' }}</span>
                    @endif
                </td>
                <td>
                    @if($canEditKoord)
                        <input type="date" id="vd-{{ $v->id }}" value="{{ $v->contract_end_date?->format('Y-m-d') }}"
                               onblur="doSaveVessel('{{ $v->id }}','{{ route('kpi-hsse.vessels.update', [$kpiReport, $v]) }}')">
                    @else
                        <span style="padding:4px 6px;font-size:.82rem;display:block;">{{ $v->contract_end_date?->format('d/m/Y') ?? '—' }}</span>
                    @endif
                </td>
                <td>
                    @if($canEditKoord)
                        <input type="text" id="vco-{{ $v->id }}" value="{{ $v->vessel_count }}" placeholder="Misal: 2 unit"
                               onblur="doSaveVessel('{{ $v->id }}','{{ route('kpi-hsse.vessels.update', [$kpiReport, $v]) }}')">
                    @else
                        <span style="padding:4px 6px;font-size:.82rem;display:block;">{{ $v->vessel_count ?: '—' }}</span>
                    @endif
                </td>
                <td colspan="{{ ($isHsse || $isSA) ? 4 : 1 }}">
                    @if($canEditKoord)
                        <input type="text" id="vn-{{ $v->id }}" value="{{ $v->vessel_name }}" placeholder="Nama kapal / unit" style="flex:1;width:100%;"
                               onblur="doSaveVessel('{{ $v->id }}','{{ route('kpi-hsse.vessels.update', [$kpiReport, $v]) }}')">
                    @else
                        <span style="padding:4px 6px;font-size:.82rem;display:block;">{{ $v->vessel_name }}</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>

        {{-- Column headers --}}
        <tr class="r-chdr">
            <th colspan="2" style="width:36px;">No</th>
            <th style="min-width:195px;">Item KPI</th>
            <th style="width:90px;">∑ / %</th>
            <th style="min-width:150px;">Keterangan</th>
            <th style="min-width:115px;">Lampiran <span style="color:#f87171;">*</span></th>
            @if($isHsse || $isSA)
                <th style="width:68px;">Nilai <span style="color:#93c5fd;font-size:.6rem;">(HSSE)</span></th>
                <th style="width:52px;">Bobot</th>
                <th style="width:58px;">Score</th>
            @endif
        </tr>

        {{-- KPI rows --}}
        @foreach($sections as $sKey => $sec)
            @php $sItems = $kpiItems->get($sKey, collect());
            $sBobot = round($sItems->sum('bobot') * 100); @endphp
            <tr class="{{ $sec['cls'] }}">
                <td colspan="{{ $colSpan }}">{{ $sec['label'] }}<span class="sect-pill">Bobot {{ $sBobot }}%</span></td>
            </tr>

            @foreach($sItems as $item)
                @php
                    $det = $existingDetails[$item->id] ?? null;
                    $isAS = !$item->is_scored;
                    $isRej = $det && $det->review_status === 'rejected';
                    $isOK = $det && $det->review_status === 'approved';
                    $inGrp = ($sKey === 'lagging' && in_array($item->item_no, $LAG_GROUP));
                    $isAnch = ($sKey === 'lagging' && $item->item_no === 1);

                    if ($inGrp) {
                        $evs = $lagAnchorDetail ? $lagAnchorDetail->evidences : collect();
                        $evCnt = $lagEvCount;
                        $upId = $lagAnchorId;
                    } else {
                        $evs = $det ? $det->evidences : collect();
                        $evCnt = $evs->count();
                        $upId = $det?->id ?? '';
                    }

                    $unitLabel = $item->unit_label ?? ($item->unit ?? '∑');
                    $isPercent = ($unitLabel === '%');
                    [$iName, $iTarget] = array_pad(explode("\n", $item->name, 2), 2, '');
                    $iName = trim($iName);
                    $iTarget = trim($iTarget);
                    $nilaiVal = (float) ($det?->nilai ?? 0);
                    $score = $det?->score;
                    $scCls = '';
                    if ($score > 0)
                        $scCls = $nilaiVal >= 90 ? 's-exc' : ($nilaiVal >= 75 ? 's-good' : ($nilaiVal >= 60 ? 's-fair' : 's-poor'));
                    $rowCls = trim(($isAS ? 'as-rep ' : '') . ($isRej ? 'rej ' : '') . ($inGrp && !$isAnch ? 'lag-sh' : ''));
                @endphp

                <tr class="r-kpi {{ $rowCls }}" data-section="{{ $sKey }}" data-item-no="{{ $item->item_no }}">

                    <td class="td-no" colspan="2">{{ $item->item_no }}</td>

                    {{-- Item name + target --}}
                    <td class="td-item">
                        <div class="i-name">{{ $iName }}</div>
                        @if($iTarget)<div class="i-target">{{ $iTarget }}</div>@endif
                        <div class="mt-1 d-flex flex-wrap gap-1">
                            @if($isAS)  <span class="i-tag asrep">As Reported</span>@endif
                            @if($isRej) <span class="i-tag rej">✗ Ditolak</span>@endif
                            @if($isOK)  <span class="i-tag ok">✓ Disetujui</span>@endif
                        </div>
                        @if($isRej && $det?->hsse_catatan)<div class="rej-cmt">{{ $det->hsse_catatan }}</div>@endif
                    </td>

                    {{-- ∑/% — autosave via JS, NO nested Blade expression (FIXED) --}}
                    <td class="td-jml">
                        @if($canEditKoord)
                            <div class="jml-inner">
                                <input type="number"
                                       id="ac-{{ $item->id }}"
                                       value="{{ old('items.' . $item->id . '.actual_count', $det?->actual_count) }}"
                                       min="0" step="{{ $isPercent ? '0.01' : '1' }}"
                                       placeholder="{{ $isAS ? '' : ' 0' }}"
                                       data-item="{{ $item->id }}"
                                       data-save-url="{{ route('kpi-hsse.update', $kpiReport) }}"
                                       onchange="saveRow(this)"
                                       onblur="saveRow(this)">
                                <span class="unit-chip">{{ $unitLabel }}</span>
                            </div>
                        @else
                            <div style="text-align:center;font-weight:700;font-size:.84rem;padding:.4rem 0;">
                                {{ $det?->actual_count ?? '—' }}
                                <div style="font-size:.66rem;color:#94a3b8;">{{ $unitLabel }}</div>
                            </div>
                        @endif
                    </td>

                    {{-- Keterangan — autosave --}}
                    <td class="td-ket">
                        @if($canEditKoord)
                            <textarea id="ket-{{ $item->id }}"
                                      rows="2"
                                      placeholder="Keterangan implementasi..."
                                      data-item="{{ $item->id }}"
                                      data-save-url="{{ route('kpi-hsse.update', $kpiReport) }}"
                                      onblur="saveKet(this)">{{ old('items.' . $item->id . '.keterangan', $det?->keterangan) }}</textarea>
                        @else
                            <div style="padding:.35rem .5rem;font-size:.79rem;color:#374151;line-height:1.45;">
                                {{ $det?->keterangan ?: '—' }}
                            </div>
                        @endif
                    </td>

                    {{-- Lampiran --}}
                    <td class="td-ev">
                        @if($isAS)
                            <span style="font-size:.68rem;color:#cbd5e1;font-style:italic;">Tidak perlu</span>

                        @elseif($inGrp && !$isAnch)
                            <span class="ev-st-sh">Shared 1–5</span>
                            <div style="margin-top:3px;">
                                @if($lagEvCount > 0)
                                    <span class="ev-st-ok">{{ $lagEvCount }} file</span>
                                @else
                                    <span class="ev-st-req">Belum ada</span>
                                @endif
                            </div>

                        @else
                            <div class="ev-row" id="ev-row-{{ $upId }}">
                                @foreach($evs as $ev)
                                    @php $isPdf = str_contains($ev->file_type ?? '', 'pdf'); @endphp
                                    <div class="ev-wrap" id="ev-item-{{ $ev->id }}">
                                        @if($isPdf)
                                            <a href="{{ Storage::disk('public')->url($ev->file_path) }}" target="_blank" class="ev-pdf-thumb" title="{{ $ev->file_name }}">📄</a>
                                        @else
                                            <img class="ev-img" src="{{ Storage::disk('public')->url($ev->file_path) }}"
                                                 onclick="previewImg('{{ Storage::disk('public')->url($ev->file_path) }}','{{ e($ev->file_name) }}')"
                                                 title="{{ $ev->caption ?? $ev->file_name }}">
                                        @endif
                                        @if($canEditKoord)
                                            <div class="ev-del" onclick="doDelEv('{{ $ev->id }}','{{ route('kpi-hsse.evidences.delete', [$kpiReport, $ev]) }}','{{ $upId }}')">×</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            @if($canEditKoord && ($det || ($inGrp && $lagAnchorDetail)))
                                <label class="btn-ev-up" id="lbl-{{ $upId }}" for="ev-up-{{ $upId }}">+ Lampiran</label>
                                <input type="file" id="ev-up-{{ $upId }}" class="d-none ev-input"
                                       accept=".jpg,.jpeg,.png,.webp,.pdf" multiple
                                       data-uid="{{ $upId }}"
                                       data-url="{{ route('kpi-hsse.evidences.upload', $kpiReport) }}"
                                       data-lag="{{ $isAnch ? '1' : '0' }}">
                                <div class="ev-prog" id="ev-prog-{{ $upId }}"><div class="ev-bar" id="ev-bar-{{ $upId }}"></div></div>
                                <div class="ev-err" id="ev-err-{{ $upId }}"></div>
                            @endif

                            <div id="ev-st-{{ $upId }}" style="margin-top:3px;">
                                @if($evCnt > 0)
                                    <span class="ev-st-ok">{{ $evCnt }} file</span>
                                @elseif($item->is_scored && $canEditKoord)
                                    <span class="ev-st-req">Wajib</span>
                                @endif
                            </div>
                            @if($isAnch)<div style="font-size:.64rem;color:#92400e;margin-top:2px;">↑ No.1–5</div>@endif
                        @endif
                    </td>

                    {{-- NILAI — hanya HSSE/SA --}}
                    @if($isHsse || $isSA)
                        <td class="td-nilai">
                            @if($isAS)
                                <span style="font-size:.68rem;color:#cbd5e1;font-style:italic;display:block;text-align:center;">A/R</span>
                            @else
                                <input type="number" id="nilai-{{ $item->id }}"
                                       value="{{ $nilaiVal > 0 ? $nilaiVal : '' }}"
                                       min="0" max="100" step="0.01"
                                       placeholder="{{ $canEditHsse ? '0–100' : '—' }}"
                                       {{ $canEditHsse ? '' : 'disabled' }}
                                       onchange="doUpdateScore('{{ $item->id }}')">
                            @endif
                        </td>
                        <td class="td-bobot">
                            @if($item->is_scored) {{ number_format((float) $item->bobot * 100, 1) }}%
                            @else <span style="color:#e2e8f0;">—</span>@endif
                        </td>
                        <td class="td-score {{ $scCls }}" id="sc-{{ $item->id }}">
                            @if($isAS) A/R @elseif($score !== null) {{ number_format((float) $score, 2) }} @else — @endif
                        </td>
                    @endif

                </tr>
            @endforeach
        @endforeach

        {{-- Total --}}
        <tr class="r-total">
            <td colspan="{{ ($isHsse || $isSA) ? 6 : 3 }}" style="text-align:right;padding:.5rem .8rem;font-size:.8rem;opacity:.7;">TOTAL SCORE</td>
            <td colspan="3" style="text-align:center;">
                <div style="display:flex;align-items:center;justify-content:center;gap:1rem;padding:.3rem;">
                    <div class="total-chip" style="background:rgba(245,158,11,.15);">
                        <span style="font-size:.58rem;color:#fde68a;letter-spacing:.3px;">LAGGING</span>
                        <strong style="color:#fde68a;font-size:.9rem;" id="t-lag">{{ number_format($kpiReport->total_score_lagging, 2) }}</strong>
                    </div>
                    <div style="font-size:1.3rem;font-weight:800;color:#fff;" id="t-total">{{ number_format((float) $kpiReport->total_score, 2) }}</div>
                    <div class="total-chip" style="background:rgba(147,197,253,.15);">
                        <span style="font-size:.58rem;color:#bfdbfe;letter-spacing:.3px;">LEADING</span>
                        <strong style="color:#bfdbfe;font-size:.9rem;" id="t-lead">{{ number_format($kpiReport->total_score_leading, 2) }}</strong>
                    </div>
                </div>
            </td>
        </tr>

    </table>
    </div>
    </div>
    </div>

    {{-- Sidebar --}}
    <div>
    <div class="sticky-sb">

        <div class="sb-card">
            <div class="sb-title">Score</div>
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="s-ring {{ $ringCls }}" id="sb-ring">
                    <span style="font-size:.92rem;line-height:1;" id="sb-val">{{ $ts > 0 ? number_format($ts, 1) : '—' }}</span>
                    <span style="font-size:.55rem;opacity:.7;">/100</span>
                </div>
                <div style="flex:1;">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:.7rem;color:#94a3b8;">Lagging</span>
                        <strong id="sb-lag" style="font-size:.84rem;">{{ number_format($kpiReport->total_score_lagging, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span style="font-size:.7rem;color:#94a3b8;">Leading</span>
                        <strong id="sb-lead" style="font-size:.84rem;">{{ number_format($kpiReport->total_score_leading, 2) }}</strong>
                    </div>
                </div>
            </div>
            <div class="mini-prog"><div class="mini-prog-fill" id="sb-bar" style="width:{{ min($ts, 100) }}%;"></div></div>
            @if(!($isHsse || $isSA))
                <p class="mb-0 mt-2" style="font-size:.67rem;color:#cbd5e1;">Nilai & Score diisi HSSE setelah submit.</p>
            @endif
        </div>

        <div class="sb-card">
            <div class="sb-title">Aksi</div>
            <div class="d-grid gap-2">
                @if($canEditKoord)
                    {{-- Submit ke HSSE — form POST biasa (bukan form update) --}}
                    <button type="button" class="btn btn-success btn-sm fw-semibold rounded-pill"
                            data-bs-toggle="modal" data-bs-target="#submitModal">
                        🚀 Submit ke HSSE
                    </button>
                @endif
                @if($canEditHsse)
                    <button type="button" class="btn btn-primary btn-sm fw-semibold rounded-pill"
                            onclick="saveAllNilai()">💾 Simpan Semua Nilai</button>
                @endif
                <a href="{{ route('kpi-hsse.show', $kpiReport) }}" class="btn btn-outline-secondary btn-sm rounded-pill">Lihat Detail</a>
            </div>
        </div>

        @if($canEditKoord)
            <div class="sb-card" style="background:#0f2d4a;border-color:#0f2d4a;">
                <div class="sb-title" style="color:#60a5fa;">Panduan</div>
                <ul class="mb-0 ps-3" style="color:rgba(255,255,255,.82);font-size:.74rem;line-height:1.95;">
                    <li>Isi <strong>∑ / %</strong> tiap item scored</li>
                    <li><strong>Tersimpan otomatis</strong> saat pindah kolom</li>
                    <li>Upload <strong>Lampiran</strong> PDF/foto ≤ 2 MB</li>
                    <li>Lagging 1–5: cukup <strong>1 lampiran di No.1</strong></li>
                    <li>Item merah = ditolak HSSE, perbaiki</li>
                </ul>
            </div>
        @endif
        @if($canEditHsse)
            <div class="sb-card" style="background:#0c2044;border-color:#0c2044;">
                <div class="sb-title" style="color:#93c5fd;">Panduan HSSE</div>
                <ul class="mb-0 ps-3" style="color:rgba(255,255,255,.82);font-size:.74rem;line-height:1.95;">
                    <li>Isi <strong>Nilai</strong> 0–100 tiap item scored</li>
                    <li>Score = Nilai × Bobot (otomatis)</li>
                    <li><strong>SUM:</strong> Lagging + Leading 1,7,8,9,13</li>
                    <li><strong>AVG:</strong> Leading 2,3,4,5,6,10,11,12,14,15</li>
                </ul>
            </div>
        @endif

        @if($kpiReport->status === 'draft' && $canEditKoord)
            <div class="sb-card" style="border-color:#fca5a5;text-align:center;">
                <form method="POST" action="{{ route('kpi-hsse.destroy', $kpiReport) }}"
                      onsubmit="return confirm('Hapus draft ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">🗑 Hapus Draft</button>
                </form>
            </div>
        @endif
        @if($isSA)
            <div class="sb-card" style="border-color:#fca5a5;text-align:center;">
                <form method="POST" action="{{ route('kpi-hsse.destroy', $kpiReport) }}"
                      onsubmit="return confirm('Hapus laporan ini? Semua lampiran ikut terhapus.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3">🗑 Hapus Laporan</button>
                </form>
            </div>
        @endif

    </div>
    </div>
    </div>{{-- /edit-layout --}}

    {{-- Toast --}}
    <div id="kpi-toast"></div>

    </div>
    </div>
    </div>
    </div>

    {{-- SUBMIT MODAL — form POST biasa ke route submit --}}
    <div class="modal fade" id="submitModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <form action="{{ route('kpi-hsse.submit', $kpiReport) }}" method="POST">
        @csrf
        <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold">🚀 Submit ke HSSE</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="alert alert-info py-2 px-3 mb-3" style="font-size:.8rem;">
                Pastikan semua item scored sudah diisi <strong>∑/%</strong> dan memiliki <strong>lampiran</strong>.
                Item As-Reported (FAC, Nearmiss, Manhours) tidak perlu lampiran.
            </div>
            <label class="form-label fw-semibold" style="font-size:.8rem;">Catatan untuk HSSE (opsional)</label>
            <textarea name="comment" class="form-control form-control-sm" rows="3" placeholder="Catatan tambahan..."></textarea>
        </div>
        <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-success btn-sm fw-bold rounded-pill px-4">🚀 Kirim ke HSSE</button>
        </div>
        </form>
    </div>
    </div>
    </div>

    {{-- IMAGE PREVIEW MODAL --}}
    <div class="modal fade" id="imgModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
        <div class="modal-header py-2 border-0">
            <h6 class="modal-title" id="img-title">Preview</h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center p-2">
            <img id="img-src" src="" class="img-fluid rounded" style="max-height:78vh;object-fit:contain;">
        </div>
    </div>
    </div>
    </div>
@endsection

@push('scripts')
    <script>
    const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const MAX_BYTES = 2 * 1024 * 1024;
    const ALLOWED   = ['image/jpeg','image/png','image/webp','application/pdf'];
    const LAG_ANCHOR= '{{ $lagAnchorId }}';
    const SCORE_URL = '{{ route("kpi-hsse.score.update", $kpiReport) }}';

    // ── Toast helper ─────────────────────────────────────────────────────────
    let toastTimer;
    function toast(msg, type='ok') {
        const el = document.getElementById('kpi-toast');
        if (!el) return;
        el.textContent = msg;
        el.className   = type;
        el.style.display = 'block';
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { el.style.display='none'; }, 2800);
    }

    // ── AUTOSAVE: ∑/% per baris ──────────────────────────────────────────────
    let saveTimers = {};
    async function saveRow(inp) {
        const itemId = inp.dataset.item;
        if (!itemId) return;
        const url    = inp.dataset.saveUrl;

        clearTimeout(saveTimers[itemId]);
        inp.classList.remove('saved','err');
        inp.classList.add('saving');

        saveTimers[itemId] = setTimeout(async () => {
            const ketEl = document.getElementById('ket-' + itemId);
            const fd    = new FormData();
            fd.append('_method',      'PUT');
            fd.append('kpi_item_id',  itemId);
            fd.append('actual_count', inp.value);
            fd.append('keterangan',   ketEl ? ketEl.value : '');

            try {
                const res  = await fetch(url, {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}, body:fd});
                const data = await res.json();
                inp.classList.remove('saving');
                if (data.success) {
                    inp.classList.add('saved');
                    toast('✅ Tersimpan', 'ok');
                    setTimeout(()=>inp.classList.remove('saved'), 1800);
                } else {
                    inp.classList.add('err');
                    toast('❌ ' + (data.message||'Gagal simpan'), 'err');
                }
            } catch(e) {
                inp.classList.remove('saving');
                inp.classList.add('err');
                toast('❌ Koneksi error', 'err');
            }
        }, 400);
    }

    // ── AUTOSAVE: Keterangan ─────────────────────────────────────────────────
    let ketTimers = {};
    async function saveKet(ta) {
        const itemId = ta.dataset.item;
        if (!itemId) return;
        const url    = ta.dataset.saveUrl;

        clearTimeout(ketTimers[itemId]);
        ta.classList.remove('saved','err');

        ketTimers[itemId] = setTimeout(async () => {
            const acEl = document.getElementById('ac-' + itemId);
            const fd   = new FormData();
            fd.append('_method',      'PUT');
            fd.append('kpi_item_id',  itemId);
            fd.append('actual_count', acEl ? acEl.value : '');
            fd.append('keterangan',   ta.value);

            try {
                const res  = await fetch(url, {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}, body:fd});
                const data = await res.json();
                if (data.success) {
                    ta.classList.add('saved');
                    toast('✅ Tersimpan', 'ok');
                    setTimeout(()=>ta.classList.remove('saved'), 1800);
                } else {
                    ta.classList.add('err');
                    toast('❌ ' + (data.message||'Gagal simpan'), 'err');
                }
            } catch(e) {
                ta.classList.add('err');
                toast('❌ Koneksi error', 'err');
            }
        }, 600);
    }

    // ── Evidence upload ──────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.ev-input').forEach(inp => {
            inp.addEventListener('change', () => handleUpload(inp));
        });
    });

    async function handleUpload(inp) {
        const files = Array.from(inp.files);
        if (!files.length) return;
        const uid   = inp.dataset.uid;
        const url   = inp.dataset.url;
        const isLag = inp.dataset.lag === '1';
        const lbl   = document.getElementById('lbl-' + uid);
        const row   = document.getElementById('ev-row-' + uid);
        const prog  = document.getElementById('ev-prog-' + uid);
        const bar   = document.getElementById('ev-bar-' + uid);
        const err   = document.getElementById('ev-err-' + uid);

        const badSz = files.filter(f => f.size > MAX_BYTES);
        if (badSz.length) { showErr(err,'❌ Melebihi 2 MB: '+badSz.map(f=>f.name).join(', ')); inp.value=''; return; }
        const badTy = files.filter(f => !ALLOWED.includes(f.type));
        if (badTy.length) { showErr(err,'❌ Format tidak didukung (jpg/png/webp/pdf)'); inp.value=''; return; }
        hideErr(err);

        if (lbl)  lbl.classList.add('busy');
        if (prog) prog.classList.add('on');

        let done = 0;
        for (const file of files) {
            const fd = new FormData();
            fd.append('kpi_report_detail_id', uid);
            fd.append('file', file);
            try {
                const res  = await fetch(url, {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}, body:fd});
                const data = await res.json();
                if (data.success) {
                    const ev    = data.evidence;
                    const isPdf = (ev.type||'').includes('pdf');
                    const delUrl= '{{ route("kpi-hsse.evidences.delete", [$kpiReport, "__EID__"]) }}'.replace('__EID__',ev.id);
                    const wrap  = document.createElement('div');
                    wrap.className='ev-wrap'; wrap.id='ev-item-'+ev.id;
                    wrap.innerHTML = isPdf
                        ? `<a href="${ev.url}" target="_blank" class="ev-pdf-thumb" title="${esc(ev.name)}">📄</a>
                           <div class="ev-del" onclick="doDelEv('${ev.id}','${delUrl}','${uid}')">×</div>`
                        : `<img class="ev-img" src="${ev.url}" onclick="previewImg('${ev.url}','${esc(ev.name)}')" title="${esc(ev.name)}">
                           <div class="ev-del" onclick="doDelEv('${ev.id}','${delUrl}','${uid}')">×</div>`;
                    if (row) row.appendChild(wrap);
                    if (isLag) updateLagBadges();
                    toast('📎 File terupload', 'ok');
                } else {
                    showErr(err,'❌ '+(data.message||'Upload gagal'));
                }
            } catch(e) { showErr(err,'❌ Koneksi error'); }
            done++;
            if (bar) bar.style.width = Math.round(done/files.length*100)+'%';
        }

        refreshEvStatus(uid, row);
        setTimeout(() => {
            if (prog) { prog.classList.remove('on'); if(bar) bar.style.width='0%'; }
            if (lbl)  lbl.classList.remove('busy');
        }, 700);
        inp.value = '';
    }

    function refreshEvStatus(uid, row) {
        const st = document.getElementById('ev-st-'+uid);
        if (!st) return;
        const n = row ? row.querySelectorAll('.ev-wrap').length : 0;
        st.innerHTML = n>0 ? `<span class="ev-st-ok">${n} file</span>` : `<span class="ev-st-req">Wajib</span>`;
    }

    function updateLagBadges() {
        const row = document.getElementById('ev-row-'+LAG_ANCHOR);
        const n   = row ? row.querySelectorAll('.ev-wrap').length : 0;
        document.querySelectorAll('.r-kpi[data-section="lagging"]').forEach(tr => {
            const no = parseInt(tr.dataset.itemNo);
            if (no < 2 || no > 5) return;
            const b = tr.querySelector('.ev-st-req, .ev-st-ok');
            if (b) { b.className = n>0?'ev-st-ok':'ev-st-req'; b.textContent = n>0?n+' file':'Belum ada'; }
        });
    }

    async function doDelEv(evId, url, uid) {
        if (!confirm('Hapus lampiran ini?')) return;
        try {
            const res  = await fetch(url, {method:'DELETE', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'}});
            const data = await res.json();
            if (data.success) {
                document.getElementById('ev-item-'+evId)?.remove();
                refreshEvStatus(uid, document.getElementById('ev-row-'+uid));
                updateLagBadges();
            } else { alert(data.message||'Gagal hapus.'); }
        } catch(e) { alert('Koneksi error.'); }
    }

    // ── HSSE Score AJAX ──────────────────────────────────────────────────────
    @if($canEditHsse)
        async function doUpdateScore(itemId) {
            const inp = document.getElementById('nilai-'+itemId);
            if (!inp) return;
            const nilai = parseFloat(inp.value);
            if (isNaN(nilai)||nilai<0||nilai>100) { inp.style.borderColor='#ef4444'; return; }
            inp.style.borderColor='';
            try {
                const fd = new FormData();
                fd.append('kpi_item_id', itemId);
                fd.append('nilai', nilai);
                const res  = await fetch(SCORE_URL, {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}, body:fd});
                const data = await res.json();
                if (data.success) {
                    const scEl = document.getElementById('sc-'+itemId);
                    if (scEl) {
                        scEl.textContent = parseFloat(data.detail.score).toFixed(2);
                        const n = parseFloat(data.detail.nilai);
                        scEl.className = 'td-score '+(n>=90?'s-exc':n>=75?'s-good':n>=60?'s-fair':'s-poor');
                    }
                    if (data.totals) updateTotals(data.totals);
                    toast('✅ Nilai tersimpan','ok');
                } else { toast('❌ '+(data.message||'Gagal'),'err'); }
            } catch(e) { toast('❌ Koneksi error','err'); }
        }

        async function saveAllNilai() {
            const inputs = document.querySelectorAll('[id^="nilai-"]:not(:disabled)');
            let saved = 0;
            for (const inp of inputs) {
                if (inp.value !== '') { await doUpdateScore(inp.id.replace('nilai-','')); saved++; }
            }
            toast(saved>0 ? `✅ ${saved} nilai disimpan` : 'Tidak ada nilai baru', saved>0?'ok':'err');
        }
    @else
        function doUpdateScore() {}
        function saveAllNilai() {}
    @endif

    function updateTotals(t) {
        const lag   = parseFloat(t.lagging||0).toFixed(2);
        const lead  = parseFloat(t.leading||0).toFixed(2);
        const total = parseFloat(t.total  ||0).toFixed(2);
        const map   = {'t-lag':lag,'t-lead':lead,'t-total':total,'sb-lag':lag,'sb-lead':lead,'sb-val':parseFloat(total).toFixed(1)};
        Object.entries(map).forEach(([id,v])=>{ const el=document.getElementById(id); if(el) el.textContent=v; });
        const bar = document.getElementById('sb-bar');
        if (bar) bar.style.width = Math.min(parseFloat(total),100)+'%';
    }

    // ── Vessel AJAX ──────────────────────────────────────────────────────────
    async function doSaveVessel(id, url) {
        const fd = new FormData();
        fd.append('_method','PUT');
        fd.append('vessel_name',       document.getElementById('vn-' +id)?.value||'');
        fd.append('vessel_count',      document.getElementById('vco-'+id)?.value||'');
        fd.append('contract_number',   document.getElementById('vc-' +id)?.value||'');
        fd.append('contract_end_date', document.getElementById('vd-' +id)?.value||'');
        await fetch(url, {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}, body:fd}).catch(()=>{});
    }

    async function doDelVessel(id, url) {
        if (!confirm('Hapus kapal / unit ini?')) return;
        const fd = new FormData(); fd.append('_method','DELETE');
        const res  = await fetch(url, {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}, body:fd});
        const data = await res.json();
        if (data.success) {
            document.getElementById('vrow-'+id)?.remove();
            document.querySelectorAll('#vessel-tbody tr').forEach((r,i)=>{
                const no = r.querySelector('td:first-child'); if(no) no.textContent=i+1;
            });
        } else { alert(data.message||'Gagal.'); }
    }

    async function doAddVessel() {
        const name  = prompt('Nama kapal / unit:');
        if (!name?.trim()) return;
        const count = prompt('Jumlah (contoh: 2 unit, 3 kapal):','1 unit');
        const fd = new FormData();
        fd.append('vessel_name',  name.trim());
        fd.append('vessel_count', (count||'').trim());
        const res  = await fetch('{{ route("kpi-hsse.vessels.store", $kpiReport) }}',
            {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}, body:fd});
        const data = await res.json();
        if (data.success) window.location.reload();
        else alert(data.message||'Gagal.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────
    function previewImg(url, name) {
        document.getElementById('img-src').src = url;
        document.getElementById('img-title').textContent = name||'Preview';
        new bootstrap.Modal(document.getElementById('imgModal')).show();
    }
    function showErr(el,msg){ if(el){el.textContent=msg;el.classList.add('on');} }
    function hideErr(el)    { if(el){el.textContent='';el.classList.remove('on');} }
    function esc(s){ return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    </script>
@endpush