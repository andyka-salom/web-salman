


<?php
    $isCreate = ($mode === 'create');
    $isEdit   = ($mode === 'edit');
    $isShow   = ($mode === 'show');
    $report   = $kpiReport ?? null;
    $rStatus  = $report ? $report->status : 'draft';

    $pageTitle = $isCreate
        ? 'Buat Laporan KPI HSSE'
        : ($isEdit
            ? 'Edit KPI HSSE — ' . ($report?->kpiPeriod?->label ?? '')
            : 'Detail KPI HSSE — ' . ($report?->kpiPeriod?->label ?? ''));

    $isKoord = isset($isKoord) ? (bool) $isKoord : false;
    $isHsse  = isset($isHsse)  ? (bool) $isHsse  : false;
    $isSA    = isset($isSA)    ? (bool) $isSA    : false;

    $ts     = $report ? (float) $report->total_score         : 0;
    $tsLag  = $report ? (float) $report->total_score_lagging : 0;
    $tsLead = $report ? (float) $report->total_score_leading : 0;

    $ringCls = $ts <= 0 ? 'ring-none'
        : ($ts >= 90 ? 'ring-exc' : ($ts >= 75 ? 'ring-good' : ($ts >= 60 ? 'ring-fair' : 'ring-poor')));

    $canEditKoord = isset($canEditKoord) ? (bool) $canEditKoord : false;
    $canEditHsse  = isset($canEditHsse)  ? (bool) $canEditHsse  : false;
    $canReview    = isset($canReview)    ? (bool) $canReview    : false;

    $rejCnt     = isset($rejCnt)     ? (int) $rejCnt     : 0;
    $mCnt       = isset($mCnt)       ? (int) $mCnt       : 0;
    $totalItems = isset($totalItems) ? (int) $totalItems : 0;

    $showTabKpi = !$isCreate;

    $scCls = fn(float $nilai) => $nilai >= 90 ? 's-exc'
        : ($nilai >= 75 ? 's-good' : ($nilai >= 60 ? 's-fair' : 's-poor'));

    $canDoInlineReview = ($isHsse || $isSA) && $canReview;

    // Item lagging yang berbagi 1 pool lampiran di anchor No.1
    $lagSharedNos = [1, 2, 3, 4, 5, 6, 7];

    // Item lagging yang WAJIB direview HSSE (scored + As Reported No.6-7)
    // Item No.8 (Manhours) As Reported → tidak perlu review
    $lagReviewableNos = [1, 2, 3, 4, 5, 6, 7, 8];
?>

<?php $__env->startSection('title', $pageTitle); ?>

<?php $__env->startPush('styles'); ?>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800;1,9..40,400&display=swap" rel="stylesheet">
<style>
/* ══════ DESIGN TOKENS ══════ */
:root {
    --navy:        #0f2544;
    --navy-mid:    #1e3a5f;
    --navy-dark:   #0a1a33;
    --blue:        #1d6fe8;
    --blue-light:  #e8f0fd;
    --blue-mid:    #c2d6fb;
    --teal:        #0d9488;
    --teal-light:  #e0f7f5;
    --amber:       #d97706;
    --amber-light: #fef3c7;
    --amber-mid:   #fde68a;
    --green:       #16a34a;
    --green-light: #dcfce7;
    --green-mid:   #86efac;
    --red:         #dc2626;
    --red-light:   #fee2e2;
    --red-mid:     #fca5a5;
    --orange:      #ea580c;
    --orange-light:#fff7ed;
    --orange-mid:  #fdba74;
    --gray-50:  #f8fafc; --gray-100: #f1f5f9; --gray-200: #e2e8f0;
    --gray-300: #cbd5e1; --gray-400: #94a3b8; --gray-500: #64748b;
    --gray-600: #475569; --gray-700: #334155; --gray-900: #0f172a;
    --radius:    12px;
    --radius-sm: 8px;
    --radius-xs: 6px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.07),0 1px 2px rgba(0,0,0,.05);
    --shadow-md: 0 4px 16px rgba(0,0,0,.09),0 2px 4px rgba(0,0,0,.06);
    --shadow-lg: 0 8px 32px rgba(0,0,0,.12),0 4px 8px rgba(0,0,0,.06);
}
*,*::before,*::after{box-sizing:border-box}
.kpi-page{font-family:'DM Sans','Segoe UI',system-ui,sans-serif;color:var(--gray-900);-webkit-font-smoothing:antialiased}

/* ── LAYOUT ── */
.kpi-wrap{max-width:1160px;margin:0 auto;padding:0 1rem 100px}
.kpi-main{display:grid;grid-template-columns:1fr 288px;gap:1.5rem;align-items:start}
@media(max-width:1040px){.kpi-main{grid-template-columns:1fr}}
@media(min-width:1041px){.kpi-sidebar-inner{position:sticky;top:1.5rem}}

