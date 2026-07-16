<?php $__env->startSection('title', 'Detail Evaluasi — ' . $hsseEvaluation->crew_name); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?php echo e(asset('plugins/src/sweetalerts2/sweetalerts2.css')); ?>">
<style>
/* ═══════════════════════════════════════════════════════════
   HSSE SHOW — Design System
   Konsisten dengan dashboard.blade.php & edit.blade.php
═══════════════════════════════════════════════════════════ */

/* ── Hero Banner ─────────────────────────────────────────── */
.show-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8e 55%, #1a7fc1 100%);
    border-radius: 16px;
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.show-hero::before {
    content: '';
    position: absolute; top: -50px; right: -40px;
    width: 220px; height: 220px; border-radius: 50%;
    background: rgba(255,255,255,0.05); pointer-events: none;
}
.show-hero::after {
    content: '';
    position: absolute; bottom: -70px; right: 100px;
    width: 160px; height: 160px; border-radius: 50%;
    background: rgba(255,255,255,0.04); pointer-events: none;
}

/* Score circle */
.hero-score-ring {
    width: 92px; height: 92px; flex-shrink: 0;
    border-radius: 50%; position: relative; z-index: 1;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    border: 4px solid rgba(255,255,255,0.25);
    background: rgba(255,255,255,0.10);
}
.hero-score-ring .snum {
    font-size: 2rem; font-weight: 900; line-height: 1;
    color: #fff; letter-spacing: -0.03em;
}
.hero-score-ring .smax {
    font-size: 0.68rem; color: rgba(255,255,255,0.6);
    font-weight: 600; margin-top: 2px;
}

