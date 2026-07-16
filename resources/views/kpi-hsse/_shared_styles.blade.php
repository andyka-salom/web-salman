{{-- _shared_styles.blade.php — included via @includeIf or copy-pasted --}}
<style>
/* ── Status badges ───────────────────────────────────────────────── */
.badge-status-draft     { background:#f1f1f1;color:#6b7280;border:1px solid #d6d8db; }
.badge-status-submitted { background:#d0e9ff;color:#014c8c;border:1px solid #b8daff; }
.badge-status-validated { background:#d1f3d1;color:#0e6245;border:1px solid #c3e6cb; }
.badge-status-rejected  { background:#ffe0e0;color:#d93025;border:1px solid #f5c6cb; }
.badge-kpi-status { border-radius:20px;padding:3px 10px;font-size:.75rem;font-weight:600;display:inline-block; }
.badge-light-success  { background:#d1f3d1;color:#0e6245;border:1px solid #c3e6cb;border-radius:4px;padding:2px 7px;font-size:.76rem;font-weight:600; }
.badge-light-danger   { background:#ffe0e0;color:#d93025;border:1px solid #f5c6cb;border-radius:4px;padding:2px 7px;font-size:.76rem;font-weight:600; }
.badge-light-warning  { background:#fff4cc;color:#965a00;border:1px solid #ffeeba;border-radius:4px;padding:2px 7px;font-size:.76rem;font-weight:600; }
.badge-light-info     { background:#d0e9ff;color:#014c8c;border:1px solid #b8daff;border-radius:4px;padding:2px 7px;font-size:.76rem;font-weight:600; }
.badge-light-secondary{ background:#f1f1f1;color:#6b7280;border:1px solid #d6d8db;border-radius:4px;padding:2px 7px;font-size:.76rem;font-weight:600; }

/* ── Score pill ──────────────────────────────────────────────────── */
.score-pill { display:inline-flex;align-items:center;justify-content:center;border-radius:50%;font-weight:700;border:3px solid; }
.score-pill.excellent { border-color:#0e6245;color:#0e6245;background:#d1f3d1; }
.score-pill.good      { border-color:#014c8c;color:#014c8c;background:#d0e9ff; }
.score-pill.fair      { border-color:#965a00;color:#965a00;background:#fff4cc; }
.score-pill.poor      { border-color:#d93025;color:#d93025;background:#ffe0e0; }
.score-pill.none      { border-color:#9ca3af;color:#9ca3af;background:#f1f1f1; }

/* ── KPI Excel table core ────────────────────────────────────────── */
.kpi-excel-wrap { overflow-x:auto; }
.kpi-excel-table { width:100%;border-collapse:collapse;font-size:.84rem;min-width:960px; }
.kpi-excel-table th,.kpi-excel-table td { border:1px solid #b0b0b0;padding:0;vertical-align:middle; }
.row-title td   { background:#1f3864;color:#fff;font-weight:700;font-size:.95rem;text-align:center;padding:.65rem 1rem;letter-spacing:.5px; }
.row-company td { background:#2f5496;color:#fff;padding:.4rem 1rem;font-weight:600;font-size:.88rem; }
.row-company .td-right { text-align:right;font-size:.78rem;opacity:.9; }
.row-vessel-label td { background:#d6e4f7;font-weight:700;font-size:.74rem;text-transform:uppercase;letter-spacing:.4px;padding:.35rem .6rem;color:#1f3864;border-color:#9ab7e0; }
.row-vessel-data td  { background:#fff;padding:.35rem .5rem;border-color:#9ab7e0;font-size:.84rem; }
.td-vessel-count { text-align:center;font-weight:700;font-size:1rem;color:#1f3864;background:#eef2ff;min-width:46px; }
.row-col-header th { background:#1f3864;color:#fff;font-size:.73rem;font-weight:700;text-align:center;padding:.5rem .4rem;text-transform:uppercase;letter-spacing:.3px;border-color:#2f5496;white-space:nowrap; }
.row-section-lagging td { background:linear-gradient(90deg,#fef3c7,#fde68a);color:#78350f;font-weight:800;font-size:.85rem;padding:.5rem .75rem;border-color:#d97706; }
.row-section-leading td { background:linear-gradient(90deg,#dbeafe,#bfdbfe);color:#1e3a8a;font-weight:800;font-size:.85rem;padding:.5rem .75rem;border-color:#3b82f6; }
.section-bobot-badge { float:right;font-size:.76rem;background:rgba(0,0,0,.14);padding:1px 8px;border-radius:20px; }
.row-kpi td { background:#fff; }
.row-kpi:hover td { background:#f8faff; }
.row-kpi.row-rejected td { background:#fff5f5!important; }
.row-kpi.row-rejected .td-no { border-left:3px solid #ef4444; }
.row-kpi.row-approved td { background:#f0fdf4!important; }
.row-kpi.as-reported td { background:#fafafa; }
.td-no { text-align:center;font-weight:700;width:36px;color:#374151;padding:.4rem;background:#f9fafb; }
.td-item { padding:.45rem .6rem;min-width:185px;max-width:240px;line-height:1.4; }
.td-item .nm  { font-weight:600;color:#111827; }
.td-item .tgt { font-size:.72rem;color:#6b7280;margin-top:2px;padding-top:2px;border-top:1px dashed #e5e7eb; }
.td-guidance { min-width:150px;max-width:195px;padding:.4rem .6rem;font-size:.76rem;color:#6b7280;line-height:1.4; }
.td-val { text-align:center;padding:.4rem;font-size:.84rem;font-weight:600; }
.td-keterangan { padding:.4rem .6rem;min-width:180px;max-width:220px;font-size:.82rem;line-height:1.4;color:#374151; }
.td-bobot { text-align:center;width:58px;font-weight:600;color:#374151;background:#f3f4f6;padding:.4rem;font-size:.82rem; }
.td-score { text-align:center;width:62px;font-weight:700;background:#eff6ff;padding:.4rem;font-size:.88rem;transition:background .2s,color .2s; }
.td-score.excellent { color:#0e6245;background:#d1f3d1; }
.td-score.good      { color:#014c8c;background:#d0e9ff; }
.td-score.fair      { color:#965a00;background:#fff4cc; }
.td-score.poor      { color:#d93025;background:#ffe0e0; }
.row-kpi.as-reported .td-bobot,.row-kpi.as-reported .td-score { background:#f3f4f6;color:#9ca3af; }
.row-total td { background:#1f3864;color:#fff;font-weight:700;padding:.55rem .75rem;font-size:.88rem; }

/* ── Evidence mini thumbnails ────────────────────────────────────── */
.td-evidence { padding:4px;min-width:115px;vertical-align:top; }
.ev-mini-wrap { display:flex;flex-wrap:wrap;gap:3px;margin-bottom:3px; }
.ev-mini-wrapper { position:relative;display:inline-block; }
.ev-mini-img { width:42px;height:42px;border-radius:4px;object-fit:cover;border:1px solid #e5e7eb;cursor:pointer;transition:transform .15s; }
.ev-mini-img:hover { transform:scale(1.12);border-color:#6366f1; }
.ev-mini-del { position:absolute;top:-5px;right:-5px;width:16px;height:16px;border-radius:50%;background:#ef4444;border:2px solid #fff;color:#fff;font-size:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;z-index:2; }
.btn-upload-mini { display:inline-flex;align-items:center;gap:2px;padding:3px 7px;font-size:.7rem;border-radius:4px;background:#f0f9ff;border:1px dashed #7dd3fc;color:#0369a1;cursor:pointer;white-space:nowrap; }
.btn-upload-mini:hover { background:#e0f2fe; }

/* ── Input cells (edit) ──────────────────────────────────────────── */
.td-inp-jumlah { padding:3px 4px;width:70px; }
.td-inp-ket    { padding:3px 4px;min-width:175px; }
.td-inp-jumlah input {
    width:100%;border:1px solid #e5e7eb;border-radius:4px;
    padding:4px 5px;font-size:.84rem;font-weight:700;
    text-align:center;background:#fffbeb;outline:none;
    transition:border-color .15s,background .15s;
}
.td-inp-jumlah input:focus { border-color:#fbbf24;background:#fefce8;box-shadow:0 0 0 2px rgba(251,191,36,.2); }
.td-inp-ket textarea {
    width:100%;border:1px solid #e5e7eb;border-radius:4px;
    padding:4px 6px;font-size:.82rem;background:#fffbeb;
    outline:none;resize:none;transition:border-color .15s,background .15s;
}
.td-inp-ket textarea:focus { border-color:#fbbf24;background:#fefce8;box-shadow:0 0 0 2px rgba(251,191,36,.2); }

/* ── Nilai cell (HSSE) ───────────────────────────────────────────── */
.td-nilai-hsse { padding:3px 4px;width:72px; }
.td-nilai-hsse input {
    width:100%;border:1px solid #dbeafe;border-radius:4px;
    padding:4px 5px;font-size:.84rem;font-weight:700;
    text-align:center;background:#eff6ff;outline:none;
    transition:border-color .15s,background .15s;
}
.td-nilai-hsse input:focus { border-color:#3b82f6;background:#dbeafe;box-shadow:0 0 0 2px rgba(59,130,246,.2); }
.td-nilai-ro { text-align:center;width:65px;padding:.4rem;background:#f3f4f6;color:#6b7280;font-style:italic;font-size:.8rem; }

/* ── Catatan verifikasi ──────────────────────────────────────────── */
.td-catatan { padding:.4rem .5rem;min-width:150px; }
.review-decision { font-size:.75rem;font-weight:700;display:flex;align-items:center;gap:4px;margin-bottom:3px; }
.review-decision.approved { color:#0e6245; }
.review-decision.rejected { color:#d93025; }
.review-comment { font-size:.73rem;color:#4b5563;font-style:italic;line-height:1.4; }

/* ── Rejection notice in edit ────────────────────────────────────── */
.rejection-badge   { font-size:.73rem;font-weight:700;color:#d93025;display:flex;align-items:center;gap:3px;margin-bottom:2px; }
.rejection-comment { font-size:.72rem;color:#7f1d1d;font-style:italic;border-top:1px dashed #fca5a5;padding-top:2px; }

/* ── Vessel rows ─────────────────────────────────────────────────── */
.btn-del-vessel { width:18px;height:18px;border-radius:50%;background:#fee2e2;border:none;color:#dc2626;font-size:11px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:background .15s; }
.btn-del-vessel:hover { background:#fca5a5; }
.btn-add-vessel { display:inline-flex;align-items:center;gap:4px;padding:2px 9px;font-size:.74rem;background:#e8f4fd;border:1px dashed #3b82f6;border-radius:4px;color:#1d4ed8;cursor:pointer;white-space:nowrap; }
.btn-add-vessel:hover { background:#dbeafe; }
.row-vessel-data input[type="text"],.row-vessel-data input[type="date"] {
    width:100%;border:none;background:transparent;font-size:.84rem;padding:3px 5px;outline:none;
}
.row-vessel-data input:focus { background:#fffde7;outline:1px solid #fbbf24; }

/* ── Review panel ────────────────────────────────────────────────── */
.review-panel { border-radius:12px;border:2px solid #3b82f6;background:#fff; }
.review-panel-header { background:linear-gradient(135deg,#1e3a8a,#2563eb);color:#fff;padding:1rem 1.25rem;border-radius:10px 10px 0 0; }
.review-item-row { border-bottom:1px solid #e5e7eb;padding:.75rem 1rem; }
.review-item-row:last-child { border-bottom:none; }
.review-item-row.has-rejection { background:#fff5f5;border-left:4px solid #ef4444; }
.review-item-row.has-approval  { background:#f0fdf4;border-left:4px solid #22c55e; }
.btn-approve,.btn-reject { padding:3px 12px;font-size:.78rem;font-weight:700;border-radius:6px;border:2px solid;cursor:pointer;transition:all .15s; }
.btn-approve { border-color:#22c55e;color:#0e6245;background:#f0fdf4; }
.btn-approve:hover,.btn-approve.active { background:#22c55e;color:#fff; }
.btn-reject  { border-color:#ef4444;color:#d93025;background:#fff5f5; }
.btn-reject:hover,.btn-reject.active  { background:#ef4444;color:#fff; }
.comment-input { width:100%;border:1px solid #e5e7eb;border-radius:6px;padding:5px 8px;font-size:.8rem;resize:none;outline:none;margin-top:4px; }
.comment-input:focus { border-color:#3b82f6;box-shadow:0 0 0 2px rgba(59,130,246,.15); }
.comment-required-note { font-size:.7rem;color:#d93025;display:none;margin-top:2px; }
.comment-required-note.show { display:block; }

/* ── Timeline ────────────────────────────────────────────────────── */
.timeline { position:relative;padding-left:2rem; }
.timeline::before { content:'';position:absolute;left:7px;top:0;bottom:0;width:2px;background:#e5e7eb; }
.tl-item { position:relative;padding-bottom:1.25rem; }
.tl-item:last-child { padding-bottom:0; }
.tl-dot { position:absolute;left:-1.75rem;top:4px;width:14px;height:14px;border-radius:50%;border:3px solid #fff;box-shadow:0 0 0 2px #e5e7eb; }
.tl-dot.draft     { background:#9ca3af; }
.tl-dot.submitted { background:#d97706; }
.tl-dot.validated { background:#0e6245; }
.tl-dot.rejected  { background:#d93025; }

@media(min-width:992px) { .sticky-sidebar { position:sticky;top:1.5rem; } }
</style>
