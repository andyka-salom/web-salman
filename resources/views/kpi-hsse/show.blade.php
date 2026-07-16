{{-- resources/views/kpi-hsse/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Detail KPI HSSE — ' . ($kpiReport->kpiPeriod->label ?? ''))

@push('styles')
<style>
/* ─────────────────────────────────────────────
   TOKENS
───────────────────────────────────────────── */
:root{
    --navy:#1e3a5f; --blue:#2563eb; --blue2:#1e40af;
    --ok:#166534; --bg-ok:#dcfce7; --br-ok:#86efac;
    --rej:#991b1b; --bg-rej:#fee2e2; --br-rej:#fca5a5;
    --mark:#92400e; --bg-mark:#fef9c3; --br-mark:#fde68a;
    --radius:10px;
}

/* ─────────────────────────────────────────────
   STATUS CHIPS
───────────────────────────────────────────── */
.s-chip{display:inline-flex;align-items:center;gap:5px;padding:4px 13px;
    border-radius:20px;font-weight:700;font-size:.74rem;}
.s-draft    {background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;}
.s-submitted{background:#dbeafe;color:var(--blue2);border:1px solid #93c5fd;}
.s-validated{background:var(--bg-ok);color:var(--ok);border:1px solid var(--br-ok);}
.s-rejected {background:var(--bg-rej);color:var(--rej);border:1px solid var(--br-rej);}

/* ─────────────────────────────────────────────
   SCORE RING
───────────────────────────────────────────── */
.ring{border-radius:50%;border:5px solid;display:flex;flex-direction:column;
    align-items:center;justify-content:center;font-weight:800;flex-shrink:0;}
.ring.none     {border-color:#94a3b8;color:#94a3b8;background:#f1f5f9;}
.ring.excellent{border-color:#059669;color:#047857;background:#d1fae5;}
.ring.good     {border-color:var(--blue);color:var(--blue2);background:#dbeafe;}
.ring.fair     {border-color:#d97706;color:#b45309;background:#fef3c7;}
.ring.poor     {border-color:#dc2626;color:#b91c1c;background:#fee2e2;}

.ring-sm{border-radius:50%;border:3px solid;display:flex;align-items:center;
    justify-content:center;font-weight:800;font-size:.74rem;flex-shrink:0;}
.ring-sm.none     {border-color:#94a3b8;color:#94a3b8;background:#f1f5f9;}
.ring-sm.excellent{border-color:#059669;color:#047857;background:#d1fae5;}
.ring-sm.good     {border-color:var(--blue);color:var(--blue2);background:#dbeafe;}
.ring-sm.fair     {border-color:#d97706;color:#b45309;background:#fef3c7;}
.ring-sm.poor     {border-color:#dc2626;color:#b91c1c;background:#fee2e2;}

/* ─────────────────────────────────────────────
   LAYOUT
───────────────────────────────────────────── */
.show-layout{display:grid;grid-template-columns:1fr 280px;gap:1.5rem;align-items:start;}
@media(max-width:991px){.show-layout{grid-template-columns:1fr;}}

/* ─────────────────────────────────────────────
   SECTION HEADER
───────────────────────────────────────────── */
.sec-bar{display:flex;align-items:center;justify-content:space-between;
    padding:.55rem 1rem;border-radius:8px;font-weight:800;font-size:.8rem;
    letter-spacing:.3px;margin-bottom:.5rem;}
.sec-bar.lag{background:linear-gradient(90deg,#fef3c7,#fde68a);color:#78350f;}
.sec-bar.lead{background:linear-gradient(90deg,#dbeafe,#bfdbfe);color:var(--blue2);}
.sec-bar .pill{background:rgba(0,0,0,.14);padding:2px 10px;border-radius:20px;font-size:.7rem;}

/* ─────────────────────────────────────────────
   KPI CARDS
───────────────────────────────────────────── */
.kc{background:#fff;border:1px solid #e2e8f0;border-radius:var(--radius);
    margin-bottom:.55rem;overflow:hidden;transition:box-shadow .18s;}
.kc:hover{box-shadow:0 4px 18px rgba(0,0,0,.08);}
.kc.kc-ok  {border-left:4px solid #22c55e;background:#fdfffe;}
.kc.kc-rej {border-left:4px solid #ef4444;background:#fffafa;}
.kc.kc-mk  {border-right:3px solid #f59e0b;}
.kc.kc-ar  {background:#fafafa;}

/* Card header */
.kc-hdr{display:flex;align-items:flex-start;gap:.7rem;padding:.65rem .9rem .55rem;
    cursor:pointer;user-select:none;transition:background .12s;}
.kc-hdr:hover{background:#f9fafb;}

/* No badge */
.no-b{width:28px;height:28px;border-radius:7px;flex-shrink:0;display:flex;
    align-items:center;justify-content:center;font-weight:800;font-size:.74rem;}
.no-b.lag{background:#fef3c7;color:#78350f;}
.no-b.lead{background:#dbeafe;color:var(--blue2);}
.no-b.ar{background:#f1f5f9;color:#94a3b8;}

/* Item title / target */
.i-ttl{font-weight:600;color:#0f172a;font-size:.86rem;line-height:1.3;}
.i-tgt{font-size:.7rem;color:#64748b;margin-top:2px;}
.i-mini-tag{display:inline-flex;align-items:center;gap:3px;padding:1px 7px;
    border-radius:5px;font-weight:700;font-size:.63rem;}
.mt-ok  {background:var(--bg-ok);color:var(--ok);}
.mt-rej {background:var(--bg-rej);color:var(--rej);}
.mt-ar  {background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;}
.mt-mk  {background:var(--bg-mark);color:var(--mark);}

/* Chevron */
.chev{transition:transform .22s;color:#cbd5e1;flex-shrink:0;margin-top:2px;}
.chev.open{transform:rotate(180deg);}

/* Card body */
.kc-body{border-top:1px solid #f3f4f6;display:none;}

/* Data row */
.dr{display:flex;gap:.6rem;padding:.4rem .9rem;border-bottom:1px dashed #f3f4f6;
    font-size:.81rem;align-items:flex-start;}
.dr:last-child{border-bottom:none;}
.dr-l{font-weight:700;color:#94a3b8;width:88px;flex-shrink:0;font-size:.7rem;
    padding-top:2px;text-transform:uppercase;letter-spacing:.3px;}
.dr-v{color:#1e293b;flex:1;line-height:1.45;}

/* Evidence thumbs */
.ev-grid{display:flex;flex-wrap:wrap;gap:5px;margin-top:3px;}
.ev-t{width:46px;height:46px;object-fit:cover;border-radius:6px;
    border:2px solid #e2e8f0;cursor:pointer;transition:transform .15s,border-color .15s;}
.ev-t:hover{transform:scale(1.1);border-color:#6366f1;}

/* ─────────────────────────────────────────────
   REVIEW ZONE
───────────────────────────────────────────── */
.rv-zone{background:#f0f7ff;border-top:2px dashed #bfdbfe;padding:.85rem .9rem 1rem;}
.rv-title{font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.5px;
    color:var(--blue2);margin-bottom:.75rem;display:flex;align-items:center;gap:5px;}

.nilai-inp{border:2px solid #bfdbfe;border-radius:7px;padding:5px 8px;font-size:1rem;
    font-weight:700;text-align:center;background:#fff;outline:none;width:88px;
    transition:border-color .15s,box-shadow .15s;}
.nilai-inp:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(37,99,235,.12);}
.nilai-inp.saved{border-color:#22c55e;background:#f0fdf4;}

.cat-inp{width:100%;border:1.5px solid #bfdbfe;border-radius:7px;padding:6px 9px;
    font-size:.79rem;outline:none;resize:none;background:#fff;transition:border-color .15s;}
.cat-inp:focus{border-color:var(--blue);box-shadow:0 0 0 2px rgba(37,99,235,.1);}
.cat-inp.saved{border-color:#22c55e;}

.dec-btn{flex:1;padding:7px 8px;border-radius:8px;font-size:.79rem;font-weight:700;
    cursor:pointer;border:2px solid;transition:all .15s;display:flex;
    align-items:center;justify-content:center;gap:5px;}
.dec-ok {background:#f0fdf4;color:var(--ok);border-color:var(--br-ok);}
.dec-ok:hover,.dec-ok.active{background:#22c55e;color:#fff;border-color:#16a34a;}
.dec-rej{background:var(--bg-rej);color:var(--rej);border-color:var(--br-rej);}
.dec-rej:hover,.dec-rej.active{background:#ef4444;color:#fff;border-color:#dc2626;}

.cmt-box{width:100%;border:1.5px solid var(--br-rej);border-radius:7px;padding:6px 9px;
    font-size:.79rem;outline:none;resize:none;background:var(--bg-rej);
    transition:border-color .15s;margin-top:6px;}
.cmt-box.err{border-color:#ef4444 !important;}
.cmt-err{font-size:.7rem;color:#dc2626;margin-top:3px;display:none;}

.mark-btn{padding:5px 12px;border-radius:7px;font-size:.76rem;font-weight:700;
    cursor:pointer;border:2px solid var(--br-mark);background:var(--bg-mark);
    color:var(--mark);transition:all .15s;}
.mark-btn:hover,.mark-btn.on{background:#f59e0b;color:#fff;border-color:#d97706;}
.mark-note-inp{width:100%;border:1.5px solid var(--br-mark);border-radius:6px;
    padding:5px 8px;font-size:.76rem;outline:none;resize:none;background:#fffde7;margin-top:5px;}

.dec-chip{display:inline-flex;align-items:center;gap:4px;padding:2px 10px;
    border-radius:6px;font-weight:700;font-size:.74rem;}
.dc-ok {background:var(--bg-ok);color:var(--ok);}
.dc-rej{background:var(--bg-rej);color:var(--rej);}

/* ─────────────────────────────────────────────
   FILTER CHIPS
───────────────────────────────────────────── */
.fc{display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:20px;
    border:1px solid #e2e8f0;background:#f8fafc;font-size:.74rem;cursor:pointer;
    transition:all .15s;user-select:none;}
.fc:hover{background:#f1f5f9;}
.fc.on{background:var(--navy);color:#fff;border-color:var(--navy);}
.fc.on-rej{background:var(--rej);color:#fff;border-color:var(--rej);}
.fc.on-mk {background:#d97706;color:#fff;border-color:#b45309;}

/* ─────────────────────────────────────────────
   SIDEBAR CARDS
───────────────────────────────────────────── */
.sb{background:#fff;border-radius:var(--radius);border:1px solid #e2e8f0;
    padding:1.1rem;box-shadow:0 1px 5px rgba(0,0,0,.05);}
.sb+.sb{margin-top:1rem;}
.sb-ttl{font-size:.67rem;font-weight:800;text-transform:uppercase;
    letter-spacing:.6px;color:#94a3b8;margin-bottom:.85rem;}

/* Progress bar */
.prog{height:5px;border-radius:3px;background:#f1f5f9;overflow:hidden;}
.prog-f{height:100%;border-radius:3px;transition:width .5s;}

/* Stat pill */
.stat-p{text-align:center;padding:.5rem .4rem;border-radius:8px;}

/* Timeline */
.tl-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;margin-top:4px;}
.tl-dot.draft    {background:#94a3b8;}
.tl-dot.submitted{background:var(--blue);}
.tl-dot.validated{background:#22c55e;}
.tl-dot.rejected {background:#ef4444;}

/* Vessel chip */
.vc{background:#f0f4ff;border:1px solid #c7d2fe;border-radius:8px;
    padding:.5rem .75rem;margin-bottom:.45rem;}

@media(min-width:992px){.sticky-sb{position:sticky;top:1.25rem;}}
</style>
@endpush

@section('content')
@php
    $isHsse    = $user->hasAnyRole(['hsse','super-admin']);
    $isKoord   = $user->hasRole('koordinator');
    $canReview = $isHsse && $kpiReport->status === 'submitted';

    $ts   = (float)$kpiReport->total_score;
    $scls = $ts === 0 ? 'none'
          : ($ts >= 90 ? 'excellent' : ($ts >= 75 ? 'good' : ($ts >= 60 ? 'fair' : 'poor')));
    $scol = ['none'=>'#94a3b8','excellent'=>'#059669','good'=>'#2563eb','fair'=>'#d97706','poor'=>'#dc2626'][$scls];

    $mCnt   = $kpiReport->details->where('is_marked', true)->count();
    $rejCnt = $kpiReport->details->where('review_status', 'rejected')->count();

    /* Build unified item list from DB */
    $allItems = collect();
    foreach (['lagging','leading'] as $sec) {
        $items = $kpiItems->get($sec, collect());
        foreach ($items as $item) {
            $det = $kpiReport->details->first(
                fn($d) => $d->kpiItem && $d->kpiItem->section === $sec && $d->kpiItem->item_no == $item->item_no
            );
            $nameParts = explode("\n", $item->name, 2);
            $allItems->push([
                'sec'    => $sec,
                'no'     => $item->item_no,
                'name'   => trim($nameParts[0]),
                'target' => isset($nameParts[1]) ? trim($nameParts[1]) : '',
                'guide'  => $item->guidance ?? '',
                'unit'   => $item->unit_label ?? ($item->unit ?? '∑'),
                'bobot'  => (float)$item->bobot,
                'scored' => (bool)$item->is_scored,
                'det'    => $det,
                'kId'    => $det?->kpi_item_id ?? '',
                'dId'    => $det?->id ?? '',
            ]);
        }
    }
    $totalItems = $allItems->count();
@endphp

<div class="layout-px-spacing">
<div class="middle-content container-xxl p-0">
<div class="row layout-top-spacing">
<div class="col-12">

{{-- ── PAGE HEADER ────────────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
            <h2 class="fw-bold mb-0">Detail KPI HSSE</h2>
            <span class="s-chip s-{{ $kpiReport->status }}">
                {{ ['draft'=>'📝','submitted'=>'⏳','validated'=>'✅','rejected'=>'❌'][$kpiReport->status] ?? '' }}
                {{ strtoupper($kpiReport->status) }}
            </span>
            @if($mCnt > 0)
                <span class="badge text-dark fw-bold" style="background:#fef9c3;border:1px solid #fde68a;font-size:.7rem;">⚑ {{ $mCnt }}</span>
            @endif
        </div>
        <p class="text-muted mb-0" style="font-size:.82rem;">
            <strong>{{ $kpiReport->company->name ?? '-' }}</strong>
            &bull; {{ $kpiReport->kpiPeriod->label ?? '-' }}
            @if($kpiReport->submittedBy)
                &bull; Disubmit: <strong>{{ $kpiReport->submittedBy->name }}</strong>
            @endif
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if($isKoord && $kpiReport->canBeEdited())
            <a href="{{ route('kpi-hsse.edit', $kpiReport) }}" class="btn btn-warning btn-sm fw-bold rounded-pill px-3">✏️ Edit KPI</a>
        @endif
        @if($isKoord && $kpiReport->canBeSubmitted())
            <button class="btn btn-success btn-sm fw-bold rounded-pill px-3"
                    data-bs-toggle="modal" data-bs-target="#submitModal">🚀 Submit</button>
        @endif
        <a href="{{ route('kpi-hsse.export', $kpiReport) }}" class="btn btn-outline-success btn-sm rounded-pill px-3">📥 Excel</a>
        <a href="{{ route('kpi-hsse.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">← Kembali</a>
    </div>
</div>

{{-- ── ALERTS ─────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3">✅ {{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-3">{{ session('error') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if($canReview)
<div class="alert alert-info d-flex gap-2 align-items-start mb-3 py-2 px-3" style="font-size:.81rem;border-radius:10px;">
    <span style="font-size:1.1rem;flex-shrink:0;">🔍</span>
    <div><strong>Mode Review HSSE.</strong> Buka item → isi <strong>Nilai</strong> → tulis <strong>Catatan</strong> → pilih <strong>Setujui / Tolak</strong> → klik <strong>"Kirim Semua Keputusan"</strong>.</div>
</div>
@endif
@if($kpiReport->status === 'rejected' && $isKoord)
<div class="alert alert-danger d-flex justify-content-between align-items-center gap-3 mb-3 py-2 px-3" style="font-size:.81rem;border-radius:10px;">
    <div>❌ <strong>{{ $rejCnt }} item ditolak.</strong> Perbaiki item merah lalu submit ulang.</div>
    <a href="{{ route('kpi-hsse.edit', $kpiReport) }}" class="btn btn-danger btn-sm fw-bold rounded-pill">Perbaiki →</a>
</div>
@endif
@if($kpiReport->status === 'validated')
<div class="alert alert-success d-flex align-items-center gap-2 mb-3 py-2 px-3" style="font-size:.81rem;border-radius:10px;">
    🏆 <div>Tervalidasi oleh <strong>{{ $kpiReport->validatedBy->name ?? '-' }}</strong>
    · {{ $kpiReport->validated_at?->format('d M Y, H:i') }}
    @if($mCnt) <span class="badge ms-2" style="background:#fef9c3;color:#92400e;border:1px solid #fde68a;font-size:.7rem;">⚑ {{ $mCnt }} perlu tindak lanjut</span> @endif
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════ --}}
<div class="show-layout">

{{-- ══ MAIN: CARDS ════════════════════════════════════════════ --}}
<div>

    {{-- Filter chips --}}
    <div class="d-flex gap-2 flex-wrap mb-3">
        <span class="fc on" id="fc-all" onclick="doFilter('all',this)">Semua ({{ $totalItems }})</span>
        <span class="fc" id="fc-lag" onclick="doFilter('lag',this)">▌ Lagging</span>
        <span class="fc" id="fc-lead" onclick="doFilter('lead',this)">▌ Leading</span>
        @if($rejCnt > 0)
            <span class="fc" id="fc-rej" onclick="doFilter('rej',this)" style="color:var(--rej);border-color:var(--br-rej);">❌ Ditolak ({{ $rejCnt }})</span>
        @endif
        @if($mCnt > 0)
            <span class="fc" id="fc-mk" onclick="doFilter('mk',this)" style="color:var(--mark);border-color:var(--br-mark);">⚑ Marked ({{ $mCnt }})</span>
        @endif
    </div>

    @if($canReview)
    <form id="rv-form" action="{{ route('kpi-hsse.review', $kpiReport) }}" method="POST">
        @csrf
        <input type="hidden" name="general_comment" id="gc-hidden">
    @endif

    @php $ri = 0; $curSec = ''; @endphp

    @foreach($allItems as $item)
    @php
        $det    = $item['det'];
        $sec    = $item['sec'];
        $isAS   = !$item['scored'];
        $rs     = $det?->review_status ?? '';
        $isMk   = (bool)($det?->is_marked ?? false);
        $score  = (float)($det?->score ?? 0);
        $nilai  = (float)($det?->nilai ?? 0);
        $sc2    = $score > 0 ? ($nilai >= 90 ? 'excellent' : ($nilai >= 75 ? 'good' : ($nilai >= 60 ? 'fair' : 'poor'))) : 'none';
        $kId    = $item['kId'];
        $dId    = $item['dId'];
        $curRi  = $ri++;

        $cardCls = trim(
            ($rs === 'approved' ? 'kc-ok ' : '') .
            ($rs === 'rejected' ? 'kc-rej ' : '') .
            ($isMk ? 'kc-mk ' : '') .
            ($isAS ? 'kc-ar' : '')
        );
        $noBadge = $isAS ? 'ar' : ($sec === 'lagging' ? 'lag' : 'lead');
    @endphp

    {{-- Section divider --}}
    @if($sec !== $curSec) @php $curSec = $sec; @endphp
    <div class="sec-bar {{ $sec === 'lagging' ? 'lag' : 'lead' }} {{ $sec === 'leading' ? 'mt-4' : '' }}"
         data-sec="{{ $sec }}">
        {{ $sec === 'lagging' ? 'SECTION 1 — LAGGING INDICATOR' : 'SECTION 2 — LEADING INDICATOR' }}
        <span class="pill">{{ $sec === 'lagging' ? '40%' : '60%' }}</span>
    </div>
    @endif

    <div class="kc {{ $cardCls }}"
         data-sec="{{ $sec }}"
         data-rs="{{ $rs }}"
         data-mk="{{ $isMk ? '1' : '0' }}"
         id="kc-{{ $kId }}">

        {{-- Card header (clickable) --}}
        <div class="kc-hdr" onclick="toggleCard(this)">
            <div class="no-b {{ $noBadge }}">{{ $item['no'] }}</div>
            <div style="flex:1;min-width:0;">
                <div class="i-ttl">{{ $item['name'] }}</div>
                @if($item['target'])
                    <div class="i-tgt">🎯 {{ $item['target'] }}</div>
                @endif
                <div class="d-flex gap-1 flex-wrap mt-1">
                    @if($isAS)              <span class="i-mini-tag mt-ar">As Reported</span> @endif
                    @if($rs === 'approved') <span class="i-mini-tag mt-ok">✓ Disetujui</span> @endif
                    @if($rs === 'rejected') <span class="i-mini-tag mt-rej">✗ Ditolak</span> @endif
                    @if($isMk)             <span class="i-mini-tag mt-mk">⚑ Marked</span> @endif
                </div>
            </div>
            <div class="d-flex flex-column align-items-center gap-1">
                <div class="ring-sm {{ $sc2 }}" style="width:36px;height:36px;"
                     id="pill-{{ $kId }}">
                    @if($isAS) — @elseif($score > 0) {{ number_format($score, 1) }} @else — @endif
                </div>
                @if(!$isAS)
                    <div style="font-size:.6rem;color:#cbd5e1;">{{ number_format($item['bobot'] * 100, 0) }}%</div>
                @endif
                <svg class="chev" xmlns="http://www.w3.org/2000/svg" width="13" height="13"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </div>
        </div>

        {{-- Card body --}}
        <div class="kc-body">
            <div class="dr">
                <div class="dr-l">Panduan</div>
                <div class="dr-v" style="color:#64748b;font-size:.78rem;">{{ $item['guide'] }}</div>
            </div>
            <div class="dr">
                <div class="dr-l">∑ / %</div>
                <div class="dr-v">
                    <strong>{{ $det?->actual_count ?? '—' }}</strong>
                    <span style="font-size:.7rem;color:#94a3b8;margin-left:4px;">{{ $item['unit'] }}</span>
                </div>
            </div>
            <div class="dr">
                <div class="dr-l">Keterangan</div>
                <div class="dr-v">{{ $det?->keterangan ?: '—' }}</div>
            </div>
            @if($det && $det->evidences->count())
            <div class="dr">
                <div class="dr-l">Lampiran ({{ $det->evidences->count() }})</div>
                <div class="dr-v">
                    <div class="ev-grid">
                        @foreach($det->evidences as $ev)
                        @php $isPdf = str_contains($ev->file_type ?? '', 'pdf'); @endphp
                        @if($isPdf)
                            <a href="{{ Storage::url($ev->file_path) }}" target="_blank"
                               style="width:46px;height:46px;border-radius:6px;border:2px solid #bfdbfe;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:18px;text-decoration:none;"
                               title="{{ $ev->file_name }}">📄</a>
                        @else
                            <img class="ev-t"
                                 src="{{ Storage::url($ev->file_path) }}"
                                 onclick="previewImg('{{ Storage::url($ev->file_path) }}','{{ e($ev->file_name) }}')"
                                 title="{{ $ev->caption ?? $ev->file_name }}">
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- ── REVIEW ZONE (HSSE) ── --}}
            @if($canReview)
            <div class="rv-zone">
                <div class="rv-title">
                    🔍 Penilaian &amp; Keputusan HSSE
                </div>

                @if($item['scored'])
                <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
                    <div>
                        <div style="font-size:.68rem;color:#64748b;font-weight:700;margin-bottom:3px;">NILAI (0–100)</div>
                        <input type="number" class="nilai-inp"
                               id="nilai-{{ $kId }}"
                               value="{{ $nilai > 0 ? number_format($nilai, 2, '.', '') : '' }}"
                               min="0" max="100" step="0.01"
                               placeholder="0–100"
                               data-item="{{ $kId }}"
                               data-url="{{ route('kpi-hsse.score.update', $kpiReport) }}"
                               onchange="saveNilai(this)">
                    </div>
                    <span style="color:#e2e8f0;font-size:1.2rem;">→</span>
                    <div>
                        <div style="font-size:.68rem;color:#64748b;font-weight:700;margin-bottom:3px;">SCORE</div>
                        <div class="ring-sm {{ $sc2 }}" style="width:46px;height:46px;font-size:.84rem;"
                             id="pill2-{{ $kId }}">
                            {{ $score > 0 ? number_format($score, 2) : '—' }}
                        </div>
                    </div>
                    <div style="font-size:.72rem;color:#94a3b8;line-height:1.6;">
                        Bobot: <strong>{{ number_format($item['bobot'] * 100, 1) }}%</strong><br>
                        Score = Nilai × Bobot
                    </div>
                </div>
                @else
                <div class="mb-3 py-2 px-3 rounded" style="background:#f8fafc;border:1px dashed #e2e8f0;font-size:.78rem;color:#94a3b8;">
                    ℹ️ Item <strong>As Reported</strong> — tidak perlu nilai.
                </div>
                @endif

                <div class="mb-3">
                    <div style="font-size:.68rem;color:#64748b;font-weight:700;margin-bottom:3px;">
                        CATATAN VERIFIKASI <span style="color:#ef4444;">(wajib jika Tolak)</span>
                    </div>
                    <textarea class="cat-inp" rows="2"
                              id="cat-{{ $dId }}"
                              name="items[{{ $curRi }}][comment]"
                              placeholder="Catatan untuk koordinator..."
                              data-detail="{{ $dId }}"
                              data-url="{{ route('kpi-hsse.catatan.update', $kpiReport) }}"
                              onblur="saveCat(this)">{{ $det?->hsse_catatan }}</textarea>
                    <div class="cmt-err" id="cmt-err-{{ $curRi }}">⚠ Catatan wajib diisi jika item ditolak.</div>
                </div>

                <div class="d-flex gap-2 align-items-start mb-3 flex-wrap">
                    <div>
                        <div style="font-size:.68rem;color:#64748b;font-weight:700;margin-bottom:4px;">TANDA PERHATIAN</div>
                        <button type="button"
                                class="mark-btn {{ $isMk ? 'on' : '' }}"
                                id="mk-btn-{{ $dId }}"
                                onclick="toggleMark('{{ $dId }}','{{ route('kpi-hsse.mark.toggle', $kpiReport) }}',this,'{{ $kId }}',{{ $curRi }})">
                            ⚑ {{ $isMk ? 'Marked (Aktif)' : 'Tandai' }}
                        </button>
                    </div>
                    <div style="flex:1;min-width:130px;">
                        <textarea class="mark-note-inp {{ $isMk ? '' : 'd-none' }}"
                                  id="mk-note-{{ $dId }}" rows="2"
                                  placeholder="Catatan mark..."
                                  onblur="saveMkNote('{{ $dId }}','{{ route('kpi-hsse.mark.toggle', $kpiReport) }}',this,{{ $curRi }})">{{ $det?->mark_note }}</textarea>
                    </div>
                </div>

                <div>
                    <div style="font-size:.68rem;color:#64748b;font-weight:700;margin-bottom:5px;">KEPUTUSAN</div>
                    <div class="d-flex gap-2">
                        <button type="button"
                                class="dec-btn dec-ok {{ $rs === 'approved' ? 'active' : '' }}"
                                id="btn-ap-{{ $curRi }}"
                                onclick="setDec({{ $curRi }},'approved')">
                            ✓ Setujui
                        </button>
                        <button type="button"
                                class="dec-btn dec-rej {{ $rs === 'rejected' ? 'active' : '' }}"
                                id="btn-rj-{{ $curRi }}"
                                onclick="setDec({{ $curRi }},'rejected')">
                            ✗ Tolak
                        </button>
                    </div>
                    <input type="hidden" name="items[{{ $curRi }}][detail_id]" value="{{ $dId }}">
                    <input type="hidden" name="items[{{ $curRi }}][decision]" id="dec-{{ $curRi }}" value="{{ $rs }}">
                    <input type="hidden" name="items[{{ $curRi }}][is_marked]" id="mk-h-{{ $curRi }}" value="{{ $isMk ? '1' : '0' }}">
                    <input type="hidden" name="items[{{ $curRi }}][mark_note]" id="mkn-h-{{ $curRi }}" value="{{ $det?->mark_note ?? '' }}">
                </div>
            </div>

            @elseif($rs || $det?->hsse_catatan || $isMk)
            {{-- Read-only review result --}}
            <div class="rv-zone" style="background:#f8fafc;border-top-color:#e2e8f0;">
                <div class="rv-title" style="color:#64748b;">📋 Hasil Review</div>
                @if(!$isAS && $nilai > 0)
                <div class="dr" style="padding:0;border:none;margin-bottom:.35rem;">
                    <div class="dr-l">Nilai / Score</div>
                    <div class="dr-v">
                        {{ number_format($nilai, 1) }}
                        @if($score > 0)
                            <span class="ms-2" style="background:#dbeafe;color:var(--blue2);border-radius:4px;padding:1px 7px;font-size:.73rem;font-weight:700;">Score: {{ number_format($score, 2) }}</span>
                        @endif
                    </div>
                </div>
                @endif
                @if($rs)
                <div class="dr" style="padding:0;border:none;margin-bottom:.35rem;">
                    <div class="dr-l">Keputusan</div>
                    <div class="dr-v"><span class="dec-chip {{ $rs === 'approved' ? 'dc-ok' : 'dc-rej' }}">{{ $rs === 'approved' ? '✓ Disetujui' : '✗ Ditolak' }}</span></div>
                </div>
                @endif
                @if($det?->hsse_catatan)
                <div class="dr" style="padding:0;border:none;margin-bottom:.35rem;">
                    <div class="dr-l">Catatan</div>
                    <div class="dr-v" style="font-style:italic;color:#475569;">"{{ $det->hsse_catatan }}"</div>
                </div>
                @endif
                @if($isMk)
                <div class="dr" style="padding:0;border:none;">
                    <div class="dr-l">Mark</div>
                    <div class="dr-v">
                        <span style="background:var(--bg-mark);color:var(--mark);border:1px solid var(--br-mark);border-radius:5px;padding:1px 8px;font-size:.72rem;font-weight:700;">⚑ Perlu Perhatian</span>
                        @if($det?->mark_note) <span class="text-muted ms-2" style="font-size:.74rem;">{{ $det->mark_note }}</span> @endif
                    </div>
                </div>
                @endif
            </div>
            @endif

        </div>
    </div>
    @endforeach

    @if($canReview)</form>@endif

    {{-- Total score bar --}}
    <div class="sb mt-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;">📈 Total Score</div>
            <div class="d-flex align-items-center gap-4">
                <div class="text-center">
                    <div style="font-size:.65rem;color:#94a3b8;font-weight:700;">LAGGING</div>
                    <div class="fw-bold" style="color:#d97706;font-size:1.2rem;" id="tot-lag">{{ number_format($kpiReport->total_score_lagging, 2) }}</div>
                </div>
                <div class="text-center px-4" style="border-left:1px solid #e2e8f0;border-right:1px solid #e2e8f0;">
                    <div style="font-size:.65rem;color:#94a3b8;font-weight:700;">TOTAL</div>
                    <div class="fw-bold" style="font-size:2rem;color:{{ $scol }};" id="tot-all">{{ number_format($ts, 2) }}</div>
                </div>
                <div class="text-center">
                    <div style="font-size:.65rem;color:#94a3b8;font-weight:700;">LEADING</div>
                    <div class="fw-bold" style="color:var(--blue);font-size:1.2rem;" id="tot-lead">{{ number_format($kpiReport->total_score_leading, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="prog mt-3">
            <div class="prog-f" id="tot-prog" style="width:{{ min($ts,100) }}%;background:{{ $scol }};"></div>
        </div>
        <div class="d-flex justify-content-between mt-1" style="font-size:.65rem;color:#e2e8f0;">
            <span>0</span><span>50</span><span>100</span>
        </div>
    </div>

</div>{{-- /main --}}

{{-- ══ SIDEBAR ════════════════════════════════════════════════ --}}
<div>
<div class="sticky-sb d-flex flex-column gap-0">

    {{-- Score overview --}}
    <div class="sb">
        <div class="sb-ttl">Score</div>
        <div class="text-center mb-3">
            <div class="ring {{ $scls }} mx-auto" style="width:72px;height:72px;">
                <span style="font-size:1.2rem;" id="sb-ring-val">{{ $ts > 0 ? number_format($ts,1) : '—' }}</span>
                <span style="font-size:.5rem;opacity:.6;">/100</span>
            </div>
        </div>
        <div class="row g-2 mb-2">
            <div class="col-6">
                <div class="stat-p" style="background:#fef9c3;">
                    <div class="fw-bold" style="color:#d97706;font-size:1rem;" id="sb-lag">{{ number_format($kpiReport->total_score_lagging, 2) }}</div>
                    <div style="font-size:.67rem;color:#92400e;">Lagging</div>
                </div>
            </div>
            <div class="col-6">
                <div class="stat-p" style="background:#dbeafe;">
                    <div class="fw-bold" style="color:var(--blue);font-size:1rem;" id="sb-lead">{{ number_format($kpiReport->total_score_leading, 2) }}</div>
                    <div style="font-size:.67rem;color:var(--blue2);">Leading</div>
                </div>
            </div>
        </div>
        <div class="prog">
            <div class="prog-f" id="sb-prog" style="width:{{ min($ts,100) }}%;background:{{ $scol }};"></div>
        </div>
        @if($mCnt)
        <div class="mt-2 text-center py-1 px-2 rounded" style="background:var(--bg-mark);border:1px solid var(--br-mark);font-size:.74rem;color:var(--mark);">
            ⚑ {{ $mCnt }} item perlu perhatian
        </div>
        @endif
    </div>

    {{-- Review progress (HSSE only) --}}
    @if($canReview)
    <div class="sb mt-3" style="border:2px solid #bfdbfe;background:#f0f7ff;">
        <div class="sb-ttl" style="color:var(--blue2);">Progress Review</div>
        <div class="row g-2 text-center mb-3">
            <div class="col-4">
                <div class="fw-bold text-success" id="cnt-ap" style="font-size:1.4rem;">0</div>
                <div style="font-size:.66rem;color:#64748b;">Setuju</div>
            </div>
            <div class="col-4">
                <div class="fw-bold text-danger" id="cnt-rj" style="font-size:1.4rem;">0</div>
                <div style="font-size:.66rem;color:#64748b;">Tolak</div>
            </div>
            <div class="col-4">
                <div class="fw-bold" style="color:#d97706;font-size:1.4rem;" id="cnt-mk">{{ $mCnt }}</div>
                <div style="font-size:.66rem;color:#64748b;">⚑ Mark</div>
            </div>
        </div>
        <div class="prog mb-1"><div class="prog-f" id="rv-prog" style="width:0%;background:#22c55e;"></div></div>
        <div style="font-size:.71rem;color:#64748b;text-align:center;margin-bottom:.85rem;" id="rv-lbl">0 / {{ $totalItems }} diputuskan</div>

        <div class="d-flex gap-2 mb-3">
            <button type="button" class="btn btn-outline-success btn-sm fw-bold flex-fill rounded-pill"
                    onclick="setAll('approved')">✓ Semua Setuju</button>
            <button type="button" class="btn btn-outline-danger btn-sm fw-bold flex-fill rounded-pill"
                    onclick="setAll('rejected')">✗ Semua Tolak</button>
        </div>
        <div class="mb-3">
            <label class="form-label" style="font-size:.74rem;font-weight:700;">Catatan Umum (opsional)</label>
            <textarea id="gc-input" class="form-control form-control-sm" rows="2"
                      placeholder="Catatan untuk koordinator..."></textarea>
        </div>
        <button type="button" class="btn btn-primary btn-sm fw-bold w-100 rounded-pill"
                onclick="submitReview()">
            🚀 Kirim Semua Keputusan
        </button>
        <div class="alert alert-warning mt-2 mb-0 py-2 px-3 d-none rounded" id="rv-warn"
             style="font-size:.78rem;">
            ⚠ Semua item harus diberi keputusan.
        </div>
    </div>
    @endif

    {{-- Vessels --}}
    <div class="sb mt-3">
        <div class="sb-ttl">Kapal / Unit ({{ $kpiReport->vessels->count() }})</div>
        @foreach($kpiReport->vessels->sortBy('sort_order') as $v)
        <div class="vc">
            <div class="fw-bold" style="color:var(--blue2);font-size:.84rem;">
                {{ $v->vessel_name }}
                @if($v->vessel_count)
                    <span class="ms-1 px-2 py-0" style="background:rgba(37,99,235,.1);color:var(--blue2);border-radius:4px;font-size:.7rem;font-weight:700;">{{ $v->vessel_count }}</span>
                @endif
            </div>
            @if($v->contract_number)
                <div style="font-size:.72rem;color:#64748b;margin-top:2px;">📄 {{ $v->contract_number }}</div>
            @endif
            @if($v->contract_end_date)
            @php $expired = $v->contract_end_date->isPast(); $near = !$expired && $v->contract_end_date->diffInDays(now()) < 30; @endphp
            <div style="font-size:.72rem;margin-top:2px;" class="{{ $expired ? 'text-danger' : ($near ? 'text-warning' : 'text-muted') }}">
                📅 s/d {{ $v->contract_end_date->format('d M Y') }}
                @if($expired) ⚠ Expired @elseif($near) ⚠ Hampir habis @endif
            </div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Timeline --}}
    <div class="sb mt-3">
        <div class="sb-ttl">Riwayat Status</div>
        @forelse($kpiReport->statusLogs as $log)
        <div class="d-flex gap-2 mb-3">
            <div class="tl-dot {{ $log->to_status }}"></div>
            <div style="flex:1;">
                <div class="d-flex justify-content-between align-items-baseline">
                    <span class="fw-semibold" style="font-size:.82rem;">{{ ucfirst($log->to_status) }}</span>
                    <span style="font-size:.68rem;color:#94a3b8;">{{ $log->acted_at->format('d M Y') }}</span>
                </div>
                <div style="font-size:.73rem;color:#64748b;">{{ $log->actedBy->name ?? '-' }}</div>
                @if($log->comment)
                    <div style="font-size:.7rem;color:#94a3b8;font-style:italic;padding-left:6px;border-left:2px solid #e2e8f0;margin-top:2px;">"{{ Str::limit($log->comment, 65) }}"</div>
                @endif
            </div>
        </div>
        @empty
        <p class="text-muted small text-center mb-0">Belum ada riwayat.</p>
        @endforelse
    </div>

    {{-- Info --}}
    <div class="sb mt-3" style="background:var(--navy);border-color:var(--navy);">
        <div class="sb-ttl" style="color:rgba(255,255,255,.4);">Info Laporan</div>
        <ul class="mb-0 ps-3" style="color:rgba(255,255,255,.82);font-size:.76rem;line-height:2;">
            <li>Dibuat: <strong>{{ $kpiReport->createdBy->name ?? '-' }}</strong></li>
            <li>Tgl: <strong>{{ $kpiReport->created_at->format('d M Y') }}</strong></li>
            @if($kpiReport->submitted_at)
                <li>Submit: <strong>{{ $kpiReport->submitted_at->format('d M Y, H:i') }}</strong></li>
            @endif
            @if($kpiReport->validated_at)
                <li>Validasi: <strong>{{ $kpiReport->validated_at->format('d M Y, H:i') }}</strong></li>
            @endif
        </ul>
    </div>

</div>{{-- /sticky-sb --}}
</div>{{-- /sidebar --}}

</div>{{-- /show-layout --}}
</div>
</div>
</div>
</div>

{{-- Submit modal (koordinator) --}}
@if($isKoord && $kpiReport->canBeSubmitted())
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
        <div class="alert alert-info py-2 px-3 mb-3" style="font-size:.79rem;">
            Pastikan semua item sudah terisi lengkap sebelum submit.
        </div>
        <textarea name="comment" class="form-control form-control-sm" rows="3"
                  placeholder="Catatan untuk HSSE (opsional)..."></textarea>
    </div>
    <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-success btn-sm fw-bold rounded-pill px-4">Kirim ke HSSE</button>
    </div>
    </form>
</div>
</div>
</div>
@endif

{{-- Image preview --}}
<div class="modal fade" id="imgModal" tabindex="-1">
<div class="modal-dialog modal-dialog-centered modal-lg">
<div class="modal-content">
    <div class="modal-header py-2 border-0">
        <h6 class="modal-title" id="imgTitle">Preview</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body text-center p-2">
        <img id="imgSrc" src="" class="img-fluid rounded" style="max-height:78vh;object-fit:contain;">
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content || '';
const TOTAL_ITEMS = {{ $totalItems }};

// ── Card toggle ──────────────────────────────────────────────────────
function toggleCard(hdr) {
    const body = hdr.nextElementSibling;
    const chev = hdr.querySelector('.chev');
    const open = body.style.display !== 'none';
    body.style.display = open ? 'none' : 'block';
    chev?.classList.toggle('open', !open);
}

document.addEventListener('DOMContentLoaded', () => {
    const isRev = {{ $canReview ? 'true' : 'false' }};
    document.querySelectorAll('.kc').forEach(card => {
        if (card.dataset.rs === 'rejected' || card.dataset.mk === '1' || isRev) {
            const h = card.querySelector('.kc-hdr');
            if (h) toggleCard(h);
        }
    });
    updateCounts();
});

// ── Filter ────────────────────────────────────────────────────────────
function doFilter(type, chip) {
    document.querySelectorAll('.fc').forEach(c => c.className = 'fc');
    chip.classList.add('on');
    document.querySelectorAll('.kc').forEach(card => {
        let show = true;
        if (type === 'lag')  show = card.dataset.sec === 'lagging';
        if (type === 'lead') show = card.dataset.sec === 'leading';
        if (type === 'rej')  show = card.dataset.rs  === 'rejected';
        if (type === 'mk')   show = card.dataset.mk  === '1';
        card.style.display = show ? '' : 'none';
    });
    ['lagging','leading'].forEach(s => {
        const h = document.querySelector(`.sec-bar.${s === 'lagging' ? 'lag' : 'lead'}`);
        const v = [...document.querySelectorAll(`.kc[data-sec="${s}"]`)].some(c => c.style.display !== 'none');
        if (h) h.style.display = v ? '' : 'none';
    });
}

// ── Save nilai (HSSE AJAX) ────────────────────────────────────────────
async function saveNilai(inp) {
    const v = parseFloat(inp.value);
    if (isNaN(v) || v < 0 || v > 100) return;
    const fd = new FormData();
    fd.append('kpi_item_id', inp.dataset.item);
    fd.append('nilai', v);
    try {
        const res  = await fetch(inp.dataset.url, { method:'POST', headers:{'X-CSRF-TOKEN': CSRF}, body: fd });
        const data = await res.json();
        if (data.success) {
            const sc  = data.detail.score;
            const cls = v >= 90 ? 'excellent' : v >= 75 ? 'good' : v >= 60 ? 'fair' : 'poor';
            ['pill-','pill2-'].forEach(p => {
                const el = document.getElementById(p + inp.dataset.item);
                if (el) { el.textContent = sc.toFixed(2); el.className = `ring-sm ${cls}`; }
            });
            if (data.totals) updateTotals(data.totals);
            inp.classList.add('saved');
            setTimeout(() => inp.classList.remove('saved'), 1400);
        }
    } catch(e) {}
}

// ── Save catatan ──────────────────────────────────────────────────────
async function saveCat(ta) {
    if (!ta.dataset.detail) return;
    const fd = new FormData();
    fd.append('detail_id', ta.dataset.detail);
    fd.append('hsse_catatan', ta.value);
    try {
        const res  = await fetch(ta.dataset.url, { method:'POST', headers:{'X-CSRF-TOKEN': CSRF}, body: fd });
        const data = await res.json();
        if (data.success) { ta.classList.add('saved'); setTimeout(() => ta.classList.remove('saved'), 900); }
    } catch(e) {}
}

// ── Toggle mark ───────────────────────────────────────────────────────
async function toggleMark(dId, url, btn, kId, ri) {
    const isOn   = btn.classList.contains('on');
    const newMk  = !isOn;
    const noteEl = document.getElementById('mk-note-' + dId);
    const card   = btn.closest('.kc');
    const mkH    = document.getElementById('mk-h-' + ri);
    const fd     = new FormData();
    fd.append('detail_id', dId);
    fd.append('is_marked', newMk ? '1' : '0');
    fd.append('mark_note', noteEl?.value || '');
    try {
        const res  = await fetch(url, { method:'POST', headers:{'X-CSRF-TOKEN': CSRF}, body: fd });
        const data = await res.json();
        if (data.success) {
            btn.classList.toggle('on', data.is_marked);
            btn.textContent = '⚑ ' + (data.is_marked ? 'Marked (Aktif)' : 'Tandai');
            noteEl?.classList.toggle('d-none', !data.is_marked);
            if (card) { card.classList.toggle('kc-mk', data.is_marked); card.dataset.mk = data.is_marked ? '1' : '0'; }
            if (mkH) mkH.value = data.is_marked ? '1' : '0';
            const cntMk = document.getElementById('cnt-mk');
            if (cntMk) cntMk.textContent = document.querySelectorAll('.kc[data-mk="1"]').length;
        }
    } catch(e) {}
}

async function saveMkNote(dId, url, ta, ri) {
    const btn = document.getElementById('mk-btn-' + dId);
    if (!btn?.classList.contains('on')) return;
    const mknH = document.getElementById('mkn-h-' + ri);
    if (mknH) mknH.value = ta.value;
    const fd = new FormData();
    fd.append('detail_id', dId); fd.append('is_marked', '1'); fd.append('mark_note', ta.value);
    try { await fetch(url, { method:'POST', headers:{'X-CSRF-TOKEN': CSRF}, body: fd }); } catch(e) {}
}

// ── Decision ──────────────────────────────────────────────────────────
function setDec(idx, dec) {
    document.getElementById('dec-' + idx).value = dec;
    const ap   = document.getElementById('btn-ap-' + idx);
    const rj   = document.getElementById('btn-rj-' + idx);
    const cmt  = document.querySelector(`textarea[name="items[${idx}][comment]"]`);
    const err  = document.getElementById('cmt-err-' + idx);
    const card = cmt?.closest('.kc');

    if (dec === 'approved') {
        ap?.classList.add('active'); rj?.classList.remove('active');
        card?.classList.remove('kc-rej'); card?.classList.add('kc-ok');
        if (card) card.dataset.rs = 'approved';
        if (err)  err.style.display = 'none';
        cmt?.classList.remove('err');
    } else {
        rj?.classList.add('active'); ap?.classList.remove('active');
        card?.classList.add('kc-rej'); card?.classList.remove('kc-ok');
        if (card) card.dataset.rs = 'rejected';
        cmt?.focus();
    }
    updateCounts();
}

function setAll(dec) {
    for (let i = 0; i < TOTAL_ITEMS; i++) {
        if (document.getElementById('dec-' + i)) setDec(i, dec);
    }
}

function updateCounts() {
    let ap = 0, rj = 0, done = 0;
    for (let i = 0; i < TOTAL_ITEMS; i++) {
        const v = document.getElementById('dec-' + i)?.value;
        if (v === 'approved') { ap++; done++; }
        else if (v === 'rejected') { rj++; done++; }
    }
    const s = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
    s('cnt-ap', ap); s('cnt-rj', rj);
    const prog = document.getElementById('rv-prog');
    if (prog) prog.style.width = (done / TOTAL_ITEMS * 100) + '%';
    const lbl = document.getElementById('rv-lbl');
    if (lbl) lbl.textContent = done + ' / ' + TOTAL_ITEMS + ' diputuskan';
}

function submitReview() {
    const gc = document.getElementById('gc-input')?.value || '';
    const gch = document.getElementById('gc-hidden'); if (gch) gch.value = gc;
    let valid = true;
    for (let i = 0; i < TOTAL_ITEMS; i++) {
        const dec = document.getElementById('dec-' + i)?.value;
        const cmt = document.querySelector(`textarea[name="items[${i}][comment]"]`);
        const err = document.getElementById('cmt-err-' + i);
        if (!dec) { valid = false; continue; }
        if (dec === 'rejected') {
            if (!cmt?.value?.trim()) {
                valid = false;
                cmt?.classList.add('err'); if (err) err.style.display = 'block';
                const body = cmt?.closest('.kc-body');
                if (body && body.style.display === 'none') {
                    body.style.display = 'block';
                    body.closest('.kc')?.querySelector('.chev')?.classList.add('open');
                }
                cmt?.scrollIntoView({ behavior:'smooth', block:'center' });
            } else {
                cmt?.classList.remove('err'); if (err) err.style.display = 'none';
            }
        }
    }
    const warn = document.getElementById('rv-warn');
    if (!valid) { warn?.classList.remove('d-none'); return; }
    warn?.classList.add('d-none');
    document.getElementById('rv-form').submit();
}

// ── Totals update ─────────────────────────────────────────────────────
function updateTotals(t) {
    const s = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
    s('tot-lag',  t.lagging.toFixed(2));
    s('tot-lead', t.leading.toFixed(2));
    s('tot-all',  t.total.toFixed(2));
    s('sb-lag',   t.lagging.toFixed(2));
    s('sb-lead',  t.leading.toFixed(2));
    s('sb-ring-val', t.total > 0 ? t.total.toFixed(1) : '—');
    const pct = Math.min(t.total, 100);
    ['tot-prog','sb-prog'].forEach(id => { const el = document.getElementById(id); if (el) el.style.width = pct + '%'; });
}

// ── Image preview ─────────────────────────────────────────────────────
function previewImg(src, title) {
    document.getElementById('imgSrc').src = src;
    document.getElementById('imgTitle').textContent = title || 'Preview';
    new bootstrap.Modal(document.getElementById('imgModal')).show();
}
</script>
@endpush