/* Hero text */
.hero-info { position: relative; z-index: 1; }
.hero-crew-name {
    font-size: 1.45rem; font-weight: 800;
    color: #fff; margin: 0 0 3px;
    letter-spacing: -0.025em; line-height: 1.2;
}
.hero-position {
    color: rgba(255,255,255,0.65);
    font-size: 0.84rem; font-weight: 500;
}
.hero-meta {
    display: flex; flex-wrap: wrap; gap: 7px;
    margin-top: 9px;
}
.hero-chip {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 12px; border-radius: 20px;
    background: rgba(255,255,255,0.14);
    color: rgba(255,255,255,0.88);
    font-size: 0.77rem; font-weight: 600;
    border: 1px solid rgba(255,255,255,0.18);
}
.cat-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 15px; border-radius: 20px;
    font-size: 0.81rem; font-weight: 700;
    margin-top: 10px; border: 2px solid transparent;
}
.cat-pill-baik   { background: rgba(34,197,94,0.22);  color: #bbf7d0; border-color: rgba(34,197,94,0.35); }
.cat-pill-cukup  { background: rgba(245,158,11,0.22); color: #fde68a; border-color: rgba(245,158,11,0.35); }
.cat-pill-kurang { background: rgba(239,68,68,0.22);  color: #fecaca; border-color: rgba(239,68,68,0.35); }

/* Status badges in hero */
.status-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 14px; border-radius: 20px;
    font-size: 0.8rem; font-weight: 700;
}
.status-submitted { background: #dcfce7; color: #16a34a; }
.status-draft     { background: #f1f5f9; color: #64748b; }
.submit-time {
    font-size: 0.74rem; color: rgba(255,255,255,0.5);
    margin-top: 6px;
}

/* ── Shared card shell ───────────────────────────────────── */
.detail-card {
    background: var(--card-bg, #fff);
    border-radius: 14px;
    border: 1px solid var(--card-border-color, #e8ecef);
    box-shadow: 0 1px 6px rgba(0,0,0,0.05);
    overflow: hidden;
    margin-bottom: 20px;
}
.detail-card:last-child { margin-bottom: 0; }

.dc-header {
    padding: 14px 20px;
    border-bottom: 1px solid #f1f5f9;
    background: #fafbfc;
    display: flex; align-items: center; gap: 10px;
}
.dc-header .dch-icon {
    width: 30px; height: 30px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem; flex-shrink: 0;
}
.dc-header h6 {
    font-size: 0.84rem; font-weight: 800;
    color: #1e293b; margin: 0;
    text-transform: uppercase; letter-spacing: 0.05em;
    flex: 1;
}
.dc-body { padding: 20px; }

/* ── Field rows ──────────────────────────────────────────── */
.field-row {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #f8fafc;
}
.field-row:last-child { border-bottom: none; }
.fl {
    width: 155px; flex-shrink: 0;
    font-size: 0.76rem; font-weight: 700;
    color: #94a3b8; text-transform: uppercase;
    letter-spacing: 0.04em; padding-top: 1px;
}
.fv {
    font-size: 0.875rem; font-weight: 600;
    color: #1e293b; flex: 1; word-break: break-word;
}
.fv.nil { color: #94a3b8; font-weight: 500; font-style: italic; }

/* ── Criteria score table ────────────────────────────────── */
.criteria-count-pill {
    margin-left: auto;
    font-size: 0.72rem; font-weight: 700;
    background: #f1f5f9; color: #64748b;
    padding: 3px 10px; border-radius: 20px;
    border: 1px solid #e2e8f0;
}
.score-tbl { width: 100%; border-collapse: collapse; }
.score-tbl thead th {
    background: #1e3a5f;
    color: rgba(255,255,255,0.88);
    font-size: 0.74rem; font-weight: 700;
    padding: 11px 16px;
    text-transform: uppercase; letter-spacing: 0.05em;
    border: none; white-space: nowrap;
}
.score-tbl thead th:first-child { padding-left: 20px; }
.score-tbl tbody tr {
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.1s ease;
}
.score-tbl tbody tr:last-child { border-bottom: none; }
.score-tbl tbody tr:hover { background: #f8faff; }
.score-tbl tbody td {
    padding: 13px 16px;
    font-size: 0.84rem; color: #334155;
    vertical-align: middle;
}
.score-tbl tbody td:first-child { padding-left: 20px; }

/* Row accent by score */
.score-tbl tbody tr.row-kurang { border-left: 3px solid #ef4444; }
.score-tbl tbody tr.row-cukup  { border-left: 3px solid #f59e0b; }
.score-tbl tbody tr.row-baik   { border-left: 3px solid #22c55e; }

.crit-no {
    display: inline-flex; align-items: center; justify-content: center;
    width: 26px; height: 26px; border-radius: 50%;
    background: #1e3a5f; color: #fff;
    font-size: 0.71rem; font-weight: 800; flex-shrink: 0;
}
.aspect-txt {
    font-size: 0.84rem; color: #1e293b;
    font-weight: 500; line-height: 1.5;
}
.score-chip {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px; border-radius: 8px;
    font-size: 0.79rem; font-weight: 700;
    white-space: nowrap;
}
.sc-1 { background: #fee2e2; color: #dc2626; }
.sc-2 { background: #fef3c7; color: #d97706; }
.sc-3 { background: #dcfce7; color: #16a34a; }
.ket-txt {
    font-size: 0.76rem; color: #64748b;
    font-style: italic; margin-top: 3px; line-height: 1.4;
}

/* ── Total score summary bar ─────────────────────────────── */
.summary-bar {
    display: flex; align-items: center; justify-content: space-between;
    gap: 16px; flex-wrap: wrap;
    padding: 16px 20px;
    background: #f8fafc;
    border-top: 2px solid #e2e8f0;
}
.summary-bar .sb-label {
    font-size: 0.76rem; font-weight: 700;
    color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;
}
.summary-bar .sb-score {
    font-size: 1.7rem; font-weight: 900;
    color: #0f172a; letter-spacing: -0.04em; line-height: 1;
}
.summary-bar .sb-score span {
    font-size: 0.8rem; color: #94a3b8; font-weight: 500; margin-left: 2px;
}
.score-ref {
    display: flex; gap: 10px; flex-wrap: wrap; margin-top: 4px;
}
.sr-item {
    display: flex; align-items: center; gap: 5px;
    font-size: 0.72rem; color: #94a3b8;
}
.sr-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.cat-chip {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 14px; border-radius: 20px;
    font-size: 0.81rem; font-weight: 700;
}
.cat-chip.baik   { background: #dcfce7; color: #16a34a; }
.cat-chip.cukup  { background: #fef3c7; color: #d97706; }
.cat-chip.kurang { background: #fee2e2; color: #dc2626; }

/* ── Digital Signature ───────────────────────────────────── */
.dig-sig-box {
    border: 1.5px solid #c7d2fe;
    border-radius: 10px;
    padding: 12px 16px;
    background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 100%);
    position: relative;
    min-width: 180px;
    display: inline-block;
}
.dig-sig-box::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; bottom: 0;
    border-radius: 9px;
    background: repeating-linear-gradient(
        45deg, transparent, transparent 4px,
        rgba(99,102,241,0.04) 4px, rgba(99,102,241,0.04) 5px
    );
    pointer-events: none;
}
.dig-sig-name {
    font-family: 'Georgia', serif;
    font-size: 1.05rem; font-weight: 700;
    color: #1e3a5f; letter-spacing: -0.01em;
    margin-bottom: 3px;
}
.dig-sig-pos {
    font-size: 0.74rem; color: #6366f1; font-weight: 600;
    margin-bottom: 5px;
}
.dig-sig-date {
    font-size: 0.72rem; color: #64748b; font-weight: 500;
}
.dig-sig-stamp {
    display: inline-flex; align-items: center; gap: 4px;
    margin-top: 8px; padding: 3px 9px;
    background: #e0e7ff; color: #4338ca;
    border-radius: 20px; font-size: 0.68rem; font-weight: 700;
    border: 1px solid #c7d2fe;
}

/* ── Card footer / meta ──────────────────────────────────── */
.dc-footer {
    padding: 11px 20px;
    background: #fafbfc;
    border-top: 1px solid #f1f5f9;
}
.dc-footer small {
    font-size: 0.73rem; color: #94a3b8; font-weight: 500;
}

/* ── Notes blockquote ────────────────────────────────────── */
.note-block {
    border-left: 3px solid #e2e8f0;
    padding: 10px 14px;
    background: #f8fafc;
    border-radius: 0 8px 8px 0;
    font-size: 0.84rem; color: #475569; line-height: 1.6;
}

/* ── Sticky sidebar ──────────────────────────────────────── */
.sticky-sidebar-col { position: sticky; top: 1.5rem; }

/* Action card */
.action-card {
    background: var(--card-bg, #fff);
    border-radius: 14px;
    border: 1px solid var(--card-border-color, #e8ecef);
    box-shadow: 0 1px 6px rgba(0,0,0,0.05);
    padding: 18px 20px;
    margin-bottom: 16px;
}
.action-card .ac-label {
    font-size: 0.76rem; font-weight: 800;
    color: #94a3b8; text-transform: uppercase;
    letter-spacing: 0.06em; margin-bottom: 14px;
}
.ac-btn {
    display: flex; align-items: center; gap: 9px;
    width: 100%; padding: 10px 16px;
    border-radius: 10px; font-size: 0.84rem; font-weight: 700;
    border: none; cursor: pointer; text-decoration: none;
    transition: all 0.15s ease; margin-bottom: 8px;
    justify-content: flex-start;
}
.ac-btn:last-child { margin-bottom: 0; }
.ac-btn-edit {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff;
    box-shadow: 0 2px 6px rgba(217,119,6,0.28);
}
.ac-btn-edit:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(217,119,6,0.38);
    color: #fff;
}
.ac-btn-delete {
    background: #fff; color: #dc2626;
    border: 1.5px solid #fecaca;
}
.ac-btn-delete:hover { background: #fee2e2; color: #b91c1c; }
.ac-btn-back {
    background: #f8fafc; color: #64748b;
    border: 1.5px solid #e2e8f0;
}
.ac-btn-back:hover { background: #f1f5f9; color: #334155; }

/* Score summary sidebar */
.score-summary-card {
    background: var(--card-bg, #fff);
    border-radius: 14px;
    border: 1px solid var(--card-border-color, #e8ecef);
    box-shadow: 0 1px 6px rgba(0,0,0,0.05);
    overflow: hidden;
}
.ssc-header {
    padding: 14px 18px;
    border-bottom: 1px solid #f1f5f9;
    background: #fafbfc;
    display: flex; align-items: center; gap: 10px;
}
.ssc-header .ssch-icon {
    width: 28px; height: 28px; border-radius: 7px;
    background: #fef3c7; color: #d97706;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.82rem;
}
.ssc-header span {
    font-size: 0.82rem; font-weight: 800;
    color: #1e293b; text-transform: uppercase; letter-spacing: 0.05em;
}
.ssc-body { padding: 18px; }
.crit-mini { margin-bottom: 10px; }
.crit-mini:last-of-type { margin-bottom: 0; }
.crit-mini-top {
    display: flex; justify-content: space-between;
    align-items: center; margin-bottom: 4px;
}
.crit-mini-name {
    font-size: 0.73rem; font-weight: 700;
    color: #64748b; line-height: 1.3;
    flex: 1; padding-right: 6px;
}
.crit-mini-score {
    font-size: 0.73rem; font-weight: 800;
    flex-shrink: 0;
}
.crit-mini-bar {
    height: 5px; border-radius: 99px;
    background: #f1f5f9; overflow: hidden;
}
.crit-mini-fill {
    height: 100%; border-radius: 99px;
}
.ssc-divider {
    border: none; border-top: 1px solid #f1f5f9;
    margin: 14px 0;
}
.ssc-total-row {
    display: flex; align-items: center;
    justify-content: space-between;
}
.ssc-total-label {
    font-size: 0.76rem; font-weight: 700;
    color: #64748b; text-transform: uppercase;
    letter-spacing: 0.04em;
}
.ssc-total-val {
    font-size: 1.45rem; font-weight: 900;
    color: #0f172a; letter-spacing: -0.03em;
    line-height: 1;
}
.ssc-total-max {
    font-size: 0.74rem; color: #94a3b8;
    margin-left: 2px; font-weight: 500;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">
            <div class="col-12">

                
                <?php
                    $sc = $hsseEvaluation->score_category;
                    $ringColor = match($sc) {
                        'baik'   => 'rgba(34,197,94,0.4)',
                        'cukup'  => 'rgba(245,158,11,0.4)',
                        'kurang' => 'rgba(239,68,68,0.4)',
                        default  => 'rgba(255,255,255,0.22)',
                    };
                    $pillClass = match($sc) {
                        'baik'   => 'cat-pill-baik',
                        'cukup'  => 'cat-pill-cukup',
                        'kurang' => 'cat-pill-kurang',
                        default  => 'cat-pill-cukup',
                    };
                    $pillIcon = match($sc) {
                        'baik'   => 'bi-check-circle-fill',
                        'cukup'  => 'bi-dash-circle-fill',
                        'kurang' => 'bi-x-circle-fill',
                        default  => 'bi-circle',
                    };
                ?>

                <div class="show-hero">
                    <div class="row align-items-center g-3">

                        
                        <div class="col-md-7">
                            <div class="d-flex align-items-center gap-4 flex-wrap">
                                <div class="hero-score-ring" style="border-color:<?php echo e($ringColor); ?>;">
                                    <div class="snum"><?php echo e($hsseEvaluation->total_score ?? '—'); ?></div>
                                    <div class="smax">/ 15</div>
                                </div>
                                <div class="hero-info">
                                    <div class="hero-crew-name"><?php echo e($hsseEvaluation->crew_name); ?></div>
                                    <div class="hero-position">
                                        <?php echo e($hsseEvaluation->crew_position ?? 'Jabatan tidak dicatat'); ?>

                                    </div>
                                    <div class="hero-meta">
                                        <span class="hero-chip">
                                            <i class="bi bi-tsunami"></i>
                                            <?php echo e($hsseEvaluation->vessel?->name ?? '—'); ?>

                                        </span>
                                        <span class="hero-chip">
                                            <i class="bi bi-building"></i>
                                            <?php echo e($hsseEvaluation->company?->name ?? '—'); ?>

                                        </span>
                                        <span class="hero-chip">
                                            <i class="bi bi-calendar3"></i>
                                            <?php echo e(\Carbon\Carbon::parse($hsseEvaluation->evaluated_date)->translatedFormat('d M Y')); ?>

                                        </span>
                                    </div>
                                    <?php if($sc): ?>
                                    <div>
                                        <span class="cat-pill <?php echo e($pillClass); ?>">
                                            <i class="bi <?php echo e($pillIcon); ?>"></i>
                                            Kategori: <?php echo e(ucfirst($sc)); ?>

                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        
                        <div class="col-md-5 text-md-end">
                            <?php if($hsseEvaluation->status === 'submitted'): ?>
                                <span class="status-badge status-submitted">
                                    <i class="bi bi-send-check-fill"></i> Submitted
                                </span>
                            <?php else: ?>
                                <span class="status-badge status-draft">
                                    <i class="bi bi-file-earmark-text"></i> Draft
                                </span>
                            <?php endif; ?>

                            <?php if($hsseEvaluation->submitted_at): ?>
                            <div class="submit-time">
                                <i class="bi bi-clock me-1"></i>
                                Disubmit
                                <?php echo e(\Carbon\Carbon::parse($hsseEvaluation->submitted_at)->translatedFormat('d M Y, H:i')); ?>

                            </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>

                
                <div class="row g-4 align-items-start">

                    
                    <div class="col-lg-8">

                        
                        <div class="detail-card">
                            <div class="dc-header">
                                <div class="dch-icon" style="background:#dbeafe; color:#1d4ed8;">
                                    <i class="bi bi-person-badge-fill"></i>
                                </div>
                                <h6>Identitas Kru &amp; Kapal</h6>
                            </div>
                            <div class="dc-body">
                                <div class="row g-0">

                                    
                                    <div class="col-md-6 pe-md-4">
                                        <div class="field-row">
                                            <div class="fl">Nama Kru</div>
                                            <div class="fv"><?php echo e($hsseEvaluation->crew_name); ?></div>
                                        </div>
                                        <div class="field-row">
                                            <div class="fl">Jabatan Kru</div>
                                            <div class="fv <?php echo e(!$hsseEvaluation->crew_position ? 'nil' : ''); ?>">
                                                <?php echo e($hsseEvaluation->crew_position ?? 'Tidak dicatat'); ?>

                                            </div>
                                        </div>
                                        <div class="field-row">
                                            <div class="fl">Tgl Evaluasi</div>
                                            <div class="fv">
                                                <i class="bi bi-calendar3 me-1" style="color:#1d4ed8;"></i>
                                                <?php echo e(\Carbon\Carbon::parse($hsseEvaluation->evaluated_date)->translatedFormat('d M Y')); ?>

                                            </div>
                                        </div>

                                    </div>

                                    
                                    <div class="col-md-6">
                                        <div class="field-row">
                                            <div class="fl">Kapal</div>
                                            <div class="fv">
                                                <i class="bi bi-tsunami me-1" style="color:#059669;"></i>
                                                <?php echo e($hsseEvaluation->vessel?->name ?? '—'); ?>

                                            </div>
                                        </div>
                                        <div class="field-row">
                                            <div class="fl">Perusahaan</div>
                                            <div class="fv">
                                                <i class="bi bi-building me-1" style="color:#64748b;"></i>
                                                <?php echo e($hsseEvaluation->company?->name ?? '—'); ?>

                                            </div>
                                        </div>
                                        <div class="field-row">
                                            <div class="fl">Status</div>
                                            <div class="fv">
                                                <?php if($hsseEvaluation->status === 'submitted'): ?>
                                                <span style="font-size:.78rem; font-weight:700;
                                                             background:#dcfce7; color:#16a34a;
                                                             padding:3px 10px; border-radius:20px;">
                                                    <i class="bi bi-send-check-fill me-1"></i>Submitted
                                                </span>
                                                <?php else: ?>
                                                <span style="font-size:.78rem; font-weight:700;
                                                             background:#f1f5f9; color:#64748b;
                                                             padding:3px 10px; border-radius:20px;">
                                                    <i class="bi bi-file-earmark me-1"></i>Draft
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                
                                <?php if($hsseEvaluation->notes): ?>
                                <div class="field-row" style="margin-top:4px; border-top:1px solid #f1f5f9; padding-top:14px;">
                                    <div class="fl">Catatan</div>
                                    <div class="fv" style="flex:1;">
                                        <div class="note-block"><?php echo e($hsseEvaluation->notes); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        
                        <div class="detail-card">
                            <div class="dc-header">
                                <div class="dch-icon" style="background:#ede9fe; color:#7c3aed;">
                                    <i class="bi bi-list-check"></i>
                                </div>
                                <h6>Hasil Penilaian Kriteria</h6>
                                <span class="criteria-count-pill">
                                    <?php echo e($hsseEvaluation->scores->count()); ?> kriteria
                                </span>
                            </div>

                            
                            <div class="table-responsive">
                                <table class="score-tbl">
                                    <thead>
                                        <tr>
                                            <th style="width:40px;">#</th>
                                            <th>Aspek Penilaian</th>
                                            <th class="text-center" style="width:140px;">Nilai</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $sortedScores = $hsseEvaluation->scores
                                                ->sortBy(fn($s) => $s->criteria?->order_no ?? 99);
                                        ?>
                                        <?php $__empty_1 = true; $__currentLoopData = $sortedScores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $score): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <?php
                                            $rowClass  = match((int)$score->score) { 1=>'row-kurang', 2=>'row-cukup', 3=>'row-baik', default=>'' };
                                            $chipClass = 'sc-' . $score->score;
                                            $scoreLabel = match((int)$score->score) { 1=>'Kurang', 2=>'Cukup', 3=>'Baik', default=>'-' };
                                            $scoreIcon  = match((int)$score->score) {
                                                1 => 'bi-x-circle-fill',
                                                2 => 'bi-dash-circle-fill',
                                                3 => 'bi-check-circle-fill',
                                                default => 'bi-circle',
                                            };
                                        ?>
                                        <tr class="<?php echo e($rowClass); ?>">
                                            <td>
                                                <div class="crit-no">
                                                    <?php echo e($score->criteria?->order_no ?? '?'); ?>

                                                </div>
                                            </td>
                                            <td>
                                                <div class="aspect-txt">
                                                    <?php echo e($score->criteria?->aspect ?? '—'); ?>

                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="score-chip <?php echo e($chipClass); ?>">
                                                    <i class="bi <?php echo e($scoreIcon); ?>"></i>
                                                    <?php echo e($score->score); ?> &mdash; <?php echo e($scoreLabel); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <?php if($score->keterangan): ?>
                                                <div class="ket-txt"><?php echo e($score->keterangan); ?></div>
                                                <?php else: ?>
                                                <span style="color:#d1d5db; font-size:.8rem;">—</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                                Belum ada data penilaian.
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            
                            <?php
                                $catClass = match($sc) { 'baik'=>'baik', 'cukup'=>'cukup', 'kurang'=>'kurang', default=>'cukup' };
                                $catIcon  = match($sc) {
                                    'baik'   => 'bi-check-circle-fill',
                                    'cukup'  => 'bi-dash-circle-fill',
                                    'kurang' => 'bi-x-circle-fill',
                                    default  => 'bi-circle',
                                };
                            ?>
                            <div class="summary-bar">
                                <div>
                                    <div class="sb-label">Total Skor</div>
                                    <div class="score-ref">
                                        <div class="sr-item">
                                            <div class="sr-dot" style="background:#ef4444;"></div>
                                            5–8 = Kurang
                                        </div>
                                        <div class="sr-item">
                                            <div class="sr-dot" style="background:#f59e0b;"></div>
                                            9–11 = Cukup
                                        </div>
                                        <div class="sr-item">
                                            <div class="sr-dot" style="background:#22c55e;"></div>
                                            12–15 = Baik
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div class="sb-score">
                                        <?php echo e($hsseEvaluation->total_score ?? '—'); ?>

                                        <span>/ 15</span>
                                    </div>
                                </div>
                                <div>
                                    <?php if($sc): ?>
                                    <span class="cat-chip <?php echo e($catClass); ?>">
                                        <i class="bi <?php echo e($catIcon); ?>"></i>
                                        <?php echo e(ucfirst($sc)); ?>

                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        
                        <div class="detail-card">
                            <div class="dc-header">
                                <div class="dch-icon" style="background:#dcfce7; color:#16a34a;">
                                    <i class="bi bi-person-check-fill"></i>
                                </div>
                                <h6>Data Assessor</h6>
                            </div>
                            <div class="dc-body">
                                <div class="row g-0">

                                    
                                    <div class="col-md-6 pe-md-4">
                                        <div class="field-row">
                                            <div class="fl">Nama</div>
                                            <div class="fv <?php echo e(!$hsseEvaluation->assessor_name ? 'nil' : ''); ?>">
                                                <?php echo e($hsseEvaluation->assessor_name ?? 'Tidak dicatat'); ?>

                                            </div>
                                        </div>
                                        <div class="field-row">
                                            <div class="fl">Jabatan</div>
                                            <div class="fv <?php echo e(!$hsseEvaluation->assessor_position ? 'nil' : ''); ?>">
                                                <?php echo e($hsseEvaluation->assessor_position ?? 'Tidak dicatat'); ?>

                                            </div>
                                        </div>
                                        <?php if($hsseEvaluation->assessor): ?>
                                        <div class="field-row">
                                            <div class="fl">Akun</div>
                                            <div class="fv" style="font-size:.8rem;">
                                                <?php echo e($hsseEvaluation->assessor->name); ?>

                                                <?php if($hsseEvaluation->assessor->email): ?>
                                                <div style="color:#94a3b8; font-weight:500;">
                                                    <?php echo e($hsseEvaluation->assessor->email); ?>

                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>

                                    
                                    <div class="col-md-6">
                                        <div class="field-row">
                                            <div class="fl">Tanda Tangan</div>
                                            <div class="fv">
                                                <div class="dig-sig-box">
                                                    <div class="dig-sig-name">
                                                        <?php echo e($hsseEvaluation->assessor_name ?? '—'); ?>

                                                    </div>
                                                    <?php if($hsseEvaluation->assessor_position): ?>
                                                    <div class="dig-sig-pos">
                                                        <?php echo e($hsseEvaluation->assessor_position); ?>

                                                    </div>
                                                    <?php endif; ?>
                                                    <div class="dig-sig-date">
                                                        <i class="bi bi-calendar3 me-1"></i>
                                                        <?php echo e(\Carbon\Carbon::parse($hsseEvaluation->evaluated_date)->translatedFormat('d M Y')); ?>

                                                    </div>
                                                    <div class="dig-sig-stamp">
                                                        <i class="bi bi-patch-check-fill me-1"></i>Digital
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="dc-footer">
                                <small>
                                    <i class="bi bi-clock me-1"></i>
                                    Dibuat:
                                    <?php echo e(\Carbon\Carbon::parse($hsseEvaluation->created_at)->translatedFormat('d M Y, H:i')); ?>

                                    <?php if($hsseEvaluation->updated_at->ne($hsseEvaluation->created_at)): ?>
                                    &nbsp;&middot;&nbsp;
                                    Diperbarui:
                                    <?php echo e(\Carbon\Carbon::parse($hsseEvaluation->updated_at)->translatedFormat('d M Y, H:i')); ?>

                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>

                    </div>

                    
                    <div class="col-lg-4">
                        <div class="sticky-sidebar-col">

                            
                            <div class="action-card">
                                <div class="ac-label">Aksi</div>

                                <?php if(Auth::user()->hasAnyRole(['super-admin', 'hsse'])): ?>
                                <a href="<?php echo e(route('hsse-evaluation.edit', $hsseEvaluation->id)); ?>"
                                   class="ac-btn ac-btn-edit">
                                    <i class="bi bi-pencil-square"></i>
                                    Edit Evaluasi
                                </a>
                                <button type="button"
                                        class="ac-btn ac-btn-delete"
                                        id="btn-delete"
                                        data-url="<?php echo e(route('hsse-evaluation.destroy', $hsseEvaluation->id)); ?>"
                                        data-name="<?php echo e($hsseEvaluation->crew_name); ?>">
                                    <i class="bi bi-trash3"></i>
                                    Hapus Evaluasi
                                </button>
                                <?php endif; ?>

                                <a href="<?php echo e(route('hsse-evaluation.index')); ?>"
                                   class="ac-btn ac-btn-back">
                                    <i class="bi bi-arrow-left"></i>
                                    Kembali ke Daftar
                                </a>
                            </div>

                            
                            <div class="score-summary-card">
                                <div class="ssc-header">
                                    <div class="ssch-icon">
                                        <i class="bi bi-bar-chart-line-fill"></i>
                                    </div>
                                    <span>Ringkasan Skor</span>
                                </div>
                                <div class="ssc-body">

                                    <?php $__currentLoopData = $sortedScores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $score): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $barColor = match((int)$score->score) {
                                            1 => '#ef4444',
                                            2 => '#f59e0b',
                                            3 => '#22c55e',
                                            default => '#e2e8f0',
                                        };
                                        $barPct = $score->score ? (($score->score / 3) * 100) : 0;
                                    ?>
                                    <div class="crit-mini">
                                        <div class="crit-mini-top">
                                            <div class="crit-mini-name">
                                                <span style="color:#94a3b8;">K<?php echo e($score->criteria?->order_no); ?>.</span>
                                                <?php echo e(\Illuminate\Support\Str::limit($score->criteria?->aspect ?? '—', 30)); ?>

                                            </div>
                                            <div class="crit-mini-score" style="color:<?php echo e($barColor); ?>;">
                                                <?php echo e($score->score ?? '—'); ?>/3
                                            </div>
                                        </div>
                                        <div class="crit-mini-bar">
                                            <div class="crit-mini-fill"
                                                 style="width:<?php echo e($barPct); ?>%; background:<?php echo e($barColor); ?>;"></div>
                                        </div>
                                    </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                    <hr class="ssc-divider">

                                    <div class="ssc-total-row">
                                        <div class="ssc-total-label">Total</div>
                                        <div class="d-flex align-items-baseline gap-2">
                                            <span class="ssc-total-val">
                                                <?php echo e($hsseEvaluation->total_score ?? '—'); ?>

                                            </span>
                                            <span class="ssc-total-max">/ 15</span>
                                            <?php if($sc): ?>
                                            <span class="cat-chip <?php echo e($catClass); ?>"
                                                  style="padding:3px 10px; font-size:.73rem;">
                                                <?php echo e(ucfirst($sc)); ?>

                                            </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('plugins/src/sweetalerts2/sweetalerts2.min.js')); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    var btnDelete = document.getElementById('btn-delete');
    if (!btnDelete) return;

    btnDelete.addEventListener('click', function () {
        var url  = this.dataset.url;
        var name = this.dataset.name;

        Swal.fire({
            title: 'Hapus Permanen?',
            html : 'Evaluasi kru <strong>' + name + '</strong> akan dihapus ' +
                   '<strong>permanen</strong> beserta semua data penilaian dan ' +
                   'tanda tangan.<br><br>' +
                   '<small class="text-danger">' +
                   '<i class="bi bi-exclamation-triangle me-1"></i>' +
                   'Tindakan ini tidak dapat dibatalkan.' +
                   '</small>',
            icon : 'warning',
            showCancelButton  : true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor : '#6b7280',
            confirmButtonText : '<i class="bi bi-trash3 me-1"></i>Ya, Hapus Permanen',
            cancelButtonText  : 'Batal',
            reverseButtons    : true,
        }).then(function (result) {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Menghapus...',
                allowOutsideClick: false,
                showConfirmButton : false,
                didOpen: function () { Swal.showLoading(); },
            });

            fetch(url, {
                method : 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    Swal.fire({
                        icon : 'success',
                        title: 'Dihapus!',
                        text : res.message,
                        timer: 1600,
                        showConfirmButton: false,
                    }).then(function () {
                        window.location.href = '<?php echo e(route("hsse-evaluation.index")); ?>';
                    });
                } else {
                    Swal.fire('Gagal', res.message || 'Terjadi kesalahan.', 'error');
                }
            })
            .catch(function () {
                Swal.fire('Error', 'Gagal menghubungi server. Coba lagi.', 'error');
            });
        });
    });

});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/features/hsse-evaluation/show.blade.php ENDPATH**/ ?>