/* ── PAGE HEADER ── */
.kpi-header{background:linear-gradient(135deg,var(--navy) 0%,var(--navy-mid) 100%);border-radius:var(--radius);padding:1.35rem 1.6rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;box-shadow:0 4px 20px rgba(15,37,68,.25)}
.kpi-header-left{display:flex;flex-direction:column;gap:.3rem}
.kpi-header-title{font-size:1.05rem;font-weight:800;color:#fff;letter-spacing:-.2px;margin:0}
.kpi-header-meta{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap}
.kpi-header-meta span{font-size:.75rem;color:rgba(255,255,255,.55)}
.kpi-header-meta strong{font-size:.75rem;color:rgba(255,255,255,.9);font-weight:600}
.kpi-header-meta .sep{color:rgba(255,255,255,.2)}

/* ── STATUS BADGES ── */
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:.68rem;font-weight:700;letter-spacing:.2px;border:1px solid transparent}
.badge-draft{background:rgba(255,255,255,.12);color:rgba(255,255,255,.8);border-color:rgba(255,255,255,.2)}
.badge-submitted{background:#dbeafe;color:#1e40af;border-color:#bfdbfe}
.badge-validated{background:var(--green-light);color:#166534;border-color:var(--green-mid)}
.badge-rejected{background:var(--red-light);color:#991b1b;border-color:var(--red-mid)}

/* ── WIZARD ── */
.wizard-wrap{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius);padding:.9rem 1.3rem;margin-bottom:1.25rem;box-shadow:var(--shadow-sm)}
.wizard-track{display:flex;align-items:center;gap:0}
.wizard-step{flex:1;display:flex;flex-direction:column;align-items:center;gap:.35rem;position:relative;cursor:pointer}
.wizard-step:not(:last-child)::after{content:'';position:absolute;left:50%;right:-50%;top:16px;height:2px;background:var(--gray-200);z-index:0;transition:background .3s}
.wizard-step.done:not(:last-child)::after{background:var(--blue)}
.wizard-step.active:not(:last-child)::after{background:linear-gradient(90deg,var(--blue),var(--gray-200))}
.ws-circle{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;position:relative;z-index:1;transition:all .25s;border:2px solid var(--gray-200);background:#fff;color:var(--gray-400)}
.wizard-step.active .ws-circle{border-color:var(--blue);background:var(--blue);color:#fff;box-shadow:0 0 0 3px var(--blue-light)}
.wizard-step.done  .ws-circle{border-color:var(--blue);background:var(--blue-light);color:var(--blue)}
.ws-icon{font-size:13px}
.ws-label{font-size:.64rem;font-weight:600;color:var(--gray-400);text-align:center;line-height:1.3;white-space:nowrap}
.wizard-step.active .ws-label{color:var(--blue)}
.wizard-step.done  .ws-label{color:var(--navy-mid)}

/* ── STEP PANELS ── */
.step-panel{display:none}
.step-panel.active{display:block}

/* ── CARD ── */
.card{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius);box-shadow:var(--shadow-sm);margin-bottom:1.25rem;overflow:hidden}
.card-header{display:flex;align-items:center;gap:.75rem;padding:.9rem 1.25rem;background:var(--gray-50);border-bottom:1px solid var(--gray-200)}
.card-icon{width:34px;height:34px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.card-title{font-weight:700;font-size:.88rem;color:var(--gray-900);margin:0}
.card-sub{font-size:.71rem;color:var(--gray-400);margin:.12rem 0 0}
.card-body{padding:1.25rem}

/* ── SECTION BANNERS ── */
.sect-banner{display:flex;align-items:center;justify-content:space-between;padding:.55rem .95rem;border-radius:var(--radius-sm);font-weight:700;font-size:.78rem;margin-bottom:.75rem;letter-spacing:.1px}
.sect-banner.lag{background:linear-gradient(135deg,#fffdf5 0%,var(--amber-light) 100%);color:#78350f;border:1px solid #fde68a}
.sect-banner.lead{background:linear-gradient(135deg,#f8fbff 0%,#e0e7ff 100%);color:#1e40af;border:1px solid var(--blue-mid)}
.sect-banner .pill{font-size:.63rem;background:rgba(0,0,0,.09);padding:2px 9px;border-radius:20px;font-weight:700}

/* ── FORM FIELDS ── */
.field{margin-bottom:1rem}
.field:last-child{margin-bottom:0}
.field-lbl{display:block;font-size:.7rem;font-weight:700;color:var(--gray-600);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.38rem}
.field-req{color:#ef4444}
.field-input,.field-select,.field-textarea{width:100%;border:1.5px solid var(--gray-200);border-radius:var(--radius-sm);padding:.55rem .8rem;font-size:.87rem;color:var(--gray-900);background:#fff;outline:none;transition:border-color .15s,box-shadow .15s;font-family:inherit}
.field-input:focus,.field-select:focus,.field-textarea:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(29,111,232,.1)}
.field-textarea{resize:vertical}
.field-select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%2394a3b8' fill='none' stroke-width='1.8' stroke-linecap='round'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right .75rem center;padding-right:2.2rem}

/* ── VESSEL TABLE ── */
.vtbl-wrap{overflow-x:auto}
.vtbl{width:100%;border-collapse:collapse;font-size:.83rem;min-width:580px}
.vtbl th{background:var(--gray-50);color:var(--gray-600);font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;padding:.55rem .75rem;border:1px solid var(--gray-200);white-space:nowrap}
.vtbl td{border:1px solid var(--gray-200);padding:0;background:#fff;vertical-align:middle}
.vtbl-inp{width:100%;border:none;outline:none;padding:.52rem .75rem;font-size:.83rem;background:transparent;color:var(--gray-900);font-family:inherit;transition:background .12s}
.vtbl-inp:focus{background:#fefce8}
.vtbl tr:hover td{background:#fafbff}
.vtbl .td-no{width:34px;text-align:center;background:var(--gray-50) !important;font-size:.7rem;font-weight:700;color:var(--gray-400);padding:.5rem .4rem}
.vtbl .td-del{width:36px;text-align:center;background:var(--gray-50) !important;padding:.4rem}
.btn-del-vessel{width:24px;height:24px;border-radius:50%;background:var(--red-light);border:none;color:var(--red);font-size:14px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:background .15s;line-height:1}
.btn-del-vessel:hover{background:var(--red-mid)}

/* ── ADD ROW BUTTON ── */
.btn-add-row{display:inline-flex;align-items:center;gap:6px;padding:.4rem .9rem;font-size:.76rem;font-weight:600;background:#f0f7ff;border:1.5px dashed var(--blue);border-radius:var(--radius-sm);color:var(--blue);cursor:pointer;transition:background .15s;margin-top:.6rem;font-family:inherit}
.btn-add-row:hover{background:var(--blue-light)}

/* ── KPI TABLE ── */
.kpi-tbl-wrap{overflow-x:auto;border-radius:0 0 var(--radius) var(--radius)}
.kpi-tbl{width:100%;border-collapse:collapse;font-size:.8rem;min-width:780px}
.kpi-tbl-head th{background:var(--navy);color:#fff;font-size:.61rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:.5rem .65rem;border-right:1px solid rgba(255,255,255,.07);border-bottom:none;white-space:nowrap;vertical-align:middle}
.kpi-tbl-head th:last-child{border-right:none}

/* ── KPI ROW ── */
.kpi-row{transition:background .1s}
.kpi-row td{border-right:1px solid var(--gray-200);border-bottom:1px solid #f0f4f8;vertical-align:middle}
.kpi-row td:last-child{border-right:none}
.kpi-row:hover td{background:#f9fbff !important}
.kpi-row.r-rej td{background:#fffafa}
.kpi-row.r-ok  td{background:#f9fffe}
.kpi-row.r-ar  td{background:#fafbfc}

/* ── NO COLUMN ── */
.td-no-kpi{width:36px;text-align:center;padding:.4rem .25rem;font-size:.7rem;font-weight:800;color:var(--gray-400);background:var(--gray-50) !important;border-right:2px solid var(--gray-100) !important}
.kpi-row.r-rej .td-no-kpi{border-left:3px solid #ef4444 !important;color:#ef4444;background:#fff5f5 !important}
.kpi-row.r-ok  .td-no-kpi{border-left:3px solid var(--green) !important;color:var(--green);background:#f5fff8 !important}

/* ── ITEM KPI COLUMN ── */
.td-item{padding:.5rem .8rem;min-width:175px;max-width:215px;vertical-align:top}
.item-name{font-weight:600;color:var(--gray-900);font-size:.8rem;line-height:1.4}
.item-target{font-size:.63rem;color:var(--gray-400);margin-top:3px;padding-top:3px;border-top:1px dashed var(--gray-200);font-style:italic;line-height:1.4}
.item-tags{display:flex;flex-wrap:wrap;gap:3px;margin-top:4px}
.itag{display:inline-flex;align-items:center;gap:2px;padding:1px 6px;border-radius:3px;font-size:.59rem;font-weight:700;border:1px solid transparent}
.itag-ar {background:var(--gray-100);color:var(--gray-500);border-color:var(--gray-200)}
.itag-rej{background:var(--red-light);color:#991b1b;border-color:var(--red-mid)}
.itag-ok {background:var(--green-light);color:#166534;border-color:var(--green-mid)}

/* ── REMARK BOX (koordinator) — UPGRADED dari .item-rej-note ── */
.rej-remark-box{
    background:#fff1f2;
    border:1px solid #fca5a5;
    border-left:3px solid #ef4444;
    border-radius:5px;
    padding:5px 8px;
    margin-top:5px;
}
.rej-remark-label{
    font-size:.58rem;
    font-weight:800;
    color:#991b1b;
    text-transform:uppercase;
    letter-spacing:.3px;
    margin-bottom:2px;
    display:flex;
    align-items:center;
    gap:3px;
}
.rej-remark-text{
    font-size:.73rem;
    color:#7f1d1d;
    line-height:1.5;
    font-style:italic;
}

/* ── JML / INPUT COLUMN ── */
.td-jml{padding:4px 5px;width:100px}
.jml-wrap{display:flex;align-items:center;gap:3px}
.jml-inp{flex:1;min-width:0;border:1.5px solid var(--gray-200);border-radius:var(--radius-xs);padding:4px 5px;font-size:.82rem;font-weight:700;text-align:center;background:#fffbeb;outline:none;transition:border-color .15s,background .15s,box-shadow .15s;font-family:inherit}
.jml-inp:focus{border-color:#f59e0b;background:#fefce8;box-shadow:0 0 0 2px rgba(245,158,11,.12)}
.jml-inp.saving{border-color:#f59e0b;background:#fefce8}
.jml-inp.saved{border-color:var(--green-mid);background:var(--green-light)}
.jml-inp.err{border-color:var(--red-mid);background:var(--red-light)}
.jml-inp:disabled{background:var(--gray-100);color:var(--gray-400);cursor:not-allowed}
.jml-ar{background:#f8fafc !important;border-style:dashed !important;color:var(--gray-500) !important}
.jml-ar:focus{background:#fefce8 !important;border-color:#f59e0b !important;border-style:solid !important}
.jml-inp.mandatory-empty{border-color:var(--red-mid) !important;background:#fff8f8 !important}
.nilai-inp.mandatory-empty{border-color:var(--red-mid) !important;background:#fff8f8 !important}
.unit-tag{flex-shrink:0;background:var(--gray-100);border:1px solid var(--gray-200);border-radius:3px;padding:1px 4px;font-size:.58rem;font-weight:700;color:var(--gray-500)}
.jml-readonly{text-align:center;font-weight:700;font-size:.82rem;padding:.35rem .3rem;color:var(--gray-900);line-height:1.2}
.jml-readonly .jml-unit{font-size:.6rem;color:var(--gray-400);font-weight:400;margin-top:1px}

/* ── KETERANGAN COLUMN ── */
.td-ket{padding:4px 5px;min-width:140px}
.ket-inp{width:100%;border:1.5px solid var(--gray-200);border-radius:var(--radius-xs);padding:4px 6px;font-size:.74rem;background:#fffbeb;outline:none;resize:none;transition:border-color .15s,box-shadow .15s;font-family:inherit;min-height:42px;line-height:1.4}
.ket-inp:focus{border-color:#f59e0b;background:#fefce8;box-shadow:0 0 0 2px rgba(245,158,11,.12)}
.ket-inp.saved{border-color:var(--green-mid);background:var(--green-light)}
.ket-readonly{padding:.35rem .5rem;font-size:.76rem;color:#374151;line-height:1.5}

/* ── EVIDENCE / LAMPIRAN COLUMN ── */
.td-ev{padding:5px 7px;min-width:150px;max-width:190px;vertical-align:top}
.ev-grid{display:flex;flex-wrap:wrap;gap:3px;margin-bottom:4px}
.ev-item{position:relative;display:inline-block}
.ev-thumb{width:30px;height:30px;object-fit:cover;border-radius:4px;border:1px solid var(--gray-200);cursor:pointer;display:block;transition:transform .15s,border-color .15s,box-shadow .15s}
.ev-thumb:hover{transform:scale(1.15);border-color:#6366f1;box-shadow:0 2px 8px rgba(99,102,241,.25)}
.ev-pdf{width:30px;height:30px;border-radius:4px;border:1px solid var(--blue-mid);background:var(--blue-light);display:flex;align-items:center;justify-content:center;font-size:13px;text-decoration:none;transition:transform .15s}
.ev-pdf:hover{transform:scale(1.1)}
.ev-del-btn{position:absolute;top:-4px;right:-4px;width:14px;height:14px;border-radius:50%;background:var(--red);color:#fff;font-size:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;border:1.5px solid #fff;opacity:0;transition:opacity .15s;z-index:4;line-height:1}
.ev-item:hover .ev-del-btn{opacity:1}
.ev-upload-btn{display:inline-flex;align-items:center;gap:3px;padding:3px 7px;font-size:.64rem;font-weight:600;background:#f0f9ff;border:1px dashed #7dd3fc;border-radius:4px;color:#0369a1;cursor:pointer;white-space:nowrap;transition:background .12s;font-family:inherit;margin-bottom:2px}
.ev-upload-btn:hover{background:#e0f2fe;border-color:#38bdf8}
.ev-upload-btn.busy{opacity:.5;pointer-events:none}
.ev-prog{display:none;height:2px;background:var(--gray-200);border-radius:2px;margin-top:3px;overflow:hidden}
.ev-prog.on{display:block}
.ev-bar{height:100%;background:var(--blue);width:0;transition:width .2s}
.ev-err{font-size:.6rem;color:var(--red);margin-top:2px;display:none;line-height:1.3}
.ev-err.on{display:block}
.ev-na{font-size:.64rem;color:var(--gray-300);font-style:italic;display:block;text-align:center;padding:.4rem 0}
.ev-hint{font-size:.62rem;color:var(--gray-300);font-style:italic}
.ev-stat-ok{display:inline-flex;align-items:center;gap:3px;font-size:.6rem;background:var(--green-light);color:#166534;border:1px solid var(--green-mid);border-radius:4px;padding:1px 6px;font-weight:700}
.ev-stat-req{display:inline-flex;align-items:center;gap:3px;font-size:.6rem;background:var(--red-light);color:#991b1b;border:1px solid var(--red-mid);border-radius:4px;padding:1px 6px;font-weight:700}
.ev-shared-label{font-size:.58rem;color:var(--blue);font-weight:700;margin-bottom:2px;display:flex;align-items:center;gap:3px}
.ev-shared-sub{font-size:.57rem;color:var(--gray-400);margin-bottom:3px;display:block;font-style:italic}

/* ── NILAI / BOBOT / SCORE ── */
.td-nilai{padding:3px 4px;width:72px;text-align:center}
.nilai-inp{width:100%;border:1.5px solid var(--blue-mid);border-radius:var(--radius-xs);padding:5px 3px;font-size:.82rem;font-weight:700;text-align:center;background:var(--blue-light);outline:none;transition:border-color .15s,box-shadow .15s;font-family:inherit}
.nilai-inp:focus{border-color:var(--blue);background:#dbeafe;box-shadow:0 0 0 2px rgba(29,111,232,.1)}
.nilai-inp.saving{border-color:#f59e0b;background:#fefce8}
.nilai-inp:disabled{background:var(--gray-100);color:var(--gray-400);border-color:var(--gray-200);cursor:not-allowed}
.nilai-inp.saved{border-color:#22c55e;background:var(--green-light)}
.nilai-inp.err{border-color:var(--red);background:var(--red-light)}
.nilai-ar{font-size:.63rem;color:var(--gray-300);font-style:italic}
.td-bobot{text-align:center;width:50px;font-weight:600;color:var(--gray-500);background:var(--gray-50);padding:.35rem .25rem;font-size:.73rem;vertical-align:middle}
.td-bobot-na{color:var(--gray-300)}
.td-score{text-align:center;width:60px;font-weight:700;padding:.35rem .25rem;font-size:.79rem;vertical-align:middle;background:var(--gray-50);color:var(--gray-400)}
.td-score.s-exc {color:#065f46;background:var(--green-light)}
.td-score.s-good{color:#1e40af;background:var(--blue-light)}
.td-score.s-fair{color:#78350f;background:var(--amber-light)}
.td-score.s-poor{color:#991b1b;background:var(--red-light)}

/* ── INLINE REVIEW ZONE ── */
.td-review{padding:0;min-width:185px;vertical-align:top}
.inline-review{padding:5px 7px;background:#f5f8ff;border-left:2px solid var(--blue-mid);min-height:56px}
.inline-review.ar-zone{background:var(--gray-50);border-left-color:var(--gray-100)}
/* Khusus lagging 6-7: As Reported tapi perlu review — warna amber */
.inline-review.ar-review-zone{background:#fffbeb;border-left:2px solid var(--amber-mid)}
.ir-catatan{width:100%;border:1.5px solid var(--blue-mid);border-radius:var(--radius-xs);padding:3px 6px;font-size:.7rem;background:#fff;outline:none;resize:none;transition:border-color .15s;font-family:inherit;margin-bottom:4px;min-height:36px;line-height:1.4}
.ir-catatan:focus{border-color:var(--blue)}
.ir-catatan.saved{border-color:var(--green-mid)}
.ir-catatan.err-bdr{border-color:#ef4444}
.ir-catatan:disabled{background:var(--gray-50);color:var(--gray-400);cursor:not-allowed}
.ir-dec-row{display:flex;gap:3px}
.ir-dec-btn{flex:1;padding:3px 5px;border-radius:var(--radius-xs);font-size:.67rem;font-weight:700;cursor:pointer;border:1.5px solid;transition:all .15s;display:flex;align-items:center;justify-content:center;gap:3px;font-family:inherit;white-space:nowrap}
.ir-ok{background:var(--green-light);color:#166534;border-color:var(--green-mid)}
.ir-ok:hover,.ir-ok.active{background:#22c55e;color:#fff;border-color:#16a34a}
.ir-rej{background:var(--red-light);color:#991b1b;border-color:var(--red-mid)}
.ir-rej:hover,.ir-rej.active{background:#ef4444;color:#fff;border-color:var(--red)}
.ir-dec-btn:disabled{opacity:.5;cursor:not-allowed;pointer-events:none}
.ir-label{font-size:.55rem;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:#1e40af;margin-bottom:3px;display:block}
.ir-label.ar{color:var(--gray-300)}
.ir-label.ar-review{color:#92400e}  /* amber — As Reported tapi perlu review */
.ir-chip-ok {display:inline-flex;align-items:center;gap:3px;padding:2px 7px;border-radius:4px;font-size:.65rem;font-weight:700;background:var(--green-light);color:#166534;border:1px solid var(--green-mid)}
.ir-chip-rej{display:inline-flex;align-items:center;gap:3px;padding:2px 7px;border-radius:4px;font-size:.65rem;font-weight:700;background:var(--red-light);color:#991b1b;border:1px solid var(--red-mid)}

/* ── TOTAL / SUBTOTAL ROW ── */
.kpi-total-row td{background:var(--navy);color:#fff;font-weight:700;padding:.6rem .85rem;font-size:.84rem;border:none}
.score-badges{display:flex;align-items:center;justify-content:center;gap:1.5rem}
.score-sub{display:flex;flex-direction:column;align-items:center;padding:4px 16px;border-radius:6px}
.score-sub.lag{background:rgba(217,119,6,.18)}
.score-sub.lead{background:rgba(147,197,253,.18)}
.score-sub .lbl{font-size:.54rem;letter-spacing:.4px;opacity:.7;text-transform:uppercase}
.score-sub .val{font-size:.92rem;font-weight:800}

/* ── SIDEBAR ── */
.sidebar-card{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius);padding:1rem 1.1rem;box-shadow:var(--shadow-sm);margin-bottom:1rem}
.sb-title{font-size:.63rem;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:var(--gray-400);margin-bottom:.8rem}
.score-ring{width:64px;height:64px;border-radius:50%;border:4px solid;display:flex;flex-direction:column;align-items:center;justify-content:center;font-weight:800;flex-shrink:0}
.ring-none{border-color:var(--gray-300);color:var(--gray-400);background:var(--gray-100)}
.ring-exc {border-color:#16a34a;color:#16a34a;background:var(--green-light)}
.ring-good{border-color:var(--blue);color:var(--blue);background:var(--blue-light)}
.ring-fair{border-color:var(--amber);color:var(--amber);background:var(--amber-light)}
.ring-poor{border-color:var(--red);color:var(--red);background:var(--red-light)}
.mini-prog{height:5px;border-radius:3px;background:var(--gray-200);overflow:hidden;margin-top:.5rem}
.mini-prog-fill{height:100%;border-radius:3px;background:var(--blue);transition:width .4s}
.vessel-chip{background:#eef3ff;border:1px solid var(--blue-mid);border-radius:var(--radius-sm);padding:.5rem .75rem;margin-bottom:.45rem}
.vessel-chip-name{font-weight:700;color:#1e40af;font-size:.82rem}
.vessel-chip-meta{font-size:.7rem;color:var(--gray-400);margin-top:2px}
.tl-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;margin-top:4px}
.tl-dot.draft    {background:var(--gray-400)}
.tl-dot.submitted{background:var(--blue)}
.tl-dot.validated{background:#22c55e}
.tl-dot.rejected {background:var(--red)}

/* ── INFO BANNERS ── */
.info-banner{display:flex;gap:.5rem;align-items:flex-start;border-radius:var(--radius-sm);padding:.6rem .9rem;margin-bottom:.85rem;font-size:.76rem;line-height:1.6}
.info-banner.blue  {background:#eff6ff;border:1px solid var(--blue-mid);color:#1e40af}
.info-banner.green {background:var(--green-light);border:1px solid var(--green-mid);color:#166534}
.info-banner.amber {background:var(--amber-light);border:1px solid var(--amber-mid);color:#78350f}
.info-banner.red   {background:var(--red-light);border:1px solid var(--red-mid);color:#991b1b}
.info-banner ol,.info-banner ul{margin:0;padding-left:1.1rem}

/* ── STEP NAV ── */
.step-nav{display:flex;justify-content:space-between;align-items:center;margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--gray-200);gap:.75rem}
.btn-step{display:inline-flex;align-items:center;gap:6px;padding:.58rem 1.3rem;border-radius:50px;font-size:.82rem;font-weight:700;cursor:pointer;border:none;transition:all .18s;font-family:inherit;letter-spacing:.1px}
.btn-step-primary{background:var(--blue);color:#fff}
.btn-step-primary:hover{background:#1559c4;box-shadow:0 4px 12px rgba(29,111,232,.3)}
.btn-step-outline{background:#fff;color:var(--gray-600);border:1.5px solid var(--gray-200)}
.btn-step-outline:hover{border-color:var(--gray-400);color:var(--gray-900)}
.btn-step-success{background:#16a34a;color:#fff}
.btn-step-success:hover{background:#15803d;box-shadow:0 4px 12px rgba(22,163,74,.3)}
.btn-step-draft{background:var(--amber-light);color:#78350f;border:1.5px solid var(--amber-mid)}
.btn-step-draft:hover{background:var(--amber-mid)}

/* ── STICKY BAR ── */
.sticky-bar{position:fixed;bottom:0;left:0;right:0;z-index:1040;background:#fff;border-top:1px solid var(--gray-200);box-shadow:0 -4px 20px rgba(0,0,0,.08);padding:.65rem 1.5rem;display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap}
.bar-info{font-size:.77rem;color:var(--gray-600)}
.bar-info strong{color:var(--gray-900)}
.autosave-badge{display:inline-flex;align-items:center;gap:5px;font-size:.71rem;color:var(--green);font-weight:600;background:var(--green-light);border:1px solid var(--green-mid);border-radius:6px;padding:2px 9px}

/* ── TOAST ── */
#kpi-toast{position:fixed;bottom:80px;right:22px;z-index:9999;display:none;padding:10px 18px;border-radius:var(--radius-sm);font-size:.81rem;font-weight:600;box-shadow:var(--shadow-lg);animation:toastIn .22s ease;max-width:300px}
#kpi-toast.t-ok {background:var(--green-light);color:#166534;border:1px solid var(--green-mid)}
#kpi-toast.t-err{background:var(--red-light);color:#991b1b;border:1px solid var(--red-mid)}
@keyframes toastIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}

/* ── DELETE MODAL ── */
.del-modal-header{padding:1.35rem 1.5rem 1rem;display:flex;align-items:flex-start;gap:.85rem;border-bottom:none}
.del-modal-header.shared{background:linear-gradient(135deg,#fff1f2 0%,#fee2e2 100%)}
.del-modal-header.single{background:linear-gradient(135deg,#fff7ed 0%,#ffedd5 100%)}
.del-modal-icon{width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:17px}
.del-modal-icon.shared{background:#fca5a5}
.del-modal-icon.single{background:#fdba74}
.del-modal-title{font-weight:800;font-size:.97rem;margin:0 0 .3rem;letter-spacing:-.1px}
.del-modal-title.shared{color:#7f1d1d}
.del-modal-title.single{color:#7c2d12}
.del-modal-desc{font-size:.8rem;margin:0;line-height:1.55}
.del-modal-desc.shared{color:#991b1b}
.del-modal-desc.single{color:#9a3412}
.del-modal-body{padding:.85rem 1.5rem;font-size:.8rem;color:var(--gray-600);background:#fff;border-top:1px solid rgba(0,0,0,.06)}
.del-modal-meta{display:flex;align-items:center;gap:.5rem;background:var(--gray-50);border:1px solid var(--gray-200);border-radius:var(--radius-sm);padding:.55rem .8rem;font-size:.76rem;color:var(--gray-600)}

/* ── UTILS ── */
.d-flex{display:flex} .d-none{display:none} .align-center{align-items:center}
.justify-between{justify-content:space-between} .text-muted{color:var(--gray-400) !important}
.fw-bold{font-weight:700} .text-sm{font-size:.78rem}
.mb-0{margin-bottom:0} .mb-1{margin-bottom:.4rem} .mb-2{margin-bottom:.65rem} .mb-3{margin-bottom:1rem}
.mt-1{margin-top:.4rem} .mt-2{margin-top:.65rem} .mt-3{margin-top:1rem}
.w-100{width:100%} .text-center{text-align:center} .gap-2{gap:.5rem} .gap-3{gap:.75rem}

@media(max-width:640px){
    .wizard-step .ws-label{display:none}
    .kpi-tbl{min-width:620px}
}
@media print{
    .kpi-header .btn,.kpi-header button,.wizard-wrap,.step-nav,.sticky-bar,
    #panduan-panel,.kpi-sidebar-inner,.ev-upload-btn,.btn-del-vessel,
    .btn-add-row,.ir-dec-row,.ir-catatan,.modal{display:none !important}
    .kpi-main{grid-template-columns:1fr !important}
    .step-panel{display:block !important}
    .kpi-tbl{font-size:.72rem !important}
    .kpi-tbl-wrap{overflow:visible !important}
    body{background:#fff !important}
    .kpi-header{background:#0f2544 !important;-webkit-print-color-adjust:exact;print-color-adjust:exact}
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="layout-px-spacing kpi-page">
<div class="middle-content container-xxl p-0">
<div class="row layout-top-spacing">
<div class="col-12" style="padding-bottom:90px;">


<div class="kpi-header">
    <div class="kpi-header-left">
        <h1 class="kpi-header-title">
            <?php if($isCreate): ?> Buat Laporan KPI HSSE
            <?php elseif($isEdit): ?> Edit KPI HSSE
            <?php else: ?> Detail KPI HSSE
            <?php endif; ?>
        </h1>
        <div class="kpi-header-meta">
            <?php if($report): ?>
                <strong><?php echo e($report->company->name ?? '-'); ?></strong>
                <span class="sep">•</span>
                <span><?php echo e($report->kpiPeriod->label ?? '-'); ?></span>
                <span class="sep">•</span>
                <span class="badge badge-<?php echo e($rStatus); ?>">
                    <?php echo e(['draft'=>'Draft','submitted'=>'Submitted','validated'=>'Validated','rejected'=>'Rejected'][$rStatus] ?? ucfirst($rStatus)); ?>

                </span>
            <?php else: ?>
                <span>Laporan Baru</span>
            <?php endif; ?>
        </div>
    </div>
    <div style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:center;">
        <button type="button" id="btn-panduan-toggle"
            class="btn btn-sm btn-outline-light rounded-pill px-3"
            style="font-size:.77rem;" onclick="togglePanduan()">📖 Panduan</button>
        <?php if($report): ?>
            <?php if($isShow && ($canEditKoord || $canEditHsse)): ?>
                <a href="<?php echo e(route('kpi-hsse.edit', $report)); ?>"
                    class="btn btn-sm btn-light rounded-pill px-3" style="font-size:.77rem;font-weight:700;">✏ Edit</a>
            <?php endif; ?>
            <?php if($isEdit): ?>
                <a href="<?php echo e(route('kpi-hsse.show', $report)); ?>"
                    class="btn btn-sm btn-light rounded-pill px-3" style="font-size:.77rem;">👁 Detail</a>
            <?php endif; ?>
            <a href="<?php echo e(route('kpi-hsse.export-pdf', $report)); ?>"
                class="btn btn-sm btn-outline-light rounded-pill px-3" style="font-size:.77rem;">📄 Export PDF</a>
            <a href="<?php echo e(route('kpi-hsse.export', $report)); ?>"
                class="btn btn-sm btn-outline-light rounded-pill px-3" style="font-size:.77rem;">📊 Excel</a>
            <?php if($isSA || ($isKoord && $rStatus === 'draft')): ?>
                <form method="POST" action="<?php echo e(route('kpi-hsse.destroy', $report)); ?>"
                    onsubmit="return confirm('Hapus laporan ini?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button class="btn btn-sm btn-outline-light rounded-pill px-3" style="font-size:.77rem;">🗑 Hapus</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
        <a href="<?php echo e(route('kpi-hsse.index')); ?>"
            class="btn btn-sm btn-outline-light rounded-pill px-3" style="font-size:.77rem;">← Kembali</a>
    </div>
</div>


<?php $__currentLoopData = ['success','error']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if(session($k)): ?>
        <div class="alert alert-<?php echo e($k==='success'?'success':'danger'); ?> alert-dismissible fade show mb-3"
            style="font-size:.84rem;border-radius:var(--radius);">
            <?php echo e(session($k)); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php if($errors->any()): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-3" style="border-radius:var(--radius);">
        <strong>Validasi gagal:</strong>
        <ul class="mb-0 mt-1 ps-3">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li style="font-size:.83rem;"><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if(!$isCreate && $rStatus === 'rejected'): ?>
    <div class="info-banner amber mb-3">
        ⚠️ <div><strong>Laporan ditolak HSSE.</strong>
        Perbaiki item bertanda merah, perhatikan catatan HSSE di setiap baris, lalu submit ulang.</div>
    </div>
<?php endif; ?>


<div id="panduan-panel" style="display:none;margin-bottom:1rem;">
    <div style="background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius);padding:1.1rem 1.4rem;box-shadow:var(--shadow-sm);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.8rem;">
            <strong style="font-size:.88rem;color:var(--navy);">📖 Panduan Pengisian KPI HSSE</strong>
            <button type="button" onclick="togglePanduan()"
                style="background:none;border:none;cursor:pointer;color:var(--gray-400);font-size:1.1rem;line-height:1;">✕</button>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem;font-size:.8rem;line-height:1.7;">
            <?php if($isKoord || $isCreate): ?>
            <div>
                <div style="font-weight:700;color:var(--navy-mid);margin-bottom:.3rem;font-size:.77rem;text-transform:uppercase;letter-spacing:.4px;">Koordinator</div>
                <ol style="margin:0;padding-left:1.2rem;color:var(--gray-700);">
                    <li>Pilih <strong>Periode</strong> &amp; isi data Kapal/Unit, lalu <strong>Simpan Draft</strong></li>
                    <li>Pindah ke tab <strong>Lagging</strong> &amp; <strong>Leading</strong>, isi kolom <strong>∑ / %</strong></li>
                    <li>Upload <strong>lampiran</strong> di item No.1 — berlaku bersama untuk No.1–7</li>
                    <li>Item No.8 (Manhours) memerlukan lampiran sendiri</li>
                    <li>Data tersimpan <strong>otomatis</strong> saat berpindah kolom</li>
                    <li>Setelah semua item terisi, klik <strong>Submit ke HSSE</strong></li>
                    <li>Jika ada item ditolak, perhatikan <strong>catatan merah dari HSSE</strong> di setiap baris</li>
                </ol>
            </div>
            <?php endif; ?>
            <?php if($isHsse || $isSA): ?>
            <div>
                <div style="font-weight:700;color:var(--navy-mid);margin-bottom:.3rem;font-size:.77rem;text-transform:uppercase;letter-spacing:.4px;">HSSE / Verifikator</div>
                <ol style="margin:0;padding-left:1.2rem;color:var(--gray-700);">
                    <li>Buka laporan berstatus <strong>Submitted</strong></li>
                    <li>Isi kolom <strong>Nilai (0–100)</strong> untuk setiap item <em>scored</em></li>
                    <li>Score dihitung otomatis: <strong>Score = Nilai × Bobot</strong></li>
                    <li>Klik <strong>✓ Accept</strong> atau <strong>✗ Reject</strong> di kolom Review <strong>semua item</strong> (termasuk item No.6 FAC &amp; No.7 Nearmiss)</li>
                    <li>Item No.8 (Manhours) dan Leading As-Reported tidak perlu direview</li>
                    <li>Jika Reject, isi <strong>catatan</strong> (wajib) sebagai feedback ke koordinator</li>
                </ol>
            </div>
            <?php endif; ?>
            <div>
                <div style="font-weight:700;color:var(--navy-mid);margin-bottom:.3rem;font-size:.77rem;text-transform:uppercase;letter-spacing:.4px;">Keterangan Item</div>
                <ul style="margin:0;padding-left:1.2rem;color:var(--gray-700);">
                    <li><strong>Scored</strong> — wajib isi ∑/%, upload lampiran, &amp; dinilai HSSE</li>
                    <li><strong>As Reported (A/R) + Review</strong> — isi ∑ saja (FAC &amp; Nearmiss), wajib Accept/Reject HSSE</li>
                    <li><strong>As Reported (A/R)</strong> — isi keterangan saja, tidak direview (Manhours)</li>
                    <li>Lagging No.1–7: <strong>cukup 1 pool lampiran bersama</strong> di baris No.1</li>
                    <li>Total Score = Lagging (40%) + Leading (60%)</li>
                </ul>
            </div>
        </div>
    </div>
</div>


<div class="wizard-wrap">
    <div class="wizard-track">
        <div class="wizard-step active" id="ws-0" onclick="<?php echo e(!$isCreate ? 'jumpStep(0)' : ''); ?>">
            <div class="ws-circle"><span class="ws-icon">🚢</span></div>
            <div class="ws-label">Info &amp; Kapal</div>
        </div>
        <?php if($showTabKpi): ?>
        <div class="wizard-step" id="ws-1" onclick="jumpStep(1)">
            <div class="ws-circle">
                <span class="ws-icon">📉</span>
                <?php if($rejCnt > 0): ?>
                <span style="position:absolute;top:-4px;right:-4px;width:14px;height:14px;border-radius:50%;background:#ef4444;color:#fff;font-size:8px;display:flex;align-items:center;justify-content:center;font-weight:800;border:2px solid #fff;"><?php echo e($rejCnt); ?></span>
                <?php endif; ?>
            </div>
            <div class="ws-label">Lagging</div>
        </div>
        <div class="wizard-step" id="ws-2" onclick="jumpStep(2)">
            <div class="ws-circle"><span class="ws-icon">📈</span></div>
            <div class="ws-label">Leading</div>
        </div>
        <?php endif; ?>
    </div>
</div>


<div class="kpi-main">
<div>


<div class="step-panel active" id="step-0">

    <?php if($isCreate): ?>
    <div class="info-banner blue">
        ℹ️
        <ol>
            <li>Pilih <strong>Periode</strong> &amp; isi data Kapal/Unit</li>
            <li>Klik <strong>Simpan Draft</strong> untuk menyimpan</li>
            <li>Lanjutkan isi <strong>Data KPI</strong> per section</li>
            <li><strong>Submit ke HSSE</strong> bila sudah lengkap</li>
        </ol>
    </div>
    <form method="POST" action="<?php echo e(route('kpi-hsse.store')); ?>" id="form-create">
    <?php echo csrf_field(); ?>
    <?php endif; ?>

    
    <div class="card">
        <div class="card-header">
            <div class="card-icon" style="background:#dbeafe;">📅</div>
            <div>
                <p class="card-title">Periode Laporan</p>
                <p class="card-sub"><?php echo e($isCreate ? 'Pilih bulan dan tahun laporan KPI' : 'Periode laporan ini'); ?></p>
            </div>
        </div>
        <div class="card-body">
            <?php if($isCreate): ?>
            <div class="field">
                <label class="field-lbl">Bulan &amp; Tahun <span class="field-req">*</span></label>
                <select name="period" class="field-select" style="max-width:280px;" required>
                    <option value="">— Pilih periode —</option>
                    <?php $__empty_1 = true; $__currentLoopData = $availableMonths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <option value="<?php echo e($mo['value']); ?>" <?php echo e(old('period')==$mo['value']?'selected':''); ?>><?php echo e($mo['label']); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <option disabled>Semua periode sudah ada laporan</option>
                    <?php endif; ?>
                </select>
            </div>
            <?php elseif($isEdit && ($isKoord || $isSA)): ?>
            <div class="field mb-0">
                <label class="field-lbl">Ubah Periode</label>
                <div style="display:flex;align-items:center;gap:.75rem;">
                    <select id="select-period-edit" class="field-select" style="max-width:280px;" 
                        onchange="doUpdatePeriod(this.value, '<?php echo e(route('kpi-hsse.period.update_report', $report)); ?>')">
                        <?php 
                            $currKey = $report->kpiPeriod->period_month . '-' . $report->kpiPeriod->period_year;
                            $hasCurr = false;
                        ?>
                        <?php $__currentLoopData = $availableMonths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($mo['value'] == $currKey): ?> <?php $hasCurr = true; ?> <?php endif; ?>
                            <option value="<?php echo e($mo['value']); ?>" <?php echo e($mo['value'] == $currKey ? 'selected' : ''); ?>>
                                <?php echo e($mo['label']); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if(!$hasCurr): ?>
                            <option value="<?php echo e($currKey); ?>" selected><?php echo e($report->kpiPeriod->label); ?></option>
                        <?php endif; ?>
                    </select>
                    <div id="period-spinner" class="spinner-border spinner-border-sm text-primary d-none"></div>
                </div>
                <p style="font-size:.7rem;color:var(--gray-400);margin-top:.4rem;">💡 Anda dapat memindahkan laporan ini ke periode lain.</p>
            </div>
            <?php else: ?>
            <div style="display:inline-flex;align-items:center;gap:.75rem;background:var(--blue-light);border:1px solid var(--blue-mid);border-radius:var(--radius-sm);padding:.65rem 1.1rem;">
                <span style="font-size:1.1rem;">📅</span>
                <div>
                    <div id="label-period-display" style="font-weight:700;color:#1e40af;font-size:.9rem;"><?php echo e($report->kpiPeriod->label ?? '-'); ?></div>
                    <?php if($report->kpiPeriod?->submission_end): ?>
                    <div style="font-size:.72rem;color:#3b82f6;margin-top:2px;">Batas submit: <?php echo e($report->kpiPeriod->submission_end->format('d M Y')); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="card">
        <div class="card-header">
            <div class="card-icon" style="background:#dcfce7;">🚢</div>
            <div style="flex:1;">
                <p class="card-title">Kapal / Unit &amp; Kontrak</p>
                <p class="card-sub">Satu kontrak bisa mencakup beberapa kapal/unit</p>
            </div>
            <?php if(!$isCreate && $canEditKoord): ?>
            <button type="button" class="btn-add-row" style="margin-top:0;" onclick="doAddVessel()">+ Tambah</button>
            <?php endif; ?>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="vtbl-wrap">
                <?php if($isCreate): ?>
                <table class="vtbl" id="vessel-table">
                    <thead><tr>
                        <th class="td-no">#</th>
                        <th style="min-width:155px;">No. Kontrak</th>
                        <th style="width:145px;">Akhir Kontrak</th>
                        <th style="width:85px;">JML</th>
                        <th>Nama Kapal / Unit <span style="color:#ef4444;">*</span></th>
                        <th class="td-del"></th>
                    </tr></thead>
                    <tbody id="vessel-tbody">
                        <tr id="vrow-0">
                            <td class="td-no">1</td>
                            <td><input type="text" class="vtbl-inp" name="vessels[0][contract_number]" value="<?php echo e(old('vessels.0.contract_number')); ?>" placeholder="PHM/2025/001"></td>
                            <td><input type="date" class="vtbl-inp" name="vessels[0][contract_end_date]" value="<?php echo e(old('vessels.0.contract_end_date')); ?>"></td>
                            <td><input type="text" class="vtbl-inp" name="vessels[0][vessel_count]" value="<?php echo e(old('vessels.0.vessel_count')); ?>" placeholder="1 unit"></td>
                            <td><input type="text" class="vtbl-inp" name="vessels[0][vessel_name]" value="<?php echo e(old('vessels.0.vessel_name')); ?>" placeholder="KM Harapan Jaya" required></td>
                            <td class="td-del"><button type="button" class="btn-del-vessel d-none" onclick="delVessel(0)">×</button></td>
                        </tr>
                    </tbody>
                </table>
                <div style="padding:.75rem 1.25rem;">
                    <button type="button" class="btn-add-row" onclick="addVessel()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Tambah Baris
                    </button>
                    <p style="font-size:.71rem;color:var(--gray-400);margin:.5rem 0 0;">💡 Jika 1 kontrak mencakup beberapa kapal, tuliskan semua nama di kolom Nama Kapal/Unit</p>
                </div>
                <?php else: ?>
                <table class="vtbl">
                    <thead><tr>
                        <th class="td-no">#</th>
                        <th>No. Kontrak</th>
                        <th style="width:145px;">Akhir Kontrak</th>
                        <th style="width:85px;">JML</th>
                        <th>Nama Kapal / Unit</th>
                        <?php if($canEditKoord): ?><th class="td-del"></th><?php endif; ?>
                    </tr></thead>
                    <tbody id="vessel-tbody">
                        <?php $__currentLoopData = $report->vessels->sortBy('sort_order'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vi => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr id="vrow-<?php echo e($v->id); ?>">
                            <td class="td-no"><?php echo e($vi+1); ?></td>
                            <?php if($canEditKoord): ?>
                            <td><input type="text" class="vtbl-inp" id="vc-<?php echo e($v->id); ?>" value="<?php echo e($v->contract_number); ?>" placeholder="No. kontrak" onblur="doSaveVessel('<?php echo e($v->id); ?>','<?php echo e(route('kpi-hsse.vessels.update',[$report,$v])); ?>')"></td>
                            <td><input type="date" class="vtbl-inp" id="vd-<?php echo e($v->id); ?>" value="<?php echo e($v->contract_end_date?->format('Y-m-d')); ?>" onblur="doSaveVessel('<?php echo e($v->id); ?>','<?php echo e(route('kpi-hsse.vessels.update',[$report,$v])); ?>')"></td>
                            <td><input type="text" class="vtbl-inp" id="vco-<?php echo e($v->id); ?>" value="<?php echo e($v->vessel_count); ?>" placeholder="1 unit" onblur="doSaveVessel('<?php echo e($v->id); ?>','<?php echo e(route('kpi-hsse.vessels.update',[$report,$v])); ?>')"></td>
                            <td><input type="text" class="vtbl-inp" id="vn-<?php echo e($v->id); ?>" value="<?php echo e($v->vessel_name); ?>" placeholder="Nama kapal" onblur="doSaveVessel('<?php echo e($v->id); ?>','<?php echo e(route('kpi-hsse.vessels.update',[$report,$v])); ?>')"></td>
                            <td class="td-del">
                                <button type="button" class="btn-del-vessel <?php echo e($report->vessels->count()<=1?'d-none':''); ?>" onclick="doDelVessel('<?php echo e($v->id); ?>','<?php echo e(route('kpi-hsse.vessels.destroy',[$report,$v])); ?>')">×</button>
                            </td>
                            <?php else: ?>
                            <td style="padding:.5rem .75rem;font-size:.82rem;"><?php echo e($v->contract_number ?: '—'); ?></td>
                            <td style="padding:.5rem .75rem;font-size:.82rem;"><?php echo e($v->contract_end_date?->format('d/m/Y') ?? '—'); ?></td>
                            <td style="padding:.5rem .75rem;font-size:.82rem;"><?php echo e($v->vessel_count ?: '—'); ?></td>
                            <td style="padding:.5rem .75rem;font-size:.82rem;font-weight:600;"><?php echo e($v->vessel_name); ?></td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <?php if(!$isCreate): ?>
    <div class="card">
        <div class="card-header">
            <div class="card-icon" style="background:var(--gray-100);">📝</div>
            <div><p class="card-title">Informasi Laporan</p></div>
        </div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(175px,1fr));gap:1rem;">
                <div>
                    <div class="field-lbl">Dibuat oleh</div>
                    <div style="font-weight:600;font-size:.85rem;"><?php echo e($report->createdBy->name ?? '-'); ?></div>
                    <div style="font-size:.72rem;color:var(--gray-400);"><?php echo e($report->created_at->format('d M Y, H:i')); ?></div>
                </div>
                <?php if($report->submitted_at): ?>
                <div>
                    <div class="field-lbl">Disubmit</div>
                    <div style="font-weight:600;font-size:.85rem;"><?php echo e($report->submittedBy->name ?? '-'); ?></div>
                    <div style="font-size:.72rem;color:var(--gray-400);"><?php echo e($report->submitted_at->format('d M Y, H:i')); ?></div>
                </div>
                <?php endif; ?>
                <?php if($report->validated_at): ?>
                <div>
                    <div class="field-lbl">Divalidasi</div>
                    <div style="font-weight:600;font-size:.85rem;"><?php echo e($report->validatedBy->name ?? '-'); ?></div>
                    <div style="font-size:.72rem;color:var(--gray-400);"><?php echo e($report->validated_at->format('d M Y, H:i')); ?></div>
                </div>
                <?php endif; ?>
                <div>
                    <div class="field-lbl">Update terakhir</div>
                    <div style="font-size:.82rem;color:var(--gray-400);"><?php echo e($report->updated_at->diffForHumans()); ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($isCreate): ?></form><?php endif; ?>

    <div class="step-nav">
        <?php if($showTabKpi): ?>
            <div></div>
            <button type="button" class="btn-step btn-step-primary" onclick="goStep(1)">Lanjut: Lagging →</button>
        <?php elseif($isCreate): ?>
            <div></div>
            <button type="button" class="btn-step btn-step-primary" onclick="doSaveDraft()">💾 Simpan Draft</button>
        <?php endif; ?>
    </div>
</div>

<?php if($showTabKpi): ?>


<div class="step-panel" id="step-1">

    <?php if($canEditKoord): ?>
    <div class="info-banner green">
        ✅ <ul>
            <li><strong>Auto-save aktif</strong> — data tersimpan otomatis saat pindah kolom.</li>
            <li>Lampiran No.1–7 cukup <strong>upload 1x di baris No.1</strong> — otomatis berlaku untuk semua.</li>
        </ul>
    </div>
    <?php endif; ?>
    <?php if($canDoInlineReview): ?>
    <div class="info-banner blue">
        🔵 Isi <strong>Nilai (0–100)</strong> untuk item scored. Beri <strong>Accept/Reject</strong> untuk <strong>semua item No.1–7</strong> (termasuk FAC &amp; Nearmiss).
        Item No.8 (Manhours) tidak perlu direview.
    </div>
    <?php endif; ?>

    <div class="card" style="padding:0;overflow:hidden;">
        <div class="sect-banner lag" style="margin:1rem 1rem .65rem;">
            <span>📉 Section 1 — Lagging Indicator</span>
            <span class="pill">Bobot 40%</span>
        </div>
        <div class="kpi-tbl-wrap">
        <table class="kpi-tbl">
            <thead>
            <tr class="kpi-tbl-head">
                <th style="width:36px;text-align:center;">No</th>
                <th style="min-width:170px;text-align:left;padding-left:10px;">Item KPI</th>
                <th style="width:100px;text-align:center;">∑ / %</th>
                <th style="min-width:135px;text-align:left;padding-left:7px;">Keterangan</th>
                <th style="min-width:148px;text-align:center;">Lampiran&nbsp;<span style="color:#f87171;font-weight:400;">✱</span></th>
                <?php if($isHsse || $isSA): ?>
                <th style="width:70px;text-align:center;">Nilai</th>
                <th style="width:50px;text-align:center;">Bobot</th>
                <th style="width:58px;text-align:center;">Score</th>
                <th style="min-width:185px;text-align:center;">Review HSSE</th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php
                $lagItems        = $kpiItems->get('lagging', collect());
                $lagAnchorItem   = $lagItems->firstWhere('item_no', 1);
                $lagAnchorDetail = $lagAnchorItem ? ($existingDetails[$lagAnchorItem->id] ?? null) : null;
                $lagAnchorId     = $lagAnchorDetail?->id ?? '';
                $lagAnchorEvs    = $lagAnchorDetail ? $lagAnchorDetail->evidences : collect();
                $lagAnchorEvCnt  = $lagAnchorEvs->count();
            ?>
            <?php $__currentLoopData = $lagItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $det      = $existingDetails[$item->id] ?? null;
                $isAS     = !$item->is_scored;                  // As Reported (no score)
                $isShared = in_array((int) $item->item_no, $lagSharedNos);
                $isAnchor = ((int) $item->item_no === 1);

                // v13: item 6-7 As Reported TAPI perlu review HSSE
                // item 8 As Reported, TIDAK perlu review
                $needsReview  = $item->is_scored || in_array((int) $item->item_no, $lagReviewableNos);
                $isArNoReview = $isAS && !$needsReview; // hanya Manhours (No.8)

                $isRej    = $det && $det->review_status === 'rejected';
                $isOK     = $det && $det->review_status === 'approved';
                $unitLabel  = $item->unit_label ?? ($item->unit ?? '∑');
                $isPercent  = ($unitLabel === '%');
                [$iName, $iTarget] = array_pad(explode("\n", $item->name, 2), 2, '');
                $iName   = trim($iName);
                $iTarget = trim($iTarget);
                $nilaiVal   = (float) ($det?->nilai ?? 0);
                $score      = $det?->score;
                $scClsRow   = ($score > 0 && !$isAS) ? $scCls($nilaiVal) : '';
                $rowCls     = trim(($isAS ? 'r-ar ' : '') . ($isRej ? 'r-rej ' : '') . ($isOK ? 'r-ok ' : ''));
                $dId        = $det?->id ?? '';
                $upId       = $det?->id ?? '';
            ?>
            <tr class="kpi-row <?php echo e($rowCls); ?>" data-section="lagging" data-item-no="<?php echo e($item->item_no); ?>">
                <td class="td-no-kpi"><?php echo e($item->item_no); ?></td>

                
                <td class="td-item">
                    <div class="item-name"><?php echo e($iName); ?></div>
                    <?php if($iTarget): ?><div class="item-target">🎯 <?php echo e($iTarget); ?></div><?php endif; ?>
                    <div class="item-tags">
                        <?php if($isAS && $needsReview): ?>
                            <span class="itag itag-ar">As Reported + Review</span>
                        <?php elseif($isArNoReview): ?>
                            <span class="itag itag-ar">As Reported</span>
                        <?php endif; ?>
                        <?php if($isRej): ?><span class="itag itag-rej">✗ Ditolak</span><?php endif; ?>
                        <?php if($isOK): ?> <span class="itag itag-ok">✓ Disetujui</span><?php endif; ?>
                    </div>
                    
                    <?php if($isRej && $det?->hsse_catatan): ?>
                    <div class="rej-remark-box">
                        <div class="rej-remark-label">💬 Catatan HSSE</div>
                        <div class="rej-remark-text"><?php echo e($det->hsse_catatan); ?></div>
                    </div>
                    <?php endif; ?>
                </td>

                
                <td class="td-jml">
                    <?php if($canEditKoord): ?>
                    <div class="jml-wrap">
                        <input type="number"
                            class="jml-inp"
                            id="ac-<?php echo e($item->id); ?>"
                            value="<?php echo e(old('items.'.$item->id.'.actual_count', $det?->actual_count)); ?>"
                            min="0" step="<?php echo e($isPercent ? '0.01' : '1'); ?>"
                            placeholder="0"
                            data-item="<?php echo e($item->id); ?>"
                            data-save-url="<?php echo e(route('kpi-hsse.update', $report)); ?>"
                            onchange="saveRow(this)" onblur="saveRow(this)">
                        <span class="unit-tag"><?php echo e($unitLabel); ?></span>
                    </div>
                    <?php else: ?>
                    <div class="jml-readonly">
                        <?php echo e($det?->actual_count ?? '—'); ?>

                        <div class="jml-unit"><?php echo e($unitLabel); ?></div>
                    </div>
                    <?php endif; ?>
                </td>

                
                <td class="td-ket">
                    <?php if($canEditKoord): ?>
                    <textarea class="ket-inp" id="ket-<?php echo e($item->id); ?>" rows="2"
                        placeholder="Keterangan singkat..."
                        data-item="<?php echo e($item->id); ?>"
                        data-save-url="<?php echo e(route('kpi-hsse.update', $report)); ?>"
                        onblur="saveKet(this)"><?php echo e(old('items.'.$item->id.'.keterangan', $det?->keterangan)); ?></textarea>
                    <?php else: ?>
                    <div class="ket-readonly"><?php echo e($det?->keterangan ?: '—'); ?></div>
                    <?php endif; ?>
                </td>

                
                <td class="td-ev">
                    <?php if($isShared): ?>
                        <span class="ev-shared-label">📎 Lampiran bersama No.1–7</span>
                        <?php if($isAnchor): ?>
                            
                            <?php if($canEditKoord): ?>
                            <label class="ev-upload-btn mb-1" id="lbl-anchor-lag" for="ev-up-anchor-lag">+ Upload Lampiran</label>
                            <input type="file" id="ev-up-anchor-lag" class="d-none"
                                accept=".jpg,.jpeg,.png,.webp,.pdf" multiple
                                data-anchor-detail-id="<?php echo e($lagAnchorId); ?>"
                                data-anchor-item-id="<?php echo e($lagAnchorItem?->id ?? ''); ?>"
                                data-report-id="<?php echo e($report->id); ?>"
                                data-url="<?php echo e(route('kpi-hsse.evidences.upload', $report)); ?>">
                            <div class="ev-prog" id="ev-prog-anchor-lag"><div class="ev-bar" id="ev-bar-anchor-lag"></div></div>
                            <div class="ev-err" id="ev-err-anchor-lag"></div>
                            <?php endif; ?>
                            <div class="ev-grid" id="ev-row-lag-1">
                                <?php $__currentLoopData = $lagAnchorEvs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $isPdf = str_contains($ev->file_type ?? '', 'pdf'); ?>
                                <div class="ev-item" id="ev-item-lag1-<?php echo e($ev->id); ?>">
                                    <?php if($isPdf): ?>
                                        <a href="<?php echo e(Storage::url($ev->file_path)); ?>" target="_blank" class="ev-pdf" title="<?php echo e($ev->file_name); ?>">📄</a>
                                    <?php else: ?>
                                        <img class="ev-thumb" src="<?php echo e(Storage::url($ev->file_path)); ?>"
                                            onclick="previewImg('<?php echo e(Storage::url($ev->file_path)); ?>','<?php echo e(e($ev->file_name)); ?>')"
                                            title="<?php echo e($ev->caption ?? $ev->file_name); ?>">
                                    <?php endif; ?>
                                    <?php if($canEditKoord): ?>
                                    <div class="ev-del-btn" onclick="doDelEvShared('<?php echo e($ev->id); ?>','<?php echo e(route('kpi-hsse.evidences.delete',[$report,$ev])); ?>','<?php echo e($lagAnchorId); ?>')">×</div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <div id="ev-st-lag-1" style="margin-top:3px;">
                                <?php if($lagAnchorEvCnt > 0): ?>
                                    <span class="ev-stat-ok"><?php echo e($lagAnchorEvCnt); ?> file</span>
                                <?php elseif($canEditKoord): ?>
                                    <span class="ev-stat-req">Wajib</span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            
                            <?php if($canEditKoord): ?>
                            <span class="ev-shared-sub">↑ Upload di baris No.1</span>
                            <?php endif; ?>
                            <div class="ev-grid" id="ev-row-lag-<?php echo e($item->item_no); ?>">
                                <?php $__currentLoopData = $lagAnchorEvs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $isPdf = str_contains($ev->file_type ?? '', 'pdf'); ?>
                                <div class="ev-item" id="ev-item-lag<?php echo e($item->item_no); ?>-<?php echo e($ev->id); ?>">
                                    <?php if($isPdf): ?>
                                        <a href="<?php echo e(Storage::url($ev->file_path)); ?>" target="_blank" class="ev-pdf" title="<?php echo e($ev->file_name); ?>">📄</a>
                                    <?php else: ?>
                                        <img class="ev-thumb" src="<?php echo e(Storage::url($ev->file_path)); ?>"
                                            onclick="previewImg('<?php echo e(Storage::url($ev->file_path)); ?>','<?php echo e(e($ev->file_name)); ?>')"
                                            title="<?php echo e($ev->caption ?? $ev->file_name); ?>">
                                    <?php endif; ?>
                                    <?php if($canEditKoord): ?>
                                    <div class="ev-del-btn" onclick="doDelEvShared('<?php echo e($ev->id); ?>','<?php echo e(route('kpi-hsse.evidences.delete',[$report,$ev])); ?>','<?php echo e($lagAnchorId); ?>')">×</div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <div id="ev-st-lag-<?php echo e($item->item_no); ?>" style="margin-top:3px;">
                                <?php if($lagAnchorEvCnt > 0): ?>
                                    <span class="ev-stat-ok"><?php echo e($lagAnchorEvCnt); ?> file</span>
                                <?php elseif($canEditKoord): ?>
                                    <span class="ev-stat-req">Wajib</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        
                        <div class="ev-grid" id="ev-row-<?php echo e($upId); ?>">
                            <?php if($det): ?>
                            <?php $__currentLoopData = $det->evidences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $isPdf = str_contains($ev->file_type ?? '', 'pdf'); ?>
                            <div class="ev-item" id="ev-item-<?php echo e($ev->id); ?>">
                                <?php if($isPdf): ?>
                                    <a href="<?php echo e(Storage::url($ev->file_path)); ?>" target="_blank" class="ev-pdf" title="<?php echo e($ev->file_name); ?>">📄</a>
                                <?php else: ?>
                                    <img class="ev-thumb" src="<?php echo e(Storage::url($ev->file_path)); ?>"
                                        onclick="previewImg('<?php echo e(Storage::url($ev->file_path)); ?>','<?php echo e(e($ev->file_name)); ?>')"
                                        title="<?php echo e($ev->caption ?? $ev->file_name); ?>">
                                <?php endif; ?>
                                <?php if($canEditKoord): ?>
                                <div class="ev-del-btn" onclick="doDelEv('<?php echo e($ev->id); ?>','<?php echo e(route('kpi-hsse.evidences.delete',[$report,$ev])); ?>','<?php echo e($upId); ?>')">×</div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                        </div>
                        <?php if($canEditKoord && $upId): ?>
                        <label class="ev-upload-btn" id="lbl-<?php echo e($upId); ?>" for="ev-up-<?php echo e($upId); ?>">+ Lampiran</label>
                        <input type="file" id="ev-up-<?php echo e($upId); ?>" class="d-none ev-input"
                            accept=".jpg,.jpeg,.png,.webp,.pdf" multiple
                            data-uid="<?php echo e($upId); ?>"
                            data-url="<?php echo e(route('kpi-hsse.evidences.upload', $report)); ?>">
                        <div class="ev-prog" id="ev-prog-<?php echo e($upId); ?>"><div class="ev-bar" id="ev-bar-<?php echo e($upId); ?>"></div></div>
                        <div class="ev-err" id="ev-err-<?php echo e($upId); ?>"></div>
                        <?php elseif($canEditKoord && !$upId): ?>
                        <span class="ev-hint">Isi ∑/% dulu</span>
                        <?php endif; ?>
                        <?php $evCntNs = $det ? $det->evidences->count() : 0; ?>
                        <div id="ev-st-<?php echo e($upId); ?>" style="margin-top:4px;">
                            <?php if($evCntNs > 0): ?>
                                <span class="ev-stat-ok"><?php echo e($evCntNs); ?> file</span>
                            <?php elseif($canEditKoord): ?>
                                <span class="ev-stat-req">Wajib</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </td>

                
                <?php if($isHsse || $isSA): ?>
                <td class="td-nilai">
                    <?php if($isAS): ?>
                        <span class="nilai-ar">A/R</span>
                    <?php else: ?>
                        <input type="number" class="nilai-inp" id="nilai-<?php echo e($item->id); ?>"
                            value="<?php echo e($nilaiVal > 0 ? number_format($nilaiVal,2,'.','') : ''); ?>"
                            min="0" max="100" step="0.01"
                            placeholder="<?php echo e($canEditHsse ? '0–100' : '—'); ?>"
                            <?php echo e($canEditHsse ? '' : 'disabled'); ?>

                            onchange="doUpdateScore('<?php echo e($item->id); ?>')">
                    <?php endif; ?>
                </td>
                <td class="td-bobot">
                    <?php if($item->is_scored): ?>
                        <?php echo e(number_format((float)$item->bobot*100,1)); ?>%
                    <?php else: ?>
                        <span class="td-bobot-na">—</span>
                    <?php endif; ?>
                </td>
                <td class="td-score <?php echo e($scClsRow); ?>" id="sc-<?php echo e($item->id); ?>">
                    <?php echo e($isAS ? 'A/R' : ($score !== null ? number_format((float)$score,2) : '—')); ?>

                </td>

                
                <td class="td-review">
                    <?php if($isArNoReview): ?>
                        
                        <div class="inline-review ar-zone">
                            <span class="ir-label ar">As Reported — tidak direview</span>
                        </div>
                    <?php elseif($canDoInlineReview): ?>
                        
                        <div class="inline-review<?php echo e(($isAS && $needsReview) ? ' ar-review-zone' : ''); ?>">
                            <?php if($isAS && $needsReview): ?>
                                <span class="ir-label ar-review">Verifikasi Laporan (A/R)</span>
                            <?php else: ?>
                                <span class="ir-label">Catatan Verifikasi</span>
                            <?php endif; ?>
                            <textarea class="ir-catatan" id="ircmt-<?php echo e($dId); ?>" rows="2"
                                placeholder="Catatan untuk koordinator..."
                                data-detail="<?php echo e($dId); ?>"
                                data-url="<?php echo e(route('kpi-hsse.catatan.update', $report)); ?>"
                                onblur="saveCat(this)"><?php echo e($det?->hsse_catatan); ?></textarea>
                            <div class="ir-dec-row" id="irdec-<?php echo e($dId); ?>">
                                <button type="button"
                                    class="ir-dec-btn ir-ok <?php echo e($isOK ? 'active' : ''); ?>"
                                    id="irbtn-ap-<?php echo e($dId); ?>"
                                    onclick="doInlineDecision('<?php echo e($dId); ?>','approved','<?php echo e(route('kpi-hsse.decision.update',$report)); ?>','<?php echo e($item->id); ?>')">
                                    ✓ Accept
                                </button>
                                <button type="button"
                                    class="ir-dec-btn ir-rej <?php echo e($isRej ? 'active' : ''); ?>"
                                    id="irbtn-rj-<?php echo e($dId); ?>"
                                    onclick="doInlineDecision('<?php echo e($dId); ?>','rejected','<?php echo e(route('kpi-hsse.decision.update',$report)); ?>','<?php echo e($item->id); ?>')">
                                    ✗ Reject
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        
                        <div class="inline-review" style="background:var(--gray-50);border-left-color:var(--gray-200);">
                            <?php if($isArNoReview): ?>
                                <span style="font-size:.68rem;color:var(--gray-300);">Tidak direview</span>
                            <?php elseif($isOK): ?>
                                <span class="ir-chip-ok">✓ Disetujui</span>
                            <?php elseif($isRej): ?>
                                <span class="ir-chip-rej">✗ Ditolak</span>
                                <?php if($det?->hsse_catatan): ?>
                                <div style="font-size:.67rem;color:#b91c1c;margin-top:4px;font-style:italic;"><?php echo e($det->hsse_catatan); ?></div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="font-size:.68rem;color:var(--gray-400);">Belum direview</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
            <tfoot>
            <tr class="kpi-total-row">
                <td colspan="<?php echo e(($isHsse || $isSA) ? 7 : 4); ?>"
                    style="text-align:right;font-size:.74rem;opacity:.65;letter-spacing:.2px;">
                    Subtotal Lagging (40%)
                </td>
                <td colspan="<?php echo e(($isHsse || $isSA) ? 2 : 1); ?>" style="text-align:center;padding:.5rem;">
                    <div style="font-size:.92rem;font-weight:800;color:#fde68a;letter-spacing:.3px;" id="t-lag-sub">
                        <?php echo e(number_format($tsLag,2)); ?>

                    </div>
                </td>
            </tr>
            </tfoot>
        </table>
        </div>
    </div>

    <div class="step-nav">
        <button type="button" class="btn-step btn-step-outline" onclick="goStep(0)">← Info &amp; Kapal</button>
        <div style="display:flex;gap:.6rem;align-items:center;">
            <?php if(!$isCreate && ($canEditKoord || $canEditHsse || $isSA)): ?>
            <button type="button" class="btn-step btn-step-draft" onclick="doSaveDraft()">💾 Simpan Progres</button>
            <?php endif; ?>
            <button type="button" class="btn-step btn-step-primary" onclick="goStep(2)">Lanjut: Leading →</button>
        </div>
    </div>
</div>



<div class="step-panel" id="step-2">

    <?php if($canEditKoord): ?>
    <div class="info-banner green">
        ✅ <strong>Auto-save aktif.</strong> Setiap item scored wajib upload lampiran. Item As Reported tidak perlu lampiran &amp; tidak direview.
    </div>
    <?php endif; ?>
    <?php if($canDoInlineReview): ?>
    <div class="info-banner blue">
        🔵 Isi <strong>Nilai (0–100)</strong> dan beri <strong>Accept/Reject</strong> untuk item scored. Item As Reported di Leading tidak perlu direview.
    </div>
    <?php endif; ?>

    <div class="card" style="padding:0;overflow:hidden;">
        <div class="sect-banner lead" style="margin:1rem 1rem .65rem;">
            <span>📈 Section 2 — Leading Indicator</span>
            <span class="pill">Bobot 60%</span>
        </div>
        <div class="kpi-tbl-wrap">
        <table class="kpi-tbl">
            <thead>
            <tr class="kpi-tbl-head">
                <th style="width:36px;text-align:center;">No</th>
                <th style="min-width:170px;text-align:left;padding-left:10px;">Item KPI</th>
                <th style="width:100px;text-align:center;">∑ / %</th>
                <th style="min-width:135px;text-align:left;padding-left:7px;">Keterangan</th>
                <th style="min-width:148px;text-align:center;">Lampiran&nbsp;<span style="color:#f87171;font-weight:400;">✱</span></th>
                <?php if($isHsse || $isSA): ?>
                <th style="width:70px;text-align:center;">Nilai</th>
                <th style="width:50px;text-align:center;">Bobot</th>
                <th style="width:58px;text-align:center;">Score</th>
                <th style="min-width:185px;text-align:center;">Review HSSE</th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php $leadItems = $kpiItems->get('leading', collect()); ?>
            <?php $__currentLoopData = $leadItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $det        = $existingDetails[$item->id] ?? null;
                $isAS       = !$item->is_scored;
                $isRej      = $det && $det->review_status === 'rejected';
                $isOK       = $det && $det->review_status === 'approved';
                $unitLabel  = $item->unit_label ?? ($item->unit ?? '∑');
                $isPercent  = ($unitLabel === '%');
                [$iName, $iTarget] = array_pad(explode("\n", $item->name, 2), 2, '');
                $iName   = trim($iName);
                $iTarget = trim($iTarget);
                $evs     = $det ? $det->evidences : collect();
                $evCnt   = $evs->count();
                $upId    = $det?->id ?? '';
                $nilaiVal   = (float) ($det?->nilai ?? 0);
                $score      = $det?->score;
                $scClsRow   = ($score > 0 && !$isAS) ? $scCls($nilaiVal) : '';
                $rowCls     = trim(($isAS ? 'r-ar ' : '') . ($isRej ? 'r-rej ' : '') . ($isOK ? 'r-ok ' : ''));
                $dId        = $det?->id ?? '';
            ?>
            <tr class="kpi-row <?php echo e($rowCls); ?>" data-section="leading" data-item-no="<?php echo e($item->item_no); ?>">
                <td class="td-no-kpi"><?php echo e($item->item_no); ?></td>

                
                <td class="td-item">
                    <div class="item-name"><?php echo e($iName); ?></div>
                    <?php if($iTarget): ?><div class="item-target">🎯 <?php echo e($iTarget); ?></div><?php endif; ?>
                    <div class="item-tags">
                        <?php if($isAS): ?><span class="itag itag-ar">As Reported</span><?php endif; ?>
                        <?php if($isRej): ?><span class="itag itag-rej">✗ Ditolak</span><?php endif; ?>
                        <?php if($isOK): ?> <span class="itag itag-ok">✓ Disetujui</span><?php endif; ?>
                    </div>
                    
                    <?php if($isRej && $det?->hsse_catatan): ?>
                    <div class="rej-remark-box">
                        <div class="rej-remark-label">💬 Catatan HSSE</div>
                        <div class="rej-remark-text"><?php echo e($det->hsse_catatan); ?></div>
                    </div>
                    <?php endif; ?>
                </td>

                
                <td class="td-jml">
                    <?php if($canEditKoord): ?>
                    <div class="jml-wrap">
                        <input type="number" class="jml-inp" id="ac-<?php echo e($item->id); ?>"
                            value="<?php echo e(old('items.'.$item->id.'.actual_count', $det?->actual_count)); ?>"
                            min="0" step="<?php echo e($isPercent ? '0.01' : '1'); ?>"
                            placeholder="<?php echo e($isAS ? '—' : '0'); ?>"
                            <?php echo e($isAS ? 'disabled' : ''); ?>

                            data-item="<?php echo e($item->id); ?>"
                            data-save-url="<?php echo e(route('kpi-hsse.update', $report)); ?>"
                            onchange="saveRow(this)" onblur="saveRow(this)">
                        <span class="unit-tag"><?php echo e($unitLabel); ?></span>
                    </div>
                    <?php else: ?>
                    <div class="jml-readonly">
                        <?php echo e($det?->actual_count ?? '—'); ?>

                        <div class="jml-unit"><?php echo e($unitLabel); ?></div>
                    </div>
                    <?php endif; ?>
                </td>

                
                <td class="td-ket">
                    <?php if($canEditKoord): ?>
                    <textarea class="ket-inp" id="ket-<?php echo e($item->id); ?>" rows="2"
                        placeholder="Keterangan singkat..."
                        data-item="<?php echo e($item->id); ?>"
                        data-save-url="<?php echo e(route('kpi-hsse.update', $report)); ?>"
                        onblur="saveKet(this)"><?php echo e(old('items.'.$item->id.'.keterangan', $det?->keterangan)); ?></textarea>
                    <?php else: ?>
                    <div class="ket-readonly"><?php echo e($det?->keterangan ?: '—'); ?></div>
                    <?php endif; ?>
                </td>

                
                <td class="td-ev">
                    <?php if($isAS): ?>
                        <span class="ev-na">Tidak diperlukan</span>
                    <?php else: ?>
                        <div class="ev-grid" id="ev-row-<?php echo e($upId); ?>">
                            <?php $__currentLoopData = $evs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $isPdf = str_contains($ev->file_type ?? '', 'pdf'); ?>
                            <div class="ev-item" id="ev-item-<?php echo e($ev->id); ?>">
                                <?php if($isPdf): ?>
                                    <a href="<?php echo e(Storage::url($ev->file_path)); ?>" target="_blank" class="ev-pdf" title="<?php echo e($ev->file_name); ?>">📄</a>
                                <?php else: ?>
                                    <img class="ev-thumb" src="<?php echo e(Storage::url($ev->file_path)); ?>"
                                        onclick="previewImg('<?php echo e(Storage::url($ev->file_path)); ?>','<?php echo e(e($ev->file_name)); ?>')"
                                        title="<?php echo e($ev->caption ?? $ev->file_name); ?>">
                                <?php endif; ?>
                                <?php if($canEditKoord): ?>
                                <div class="ev-del-btn" onclick="doDelEv('<?php echo e($ev->id); ?>','<?php echo e(route('kpi-hsse.evidences.delete',[$report,$ev])); ?>','<?php echo e($upId); ?>')">×</div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php if($canEditKoord && $det): ?>
                        <label class="ev-upload-btn" id="lbl-<?php echo e($upId); ?>" for="ev-up-<?php echo e($upId); ?>">+ Lampiran</label>
                        <input type="file" id="ev-up-<?php echo e($upId); ?>" class="d-none ev-input"
                            accept=".jpg,.jpeg,.png,.webp,.pdf" multiple
                            data-uid="<?php echo e($upId); ?>"
                            data-url="<?php echo e(route('kpi-hsse.evidences.upload', $report)); ?>">
                        <div class="ev-prog" id="ev-prog-<?php echo e($upId); ?>"><div class="ev-bar" id="ev-bar-<?php echo e($upId); ?>"></div></div>
                        <div class="ev-err" id="ev-err-<?php echo e($upId); ?>"></div>
                        <?php elseif($canEditKoord && !$det): ?>
                        <span class="ev-hint">Isi ∑/% dulu</span>
                        <?php endif; ?>
                        <div id="ev-st-<?php echo e($upId); ?>" style="margin-top:4px;">
                            <?php if($evCnt > 0): ?>
                                <span class="ev-stat-ok"><?php echo e($evCnt); ?> file</span>
                            <?php elseif($item->is_scored && $canEditKoord): ?>
                                <span class="ev-stat-req">Wajib</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </td>

                
                <?php if($isHsse || $isSA): ?>
                <td class="td-nilai">
                    <?php if($isAS): ?><span class="nilai-ar">A/R</span>
                    <?php else: ?>
                    <input type="number" class="nilai-inp" id="nilai-<?php echo e($item->id); ?>"
                        value="<?php echo e($nilaiVal > 0 ? number_format($nilaiVal,2,'.','') : ''); ?>"
                        min="0" max="100" step="0.01"
                        placeholder="<?php echo e($canEditHsse ? '0–100' : '—'); ?>"
                        <?php echo e($canEditHsse ? '' : 'disabled'); ?>

                        onchange="doUpdateScore('<?php echo e($item->id); ?>')">
                    <?php endif; ?>
                </td>
                <td class="td-bobot">
                    <?php if($item->is_scored): ?> <?php echo e(number_format((float)$item->bobot*100,1)); ?>%
                    <?php else: ?> <span class="td-bobot-na">—</span><?php endif; ?>
                </td>
                <td class="td-score <?php echo e($scClsRow); ?>" id="sc-<?php echo e($item->id); ?>">
                    <?php echo e($isAS ? 'A/R' : ($score !== null ? number_format((float)$score,2) : '—')); ?>

                </td>

                
                <td class="td-review">
                    <?php if($isAS): ?>
                        <div class="inline-review ar-zone">
                            <span class="ir-label ar">As Reported — tidak direview</span>
                        </div>
                    <?php elseif($canDoInlineReview): ?>
                        <div class="inline-review">
                            <span class="ir-label">Catatan Verifikasi</span>
                            <textarea class="ir-catatan" id="ircmt-<?php echo e($dId); ?>" rows="2"
                                placeholder="Catatan untuk koordinator..."
                                data-detail="<?php echo e($dId); ?>"
                                data-url="<?php echo e(route('kpi-hsse.catatan.update', $report)); ?>"
                                onblur="saveCat(this)"><?php echo e($det?->hsse_catatan); ?></textarea>
                            <div class="ir-dec-row" id="irdec-<?php echo e($dId); ?>">
                                <button type="button"
                                    class="ir-dec-btn ir-ok <?php echo e($isOK ? 'active' : ''); ?>"
                                    id="irbtn-ap-<?php echo e($dId); ?>"
                                    onclick="doInlineDecision('<?php echo e($dId); ?>','approved','<?php echo e(route('kpi-hsse.decision.update',$report)); ?>','<?php echo e($item->id); ?>')">
                                    ✓ Accept
                                </button>
                                <button type="button"
                                    class="ir-dec-btn ir-rej <?php echo e($isRej ? 'active' : ''); ?>"
                                    id="irbtn-rj-<?php echo e($dId); ?>"
                                    onclick="doInlineDecision('<?php echo e($dId); ?>','rejected','<?php echo e(route('kpi-hsse.decision.update',$report)); ?>','<?php echo e($item->id); ?>')">
                                    ✗ Reject
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="inline-review" style="background:var(--gray-50);border-left-color:var(--gray-200);">
                            <?php if($isOK): ?> <span class="ir-chip-ok">✓ Disetujui</span>
                            <?php elseif($isRej): ?> <span class="ir-chip-rej">✗ Ditolak</span>
                                <?php if($det?->hsse_catatan): ?>
                                <div style="font-size:.67rem;color:#b91c1c;margin-top:4px;font-style:italic;"><?php echo e($det->hsse_catatan); ?></div>
                                <?php endif; ?>
                            <?php else: ?> <span style="font-size:.68rem;color:var(--gray-400);">Belum direview</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
            <tfoot>
            <tr class="kpi-total-row">
                <td colspan="<?php echo e(($isHsse || $isSA) ? 7 : 4); ?>"
                    style="text-align:right;font-size:.74rem;opacity:.65;letter-spacing:.2px;">
                    Total Score KPI HSSE
                </td>
                <td colspan="<?php echo e(($isHsse || $isSA) ? 2 : 1); ?>" style="padding:.5rem;">
                    <div class="score-badges">
                        <div class="score-sub lag">
                            <span class="lbl">Lagging</span>
                            <span class="val" style="color:#fde68a;" id="t-lag"><?php echo e(number_format($tsLag,2)); ?></span>
                        </div>
                        <div style="font-size:1.45rem;font-weight:800;color:#fff;" id="t-total"><?php echo e(number_format($ts,2)); ?></div>
                        <div class="score-sub lead">
                            <span class="lbl">Leading</span>
                            <span class="val" style="color:#bfdbfe;" id="t-lead"><?php echo e(number_format($tsLead,2)); ?></span>
                        </div>
                    </div>
                </td>
            </tr>
            </tfoot>
        </table>
        </div>
    </div>

    <div class="step-nav">
        <button type="button" class="btn-step btn-step-outline" onclick="goStep(1)">← Lagging</button>
        <div style="display:flex;gap:.6rem;align-items:center;">
            <?php if(!$isCreate && ($canEditKoord || $canEditHsse || $isSA)): ?>
            <button type="button" class="btn-step btn-step-draft" onclick="doSaveDraft()">💾 Simpan Progres</button>
            <?php endif; ?>
            <?php if($canEditKoord): ?>
            <button type="button" class="btn-step btn-step-success" data-bs-toggle="modal" data-bs-target="#submitModal">🚀 Submit ke HSSE</button>
            <?php endif; ?>
            <?php if(($canEditHsse || $isSA) && $isEdit): ?>
            <a href="<?php echo e(route('kpi-hsse.show', $report)); ?>" class="btn-step btn-step-primary" style="text-decoration:none;">👁 Lihat Detail</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php endif; ?> 

</div>


<div>
<div class="kpi-sidebar-inner">

    <?php if(!$isCreate): ?>
    <div class="sidebar-card">
        <div class="sb-title">Score KPI</div>
        <div style="display:flex;align-items:center;gap:.9rem;margin-bottom:.85rem;">
            <div class="score-ring <?php echo e($ringCls); ?>" style="font-size:.88rem;" id="sb-ring">
                <span id="sb-val"><?php echo e($ts > 0 ? number_format($ts,1) : '—'); ?></span>
                <span style="font-size:.48rem;opacity:.65;">/100</span>
            </div>
            <div style="flex:1;">
                <div style="display:flex;justify-content:space-between;margin-bottom:.35rem;">
                    <span style="font-size:.68rem;color:var(--gray-400);">Lagging</span>
                    <strong id="sb-lag" style="font-size:.82rem;color:var(--amber);"><?php echo e(number_format($tsLag,2)); ?></strong>
                </div>
                <div style="display:flex;justify-content:space-between;">
                    <span style="font-size:.68rem;color:var(--gray-400);">Leading</span>
                    <strong id="sb-lead" style="font-size:.82rem;color:var(--blue);"><?php echo e(number_format($tsLead,2)); ?></strong>
                </div>
            </div>
        </div>
        <div class="mini-prog">
            <div class="mini-prog-fill" id="sb-bar" style="width:<?php echo e(min($ts,100)); ?>%;"></div>
        </div>
        <?php if(!($isHsse || $isSA)): ?>
        <p style="font-size:.66rem;color:var(--gray-400);margin:.6rem 0 0;">Nilai &amp; Score diisi HSSE setelah submit.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if(!$isCreate && ($isHsse || $isSA) && $totalItems > 0): ?>
    <?php
        // v13: hitung reviewed = semua yg punya review_status (termasuk item 6-7)
        $apCnt   = collect($existingDetails)->filter(fn($d) => $d->review_status === 'approved')->count();
        $rjCnt2  = collect($existingDetails)->filter(fn($d) => $d->review_status === 'rejected')->count();
        $rvDone  = $apCnt + $rjCnt2;
        $rvPct   = $totalItems > 0 ? round($rvDone / $totalItems * 100) : 0;
    ?>
    <div class="sidebar-card">
        <div class="sb-title">Progress Review</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;text-align:center;margin-bottom:.75rem;">
            <div>
                <div style="font-weight:800;color:var(--green);font-size:1.35rem;" id="cnt-ap"><?php echo e($apCnt); ?></div>
                <div style="font-size:.63rem;color:var(--gray-400);">✓ Disetujui</div>
            </div>
            <div>
                <div style="font-weight:800;color:var(--red);font-size:1.35rem;" id="cnt-rj"><?php echo e($rjCnt2); ?></div>
                <div style="font-size:.63rem;color:var(--gray-400);">✗ Ditolak</div>
            </div>
        </div>
        <div class="mini-prog mb-2">
            <div class="mini-prog-fill" id="rv-prog-fill" style="width:<?php echo e($rvPct); ?>%;"></div>
        </div>
        <div style="font-size:.69rem;color:var(--gray-400);text-align:center;margin-bottom:.5rem;" id="rv-lbl">
            <?php echo e($rvDone); ?> / <?php echo e($totalItems); ?> direview
        </div>
    </div>
    <?php endif; ?>

    <div class="sidebar-card">
        <div class="sb-title">⚡ Aksi</div>
        <div style="display:grid;gap:.5rem;">
            <?php if($isCreate): ?>
                <p style="font-size:.73rem;color:var(--gray-400);margin:0;">Isi Periode &amp; Kapal, klik <strong>Simpan Draft</strong>.</p>
                <button type="button" class="btn btn-primary btn-sm fw-bold rounded-pill w-100" onclick="doSaveDraft()">💾 Simpan Draft</button>
            <?php elseif($canEditKoord): ?>
                <div style="display:flex;align-items:center;gap:.5rem;padding:.5rem .75rem;border-radius:var(--radius-sm);background:var(--green-light);border:1px solid var(--green-mid);font-size:.73rem;color:var(--green);font-weight:700;">
                    ✅ Auto-save aktif
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill w-100" onclick="doSaveDraft()">💾 Simpan Progres</button>
                <button type="button" class="btn btn-success btn-sm fw-bold rounded-pill w-100" data-bs-toggle="modal" data-bs-target="#submitModal">🚀 Submit ke HSSE</button>
            <?php elseif($canEditHsse || ($isSA && !$isCreate)): ?>
                <div style="display:flex;align-items:center;gap:.5rem;padding:.5rem .75rem;border-radius:var(--radius-sm);background:#e0f2fe;border:1px solid #7dd3fc;font-size:.73rem;color:#0369a1;font-weight:700;">
                    🔵 Mode Penilaian HSSE
                </div>
                <button type="button" class="btn btn-primary btn-sm fw-bold rounded-pill w-100" onclick="saveAllNilai()">💾 Simpan Semua Nilai</button>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill w-100" onclick="doSaveDraft()">✔ Konfirmasi Tersimpan</button>
            <?php else: ?>
                <?php if($report && $canEditKoord): ?>
                <a href="<?php echo e(route('kpi-hsse.edit', $report)); ?>" class="btn btn-outline-primary btn-sm rounded-pill w-100">✏ Edit</a>
                <?php endif; ?>
                <?php if($report && ($isSA || ($isHsse && in_array($rStatus, ['submitted','validated'])))): ?>
                <a href="<?php echo e(route('kpi-hsse.edit', $report)); ?>" class="btn btn-outline-primary btn-sm rounded-pill w-100">✏ Edit Penilaian</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if(!$isCreate): ?>
    <div class="sidebar-card">
        <div class="sb-title">Kapal / Unit (<?php echo e($report->vessels->count()); ?>)</div>
        <?php $__currentLoopData = $report->vessels->sortBy('sort_order'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="vessel-chip">
            <div class="vessel-chip-name">
                <?php echo e($v->vessel_name); ?>

                <?php if($v->vessel_count): ?><span style="background:rgba(29,111,232,.1);color:#1e40af;border-radius:4px;padding:1px 6px;font-size:.66rem;font-weight:700;margin-left:3px;"><?php echo e($v->vessel_count); ?></span><?php endif; ?>
            </div>
            <?php if($v->contract_number): ?><div class="vessel-chip-meta">📄 <?php echo e($v->contract_number); ?></div><?php endif; ?>
            <?php if($v->contract_end_date): ?>
            <?php $exp = $v->contract_end_date->isPast(); ?>
            <div class="vessel-chip-meta" style="<?php echo e($exp?'color:#dc2626;':''); ?>">
                📅 s/d <?php echo e($v->contract_end_date->format('d M Y')); ?>

                <?php if($exp): ?> ⚠ Expired <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    <?php if(!$isCreate && $report->statusLogs->count()): ?>
    <div class="sidebar-card">
        <div class="sb-title">Riwayat Status</div>
        <?php $__currentLoopData = $report->statusLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div style="display:flex;gap:.65rem;margin-bottom:.9rem;">
            <div class="tl-dot <?php echo e($log->to_status); ?>"></div>
            <div style="flex:1;">
                <div style="display:flex;justify-content:space-between;align-items:baseline;">
                    <span style="font-weight:600;font-size:.81rem;"><?php echo e(ucfirst($log->to_status)); ?></span>
                    <span style="font-size:.66rem;color:var(--gray-400);"><?php echo e($log->acted_at->format('d M Y')); ?></span>
                </div>
                <div style="font-size:.71rem;color:var(--gray-400);"><?php echo e($log->actedBy->name ?? '-'); ?></div>
                <?php if($log->comment): ?>
                <div style="font-size:.68rem;color:var(--gray-400);font-style:italic;padding-left:7px;border-left:2px solid var(--gray-200);margin-top:2px;">"<?php echo e(Str::limit($log->comment,65)); ?>"</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    <?php if($canEditKoord): ?>
    <div class="sidebar-card" style="background:var(--navy);border-color:var(--navy);">
        <div class="sb-title" style="color:#60a5fa;">Panduan Koordinator</div>
        <ul style="margin:0;padding-left:1.1rem;color:rgba(255,255,255,.8);font-size:.73rem;line-height:2;">
            <li>Isi <strong>∑ / %</strong> tiap item (termasuk FAC &amp; Nearmiss)</li>
            <li>Tersimpan <strong>otomatis</strong> saat pindah kolom</li>
            <li>Lampiran No.1–7: cukup <strong>upload 1x di baris No.1</strong></li>
            <li>Item No.8+: upload lampiran sendiri</li>
            <li>Item merah = ditolak → baca catatan HSSE &amp; perbaiki</li>
        </ul>
    </div>
    <?php endif; ?>
    <?php if($canEditHsse || ($isSA && $canReview)): ?>
    <div class="sidebar-card" style="background:#0c2044;border-color:#0c2044;">
        <div class="sb-title" style="color:#93c5fd;">Panduan HSSE</div>
        <ul style="margin:0;padding-left:1.1rem;color:rgba(255,255,255,.8);font-size:.73rem;line-height:2;">
            <li>Isi <strong>Nilai</strong> 0–100 tiap item scored</li>
            <li>Score = Nilai × Bobot (otomatis)</li>
            <li>Accept/Reject untuk item No.1–7 lagging (termasuk FAC &amp; Nearmiss)</li>
            <li>Item No.8 &amp; Leading A/R tidak perlu direview</li>
            <li>Isi catatan jika item ditolak (wajib)</li>
        </ul>
    </div>
    <?php endif; ?>

</div>
</div>

</div>
</div>
</div>
</div>
</div>


<?php if($isCreate): ?>
<div class="sticky-bar">
    <div class="bar-info"><strong>Laporan Baru</strong> — isi Periode &amp; Kapal, lalu simpan sebagai draft</div>
    <div style="display:flex;gap:.5rem;">
        <a href="<?php echo e(route('kpi-hsse.index')); ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Batal</a>
        <button type="button" class="btn btn-primary fw-bold rounded-pill px-4" onclick="doSaveDraft()">💾 Simpan Draft</button>
    </div>
</div>
<?php elseif($canEditKoord && ($isEdit || $isShow)): ?>
<div class="sticky-bar">
    <div class="bar-info">
        <strong><?php echo e($report->company->name ?? '-'); ?></strong> — <?php echo e($report->kpiPeriod->label ?? '-'); ?>

        <span class="autosave-badge ms-2">✅ Auto-save aktif</span>
    </div>
    <div style="display:flex;gap:.5rem;">
        <a href="<?php echo e(route('kpi-hsse.index')); ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">← Daftar</a>
        <button type="button" class="btn btn-outline-secondary btn-sm fw-bold rounded-pill px-3" onclick="doSaveDraft()">💾 Simpan</button>
        <button type="button" class="btn btn-success fw-bold rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#submitModal">🚀 Submit ke HSSE</button>
    </div>
</div>
<?php elseif($canEditHsse || ($isSA && !$isCreate)): ?>
<div class="sticky-bar">
    <div class="bar-info">
        Penilaian HSSE — <strong><?php echo e($report->kpiPeriod->label ?? '-'); ?></strong>
        <span class="badge badge-<?php echo e($rStatus); ?> ms-2"><?php echo e(['draft'=>'Draft','submitted'=>'Submitted','validated'=>'Validated','rejected'=>'Rejected'][$rStatus] ?? ucfirst($rStatus)); ?></span>
    </div>
    <div style="display:flex;gap:.5rem;">
        <a href="<?php echo e(route('kpi-hsse.index')); ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">← Daftar</a>
        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="saveAllNilai()">💾 Simpan Nilai</button>
        <?php if($canEditHsse || $isSA): ?>
        <button type="button" class="btn btn-warning btn-sm fw-bold rounded-pill px-3" onclick="doSaveDraft()">✔ Konfirmasi Tersimpan</button>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>


<div id="kpi-toast"></div>


<?php if(!$isCreate && $report && in_array($rStatus,['draft','rejected']) && ($isKoord || $isSA)): ?>
<div class="modal fade" id="submitModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--radius);border:none;box-shadow:var(--shadow-lg);">
            <form action="<?php echo e(route('kpi-hsse.submit', $report)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">🚀 Submit ke HSSE</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="info-banner blue mb-3">
                        ℹ️ Pastikan semua item scored sudah diisi <strong>∑/%</strong> dan memiliki <strong>lampiran</strong>.<br>
                        Item No.6 (FAC) &amp; No.7 (Nearmiss) wajib diisi ∑. Lampiran No.1–7 cukup ada di baris No.1.
                    </div>
                    <div class="field">
                        <label class="field-lbl">Catatan untuk HSSE (opsional)</label>
                        <textarea name="comment" class="field-textarea" rows="3" placeholder="Catatan tambahan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold rounded-pill px-4">🚀 Kirim ke HSSE</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>


<?php if(!$isCreate && $report && ($canEditKoord || $canEditHsse || $isSA)): ?>
<div class="modal fade" id="draftModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:var(--radius);border:none;box-shadow:var(--shadow-lg);">
            <form id="form-save-draft" action="<?php echo e(route('kpi-hsse.draft', $report)); ?>" method="POST">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" style="font-size:.95rem;">💾 Simpan Progres</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-2">
                    <?php if($canEditKoord): ?>
                    <p style="font-size:.82rem;color:var(--gray-600);margin:0;">Data ∑/%, keterangan, dan lampiran tersimpan otomatis saat Anda berpindah kolom. Klik konfirmasi untuk memastikan semua perubahan sudah tersimpan.</p>
                    <?php elseif($canEditHsse || $isSA): ?>
                    <p style="font-size:.82rem;color:var(--gray-600);margin:0;">Nilai dan catatan verifikasi tersimpan otomatis. Klik konfirmasi untuk memastikan semua progres penilaian tersimpan.</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm fw-bold rounded-pill px-4">💾 Konfirmasi Tersimpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>


<div class="modal fade" id="delEvSharedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
        <div class="modal-content" style="border-radius:var(--radius);border:none;box-shadow:var(--shadow-lg);overflow:hidden;">
            <div class="del-modal-header shared">
                <div class="del-modal-icon shared">🗑</div>
                <div>
                    <h5 class="del-modal-title shared">Hapus Lampiran Bersama?</h5>
                    <p class="del-modal-desc shared">Lampiran ini dipakai bersama oleh baris No.1–7. Menghapusnya akan menghapus dari <strong>semua</strong> baris sekaligus.</p>
                </div>
            </div>
            <div class="del-modal-body">
                <div class="del-modal-meta">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Tindakan ini <strong>tidak dapat dibatalkan</strong>.
                </div>
            </div>
            <div class="modal-footer border-0 py-3 px-4" style="gap:.5rem;background:#fff;">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal" style="font-size:.8rem;">Batal</button>
                <button type="button" id="del-ev-shared-confirm" class="btn btn-sm rounded-pill px-4 fw-bold" style="background:var(--red);color:#fff;border:none;font-size:.8rem;min-width:110px;">🗑 Hapus Semua</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="delEvSingleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content" style="border-radius:var(--radius);border:none;box-shadow:var(--shadow-lg);overflow:hidden;">
            <div class="del-modal-header single">
                <div class="del-modal-icon single">🗑</div>
                <div>
                    <h5 class="del-modal-title single">Hapus Lampiran?</h5>
                    <p class="del-modal-desc single">File lampiran ini akan dihapus permanen dari sistem.</p>
                </div>
            </div>
            <div class="del-modal-body">
                <div class="del-modal-meta">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Tindakan ini <strong>tidak dapat dibatalkan</strong>.
                </div>
            </div>
            <div class="modal-footer border-0 py-3 px-4" style="gap:.5rem;background:#fff;">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal" style="font-size:.8rem;">Batal</button>
                <button type="button" id="del-ev-single-confirm" class="btn btn-sm rounded-pill px-4 fw-bold" style="background:var(--red);color:#fff;border:none;font-size:.8rem;min-width:110px;">🗑 Hapus</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="imgModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:var(--radius);border:none;">
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

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
/* ═══════════════════════════════════════════════════════════
   GLOBALS
═══════════════════════════════════════════════════════════ */
const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content || '';
const MAX_BYTES = 2 * 1024 * 1024;
const ALLOWED   = ['image/jpeg','image/png','image/webp','application/pdf'];
const SCORE_URL = '<?php echo e(isset($kpiReport) ? route("kpi-hsse.score.update", $kpiReport) : ""); ?>';
const LAG_SHARED_NOS = [1,2,3,4,5,6,7];

let _currentStep = 0;

/* ═══════════════════════════════════════════════════════════
   WIZARD
═══════════════════════════════════════════════════════════ */
function goStep(idx) {
    document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.wizard-step').forEach((s,i) => {
        s.classList.remove('active','done');
        if (i < idx) s.classList.add('done');
    });
    const panel = document.getElementById('step-' + idx);
    const wStep = document.getElementById('ws-'   + idx);
    if (panel) panel.classList.add('active');
    if (wStep) wStep.classList.add('active');
    _currentStep = idx;
    window.scrollTo({top:0,behavior:'smooth'});
}
function jumpStep(idx) {
    if (document.getElementById('step-' + idx)) goStep(idx);
}

/* ═══════════════════════════════════════════════════════════
   TOAST
═══════════════════════════════════════════════════════════ */
let _tt;
function toast(msg, type='ok') {
    const el = document.getElementById('kpi-toast');
    if (!el) return;
    el.textContent = msg;
    el.className   = 't-' + type;
    el.style.display = 'block';
    clearTimeout(_tt);
    _tt = setTimeout(() => el.style.display = 'none', 3000);
}

/* ═══════════════════════════════════════════════════════════
   SIMPAN DRAFT
═══════════════════════════════════════════════════════════ */
function doSaveDraft() {
    const formCreate = document.getElementById('form-create');
    if (formCreate) { formCreate.submit(); return; }
    const modal = document.getElementById('draftModal');
    if (modal) new bootstrap.Modal(modal).show();
    else toast('✅ Auto-save aktif — semua perubahan sudah tersimpan','ok');
}

/* ═══════════════════════════════════════════════════════════
   CREATE: Vessel table
═══════════════════════════════════════════════════════════ */
let _vi = 1;
function addVessel() {
    const tbody = document.getElementById('vessel-tbody'); if (!tbody) return;
    const idx   = _vi++;
    const n     = tbody.querySelectorAll('tr').length + 1;
    const tr    = document.createElement('tr');
    tr.id = 'vrow-' + idx;
    tr.innerHTML = `
        <td class="td-no">${n}</td>
        <td><input type="text" class="vtbl-inp" name="vessels[${idx}][contract_number]" placeholder="No. kontrak"></td>
        <td><input type="date" class="vtbl-inp" name="vessels[${idx}][contract_end_date]"></td>
        <td><input type="text" class="vtbl-inp" name="vessels[${idx}][vessel_count]" placeholder="1 unit"></td>
        <td><input type="text" class="vtbl-inp" name="vessels[${idx}][vessel_name]" placeholder="Nama kapal / unit" required></td>
        <td class="td-del"><button type="button" class="btn-del-vessel" onclick="delVessel(${idx})">×</button></td>`;
    tbody.appendChild(tr);
    refreshDelBtns();
    tr.querySelector('.vtbl-inp')?.focus();
}
function delVessel(idx) {
    const rows = document.querySelectorAll('#vessel-tbody tr');
    if (rows.length <= 1) { alert('Minimal 1 kapal/unit.'); return; }
    document.getElementById('vrow-' + idx)?.remove();
    refreshDelBtns();
    document.querySelectorAll('#vessel-tbody tr').forEach((r,i) => {
        const no = r.querySelector('.td-no'); if (no) no.textContent = i+1;
    });
}
function refreshDelBtns() {
    const rows = document.querySelectorAll('#vessel-tbody tr');
    rows.forEach(r => {
        const b = r.querySelector('.btn-del-vessel');
        if (b) b.classList.toggle('d-none', rows.length <= 1);
    });
}

/* ═══════════════════════════════════════════════════════════
   AUTO-SAVE: ∑ / %
═══════════════════════════════════════════════════════════ */
const _sT = {};
async function saveRow(inp) {
    const id  = inp.dataset.item; if (!id) return;
    const url = inp.dataset.saveUrl;
    clearTimeout(_sT[id]);
    inp.classList.remove('saved','err'); inp.classList.add('saving');
    _sT[id] = setTimeout(async () => {
        const ket = document.getElementById('ket-' + id);
        const fd  = new FormData();
        fd.append('_method','PUT');
        fd.append('kpi_item_id', id);
        fd.append('actual_count', inp.value);
        fd.append('keterangan', ket ? ket.value : '');
        try {
            const r = await fetch(url, {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}, body:fd});
            const d = await r.json();
            inp.classList.remove('saving');
            if (d.success) {
                inp.classList.add('saved');
                toast('✅ Tersimpan','ok');
                setTimeout(() => inp.classList.remove('saved'), 1800);
                if (d.detail_id) refreshUploadBtn(id, d.detail_id, url);
            } else {
                inp.classList.add('err');
                toast('❌ ' + (d.message || 'Gagal'),'err');
            }
        } catch {
            inp.classList.remove('saving'); inp.classList.add('err');
            toast('❌ Koneksi error','err');
        }
    }, 400);
}

function refreshUploadBtn(itemId, detailId, baseUrl) {
    if (document.getElementById('ev-row-' + detailId)) return;
    const inp   = document.getElementById('ac-' + itemId); if (!inp) return;
    const row   = inp.closest('tr');
    const tdEv  = row ? row.querySelector('.td-ev') : null; if (!tdEv) return;
    tdEv.querySelector('.ev-hint')?.remove();

    const evRow = document.createElement('div');
    evRow.className = 'ev-grid'; evRow.id = 'ev-row-' + detailId;

    const lbl = document.createElement('label');
    lbl.className = 'ev-upload-btn'; lbl.id = 'lbl-' + detailId;
    lbl.setAttribute('for','ev-up-' + detailId); lbl.textContent = '+ Lampiran';

    const finp = document.createElement('input');
    finp.type = 'file'; finp.id = 'ev-up-' + detailId;
    finp.className = 'd-none ev-input'; finp.multiple = true;
    finp.accept = '.jpg,.jpeg,.png,.webp,.pdf';
    finp.dataset.uid = detailId;
    finp.dataset.url = baseUrl.replace('/update','') + '/evidences/upload';
    finp.addEventListener('change', () => handleUpload(finp));

    const prog = document.createElement('div');
    prog.className = 'ev-prog'; prog.id = 'ev-prog-' + detailId;
    prog.innerHTML = '<div class="ev-bar" id="ev-bar-' + detailId + '"></div>';

    const err = document.createElement('div');
    err.className = 'ev-err'; err.id = 'ev-err-' + detailId;

    const st = document.createElement('div');
    st.id = 'ev-st-' + detailId; st.style.marginTop = '4px';
    st.innerHTML = '<span class="ev-stat-req">Wajib</span>';

    tdEv.appendChild(evRow); tdEv.appendChild(lbl); tdEv.appendChild(finp);
    tdEv.appendChild(prog);  tdEv.appendChild(err);  tdEv.appendChild(st);
}

/* ═══════════════════════════════════════════════════════════
   AUTO-SAVE: Keterangan
═══════════════════════════════════════════════════════════ */
const _kT = {};
async function saveKet(ta) {
    const id  = ta.dataset.item; if (!id) return;
    const url = ta.dataset.saveUrl;
    clearTimeout(_kT[id]);
    ta.classList.remove('saved','err');
    _kT[id] = setTimeout(async () => {
        const ac = document.getElementById('ac-' + id);
        const fd = new FormData();
        fd.append('_method','PUT');
        fd.append('kpi_item_id', id);
        fd.append('actual_count', ac ? ac.value : '');
        fd.append('keterangan', ta.value);
        try {
            const r = await fetch(url, {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}, body:fd});
            const d = await r.json();
            if (d.success) { ta.classList.add('saved'); toast('✅ Tersimpan','ok'); setTimeout(() => ta.classList.remove('saved'),1800); }
            else { ta.classList.add('err'); toast('❌ ' + (d.message||'Gagal'),'err'); }
        } catch { ta.classList.add('err'); toast('❌ Koneksi error','err'); }
    }, 600);
}

/* ═══════════════════════════════════════════════════════════
   EVIDENCE — init on load
═══════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.ev-input').forEach(inp => inp.addEventListener('change', () => handleUpload(inp)));
    const anchorInp = document.getElementById('ev-up-anchor-lag');
    if (anchorInp) anchorInp.addEventListener('change', () => handleAnchorUpload(anchorInp));
});

/* ─── ANCHOR UPLOAD (lagging No.1 → pool No.1-7) ─── */
async function handleAnchorUpload(inp) {
    const files = Array.from(inp.files); if (!files.length) return;
    let anchorDetailId  = inp.dataset.anchorDetailId || '';
    const anchorItemId  = inp.dataset.anchorItemId   || '';
    const url           = inp.dataset.url;
    const lbl   = document.getElementById('lbl-anchor-lag');
    const prog  = document.getElementById('ev-prog-anchor-lag');
    const bar   = document.getElementById('ev-bar-anchor-lag');
    const errEl = document.getElementById('ev-err-anchor-lag');

    const badSz = files.filter(f => f.size > MAX_BYTES);
    if (badSz.length) { showErr(errEl,'❌ Melebihi 2 MB: ' + badSz.map(f=>f.name).join(', ')); inp.value=''; return; }
    const badTy = files.filter(f => !ALLOWED.includes(f.type));
    if (badTy.length) { showErr(errEl,'❌ Format tidak didukung (.jpg/.png/.webp/.pdf)'); inp.value=''; return; }
    hideErr(errEl);

    if (lbl) lbl.classList.add('busy');
    if (prog) prog.classList.add('on');

    if (!anchorDetailId && anchorItemId) {
        try {
            const saveUrl = url.replace('/evidences/upload','');
            const fd2 = new FormData();
            fd2.append('_method','PUT');
            fd2.append('kpi_item_id', anchorItemId);
            fd2.append('actual_count', document.getElementById('ac-'+anchorItemId)?.value || '0');
            fd2.append('keterangan','');
            const sr = await fetch(saveUrl, {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}, body:fd2});
            const sd = await sr.json();
            if (sd.success && sd.detail_id) {
                anchorDetailId = sd.detail_id;
                inp.dataset.anchorDetailId = anchorDetailId;
            }
        } catch {
            showErr(errEl,'❌ Gagal menyiapkan data item No.1');
            if (lbl) lbl.classList.remove('busy');
            if (prog) prog.classList.remove('on');
            inp.value=''; return;
        }
    }

    if (!anchorDetailId) {
        showErr(errEl,'❌ Isi data ∑/% item No.1 terlebih dahulu');
        if (lbl) lbl.classList.remove('busy');
        if (prog) prog.classList.remove('on');
        inp.value=''; return;
    }

    let done = 0;
    for (const file of files) {
        const fd = new FormData();
        fd.append('kpi_report_detail_id', anchorDetailId);
        fd.append('file', file);
        try {
            const r = await fetch(url, {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}, body:fd});
            const d = await r.json();
            if (d.success) {
                const ev    = d.evidence;
                const realId = d.anchor_detail_id || anchorDetailId;
                const delUrl = buildDelUrl(ev.id);
                const isPdf  = (ev.type||'').includes('pdf');
                addThumbnailToSharedRows(ev, isPdf, delUrl, realId);
                refreshAllSharedBadges();
                toast('📎 Upload berhasil','ok');
            } else {
                showErr(errEl,'❌ ' + (d.message||'Upload gagal'));
            }
        } catch { showErr(errEl,'❌ Koneksi error'); }
        done++;
        if (bar) bar.style.width = Math.round(done/files.length*100) + '%';
    }
    setTimeout(() => {
        if (prog) { prog.classList.remove('on'); if (bar) bar.style.width='0%'; }
        if (lbl)  lbl.classList.remove('busy');
    }, 700);
    inp.value='';
}

function addThumbnailToSharedRows(ev, isPdf, delUrl, anchorDetailId) {
    LAG_SHARED_NOS.forEach(no => {
        const row = document.getElementById('ev-row-lag-' + no); if (!row) return;
        const wrap = document.createElement('div');
        wrap.className = 'ev-item';
        wrap.id = 'ev-item-lag' + no + '-' + ev.id;
        wrap.innerHTML = isPdf
            ? `<a href="${ev.url}" target="_blank" class="ev-pdf" title="${esc(ev.name)}">📄</a>
               <div class="ev-del-btn" onclick="doDelEvShared('${ev.id}','${delUrl}','${anchorDetailId}')">×</div>`
            : `<img class="ev-thumb" src="${ev.url}" onclick="previewImg('${ev.url}','${esc(ev.name)}')" title="${esc(ev.name)}">
               <div class="ev-del-btn" onclick="doDelEvShared('${ev.id}','${delUrl}','${anchorDetailId}')">×</div>`;
        row.appendChild(wrap);
    });
}

function refreshAllSharedBadges() {
    const row1 = document.getElementById('ev-row-lag-1');
    const n    = row1 ? row1.querySelectorAll('.ev-item').length : 0;
    const html = n > 0
        ? `<span class="ev-stat-ok">${n} file</span>`
        : '<span class="ev-stat-req">Wajib</span>';
    LAG_SHARED_NOS.forEach(no => {
        const st = document.getElementById('ev-st-lag-' + no);
        if (st) st.innerHTML = html;
    });
}

function buildDelUrl(evId) {
    return '<?php echo e(isset($kpiReport) ? url("kpi-hsse/".$kpiReport->id."/evidences") : ""); ?>/' + evId;
}

/* ═══════════════════════════════════════════════════════════
   MODAL: Hapus Lampiran Shared (No.1–7)
═══════════════════════════════════════════════════════════ */
function doDelEvShared(evId, url, anchorDetailId) {
    const modalEl = document.getElementById('delEvSharedModal');
    const btn     = document.getElementById('del-ev-shared-confirm');
    if (!modalEl || !btn) return;
    const bsModal = new bootstrap.Modal(modalEl);

    const handler = async () => {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" style="width:12px;height:12px;border-width:2px;"></span> Menghapus...';
        try {
            const r = await fetch(url, {method:'DELETE', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'}});
            const d = await r.json();
            if (d.success) {
                LAG_SHARED_NOS.forEach(no =>
                    document.getElementById('ev-item-lag'+no+'-'+evId)?.remove()
                );
                refreshAllSharedBadges();
                toast('🗑 Lampiran dihapus dari semua baris No.1–7','ok');
            } else {
                toast('❌ ' + (d.message||'Gagal menghapus lampiran'),'err');
            }
        } catch { toast('❌ Koneksi error','err'); }
        finally {
            btn.disabled = false;
            btn.innerHTML = '🗑 Hapus Semua';
            bsModal.hide();
        }
    };
    btn.addEventListener('click', handler, {once:true});
    bsModal.show();
}

/* ═══════════════════════════════════════════════════════════
   MODAL: Hapus Lampiran Non-Shared
═══════════════════════════════════════════════════════════ */
function doDelEv(evId, url, uid) {
    const modalEl = document.getElementById('delEvSingleModal');
    const btn     = document.getElementById('del-ev-single-confirm');
    if (!modalEl || !btn) return;
    const bsModal = new bootstrap.Modal(modalEl);

    const handler = async () => {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" style="width:12px;height:12px;border-width:2px;"></span> Menghapus...';
        try {
            const r = await fetch(url, {method:'DELETE', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'}});
            const d = await r.json();
            if (d.success) {
                document.getElementById('ev-item-'+evId)?.remove();
                refreshEvStatus(uid, document.getElementById('ev-row-'+uid));
                toast('🗑 Lampiran dihapus','ok');
            } else {
                toast('❌ ' + (d.message||'Gagal menghapus lampiran'),'err');
            }
        } catch { toast('❌ Koneksi error','err'); }
        finally {
            btn.disabled = false;
            btn.innerHTML = '🗑 Hapus';
            bsModal.hide();
        }
    };
    btn.addEventListener('click', handler, {once:true});
    bsModal.show();
}

/* ─── Upload biasa (non-shared) ─── */
async function handleUpload(inp) {
    const files = Array.from(inp.files); if (!files.length) return;
    const uid   = inp.dataset.uid;
    const url   = inp.dataset.url;
    const lbl   = document.getElementById('lbl-' + uid);
    const rowEl = document.getElementById('ev-row-' + uid);
    const prog  = document.getElementById('ev-prog-' + uid);
    const bar   = document.getElementById('ev-bar-' + uid);
    const errEl = document.getElementById('ev-err-' + uid);

    const badSz = files.filter(f => f.size > MAX_BYTES);
    if (badSz.length) { showErr(errEl,'❌ Melebihi 2 MB: ' + badSz.map(f=>f.name).join(', ')); inp.value=''; return; }
    const badTy = files.filter(f => !ALLOWED.includes(f.type));
    if (badTy.length) { showErr(errEl,'❌ Format tidak didukung'); inp.value=''; return; }
    hideErr(errEl);

    if (lbl)  lbl.classList.add('busy');
    if (prog) prog.classList.add('on');

    let done = 0;
    for (const file of files) {
        const fd = new FormData();
        fd.append('kpi_report_detail_id', uid);
        fd.append('file', file);
        try {
            const r = await fetch(url, {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}, body:fd});
            const d = await r.json();
            if (d.success) {
                const ev     = d.evidence;
                const isPdf  = (ev.type||'').includes('pdf');
                const delUrl = buildDelUrl(ev.id);
                const wrap   = document.createElement('div');
                wrap.className = 'ev-item'; wrap.id = 'ev-item-' + ev.id;
                wrap.innerHTML = isPdf
                    ? `<a href="${ev.url}" target="_blank" class="ev-pdf" title="${esc(ev.name)}">📄</a><div class="ev-del-btn" onclick="doDelEv('${ev.id}','${delUrl}','${uid}')">×</div>`
                    : `<img class="ev-thumb" src="${ev.url}" onclick="previewImg('${ev.url}','${esc(ev.name)}')" title="${esc(ev.name)}"><div class="ev-del-btn" onclick="doDelEv('${ev.id}','${delUrl}','${uid}')">×</div>`;
                if (rowEl) rowEl.appendChild(wrap);
                refreshEvStatus(uid, rowEl);
                toast('📎 Upload berhasil','ok');
            } else showErr(errEl,'❌ ' + (d.message||'Upload gagal'));
        } catch { showErr(errEl,'❌ Koneksi error'); }
        done++;
        if (bar) bar.style.width = Math.round(done/files.length*100) + '%';
    }
    setTimeout(() => {
        if (prog) { prog.classList.remove('on'); if (bar) bar.style.width='0%'; }
        if (lbl)  lbl.classList.remove('busy');
    }, 700);
    inp.value='';
}

function refreshEvStatus(uid, rowEl) {
    const st = document.getElementById('ev-st-' + uid); if (!st) return;
    const n  = rowEl ? rowEl.querySelectorAll('.ev-item').length : 0;
    st.innerHTML = n > 0
        ? '<span class="ev-stat-ok">' + n + ' file</span>'
        : '<span class="ev-stat-req">Wajib</span>';
}

/* ═══════════════════════════════════════════════════════════
   HSSE: Update Score / Nilai
═══════════════════════════════════════════════════════════ */
<?php if(isset($canEditHsse) && $canEditHsse): ?>
async function doUpdateScore(itemId) {
    const inp = document.getElementById('nilai-' + itemId); if (!inp) return;
    const nilai = parseFloat(inp.value);
    if (isNaN(nilai) || nilai < 0 || nilai > 100) {
        inp.style.borderColor = '#ef4444';
        toast('⚠ Nilai harus antara 0–100','err');
        return;
    }
    inp.style.borderColor = '';
    inp.classList.add('saving');
    const fd = new FormData();
    fd.append('kpi_item_id', itemId);
    fd.append('nilai', nilai);
    try {
        const r = await fetch(SCORE_URL, {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}, body:fd});
        const d = await r.json();
        inp.classList.remove('saving');
        if (d.success) {
            const scEl = document.getElementById('sc-' + itemId);
            if (scEl) {
                scEl.textContent = parseFloat(d.detail.score).toFixed(2);
                const n = parseFloat(d.detail.nilai);
                scEl.className = 'td-score ' + (n>=90?'s-exc':n>=75?'s-good':n>=60?'s-fair':'s-poor');
            }
            if (d.totals) updateTotals(d.totals);
            inp.classList.add('saved');
            setTimeout(() => inp.classList.remove('saved'), 1800);
            toast('✅ Nilai tersimpan','ok');
        } else {
            inp.classList.add('err');
            setTimeout(() => inp.classList.remove('err'), 2500);
            toast('❌ ' + (d.message||'Gagal menyimpan nilai'),'err');
        }
    } catch {
        inp.classList.remove('saving'); inp.classList.add('err');
        setTimeout(() => inp.classList.remove('err'), 2500);
        toast('❌ Koneksi error','err');
    }
}
async function saveAllNilai() {
    const inputs = document.querySelectorAll('[id^="nilai-"]:not(:disabled)');
    let saved = 0, failed = 0;
    for (const inp of inputs) {
        if (inp.value !== '') {
            await doUpdateScore(inp.id.replace('nilai-',''));
            if (!inp.classList.contains('err')) saved++;
            else failed++;
        }
    }
    if (saved > 0 && failed === 0) toast('✅ ' + saved + ' nilai berhasil disimpan','ok');
    else if (failed > 0) toast('⚠ ' + saved + ' tersimpan, ' + failed + ' gagal','err');
    else toast('Tidak ada nilai baru untuk disimpan','ok');
}
<?php else: ?>
function doUpdateScore() {}
function saveAllNilai() {}
<?php endif; ?>

/* ═══════════════════════════════════════════════════════════
   INLINE DECISION — Accept / Reject
   v13: berlaku juga untuk item As Reported yang needsReview (No.6-7)
═══════════════════════════════════════════════════════════ */
async function doInlineDecision(detailId, decision, url, itemId) {
    if (!detailId) {
        toast('⚠ Isi data ∑/% terlebih dahulu agar item tersimpan','err');
        return;
    }
    const catEl = document.getElementById('ircmt-' + detailId);
    const apBtn = document.getElementById('irbtn-ap-' + detailId);
    const rjBtn = document.getElementById('irbtn-rj-' + detailId);

    const catatan = catEl?.value?.trim() || '';
    if (decision === 'rejected' && !catatan) {
        catEl?.focus();
        catEl?.classList.add('err-bdr');
        toast('⚠ Isi catatan terlebih dahulu sebelum menolak','err');
        return;
    }
    catEl?.classList.remove('err-bdr');

    if (apBtn) apBtn.disabled = true;
    if (rjBtn) rjBtn.disabled = true;

    try {
        const r = await fetch(url, {
            method:'POST',
            headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json','Accept':'application/json'},
            body: JSON.stringify({detail_id:detailId, decision, hsse_catatan:catatan}),
        });
        const d = await r.json();

        if (!r.ok || !d.success) {
            toast('❌ ' + (d.message||'Gagal menyimpan keputusan'),'err');
            return;
        }

        // Update button states
        apBtn?.classList.toggle('active', decision === 'approved');
        rjBtn?.classList.toggle('active', decision === 'rejected');

        // Update row styling
        const row = apBtn?.closest('tr') || rjBtn?.closest('tr');
        if (row) {
            row.classList.remove('r-ok','r-rej');
            row.classList.add(decision === 'approved' ? 'r-ok' : 'r-rej');

            const noCell = row.querySelector('.td-no-kpi');
            if (noCell) {
                noCell.style.borderLeft  = decision === 'approved' ? '3px solid #16a34a' : '3px solid #ef4444';
                noCell.style.color       = decision === 'approved' ? '#16a34a'            : '#ef4444';
            }

            const tdItem = row.querySelector('.td-item');
            if (tdItem) {
                tdItem.querySelectorAll('.itag-ok,.itag-rej').forEach(t => t.remove());
                tdItem.querySelector('.item-tags')?.insertAdjacentHTML('beforeend',
                    decision === 'approved'
                        ? '<span class="itag itag-ok">✓ Disetujui</span>'
                        : '<span class="itag itag-rej">✗ Ditolak</span>'
                );

                // Remark box — update atau hapus
                let rBox = tdItem.querySelector('.rej-remark-box');
                if (decision === 'rejected' && catatan) {
                    if (!rBox) {
                        rBox = document.createElement('div');
                        rBox.className = 'rej-remark-box';
                        tdItem.appendChild(rBox);
                    }
                    rBox.innerHTML = `<div class="rej-remark-label">💬 Catatan HSSE</div><div class="rej-remark-text">${esc(catatan)}</div>`;
                } else if (rBox) {
                    rBox.remove();
                }
            }
        }

        if (d.totals) updateTotals(d.totals);
        updateReviewCounts();

        if (catEl) { catEl.classList.add('saved'); setTimeout(() => catEl.classList.remove('saved'),900); }

        toast(decision==='approved' ? '✅ Item disetujui' : '✗ Item ditolak',
            decision==='approved' ? 'ok' : 'err');

        if (d.all_reviewed) {
            setTimeout(() => {
                toast(d.new_status === 'validated'
                    ? '🎉 Semua item direview. Laporan tervalidasi!'
                    : '⚠ Review selesai. Ada item ditolak — laporan dikembalikan.','ok');
            }, 1300);
        }

    } catch(err) {
        console.error('doInlineDecision:', err);
        toast('❌ Koneksi error','err');
    } finally {
        if (apBtn) apBtn.disabled = false;
        if (rjBtn) rjBtn.disabled = false;
    }
}

/* ═══════════════════════════════════════════════════════════
   SAVE CATATAN (blur)
═══════════════════════════════════════════════════════════ */
async function saveCat(ta) {
    if (!ta.dataset.detail || !ta.dataset.url) return;
    const fd = new FormData();
    fd.append('detail_id',    ta.dataset.detail);
    fd.append('hsse_catatan', ta.value);
    try {
        const r = await fetch(ta.dataset.url, {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}, body:fd});
        if (r.ok) { ta.classList.add('saved'); setTimeout(() => ta.classList.remove('saved'),900); }
    } catch { /* silent */ }
}

/* ═══════════════════════════════════════════════════════════
   UPDATE REVIEW COUNTS (sidebar)
═══════════════════════════════════════════════════════════ */
function updateReviewCounts() {
    const rows = document.querySelectorAll('.kpi-row');
    let apCnt = 0, rjCnt = 0;
    rows.forEach(r => {
        if (r.classList.contains('r-ok'))  apCnt++;
        if (r.classList.contains('r-rej')) rjCnt++;
    });
    const cntAp  = document.getElementById('cnt-ap');
    const cntRj  = document.getElementById('cnt-rj');
    const rvFill = document.getElementById('rv-prog-fill');
    const rvLbl  = document.getElementById('rv-lbl');
    const total  = <?php echo e($totalItems); ?>;
    if (cntAp)  cntAp.textContent  = apCnt;
    if (cntRj)  cntRj.textContent  = rjCnt;
    const done = apCnt + rjCnt;
    if (rvFill) rvFill.style.width = (total > 0 ? done/total*100 : 0) + '%';
    if (rvLbl)  rvLbl.textContent  = done + ' / ' + total + ' direview';
}

/* ═══════════════════════════════════════════════════════════
   UPDATE TOTALS
═══════════════════════════════════════════════════════════ */
function updateTotals(t) {
    const lag   = parseFloat(t.lagging||0).toFixed(2);
    const lead  = parseFloat(t.leading||0).toFixed(2);
    const total = parseFloat(t.total||0).toFixed(2);
    const map   = {
        't-lag':lag,'t-lead':lead,'t-total':total,'t-lag-sub':lag,
        'sb-lag':lag,'sb-lead':lead,'sb-val':parseFloat(total).toFixed(1),
    };
    Object.entries(map).forEach(([id,v]) => { const el = document.getElementById(id); if (el) el.textContent = v; });
    const pct = Math.min(parseFloat(total), 100);
    const bar = document.getElementById('sb-bar');
    if (bar) bar.style.width = pct + '%';
}

/* ═══════════════════════════════════════════════════════════
   VESSEL AJAX (edit mode)
═══════════════════════════════════════════════════════════ */
async function doSaveVessel(id, url) {
    const fd = new FormData();
    fd.append('_method','PUT');
    fd.append('vessel_name',       document.getElementById('vn-'+id)?.value  || '');
    fd.append('vessel_count',      document.getElementById('vco-'+id)?.value || '');
    fd.append('contract_number',   document.getElementById('vc-'+id)?.value  || '');
    fd.append('contract_end_date', document.getElementById('vd-'+id)?.value  || '');
    try { await fetch(url,{method:'POST',headers:{'X-CSRF-TOKEN':CSRF},body:fd}); toast('🚢 Kapal tersimpan','ok'); } catch {}
}
async function doDelVessel(id, url) {
    if (!confirm('Hapus kapal/unit ini?')) return;
    const fd = new FormData(); fd.append('_method','DELETE');
    const r  = await fetch(url, {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}, body:fd});
    const d  = await r.json();
    if (d.success) {
        document.getElementById('vrow-' + id)?.remove();
        document.querySelectorAll('#vessel-tbody tr').forEach((r,i) => {
            const no = r.querySelector('td:first-child'); if (no) no.textContent = i+1;
        });
    } else alert(d.message || 'Gagal.');
}
async function doAddVessel() {
    const name  = prompt('Nama kapal / unit:');
    if (!name?.trim()) return;
    const count = prompt('Jumlah (contoh: 2 unit):','1 unit');
    const fd = new FormData();
    fd.append('vessel_name',  name.trim());
    fd.append('vessel_count', (count||'').trim());
    try {
        const r = await fetch('<?php echo e(isset($kpiReport) ? route("kpi-hsse.vessels.store",$kpiReport) : ""); ?>',
            {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}, body:fd});
        const d = await r.json();
        if (d.success) window.location.reload(); else alert(d.message||'Gagal.');
    } catch { alert('Koneksi error.'); }
}

/* ═══════════════════════════════════════════════════════════
   UPDATE PERIOD (edit mode)
═══════════════════════════════════════════════════════════ */
async function doUpdatePeriod(val, url) {
    if (!val) return;
    const spinner = document.getElementById('period-spinner');
    const label   = document.getElementById('label-period-display');
    const select  = document.getElementById('select-period-edit');

    if (spinner) spinner.classList.remove('d-none');
    if (select) select.disabled = true;

    const fd = new FormData();
    fd.append('period', val);

    try {
        const r = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: fd
        });
        const d = await r.json();
        if (d.success) {
            if (label) label.textContent = d.label;
            toast('✅ Periode laporan berhasil diubah', 'ok');
            // Opsional: refresh jika ingin label header ikut berubah
            // setTimeout(() => window.location.reload(), 1000);
        } else {
            toast('❌ ' + (d.message || 'Gagal mengubah periode'), 'err');
        }
    } catch (e) {
        toast('❌ Koneksi error', 'err');
    } finally {
        if (spinner) spinner.classList.add('d-none');
        if (select) select.disabled = false;
    }
}

/* ═══════════════════════════════════════════════════════════
   HELPERS
═══════════════════════════════════════════════════════════ */
function previewImg(url, name) {
    document.getElementById('img-src').src   = url;
    document.getElementById('img-title').textContent = name || 'Preview';
    new bootstrap.Modal(document.getElementById('imgModal')).show();
}
function showErr(el, msg) { if (el) { el.textContent = msg; el.classList.add('on'); } }
function hideErr(el)       { if (el) { el.textContent = '';  el.classList.remove('on'); } }
function esc(s) {
    return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ═══════════════════════════════════════════════════════════
   PANDUAN TOGGLE
═══════════════════════════════════════════════════════════ */
function togglePanduan() {
    const panel = document.getElementById('panduan-panel');
    const btn   = document.getElementById('btn-panduan-toggle');
    if (!panel) return;
    const visible = panel.style.display !== 'none';
    panel.style.display = visible ? 'none' : 'block';
    if (btn) btn.style.opacity = visible ? '1' : '.65';
    if (!visible) panel.scrollIntoView({behavior:'smooth',block:'nearest'});
}

/* ═══════════════════════════════════════════════════════════
   MANDATORY REMINDER
═══════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.jml-inp:not(.jml-ar)').forEach(inp => {
        inp.addEventListener('blur', () => {
            if (inp.disabled) return;
            inp.classList.toggle('mandatory-empty', inp.value==='' || inp.value===null);
        });
        inp.addEventListener('input', () => inp.classList.remove('mandatory-empty'));
    });
    document.querySelectorAll('.nilai-inp:not(:disabled)').forEach(inp => {
        inp.addEventListener('blur', () => {
            if (inp.value==='' || inp.value===null) {
                inp.classList.add('mandatory-empty');
                showMandatoryToast();
            } else {
                inp.classList.remove('mandatory-empty');
            }
        });
        inp.addEventListener('input', () => inp.classList.remove('mandatory-empty'));
    });
});

let _mandatoryToastShown = false;
function showMandatoryToast() {
    if (_mandatoryToastShown) return;
    _mandatoryToastShown = true;
    toast('⚠ Nilai (Score) wajib diisi sebelum submit','err');
    setTimeout(() => { _mandatoryToastShown = false; }, 4000);
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/kpi-hsse/form.blade.php ENDPATH**/ ?>