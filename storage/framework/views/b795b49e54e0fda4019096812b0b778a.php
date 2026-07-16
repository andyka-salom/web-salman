
<?php $__env->startSection('title', 'HSSE Evaluation Dashboard'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?php echo e(asset('plugins/src/sweetalerts2/sweetalerts2.css')); ?>">
<style>
/* ═══════════════════════════════════════════════════════════
   HSSE DASHBOARD — Design System
═══════════════════════════════════════════════════════════ */
.dash-header-wrap {
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8e 60%, #1a7fc1 100%);
    border-radius: 16px;
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.dash-header-wrap::before {
    content: '';
    position: absolute;
    top: -50px; right: -50px;
    width: 220px; height: 220px;
    border-radius: 50%;
    background: rgba(255,255,255,0.05);
    pointer-events: none;
}
.dash-header-wrap::after {
    content: '';
    position: absolute;
    bottom: -70px; right: 100px;
    width: 160px; height: 160px;
    border-radius: 50%;
    background: rgba(255,255,255,0.04);
    pointer-events: none;
}
.dash-header-wrap h3 { color:#fff; font-size:1.55rem; font-weight:800; margin:0 0 4px; letter-spacing:-0.02em; }
.dash-header-wrap p  { color:rgba(255,255,255,0.70); margin:0; font-size:0.875rem; }
.readonly-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 14px; border-radius: 20px;
    background: rgba(255,255,255,0.15); color: #fff;
    font-size: 0.77rem; font-weight: 600; margin-top: 10px;
    border: 1px solid rgba(255,255,255,0.2);
}
.dash-header-actions { display:flex; gap:10px; flex-wrap:wrap; justify-content:flex-end; align-items:center; height:100%; }
.btn-hdr {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 9px 20px; border-radius: 10px;
    font-size: 0.84rem; font-weight: 700; border: none;
    cursor: pointer; transition: all 0.2s ease; text-decoration: none;
}
.btn-hdr-primary { background:#fff; color:#1e3a5f; }
.btn-hdr-primary:hover { background:#f0f7ff; color:#1e3a5f; transform:translateY(-1px); box-shadow:0 4px 14px rgba(0,0,0,0.18); }
.btn-hdr-ghost { background:rgba(255,255,255,0.15); color:#fff; border:1px solid rgba(255,255,255,0.25); }
.btn-hdr-ghost:hover { background:rgba(255,255,255,0.25); color:#fff; transform:translateY(-1px); }

/* ── Filter Panel ─────────────────────────────────────── */
.filter-panel {
    background: var(--card-bg, #fff);
    border-radius: 14px; padding: 20px 24px;
    border: 1px solid var(--card-border-color, #e8ecef);
    box-shadow: 0 1px 6px rgba(0,0,0,0.04); margin-bottom: 24px;
}
.filter-panel .filter-title {
    font-size:.76rem; font-weight:700; color:#64748b;
    text-transform:uppercase; letter-spacing:0.07em; margin-bottom:14px;
    display:flex; align-items:center; gap:7px;
}
.filter-panel .form-label { font-size:.77rem; font-weight:700; color:#475569; margin-bottom:5px; }
.filter-panel .form-control,
.filter-panel .form-select {
    border-radius:8px; border-color:#e2e8f0;
    font-size:.84rem; background-color:#f8fafc;
    transition:border-color .15s,box-shadow .15s;
}
.filter-panel .form-control:focus,
.filter-panel .form-select:focus { border-color:#1d4ed8; box-shadow:0 0 0 3px rgba(29,78,216,.10); background-color:#fff; }
.btn-apply {
    background:linear-gradient(135deg,#1d4ed8,#2563eb); color:#fff;
    border:none; border-radius:8px; padding:9px 20px; font-size:.84rem; font-weight:700;
    display:inline-flex; align-items:center; gap:7px;
    transition:all .2s ease; box-shadow:0 2px 8px rgba(29,78,216,.28); cursor:pointer;
}
.btn-apply:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(29,78,216,.38); color:#fff; }
.btn-reset-f {
    background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0;
    border-radius:8px; padding:9px 14px; font-size:.84rem; font-weight:600;
    display:inline-flex; align-items:center; gap:6px; transition:all .15s ease; cursor:pointer;
}
.btn-reset-f:hover { background:#e2e8f0; color:#334155; }

/* ── KPI Cards ─────────────────────────────────────────── */
.kpi-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px; }
@media (max-width:1200px) { .kpi-grid { grid-template-columns:repeat(2,1fr); } }
@media (max-width:576px)  { .kpi-grid { grid-template-columns:1fr; } }
.kpi-card {
    background:var(--card-bg,#fff); border-radius:14px;
    border:1px solid var(--card-border-color,#e8ecef);
    box-shadow:0 1px 6px rgba(0,0,0,.05); padding:22px 22px 20px;
    position:relative; overflow:hidden; transition:transform .2s ease,box-shadow .2s ease;
}
.kpi-card:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,0,0,.09); }
.kpi-card .kpi-accent { position:absolute; top:0; left:0; width:4px; height:100%; }
.kpi-card .kpi-top { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:14px; }
.kpi-card .kpi-icon-box { width:46px; height:46px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.25rem; flex-shrink:0; }
.kpi-card .kpi-pill { font-size:.69rem; font-weight:700; padding:3px 9px; border-radius:20px; text-transform:uppercase; letter-spacing:.04em; }
.kpi-card .kpi-value { font-size:2rem; font-weight:800; line-height:1; color:#0f172a; letter-spacing:-0.03em; margin-bottom:4px; }
.kpi-card .kpi-label { font-size:.75rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:.05em; }
.kpi-card .kpi-sub   { font-size:.76rem; color:#94a3b8; margin-top:3px; }
.comp-bar-track { height:10px; border-radius:99px; background:#f1f5f9; overflow:hidden; margin:10px 0; display:flex; gap:2px; }
.comp-bar-track .seg { height:100%; border-radius:99px; transition:width .65s cubic-bezier(.4,0,.2,1); }
.comp-legend { display:flex; gap:12px; flex-wrap:wrap; }
.comp-legend-item { display:flex; align-items:center; gap:5px; font-size:.75rem; }
.comp-dot  { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
.comp-num  { font-weight:800; color:#0f172a; }
.comp-pct2 { color:#94a3b8; font-weight:500; }

/* ── Crew KPI sub-bar (dievaluasi vs total) ─────────────── */
.crew-ratio-bar {
    height: 6px; border-radius: 99px; background: #f1f5f9;
    overflow: hidden; margin: 10px 0 6px;
}
.crew-ratio-fill {
    height: 100%; border-radius: 99px;
    background: linear-gradient(90deg, #f59e0b, #d97706);
    transition: width .65s cubic-bezier(.4,0,.2,1);
}
.crew-ratio-label {
    font-size: .72rem; color: #94a3b8; display: flex;
    justify-content: space-between; align-items: center;
}
.crew-ratio-label strong { color: #0f172a; }

/* ── Vessel Status Panel ──────────────────────────────────── */
.vessel-status-section {
    background: var(--card-bg, #fff);
    border-radius: 14px;
    border: 1px solid var(--card-border-color, #e8ecef);
    box-shadow: 0 1px 6px rgba(0,0,0,.05);
    overflow: hidden;
    margin-bottom: 24px;
}
.vessel-status-body { padding: 16px 20px 20px; }
.vs-legend-row {
    display: flex; gap: 16px; align-items: center;
    margin-bottom: 14px; flex-wrap: wrap;
}
.vs-legend-item {
    display: flex; align-items: center; gap: 6px;
    font-size: .75rem; color: #64748b; font-weight: 600;
}
.vs-legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.vessel-status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    gap: 7px;
}
.vs-chip {
    display: flex; align-items: center; gap: 8px;
    padding: 7px 11px; border-radius: 9px;
    font-size: .775rem; font-weight: 600; line-height: 1.3;
    border: 1px solid transparent;
    transition: transform .12s ease, box-shadow .12s ease;
    cursor: default;
}
.vs-chip:hover { transform: translateY(-1px); box-shadow: 0 3px 10px rgba(0,0,0,.08); }
.vs-chip.done  { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
.vs-chip.todo  { background: #fafafa; border-color: #e2e8f0; color: #94a3b8; }
.vs-chip .vs-dot {
    width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0;
}
.vs-chip.done .vs-dot { background: #22c55e; }
.vs-chip.todo .vs-dot { background: #d1d5db; }
.vs-chip .vs-name {
    flex: 1; min-width: 0; overflow: hidden;
    text-overflow: ellipsis; white-space: nowrap;
}
.vs-chip .vs-icon {
    flex-shrink: 0; font-size: .72rem; opacity: .7;
}

/* ── Criteria Profile ──────────────────────────────────── */
.criteria-profile-section {
    background:var(--card-bg,#fff); border-radius:14px;
    border:1px solid var(--card-border-color,#e8ecef);
    box-shadow:0 1px 6px rgba(0,0,0,.05); overflow:hidden; margin-bottom:24px;
}
.criteria-profile-body { padding:20px 24px; }
.criteria-legend-row {
    display:flex; gap:16px; align-items:center;
    margin-bottom:18px; padding-bottom:14px; border-bottom:1px solid #f1f5f9; flex-wrap:wrap;
}
.criteria-legend-item { display:flex; align-items:center; gap:6px; font-size:.76rem; color:#64748b; font-weight:600; }
.criteria-legend-dot  { width:14px; height:14px; border-radius:3px; flex-shrink:0; }
.marker-legend-bar { display:inline-block; width:3px; height:14px; background:#1e3a5f; border-radius:2px; vertical-align:middle; margin-right:4px; }

.criteria-row {
    display: grid;
    grid-template-columns: 26px 200px 1fr auto;
    column-gap: 14px;
    align-items: start;
    margin-bottom: 28px;
    min-height: 0;
}
.criteria-row:last-child { margin-bottom:0; }
.criteria-num-badge {
    width:26px; height:26px; border-radius:50%;
    background:#1e3a5f; color:#fff;
    font-size:.72rem; font-weight:800;
    display:flex; align-items:center; justify-content:center;
    margin-top:5px;
}
.criteria-label-txt {
    font-size:.8rem; font-weight:600; color:#334155;
    line-height:1.5; word-break:break-word; padding-top:2px;
}
.criteria-scale-wrap { position:relative; min-width:0; }

@media (max-width: 768px) {
    .criteria-row {
        grid-template-columns: 26px 1fr auto;
        grid-template-rows: auto auto;
    }
    .criteria-label-txt { grid-column: 2 / 4; grid-row: 1; padding-bottom: 8px; }
    .criteria-num-badge { grid-row: 1; }
    .criteria-scale-wrap { grid-column: 2 / 3; grid-row: 2; }
    .comp-chip { grid-column: 3 / 4; grid-row: 2; align-self: start; }
}
.scale-track { height:34px; border-radius:8px; display:flex; overflow:hidden; border:1px solid #e2e8f0; background:#f1f5f9; }
.scale-seg {
    display:flex; align-items:center; justify-content:center;
    font-size:.68rem; font-weight:800; letter-spacing:.03em;
    overflow:hidden; white-space:nowrap;
    transition:width .70s cubic-bezier(.4,0,.2,1);
    min-width:0; position:relative;
}
.scale-seg.seg-kurang { background:#fca5a5; color:#7f1d1d; }
.scale-seg.seg-kurang + .scale-seg { border-left:2px solid rgba(255,255,255,.6); }
.scale-seg.seg-cukup  { background:#fde68a; color:#78350f; }
.scale-seg.seg-cukup + .scale-seg  { border-left:2px solid rgba(255,255,255,.6); }
.scale-seg.seg-baik   { background:#86efac; color:#14532d; }
.scale-seg .seg-label { display:flex; flex-direction:column; align-items:center; line-height:1.2; pointer-events:none; opacity:0; transition:opacity .3s ease .4s; }
.scale-seg.show-label .seg-label { opacity:1; }
.scale-seg .seg-pct   { font-size:.78rem; font-weight:800; }
.scale-seg .seg-count { font-size:.62rem; font-weight:600; opacity:.75; }
.scale-marker {
    position:absolute; top:-6px; width:3px; height:46px; border-radius:2px;
    background:#1e3a5f; transform:translateX(-50%);
    box-shadow:0 0 0 2px #fff, 0 0 0 3.5px #1e3a5f;
    transition:left .70s cubic-bezier(.4,0,.2,1); pointer-events:none; z-index:2;
}
.scale-marker::before {
    content:attr(data-score); position:absolute; top:-24px; left:50%; transform:translateX(-50%);
    background:#1e3a5f; color:#fff; font-size:.67rem; font-weight:800; padding:2px 8px;
    border-radius:5px; white-space:nowrap; box-shadow:0 2px 6px rgba(0,0,0,.20);
}
.scale-marker::after {
    content:''; position:absolute; top:-6px; left:50%; transform:translateX(-50%);
    border:4px solid transparent; border-top-color:#1e3a5f;
}
.scale-tick-row { display:flex; justify-content:space-between; margin-top:5px; padding:0 2px; }
.scale-tick          { font-size:.67rem; font-weight:700; color:#94a3b8; }
.scale-tick.t-kurang { color:#ef4444; }
.scale-tick.t-cukup  { color:#d97706; }
.scale-tick.t-baik   { color:#16a34a; }
.comp-chip {
    display:inline-flex; align-items:center; gap:4px;
    padding:4px 10px; border-radius:8px;
    font-size:.78rem; font-weight:700; white-space:nowrap; margin-top:5px;
}
.comp-chip.kurang { background:#fee2e2; color:#dc2626; }
.comp-chip.cukup  { background:#fef3c7; color:#d97706; }
.comp-chip.baik   { background:#dcfce7; color:#16a34a; }
.comp-chip .cpct  { font-weight:500; opacity:.70; font-size:.71rem; }
.comp-nil         { color:#d1d5db; font-size:1rem; }
.criteria-sk-row  { display:flex; align-items:center; gap:14px; margin-bottom:20px; }

/* ── Top 10 Assessor Leaderboard ──────────────────────── */
.lb-section {
    background: var(--card-bg, #fff);
    border-radius: 14px;
    border: 1px solid var(--card-border-color, #e8ecef);
    box-shadow: 0 1px 6px rgba(0,0,0,.05);
    overflow: hidden;
    margin-bottom: 24px;
}
.lb-body-wrap { padding: 4px 0; }
.lb-row {
    display: grid;
    grid-template-columns: 48px 1fr auto auto;
    align-items: center;
    gap: 0 14px;
    padding: 12px 20px 12px 16px;
    border-bottom: 1px solid #f1f5f9;
    transition: background .12s ease;
}
.lb-row:last-child { border-bottom: none; }
.lb-row:hover { background: #f8faff; }
.lb-rank {
    width: 34px; height: 34px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .80rem; font-weight: 800; flex-shrink: 0; line-height: 1;
}
.lb-rank.r1 { background: #fef3c7; color: #92400e; font-size: 1.1rem; }
.lb-rank.r2 { background: #f1f5f9; color: #475569; font-size: 1.1rem; }
.lb-rank.r3 { background: #fff1e6; color: #7c3b1a; font-size: 1.1rem; }
.lb-rank.rn { background: #f8fafc; color: #94a3b8; border: 1px solid #e2e8f0; font-size: .80rem; }
.lb-name    { font-size: .875rem; font-weight: 700; color: #1e293b; line-height: 1.3; }
.lb-pos     { font-size: .73rem; color: #94a3b8; margin-top: 2px; }
.lb-bar-wrap { width: 120px; display: flex; flex-direction: column; gap: 5px; align-items: flex-end; }
.lb-bar-track { width: 100%; height: 6px; border-radius: 99px; background: #f1f5f9; overflow: hidden; }
.lb-bar-fill  { height: 100%; border-radius: 99px; background: linear-gradient(90deg, #3b82f6, #1d4ed8); transition: width .65s cubic-bezier(.4,0,.2,1); }
.lb-dist { font-size: .68rem; color: #94a3b8; white-space: nowrap; }
.lb-dist .d-b { color: #16a34a; font-weight: 700; }
.lb-dist .d-c { color: #d97706; font-weight: 700; }
.lb-dist .d-k { color: #dc2626; font-weight: 700; }
.lb-count-wrap { text-align: right; min-width: 60px; }
.lb-count     { font-size: .9rem; font-weight: 800; color: #0f172a; }
.lb-pct-baik  { font-size: .72rem; color: #16a34a; font-weight: 700; margin-top: 1px; }
.lb-sk-row    { display: flex; align-items: center; gap: 14px; padding: 14px 20px; border-bottom: 1px solid #f1f5f9; }
.lb-sk-row:last-child { border-bottom: none; }

/* ── Vessel Table ──────────────────────────────────────── */
.vessel-section {
    background:var(--card-bg,#fff); border-radius:14px;
    border:1px solid var(--card-border-color,#e8ecef);
    box-shadow:0 1px 6px rgba(0,0,0,.05); overflow:hidden;
}
.vs-header {
    padding:16px 24px; border-bottom:1px solid #f1f5f9;
    display:flex; align-items:center; justify-content:space-between; background:#fafbfc;
}
.vs-header h6 { font-weight:800; font-size:.9rem; color:#1e293b; margin:0; display:flex; align-items:center; gap:10px; }
.vs-icon { width:30px; height:30px; border-radius:8px; background:#dbeafe; display:flex; align-items:center; justify-content:center; color:#1d4ed8; font-size:.85rem; }
.vessel-count-pill { background:#f1f5f9; color:#64748b; font-size:.73rem; font-weight:700; padding:4px 12px; border-radius:20px; border:1px solid #e2e8f0; }
.vessel-table { width:100%; border-collapse:collapse; }
.vessel-table thead tr.th-main th { background:#1e3a5f; color:rgba(255,255,255,.88); font-size:.77rem; font-weight:700; padding:12px 16px; text-transform:uppercase; letter-spacing:.05em; border:none; white-space:nowrap; }
.vessel-table thead tr.th-main th:first-child { padding-left:24px; }
.vessel-table thead tr.th-sub th { padding:7px 16px; font-size:.73rem; font-weight:700; border:none; color:rgba(255,255,255,.85); text-align:center; letter-spacing:.03em; }
.vessel-table thead tr.th-sub .th-blank  { background:#243d5c; }
.vessel-table thead tr.th-sub .th-kurang { background:#b91c1c; }
.vessel-table thead tr.th-sub .th-cukup  { background:#b45309; }
.vessel-table thead tr.th-sub .th-baik   { background:#15803d; }
.vessel-table tbody tr { border-bottom:1px solid #f1f5f9; transition:background .12s ease; }
.vessel-table tbody tr:last-child { border-bottom:none; }
.vessel-table tbody tr:hover { background:#f8faff; }
.vessel-table tbody td { padding:14px 16px; font-size:.85rem; color:#334155; vertical-align:middle; }
.vessel-table tbody td:first-child { padding-left:24px; }
.vessel-name-cell { display:flex; align-items:center; gap:12px; }
.vessel-avatar { width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,#dbeafe,#bfdbfe); display:flex; align-items:center; justify-content:center; color:#1d4ed8; font-size:.82rem; font-weight:800; flex-shrink:0; letter-spacing:-0.02em; }
.vessel-name-txt { font-weight:700; color:#1e293b; font-size:.875rem; line-height:1.3; }
.vessel-rank     { font-size:.72rem; color:#94a3b8; font-weight:500; margin-top:1px; }
.freq-badge { display:inline-flex; align-items:center; justify-content:center; min-width:36px; height:28px; border-radius:8px; background:#f0f7ff; color:#1d4ed8; font-weight:800; font-size:.84rem; padding:0 10px; border:1px solid #bfdbfe; }
.crew-badge { display:inline-flex; align-items:center; justify-content:center; min-width:44px; height:28px; border-radius:8px; background:#f0fdf4; color:#15803d; font-weight:800; font-size:.84rem; padding:0 10px; border:1px solid #bbf7d0; }
.empty-state { padding:56px 24px; text-align:center; }
.empty-icon { width:60px; height:60px; border-radius:50%; background:#f1f5f9; display:flex; align-items:center; justify-content:center; font-size:1.5rem; color:#cbd5e1; margin:0 auto 14px; }
.empty-state p     { font-size:.86rem; font-weight:700; color:#94a3b8; margin:0 0 4px; }
.empty-state small { font-size:.77rem; color:#cbd5e1; }
.sk { display:inline-block; border-radius:6px; background:linear-gradient(90deg,#f0f4f8 25%,#e2e8f0 50%,#f0f4f8 75%); background-size:200% 100%; animation:sk-shine 1.4s infinite; }
@keyframes sk-shine { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* ── Error banner ──────────────────────────────────────── */
.api-error-banner {
    background:#fee2e2; border:1px solid #fca5a5; border-radius:10px;
    padding:14px 18px; margin-bottom:16px;
    display:flex; align-items:flex-start; gap:10px;
    font-size:.83rem; color:#991b1b; font-weight:600;
}
.api-error-banner i { font-size:1.1rem; flex-shrink:0; margin-top:1px; }
.api-error-detail { font-size:.76rem; font-weight:400; color:#b91c1c; margin-top:3px; }

/* ── Vessel status skeleton ────────────────────────────── */
.vs-sk-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    gap: 7px;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">

        
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div class="dash-header-wrap">
                    <div class="row align-items-center g-3">
                        <div class="col-md-7">
                            <h3>
                                <i class="bi bi-shield-check me-2" style="opacity:.85;font-size:1.4rem;"></i>
                                HSSE On Board Evaluation
                            </h3>
                            <p>Monitoring &amp; Rekapitulasi Kompetensi HSSE Kru Kapal</p>
                            <?php if(!Auth::user()->hasAnyRole(['super-admin', 'hsse', 'user'])): ?>
                            <span class="readonly-pill">
                                <i class="bi bi-eye-fill"></i>
                                Mode Lihat Saja &mdash; Data Perusahaan Anda
                            </span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-5">
                            <div class="dash-header-actions">
                                <?php if(Auth::user()->hasAnyRole(['super-admin', 'hsse', 'user'])): ?>
                                <a href="<?php echo e(route('hsse-evaluation.create')); ?>" class="btn-hdr btn-hdr-primary">
                                    <i class="bi bi-plus-lg"></i> Evaluasi Baru
                                </a>
                                <?php endif; ?>
                                <a href="<?php echo e(route('hsse-evaluation.index')); ?>" class="btn-hdr btn-hdr-ghost">
                                    <i class="bi bi-list-ul"></i> Semua Evaluasi
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div id="api-error-banner" class="api-error-banner" style="display:none;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <div>Gagal memuat data dashboard.</div>
                <div class="api-error-detail" id="api-error-detail"></div>
            </div>
        </div>

        
        <div class="filter-panel">
            <div class="filter-title">
                <i class="bi bi-sliders2"></i> Filter Data
            </div>
            <div class="row g-3 align-items-end">

                <?php if(Auth::user()->hasAnyRole(['super-admin', 'hsse'])): ?>
                <div class="col-xl-3 col-md-6">
                    <label class="form-label">Perusahaan</label>
                    <select class="form-select form-select-sm" id="filter_company">
                        <option value="">Semua Perusahaan</option>
                        <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="<?php echo e(Auth::user()->hasAnyRole(['super-admin','hsse']) ? 'col-xl-2 col-md-6' : 'col-xl-3 col-md-6'); ?>">
                    <label class="form-label">Kapal</label>
                    <select class="form-select form-select-sm" id="filter_vessel">
                        <option value="">Semua Kapal</option>
                        <?php $__currentLoopData = $vessels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($v->id); ?>"><?php echo e($v->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="<?php echo e(Auth::user()->hasAnyRole(['super-admin','hsse']) ? 'col-xl-2 col-md-6' : 'col-xl-3 col-md-6'); ?>">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control form-control-sm" id="filter_start">
                </div>

                <div class="<?php echo e(Auth::user()->hasAnyRole(['super-admin','hsse']) ? 'col-xl-2 col-md-6' : 'col-xl-3 col-md-6'); ?>">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" class="form-control form-control-sm" id="filter_end">
                </div>

                <div class="col-xl-3 col-12">
                    <label class="form-label d-none d-xl-block">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button class="btn-apply flex-grow-1" id="btn-apply-filter">
                            <i class="bi bi-search"></i> Terapkan Filter
                        </button>
                        <button class="btn-reset-f" id="btn-reset-filter" title="Reset semua filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        
        <div class="kpi-grid">
            
            <div class="kpi-card">
                <div class="kpi-accent" style="background:linear-gradient(180deg,#60a5fa,#1d4ed8);"></div>
                <div class="kpi-top">
                    <div class="kpi-icon-box" style="background:#dbeafe;"><i class="bi bi-clipboard2-check-fill" style="color:#1d4ed8;"></i></div>
                    <span class="kpi-pill" style="background:#dbeafe;color:#1d4ed8;">Evaluasi</span>
                </div>
                <div class="kpi-value" id="kpi-total">—</div>
                <div class="kpi-label">Total Evaluasi</div>
                <div class="kpi-sub" id="kpi-total-sub">Seluruh sesi tercatat</div>
            </div>

            
            <div class="kpi-card">
                <div class="kpi-accent" style="background:linear-gradient(180deg,#34d399,#059669);"></div>
                <div class="kpi-top">
                    <div class="kpi-icon-box" style="background:#d1fae5;"><i class="bi bi-tsunami" style="color:#059669;"></i></div>
                    <span class="kpi-pill" style="background:#d1fae5;color:#065f46;">Armada</span>
                </div>
                <div class="kpi-value" id="kpi-vessels">—</div>
                <div class="kpi-label">Kapal Dievaluasi</div>
                <div class="kpi-sub" id="kpi-vessels-sub">dari total armada</div>
            </div>

            
            <div class="kpi-card">
                <div class="kpi-accent" style="background:linear-gradient(180deg,#fbbf24,#d97706);"></div>
                <div class="kpi-top">
                    <div class="kpi-icon-box" style="background:#fef3c7;"><i class="bi bi-people-fill" style="color:#d97706;"></i></div>
                    <span class="kpi-pill" style="background:#fef3c7;color:#92400e;">Kru</span>
                </div>
                <div class="kpi-value" id="kpi-crew">—</div>
                <div class="kpi-label">Kru Dievaluasi</div>
                
                <div class="crew-ratio-bar">
                    <div class="crew-ratio-fill" id="crew-ratio-fill" style="width:0%;"></div>
                </div>
                <div class="crew-ratio-label">
                    <span id="kpi-crew-sub">dari total kru aktif</span>
                    <strong id="kpi-crew-pct">—</strong>
                </div>
            </div>

            
            <div class="kpi-card">
                <div class="kpi-accent" style="background:linear-gradient(180deg,#a78bfa,#7c3aed);"></div>
                <div class="kpi-top">
                    <div class="kpi-icon-box" style="background:#ede9fe;"><i class="bi bi-bar-chart-line-fill" style="color:#7c3aed;"></i></div>
                    <span class="kpi-pill" style="background:#ede9fe;color:#5b21b6;">Kompetensi</span>
                </div>
                <div class="kpi-label" style="margin-bottom:0;text-transform:uppercase;letter-spacing:.05em;font-size:.74rem;color:#64748b;">Distribusi Penilaian</div>
                <div class="comp-bar-track">
                    <div class="seg" id="bar-kurang" style="width:0%;background:#ef4444;"></div>
                    <div class="seg" id="bar-cukup"  style="width:0%;background:#f59e0b;"></div>
                    <div class="seg" id="bar-baik"   style="width:0%;background:#22c55e;"></div>
                </div>
                <div class="comp-legend">
                    <div class="comp-legend-item">
                        <div class="comp-dot" style="background:#ef4444;"></div>
                        <span style="color:#64748b;">Kurang</span>
                        <span id="pct-kurang"><span class="comp-num">—</span></span>
                    </div>
                    <div class="comp-legend-item">
                        <div class="comp-dot" style="background:#f59e0b;"></div>
                        <span style="color:#64748b;">Cukup</span>
                        <span id="pct-cukup"><span class="comp-num">—</span></span>
                    </div>
                    <div class="comp-legend-item">
                        <div class="comp-dot" style="background:#22c55e;"></div>
                        <span style="color:#64748b;">Baik</span>
                        <span id="pct-baik"><span class="comp-num">—</span></span>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="vessel-status-section" id="vessel-status-panel" style="display:none;">
            <div class="vs-header">
                <h6>
                    <div class="vs-icon" style="background:#ecfdf5;">
                        <i class="bi bi-check2-circle" style="color:#059669;"></i>
                    </div>
                    Status Evaluasi Kapal Aktif
                </h6>
                <span class="vessel-count-pill" id="vs-summary-pill">— kapal</span>
            </div>
            <div class="vessel-status-body">
                <div class="vs-legend-row">
                    <div class="vs-legend-item">
                        <div class="vs-legend-dot" style="background:#22c55e;"></div>
                        Sudah dievaluasi (dalam periode)
                    </div>
                    <div class="vs-legend-item">
                        <div class="vs-legend-dot" style="background:#d1d5db;"></div>
                        Belum dievaluasi
                    </div>
                </div>
                <div class="vessel-status-grid" id="vessel-status-grid">
                    
                </div>
            </div>
        </div>

        
        <div class="criteria-profile-section">
            <div class="vs-header">
                <h6>
                    <div class="vs-icon"><i class="bi bi-bar-chart-steps"></i></div>
                    Profile Item Evaluasi — Distribusi Penilaian per Kriteria
                </h6>
                <span class="vessel-count-pill">Lebar warna = proporsi Kurang / Cukup / Baik</span>
            </div>
            <div class="criteria-profile-body">
                <div class="criteria-legend-row">
                    <div class="criteria-legend-item">
                        <div class="criteria-legend-dot" style="background:#fca5a5;border:1px solid #f87171;"></div>
                        Kurang (Nilai 1)
                    </div>
                    <div class="criteria-legend-item">
                        <div class="criteria-legend-dot" style="background:#fde68a;border:1px solid #fbbf24;"></div>
                        Cukup (Nilai 2)
                    </div>
                    <div class="criteria-legend-item">
                        <div class="criteria-legend-dot" style="background:#86efac;border:1px solid #4ade80;"></div>
                        Baik (Nilai 3)
                    </div>
                    <div class="criteria-legend-item" style="margin-left:auto;">
                        <span class="marker-legend-bar"></span>
                        Posisi rata-rata skor
                    </div>
                </div>
                <div id="criteria-profile-list">
                    <?php for($i = 0; $i < 5; $i++): ?>
                    <div class="criteria-sk-row">
                        <span class="sk" style="width:26px;height:26px;border-radius:50%;flex-shrink:0;"></span>
                        <span class="sk" style="width:220px;height:14px;flex-shrink:0;border-radius:6px;"></span>
                        <span class="sk" style="flex:1;height:34px;border-radius:8px;"></span>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        
        <div class="lb-section">
            <div class="vs-header">
                <h6>
                    <div class="vs-icon" style="background:#fef3c7;">
                        <i class="bi bi-trophy-fill" style="color:#d97706;"></i>
                    </div>
                    Top 10 Assessor
                </h6>
                <span class="vessel-count-pill" id="lb-row-count">— assessor</span>
            </div>
            <div id="lb-body">
                <?php for($i = 0; $i < 5; $i++): ?>
                <div class="lb-sk-row">
                    <span class="sk" style="width:34px;height:34px;border-radius:50%;flex-shrink:0;"></span>
                    <div style="flex:1;">
                        <span class="sk" style="height:13px;width:160px;display:block;border-radius:5px;margin-bottom:7px;"></span>
                        <span class="sk" style="height:11px;width:100px;display:block;border-radius:5px;"></span>
                    </div>
                    <span class="sk" style="height:20px;width:110px;border-radius:5px;margin-right:14px;"></span>
                    <span class="sk" style="height:20px;width:54px;border-radius:5px;"></span>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        
        <div class="vessel-section">
            <div class="vs-header">
                <h6>
                    <div class="vs-icon"><i class="bi bi-table"></i></div>
                    Rekapitulasi per Kapal
                </h6>
                <span class="vessel-count-pill" id="vessel-row-count">— kapal</span>
            </div>
            <div class="table-responsive">
                <table class="vessel-table">
                    <thead>
                        <tr class="th-main">
                            <th style="width:32%;">Nama Kapal</th>
                            <th class="text-center" style="width:14%;">Frekuensi Evaluasi</th>
                            <th class="text-center" style="width:14%;">∑ Kru Unik</th>
                            <th class="text-center" colspan="3">Distribusi Kompetensi</th>
                        </tr>
                        <tr class="th-sub">
                            <th class="th-blank" colspan="3"></th>
                            <th class="th-kurang"><i class="bi bi-x-circle-fill me-1"></i>Kurang</th>
                            <th class="th-cukup"><i class="bi bi-dash-circle-fill me-1"></i>Cukup</th>
                            <th class="th-baik"><i class="bi bi-check-circle-fill me-1"></i>Baik</th>
                        </tr>
                    </thead>
                    <tbody id="vessel-table-body">
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="bi bi-sliders2"></i></div>
                                    <p>Terapkan filter untuk menampilkan data</p>
                                    <small>Pilih rentang tanggal atau kapal, lalu klik Terapkan Filter</small>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    'use strict';

    function pct(part, total) { return total ? Math.round((part / total) * 100) : 0; }
    function esc(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;')
                        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function initials(name) {
        return String(name).split(' ').slice(0,2)
            .map(function(w){ return w[0]||''; }).join('').toUpperCase();
    }

    /* ── Filter params helper ────────────────────────────── */
    function getFilterParams() {
        return new URLSearchParams({
            company_id: document.getElementById('filter_company') ? document.getElementById('filter_company').value : '',
            vessel_id:  document.getElementById('filter_vessel')  ? document.getElementById('filter_vessel').value  : '',
            start_date: document.getElementById('filter_start')   ? document.getElementById('filter_start').value   : '',
            end_date:   document.getElementById('filter_end')     ? document.getElementById('filter_end').value     : '',
        });
    }

    /* ── Error banner ────────────────────────────────────── */
    function showApiError(msg) {
        var banner = document.getElementById('api-error-banner');
        var detail = document.getElementById('api-error-detail');
        detail.textContent = msg || '';
        banner.style.display = 'flex';
    }
    function hideApiError() {
        document.getElementById('api-error-banner').style.display = 'none';
    }

    /* ── Load semua ──────────────────────────────────────── */
    function loadAll() {
        hideApiError();
        loadDashboard();
        loadTop10();
    }

    /* ── Load dashboard ──────────────────────────────────── */
    function loadDashboard() {
        showSkeleton();
        showCriteriaSkeleton();
        showVesselStatusSkeleton();

        fetch('<?php echo e(route("hsse-evaluation.dashboard-data")); ?>?' + getFilterParams().toString())
            .then(function(r) {
                return r.text().then(function(text) {
                    var json;
                    try { json = JSON.parse(text); }
                    catch (e) { throw new Error('Server mengembalikan halaman error (bukan JSON). Cek storage/logs/laravel.log untuk detail.'); }
                    if (json.error) throw new Error(json.message || 'Terjadi kesalahan server.');
                    return json;
                });
            })
            .then(function(data) {
                if (!data.summary) throw new Error('Respons tidak valid: field "summary" tidak ditemukan.');
                renderKpi(data.summary);
                renderCriteriaProfile(data.criteria_rows || []);
                renderTable(data.vessel_rows || []);
                renderVesselStatus(data.vessel_status_list || []);
            })
            .catch(function(err) {
                console.error('[HSSE Dashboard]', err);
                showApiError(err.message);
                showTableError();
                showCriteriaError();
                hideVesselStatusPanel();
            });
    }

    /* ── Load Top 10 ─────────────────────────────────────── */
    function loadTop10() {
        showTop10Skeleton();

        fetch('<?php echo e(route("hsse-evaluation.top10-assessor")); ?>?' + getFilterParams().toString())
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.error) throw new Error(data.message || 'Terjadi kesalahan server.');
                renderTop10(data.assessors || []);
            })
            .catch(function(err) {
                console.error('[HSSE Top10]', err);
                document.getElementById('lb-body').innerHTML =
                    '<div class="empty-state" style="padding:40px;">' +
                    '<div class="empty-icon" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-exclamation-triangle-fill"></i></div>' +
                    '<p style="color:#dc2626;">Gagal memuat data assessor</p>' +
                    '<small>' + esc(err.message) + '</small>' +
                    '</div>';
                document.getElementById('lb-row-count').textContent = '— assessor';
            });
    }

    /* ── Render KPI ──────────────────────────────────────── */
    function renderKpi(s) {
        // Total evaluasi
        document.getElementById('kpi-total').textContent = s.total_evaluations;
        document.getElementById('kpi-total-sub').textContent =
            s.total_evaluations > 0 ? s.total_evaluations + ' sesi tercatat' : 'Belum ada data';

        // Kapal
        document.getElementById('kpi-vessels').textContent = s.unique_vessels + ' / ' + s.total_vessels;
        document.getElementById('kpi-vessels-sub').textContent = 'dari ' + s.total_vessels + ' kapal aktif';

        // Kru — tampilkan ratio dievaluasi / total aktif
        var totalCrew    = parseInt(s.total_active_crew, 10) || 0;
        var evalCrew     = parseInt(s.unique_crew, 10) || 0;
        var crewPct      = totalCrew > 0 ? Math.round(evalCrew / totalCrew * 100) : 0;

        document.getElementById('kpi-crew').textContent =
            evalCrew + (totalCrew > 0 ? ' / ' + totalCrew : '');

        document.getElementById('crew-ratio-fill').style.width = crewPct + '%';
        document.getElementById('kpi-crew-sub').textContent =
            totalCrew > 0 ? 'dari ' + totalCrew + ' kru aktif' : 'kru dievaluasi';
        document.getElementById('kpi-crew-pct').textContent =
            totalCrew > 0 ? crewPct + '%' : '';

        // Distribusi kompetensi
        var total   = s.baik + s.cukup + s.kurang;
        var pBaik   = pct(s.baik,   total);
        var pCukup  = pct(s.cukup,  total);
        var pKurang = pct(s.kurang, total);

        document.getElementById('bar-baik').style.width   = pBaik   + '%';
        document.getElementById('bar-cukup').style.width  = pCukup  + '%';
        document.getElementById('bar-kurang').style.width = pKurang + '%';

        document.getElementById('pct-baik').innerHTML =
            '<span class="comp-num">' + s.baik + '</span><span class="comp-pct2"> (' + pBaik + '%)</span>';
        document.getElementById('pct-cukup').innerHTML =
            '<span class="comp-num">' + s.cukup + '</span><span class="comp-pct2"> (' + pCukup + '%)</span>';
        document.getElementById('pct-kurang').innerHTML =
            '<span class="comp-num">' + s.kurang + '</span><span class="comp-pct2"> (' + pKurang + '%)</span>';
    }

    /* ── Render Vessel Status Panel ──────────────────────── */
    function renderVesselStatus(list) {
        var panel = document.getElementById('vessel-status-panel');
        var grid  = document.getElementById('vessel-status-grid');
        var pill  = document.getElementById('vs-summary-pill');

        if (!list || !list.length) {
            panel.style.display = 'none';
            return;
        }

        panel.style.display = 'block';

        var done  = list.filter(function(v){ return v.is_evaluated; }).length;
        var total = list.length;
        var donePct = total > 0 ? Math.round(done / total * 100) : 0;

        pill.innerHTML =
            '<strong style="color:#15803d;">' + done + '</strong>' +
            ' / ' + total + ' dievaluasi' +
            ' <span style="color:#94a3b8;font-weight:500;">(' + donePct + '%)</span>';

        // Urutkan: sudah dievaluasi duluan, lalu belum, masing-masing A-Z
        var sorted = list.slice().sort(function(a, b) {
            if (a.is_evaluated && !b.is_evaluated) return -1;
            if (!a.is_evaluated && b.is_evaluated) return 1;
            return a.vessel_name.localeCompare(b.vessel_name);
        });

        grid.innerHTML = sorted.map(function(v) {
            var cls  = v.is_evaluated ? 'done' : 'todo';
            var icon = v.is_evaluated ? 'bi-check-circle-fill' : 'bi-circle';
            return '<div class="vs-chip ' + cls + '" title="' + esc(v.vessel_name) + '">' +
                   '<span class="vs-dot"></span>' +
                   '<span class="vs-name">' + esc(v.vessel_name) + '</span>' +
                   '<i class="bi ' + icon + ' vs-icon"></i>' +
                   '</div>';
        }).join('');
    }

    function hideVesselStatusPanel() {
        document.getElementById('vessel-status-panel').style.display = 'none';
    }

    /* ── Render Profile Kriteria ─────────────────────────── */
    function renderCriteriaProfile(criteriaRows) {
        var container = document.getElementById('criteria-profile-list');

        if (!criteriaRows || !criteriaRows.length) {
            container.innerHTML =
                '<div class="empty-state" style="padding:32px 0;">' +
                '<div class="empty-icon"><i class="bi bi-inbox"></i></div>' +
                '<p>Belum ada data kriteria</p>' +
                '<small>Terapkan filter untuk menampilkan profil penilaian</small>' +
                '</div>';
            return;
        }

        container.innerHTML = criteriaRows.map(function(c) {
            var nKurang = parseInt(c.kurang, 10) || 0;
            var nCukup  = parseInt(c.cukup,  10) || 0;
            var nBaik   = parseInt(c.baik,   10) || 0;
            var total   = nKurang + nCukup + nBaik;

            var wK = total ? (nKurang / total * 100) : 0;
            var wC = total ? (nCukup  / total * 100) : 0;
            var wB = total ? (nBaik   / total * 100) : 0;

            var LABEL_MIN = 14;

            function segHtml(cls, w, n) {
                var show  = (w >= LABEL_MIN);
                var label = show
                    ? '<span class="seg-label"><span class="seg-pct">' + Math.round(w) + '%</span><span class="seg-count">(' + n + ')</span></span>'
                    : '<span class="seg-label"></span>';
                return '<div class="scale-seg ' + cls + (show ? ' show-label' : '') +
                       '" style="width:' + w.toFixed(2) + '%">' + label + '</div>';
            }

            var trackHtml = total > 0
                ? segHtml('seg-kurang', wK, nKurang) + segHtml('seg-cukup', wC, nCukup) + segHtml('seg-baik', wB, nBaik)
                : '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:.72rem;color:#cbd5e1;font-style:italic;">Belum ada data</div>';

            var avg = c.avg_score;
            var markerHtml = '';
            if (avg !== null && total > 0) {
                var avgF = parseFloat(avg);
                var mPct;
                if (avgF <= 1)      { mPct = 0; }
                else if (avgF <= 2) { mPct = wK + (avgF - 1) * wC; }
                else                { mPct = wK + wC + (avgF - 2) * wB; }
                mPct = Math.max(0, Math.min(100, mPct));
                markerHtml = '<div class="scale-marker" style="left:' + mPct.toFixed(1) + '%" ' +
                             'data-score="Rata-rata: ' + parseFloat(avg).toFixed(2) + '"></div>';
            }

            var tickHtml = total > 0
                ? '<div class="scale-tick-row">' +
                  '<span class="scale-tick t-kurang">' + nKurang + ' Kurang</span>' +
                  '<span class="scale-tick t-cukup">'  + nCukup  + ' Cukup</span>'  +
                  '<span class="scale-tick t-baik">'   + nBaik   + ' Baik</span>'   +
                  '</div>'
                : '<div class="scale-tick-row">' +
                  '<span class="scale-tick">1 — Kurang</span>' +
                  '<span class="scale-tick">2 — Cukup</span>'  +
                  '<span class="scale-tick">3 — Baik</span>'   +
                  '</div>';

            var chipHtml = '';
            if (avg !== null && total > 0) {
                var avgV = parseFloat(avg);
                var cat  = avgV < 1.67 ? 'kurang' : (avgV < 2.34 ? 'cukup' : 'baik');
                chipHtml = '<span class="comp-chip ' + cat + '">' +
                           (cat.charAt(0).toUpperCase() + cat.slice(1)) +
                           '<span class="cpct"> ' + parseFloat(avg).toFixed(2) + '</span></span>';
            }

            return '<div class="criteria-row">' +
                   '<div class="criteria-num-badge">' + c.order_no + '</div>' +
                   '<div class="criteria-label-txt">' + esc(c.aspect) +
                   (total === 0 ? '<span style="font-size:.68rem;color:#cbd5e1;font-style:italic;display:block;margin-top:2px;">Belum ada data</span>' : '') +
                   '</div>' +
                   '<div class="criteria-scale-wrap">' +
                   '<div class="scale-track">' + trackHtml + '</div>' +
                   markerHtml + tickHtml +
                   '</div>' + chipHtml + '</div>';
        }).join('');
    }

    /* ── Render Top 10 ───────────────────────────────────── */
    function renderTop10(rows) {
        var body = document.getElementById('lb-body');
        document.getElementById('lb-row-count').textContent = rows.length + ' assessor';

        if (!rows.length) {
            body.innerHTML =
                '<div class="empty-state" style="padding:48px 24px;">' +
                '<div class="empty-icon"><i class="bi bi-inbox"></i></div>' +
                '<p>Belum ada data assessor</p>' +
                '<small>Terapkan filter untuk menampilkan data</small>' +
                '</div>';
            return;
        }

        var max    = rows[0].total_evaluations || 1;
        var medals = ['🥇', '🥈', '🥉'];
        var rankCls = ['r1', 'r2', 'r3'];

        body.innerHTML = rows.map(function(r, i) {
            var rankContent = i < 3 ? medals[i] : String(i + 1);
            var rankC       = i < 3 ? rankCls[i] : 'rn';
            var barW        = Math.round(r.total_evaluations / max * 100);

            return '<div class="lb-row">' +
                '<div class="lb-rank ' + rankC + '">' + rankContent + '</div>' +
                '<div>' +
                    '<div class="lb-name">' + esc(r.assessor_name) + '</div>' +
                    '<div class="lb-pos">'  + esc(r.assessor_position) + '</div>' +
                '</div>' +
                '<div class="lb-bar-wrap">' +
                    '<div class="lb-bar-track"><div class="lb-bar-fill" style="width:' + barW + '%"></div></div>' +
                    '<div class="lb-dist">' +
                        '<span class="d-b">' + r.total_baik   + 'B</span> &middot; ' +
                        '<span class="d-c">' + r.total_cukup  + 'C</span> &middot; ' +
                        '<span class="d-k">' + r.total_kurang + 'K</span>' +
                    '</div>' +
                '</div>' +
                '<div class="lb-count-wrap">' +
                    '<div class="lb-count">' + r.total_evaluations + ' eval</div>' +
                    '<div class="lb-pct-baik">' + r.pct_baik + '% baik</div>' +
                '</div>' +
                '</div>';
        }).join('');
    }

    /* ── Render Table ────────────────────────────────────── */
    function renderTable(rows) {
        var tbody = document.getElementById('vessel-table-body');
        document.getElementById('vessel-row-count').textContent = rows.length + ' kapal';

        if (!rows.length) {
            tbody.innerHTML =
                '<tr><td colspan="6"><div class="empty-state">' +
                '<div class="empty-icon"><i class="bi bi-inbox"></i></div>' +
                '<p>Tidak ada data ditemukan</p>' +
                '<small>Coba perluas rentang tanggal atau ubah filter</small>' +
                '</div></td></tr>';
            return;
        }

        tbody.innerHTML = rows.map(function(r, i) {
            var total = r.baik + r.cukup + r.kurang;
            var pB = pct(r.baik, total), pC = pct(r.cukup, total), pK = pct(r.kurang, total);
            return '<tr>' +
                '<td><div class="vessel-name-cell">' +
                '<div class="vessel-avatar">' + initials(r.vessel_name) + '</div>' +
                '<div><div class="vessel-name-txt">' + esc(r.vessel_name) + '</div>' +
                '<div class="vessel-rank">Armada #' + (i+1) + '</div></div>' +
                '</div></td>' +
                '<td class="text-center"><span class="freq-badge">' + r.frequency + 'x</span></td>' +
                '<td class="text-center"><span class="crew-badge">' + r.unique_crew + '</span></td>' +
                '<td class="text-center">' + (r.kurang ? '<span class="comp-chip kurang">' + r.kurang + '<span class="cpct"> ' + pK + '%</span></span>' : '<span class="comp-nil">—</span>') + '</td>' +
                '<td class="text-center">' + (r.cukup  ? '<span class="comp-chip cukup">'  + r.cukup  + '<span class="cpct"> ' + pC + '%</span></span>' : '<span class="comp-nil">—</span>') + '</td>' +
                '<td class="text-center">' + (r.baik   ? '<span class="comp-chip baik">'   + r.baik   + '<span class="cpct"> ' + pB + '%</span></span>' : '<span class="comp-nil">—</span>') + '</td>' +
                '</tr>';
        }).join('');
    }

    /* ── Skeletons ───────────────────────────────────────── */
    function showSkeleton() {
        var rows = '';
        for (var i = 0; i < 4; i++) {
            var w = [55,70,48,62][i];
            rows += '<tr>' +
                '<td style="padding:14px 24px;"><div style="display:flex;align-items:center;gap:12px;">' +
                '<span class="sk" style="width:36px;height:36px;border-radius:10px;flex-shrink:0;"></span>' +
                '<span class="sk" style="height:14px;width:' + w + '%;"></span></div></td>' +
                '<td class="text-center" style="padding:14px 16px;"><span class="sk" style="height:24px;width:40px;border-radius:8px;display:inline-block;"></span></td>' +
                '<td class="text-center" style="padding:14px 16px;"><span class="sk" style="height:24px;width:44px;border-radius:8px;display:inline-block;"></span></td>' +
                '<td class="text-center" style="padding:14px 16px;"><span class="sk" style="height:24px;width:54px;border-radius:8px;display:inline-block;"></span></td>' +
                '<td class="text-center" style="padding:14px 16px;"><span class="sk" style="height:24px;width:54px;border-radius:8px;display:inline-block;"></span></td>' +
                '<td class="text-center" style="padding:14px 16px;"><span class="sk" style="height:24px;width:54px;border-radius:8px;display:inline-block;"></span></td>' +
                '</tr>';
        }
        document.getElementById('vessel-table-body').innerHTML = rows;
    }

    function showCriteriaSkeleton() {
        var html = '';
        for (var i = 0; i < 5; i++) {
            html += '<div class="criteria-sk-row">' +
                '<span class="sk" style="width:26px;height:26px;border-radius:50%;flex-shrink:0;"></span>' +
                '<span class="sk" style="width:220px;height:14px;flex-shrink:0;border-radius:6px;"></span>' +
                '<span class="sk" style="flex:1;height:34px;border-radius:8px;"></span></div>';
        }
        document.getElementById('criteria-profile-list').innerHTML = html;
    }

    function showVesselStatusSkeleton() {
        var panel = document.getElementById('vessel-status-panel');
        var grid  = document.getElementById('vessel-status-grid');
        // Tampilkan panel dengan skeleton
        panel.style.display = 'block';
        document.getElementById('vs-summary-pill').textContent = 'Memuat...';
        var html = '<div class="vs-sk-grid">';
        for (var i = 0; i < 12; i++) {
            var w = [60,75,50,80,65,55,70,45,68,72,58,63][i];
            html += '<span class="sk" style="height:34px;border-radius:9px;width:100%;"></span>';
        }
        html += '</div>';
        grid.innerHTML = html;
    }

    function showTop10Skeleton() {
        var html = '';
        for (var i = 0; i < 5; i++) {
            html += '<div class="lb-sk-row">' +
                '<span class="sk" style="width:34px;height:34px;border-radius:50%;flex-shrink:0;"></span>' +
                '<div style="flex:1;">' +
                    '<span class="sk" style="height:13px;width:160px;display:block;border-radius:5px;margin-bottom:7px;"></span>' +
                    '<span class="sk" style="height:11px;width:100px;display:block;border-radius:5px;"></span>' +
                '</div>' +
                '<span class="sk" style="height:20px;width:110px;border-radius:5px;margin-right:14px;"></span>' +
                '<span class="sk" style="height:20px;width:54px;border-radius:5px;"></span>' +
                '</div>';
        }
        document.getElementById('lb-body').innerHTML = html;
        document.getElementById('lb-row-count').textContent = '— assessor';
    }

    /* ── Error states ────────────────────────────────────── */
    function showTableError() {
        document.getElementById('vessel-table-body').innerHTML =
            '<tr><td colspan="6"><div class="empty-state">' +
            '<div class="empty-icon" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-exclamation-triangle-fill"></i></div>' +
            '<p style="color:#dc2626;">Gagal memuat data</p>' +
            '<small>Lihat error banner di atas untuk detail</small>' +
            '</div></td></tr>';
    }

    function showCriteriaError() {
        document.getElementById('criteria-profile-list').innerHTML =
            '<div class="empty-state" style="padding:24px 0;">' +
            '<div class="empty-icon" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-exclamation-triangle-fill"></i></div>' +
            '<p style="color:#dc2626;">Gagal memuat data kriteria</p>' +
            '<small>Lihat error banner di atas untuk detail</small>' +
            '</div>';
    }

    /* ── Events ──────────────────────────────────────────── */
    document.getElementById('btn-apply-filter').addEventListener('click', loadAll);
    document.getElementById('btn-reset-filter').addEventListener('click', function() {
        ['filter_company','filter_vessel','filter_start','filter_end'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.value = '';
        });
        loadAll();
    });

    /* ── Init ────────────────────────────────────────────── */
    loadAll();
})();
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/features/hsse-evaluation/dashboard.blade.php ENDPATH**/ ?>