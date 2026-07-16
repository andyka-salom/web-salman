
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>KPI HSSE — <?php echo e($kpiReport->company->name ?? ''); ?> — <?php echo e($kpiReport->kpiPeriod->label ?? ''); ?></title>
<style>
/* ══════ RESET & BASE ══════ */
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'DejaVu Sans', 'Arial', sans-serif;
    font-size: 8pt;
    color: #1a202c;
    background: #fff;
    line-height: 1.4;
}

/* ══════ PAGE ══════ */
@page { margin: 12mm 10mm; size: A4 landscape; }

/* ══════ HEADER ══════ */
.page-header {
    background: #0f2544;
    color: #fff;
    padding: 8px 12px;
    margin-bottom: 10px;
    border-radius: 4px;
}
.page-header h1 { font-size: 12pt; font-weight: bold; margin-bottom: 2px; }
.page-header .meta { font-size: 7.5pt; color: rgba(255,255,255,.75); }
.page-header .meta span { margin-right: 12px; }

.badge {
    display: inline-block;
    padding: 1px 7px;
    border-radius: 10px;
    font-size: 7pt;
    font-weight: bold;
}
.badge-draft     { background: #e2e8f0; color: #475569; }
.badge-submitted { background: #dbeafe; color: #1e40af; }
.badge-validated { background: #dcfce7; color: #166534; }
.badge-rejected  { background: #fee2e2; color: #991b1b; }

/* ══════ INFO GRID ══════ */
.info-grid {
    display: table;
    width: 100%;
    margin-bottom: 8px;
    border-collapse: collapse;
}
.info-grid .info-col {
    display: table-cell;
    width: 33%;
    padding: 5px 8px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    vertical-align: top;
}
.info-lbl { font-size: 6.5pt; color: #64748b; text-transform: uppercase; letter-spacing: .3px; margin-bottom: 2px; }
.info-val  { font-size: 8pt; font-weight: bold; color: #0f172a; }

/* ══════ SCORE SUMMARY ══════ */
.score-bar {
    display: table;
    width: 100%;
    margin-bottom: 10px;
    border-collapse: collapse;
}
.score-cell {
    display: table-cell;
    text-align: center;
    padding: 6px 10px;
    border: 1px solid #e2e8f0;
}
.score-cell.total { background: #0f2544; color: #fff; }
.score-cell.lag   { background: #fffbeb; }
.score-cell.lead  { background: #eff6ff; }
.score-lbl { font-size: 6.5pt; text-transform: uppercase; letter-spacing: .3px; color: inherit; opacity: .7; }
.score-val { font-size: 14pt; font-weight: bold; }
.score-cell.total .score-lbl { color: rgba(255,255,255,.7); opacity:1; }
.score-cell.total .score-val { color: #fde68a; }

/* ══════ TABLE ══════ */
.kpi-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12px;
    font-size: 7.5pt;
}
.kpi-table th {
    background: #0f2544;
    color: #fff;
    padding: 4px 5px;
    text-align: left;
    font-size: 7pt;
    letter-spacing: .2px;
    border: 1px solid rgba(255,255,255,.1);
}
.kpi-table th.center { text-align: center; }
.kpi-table td {
    padding: 4px 5px;
    border: 1px solid #e2e8f0;
    vertical-align: top;
}
.kpi-table tbody tr:nth-child(even) td { background: #f8fafc; }
.kpi-table tbody tr.row-rej td { background: #fff8f8; }
.kpi-table tbody tr.row-ok  td { background: #f7fffe; }
.kpi-table tbody tr.row-ar  td { background: #f8fafc; }

.item-name   { font-weight: bold; color: #0f172a; }
.item-target { font-size: 6.5pt; color: #64748b; font-style: italic; margin-top: 2px; }
.center { text-align: center; }

.tag { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 6.5pt; font-weight: bold; }
.tag-ar  { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
.tag-rej { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.tag-ok  { background: #dcfce7; color: #166534; border: 1px solid #86efac; }

.score-exc  { background: #dcfce7; color: #065f46; font-weight: bold; }
.score-good { background: #dbeafe; color: #1e40af; font-weight: bold; }
.score-fair { background: #fef3c7; color: #78350f; font-weight: bold; }
.score-poor { background: #fee2e2; color: #991b1b; font-weight: bold; }

.sect-header td {
    background: #1e3a5f;
    color: #fff;
    font-weight: bold;
    font-size: 7.5pt;
    padding: 4px 8px;
    letter-spacing: .3px;
}

.total-row td {
    background: #0f2544;
    color: #fff;
    font-weight: bold;
    padding: 5px 8px;
    font-size: 8pt;
}

/* ══════ VESSELS ══════ */
.vessel-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10px;
    font-size: 7.5pt;
}
.vessel-table th {
    background: #334155;
    color: #fff;
    padding: 3px 6px;
    font-size: 7pt;
    border: 1px solid rgba(255,255,255,.1);
}
.vessel-table td {
    padding: 3px 6px;
    border: 1px solid #e2e8f0;
}
.vessel-table tbody tr:nth-child(even) td { background: #f8fafc; }

/* ══════ SECTION TITLE ══════ */
.section-title {
    font-size: 9pt;
    font-weight: bold;
    color: #1e3a5f;
    border-bottom: 2px solid #1e3a5f;
    padding-bottom: 3px;
    margin: 10px 0 6px;
}

/* ══════ FOOTER ══════ */
.page-footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    border-top: 1px solid #e2e8f0;
    padding: 3px 10px;
    font-size: 6.5pt;
    color: #94a3b8;
    display: table;
    width: 100%;
}
.page-footer .left  { display: table-cell; text-align: left; }
.page-footer .right { display: table-cell; text-align: right; }

/* ══════ LAMPIRAN BADGE ══════ */
.ev-badge { display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 6.5pt; font-weight: bold; }
.ev-ok  { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.ev-req { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

.page-break { page-break-after: always; }
</style>
</head>
<body>


<div class="page-footer">
    <div class="left">KPI HSSE Kontraktor — <?php echo e($kpiReport->company->name ?? '-'); ?> — <?php echo e($kpiReport->kpiPeriod->label ?? '-'); ?></div>
    <div class="right">Dicetak: <?php echo e(now()->format('d M Y, H:i')); ?> WIB</div>
</div>


<div class="page-header">
    <h1>Laporan KPI HSSE Kontraktor</h1>
    <div class="meta">
        <span><strong>Perusahaan:</strong> <?php echo e($kpiReport->company->name ?? '-'); ?></span>
        <span><strong>Periode:</strong> <?php echo e($kpiReport->kpiPeriod->label ?? '-'); ?></span>
        <span>
            <strong>Status:</strong>
            <span class="badge badge-<?php echo e($kpiReport->status); ?>">
                <?php echo e(['draft'=>'Draft','submitted'=>'Submitted','validated'=>'Validated','rejected'=>'Rejected'][$kpiReport->status] ?? ucfirst($kpiReport->status)); ?>

            </span>
        </span>
        <?php if($kpiReport->validatedBy): ?>
            <span><strong>Divalidasi oleh:</strong> <?php echo e($kpiReport->validatedBy->name); ?></span>
        <?php endif; ?>
    </div>
</div>


<?php
    $ts     = (float)($kpiReport->total_score         ?? 0);
    $tsLag  = (float)($kpiReport->total_score_lagging ?? 0);
    $tsLead = (float)($kpiReport->total_score_leading ?? 0);
    $scCls  = fn($v) => $v >= 90 ? 'score-exc' : ($v >= 75 ? 'score-good' : ($v >= 60 ? 'score-fair' : 'score-poor'));
?>
<table class="score-bar">
    <tr>
        <td class="score-cell total" style="width:34%;">
            <div class="score-lbl">Total Score KPI HSSE</div>
            <div class="score-val"><?php echo e(number_format($ts, 2)); ?></div>
        </td>
        <td class="score-cell lag" style="width:33%;">
            <div class="score-lbl">Lagging (40%)</div>
            <div class="score-val" style="color:#d97706;"><?php echo e(number_format($tsLag, 2)); ?></div>
        </td>
        <td class="score-cell lead" style="width:33%;">
            <div class="score-lbl">Leading (60%)</div>
            <div class="score-val" style="color:#1d6fe8;"><?php echo e(number_format($tsLead, 2)); ?></div>
        </td>
    </tr>
</table>


<table class="info-grid">
    <tr>
        <td class="info-col">
            <div class="info-lbl">Dibuat Oleh</div>
            <div class="info-val"><?php echo e($kpiReport->createdBy->name ?? '-'); ?></div>
            <div style="font-size:6.5pt;color:#64748b;"><?php echo e($kpiReport->created_at->format('d M Y, H:i')); ?></div>
        </td>
        <td class="info-col">
            <div class="info-lbl">Disubmit Oleh</div>
            <div class="info-val"><?php echo e($kpiReport->submittedBy->name ?? '-'); ?></div>
            <div style="font-size:6.5pt;color:#64748b;"><?php echo e($kpiReport->submitted_at?->format('d M Y, H:i') ?? '—'); ?></div>
        </td>
        <td class="info-col">
            <div class="info-lbl">Divalidasi Oleh</div>
            <div class="info-val"><?php echo e($kpiReport->validatedBy->name ?? '-'); ?></div>
            <div style="font-size:6.5pt;color:#64748b;"><?php echo e($kpiReport->validated_at?->format('d M Y, H:i') ?? '—'); ?></div>
        </td>
    </tr>
</table>


<div class="section-title">🚢 Kapal / Unit &amp; Kontrak</div>
<table class="vessel-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Nama Kapal / Unit</th>
            <th>JML</th>
            <th>No. Kontrak</th>
            <th>Akhir Kontrak</th>
        </tr>
    </thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $kpiReport->vessels->sortBy('sort_order'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vi => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $isExpired = $v->contract_end_date && $v->contract_end_date->isPast();
            $isNear    = $v->contract_end_date && !$isExpired && $v->contract_end_date->diffInMonths(now()) < 3;
        ?>
        <tr>
            <td class="center"><?php echo e($vi + 1); ?></td>
            <td><strong><?php echo e($v->vessel_name); ?></strong></td>
            <td class="center"><?php echo e($v->vessel_count ?: '—'); ?></td>
            <td><?php echo e($v->contract_number ?: '—'); ?></td>
            <td class="center" style="<?php echo e($isExpired ? 'color:#dc2626;font-weight:bold;' : ($isNear ? 'color:#d97706;font-weight:bold;' : '')); ?>">
                <?php echo e($v->contract_end_date?->format('d M Y') ?? '—'); ?>

                <?php if($isExpired): ?> ⚠ Expired <?php elseif($isNear): ?> ⚠ &lt;3bln <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="5" class="center" style="color:#64748b;">—</td></tr>
    <?php endif; ?>
    </tbody>
</table>


<?php
    $lagItems        = $kpiItems->get('lagging', collect());
    $leadItems       = $kpiItems->get('leading', collect());
    $isHsseView      = true; // PDF selalu tampilkan semua kolom

    // Anchor evidence untuk shared lagging 1–7
    $lagSharedNos    = [1, 2, 3, 4, 5, 6, 7];
    $anchorEvs       = $anchorDetail ? $anchorDetail->evidences : collect();
    $anchorEvCnt     = $anchorEvs->count();
?>

<div class="section-title">📉 Section 1 — Lagging Indicator (Bobot 40%)</div>
<table class="kpi-table">
    <thead>
        <tr>
            <th style="width:26px;" class="center">No</th>
            <th style="min-width:130px;">Item KPI</th>
            <th style="width:48px;" class="center">∑ / %</th>
            <th style="min-width:100px;">Keterangan</th>
            <th style="width:65px;" class="center">Lampiran</th>
            <th style="width:38px;" class="center">Nilai</th>
            <th style="width:36px;" class="center">Bobot</th>
            <th style="width:40px;" class="center">Score</th>
            <th style="width:55px;" class="center">Status Review</th>
            <th style="min-width:90px;">Catatan HSSE</th>
        </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $lagItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $det      = $existingDetails[$item->id] ?? null;
        $isAS     = !$item->is_scored;
        $isShared = in_array($item->item_no, $lagSharedNos);
        $isAnchor = ($item->item_no === 1);
        $isRej    = $det && $det->review_status === 'rejected';
        $isOK     = $det && $det->review_status === 'approved';
        $nilaiVal = (float)($det?->nilai ?? 0);
        $score    = $det?->score;
        [$iName, $iTarget] = array_pad(explode("\n", $item->name, 2), 2, '');
        $rowCls   = $isAS ? 'row-ar' : ($isRej ? 'row-rej' : ($isOK ? 'row-ok' : ''));

        // Evidence: shared 1–7 pakai anchor, item 8 pakai sendiri
        $evCnt = $isShared ? $anchorEvCnt : ($det ? $det->evidences->count() : 0);

        // Score CSS
        $sCls = '';
        if (!$isAS && $score !== null) {
            $sCls = $nilaiVal >= 90 ? 'score-exc' : ($nilaiVal >= 75 ? 'score-good' : ($nilaiVal >= 60 ? 'score-fair' : 'score-poor'));
        }
    ?>
    <tr class="<?php echo e($rowCls); ?>">
        <td class="center" style="font-weight:bold;<?php echo e($isRej ? 'color:#dc2626;' : ($isOK ? 'color:#166534;' : '')); ?>">
            <?php echo e($item->item_no); ?>

        </td>
        <td>
            <div class="item-name"><?php echo e(trim($iName)); ?></div>
            <?php if(trim($iTarget)): ?><div class="item-target">🎯 <?php echo e(trim($iTarget)); ?></div><?php endif; ?>
            <?php if($isAS): ?> <span class="tag tag-ar">As Reported</span> <?php endif; ?>
            <?php if($isRej): ?> <span class="tag tag-rej">✗ Ditolak</span> <?php endif; ?>
            <?php if($isOK): ?>  <span class="tag tag-ok">✓ Disetujui</span> <?php endif; ?>
        </td>
        <td class="center" style="font-weight:bold;">
            <?php echo e($det?->actual_count ?? '—'); ?>

            <div style="font-size:6.5pt;color:#64748b;"><?php echo e($item->unit ?? '∑'); ?></div>
        </td>
        <td style="font-size:7pt;color:#374151;"><?php echo e($det?->keterangan ?? '—'); ?></td>
        <td class="center">
            <?php if($isShared && !$isAnchor): ?>
                
                <?php if($anchorEvCnt > 0): ?>
                    <span class="ev-badge ev-ok"><?php echo e($anchorEvCnt); ?>f ↑1</span>
                <?php else: ?>
                    <span class="ev-badge ev-req">Wajib↑1</span>
                <?php endif; ?>
            <?php else: ?>
                <?php if($evCnt > 0): ?>
                    <span class="ev-badge ev-ok"><?php echo e($evCnt); ?> file</span>
                    <?php if($isAnchor): ?><div style="font-size:6pt;color:#64748b;">shared 1–7</div><?php endif; ?>
                <?php else: ?>
                    <span class="ev-badge ev-req">Wajib</span>
                <?php endif; ?>
            <?php endif; ?>
        </td>
        <td class="center">
            <?php if($isAS): ?> <span style="color:#94a3b8;font-size:7pt;">A/R</span>
            <?php else: ?> <?php echo e($nilaiVal > 0 ? number_format($nilaiVal, 1) : '—'); ?> <?php endif; ?>
        </td>
        <td class="center" style="color:#475569;">
            <?php if($item->is_scored): ?> <?php echo e(number_format((float)$item->bobot * 100, 1)); ?>%
            <?php else: ?> <span style="color:#cbd5e1;">—</span> <?php endif; ?>
        </td>
        <td class="center <?php echo e($sCls); ?>">
            <?php if($isAS): ?> <span style="color:#94a3b8;font-size:7pt;">A/R</span>
            <?php else: ?> <?php echo e($score !== null ? number_format((float)$score, 2) : '—'); ?> <?php endif; ?>
        </td>
        <td class="center">
            <?php if($isAS): ?> <span style="color:#94a3b8;font-size:7pt;">—</span>
            <?php elseif($isOK): ?>  <span class="tag tag-ok">✓</span>
            <?php elseif($isRej): ?> <span class="tag tag-rej">✗</span>
            <?php else: ?> <span style="color:#94a3b8;font-size:7pt;">Belum</span> <?php endif; ?>
        </td>
        <td style="font-size:7pt;color:<?php echo e($isRej ? '#b91c1c' : '#374151'); ?>;font-style:<?php echo e($isRej ? 'italic' : 'normal'); ?>;">
            <?php echo e($det?->hsse_catatan ?? '—'); ?>

        </td>
    </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="7" style="text-align:right;font-size:7pt;opacity:.7;">Subtotal Lagging (40%)</td>
            <td class="center" style="color:#fde68a;"><?php echo e(number_format($tsLag, 2)); ?></td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>


<div class="section-title">📈 Section 2 — Leading Indicator (Bobot 60%)</div>
<table class="kpi-table">
    <thead>
        <tr>
            <th style="width:26px;" class="center">No</th>
            <th style="min-width:130px;">Item KPI</th>
            <th style="width:48px;" class="center">∑ / %</th>
            <th style="min-width:100px;">Keterangan</th>
            <th style="width:65px;" class="center">Lampiran</th>
            <th style="width:38px;" class="center">Nilai</th>
            <th style="width:36px;" class="center">Bobot</th>
            <th style="width:40px;" class="center">Score</th>
            <th style="width:55px;" class="center">Status Review</th>
            <th style="min-width:90px;">Catatan HSSE</th>
        </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $leadItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $det      = $existingDetails[$item->id] ?? null;
        $isAS     = !$item->is_scored;
        $isRej    = $det && $det->review_status === 'rejected';
        $isOK     = $det && $det->review_status === 'approved';
        $nilaiVal = (float)($det?->nilai ?? 0);
        $score    = $det?->score;
        $evCnt    = $det ? $det->evidences->count() : 0;
        [$iName, $iTarget] = array_pad(explode("\n", $item->name, 2), 2, '');
        $rowCls   = $isAS ? 'row-ar' : ($isRej ? 'row-rej' : ($isOK ? 'row-ok' : ''));
        $sCls = '';
        if (!$isAS && $score !== null) {
            $sCls = $nilaiVal >= 90 ? 'score-exc' : ($nilaiVal >= 75 ? 'score-good' : ($nilaiVal >= 60 ? 'score-fair' : 'score-poor'));
        }
    ?>
    <tr class="<?php echo e($rowCls); ?>">
        <td class="center" style="font-weight:bold;<?php echo e($isRej ? 'color:#dc2626;' : ($isOK ? 'color:#166534;' : '')); ?>">
            <?php echo e($item->item_no); ?>

        </td>
        <td>
            <div class="item-name"><?php echo e(trim($iName)); ?></div>
            <?php if(trim($iTarget)): ?><div class="item-target">🎯 <?php echo e(trim($iTarget)); ?></div><?php endif; ?>
            <?php if($isAS): ?> <span class="tag tag-ar">As Reported</span> <?php endif; ?>
            <?php if($isRej): ?> <span class="tag tag-rej">✗ Ditolak</span> <?php endif; ?>
            <?php if($isOK): ?>  <span class="tag tag-ok">✓ Disetujui</span> <?php endif; ?>
        </td>
        <td class="center" style="font-weight:bold;">
            <?php echo e($det?->actual_count ?? '—'); ?>

            <div style="font-size:6.5pt;color:#64748b;"><?php echo e($item->unit ?? '∑'); ?></div>
        </td>
        <td style="font-size:7pt;color:#374151;"><?php echo e($det?->keterangan ?? '—'); ?></td>
        <td class="center">
            <?php if($isAS): ?> <span style="color:#94a3b8;font-size:7pt;">—</span>
            <?php elseif($evCnt > 0): ?> <span class="ev-badge ev-ok"><?php echo e($evCnt); ?> file</span>
            <?php else: ?> <span class="ev-badge ev-req">Wajib</span> <?php endif; ?>
        </td>
        <td class="center">
            <?php if($isAS): ?> <span style="color:#94a3b8;font-size:7pt;">A/R</span>
            <?php else: ?> <?php echo e($nilaiVal > 0 ? number_format($nilaiVal, 1) : '—'); ?> <?php endif; ?>
        </td>
        <td class="center" style="color:#475569;">
            <?php if($item->is_scored): ?> <?php echo e(number_format((float)$item->bobot * 100, 1)); ?>%
            <?php else: ?> <span style="color:#cbd5e1;">—</span> <?php endif; ?>
        </td>
        <td class="center <?php echo e($sCls); ?>">
            <?php if($isAS): ?> <span style="color:#94a3b8;font-size:7pt;">A/R</span>
            <?php else: ?> <?php echo e($score !== null ? number_format((float)$score, 2) : '—'); ?> <?php endif; ?>
        </td>
        <td class="center">
            <?php if($isAS): ?> <span style="color:#94a3b8;font-size:7pt;">—</span>
            <?php elseif($isOK): ?>  <span class="tag tag-ok">✓</span>
            <?php elseif($isRej): ?> <span class="tag tag-rej">✗</span>
            <?php else: ?> <span style="color:#94a3b8;font-size:7pt;">Belum</span> <?php endif; ?>
        </td>
        <td style="font-size:7pt;color:<?php echo e($isRej ? '#b91c1c' : '#374151'); ?>;font-style:<?php echo e($isRej ? 'italic' : 'normal'); ?>;">
            <?php echo e($det?->hsse_catatan ?? '—'); ?>

        </td>
    </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="7" style="text-align:right;font-size:7pt;opacity:.7;">Total Score KPI HSSE</td>
            <td class="center" style="font-size:11pt;color:#fde68a;"><?php echo e(number_format($ts, 2)); ?></td>
            <td colspan="2"></td>
        </tr>
        <tr class="total-row" style="background:#1e3a5f;">
            <td colspan="7" style="text-align:right;font-size:7pt;opacity:.7;">Lagging</td>
            <td class="center" style="color:#fde68a;"><?php echo e(number_format($tsLag, 2)); ?></td>
            <td colspan="2"></td>
        </tr>
        <tr class="total-row" style="background:#1e3a5f;">
            <td colspan="7" style="text-align:right;font-size:7pt;opacity:.7;">Leading</td>
            <td class="center" style="color:#bfdbfe;"><?php echo e(number_format($tsLead, 2)); ?></td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>


<table style="width:100%;border-collapse:collapse;margin-top:16px;font-size:8pt;">
    <tr>
        <td style="width:33%;padding:8px;border:1px solid #e2e8f0;vertical-align:top;">
            <div style="font-weight:bold;margin-bottom:50px;">Koordinator Kontraktor</div>
            <div style="border-top:1px solid #334155;padding-top:4px;"><?php echo e($kpiReport->submittedBy->name ?? '____________________'); ?></div>
            <div style="font-size:7pt;color:#64748b;"><?php echo e($kpiReport->submitted_at?->format('d M Y') ?? ''); ?></div>
        </td>
        <td style="width:33%;padding:8px;border:1px solid #e2e8f0;vertical-align:top;">
            <div style="font-weight:bold;margin-bottom:50px;">Verifikator HSSE</div>
            <div style="border-top:1px solid #334155;padding-top:4px;"><?php echo e($kpiReport->validatedBy->name ?? '____________________'); ?></div>
            <div style="font-size:7pt;color:#64748b;"><?php echo e($kpiReport->validated_at?->format('d M Y') ?? ''); ?></div>
        </td>
        <td style="width:34%;padding:8px;border:1px solid #e2e8f0;vertical-align:top;">
            <div style="font-weight:bold;margin-bottom:50px;">Mengetahui</div>
            <div style="border-top:1px solid #334155;padding-top:4px;">____________________</div>
            <div style="font-size:7pt;color:#64748b;">&nbsp;</div>
        </td>
    </tr>
</table>

</body>
</html>
<?php /**PATH /home/kaptensa/salman/resources/views/kpi-hsse/export-pdf.blade.php ENDPATH**/ ?>