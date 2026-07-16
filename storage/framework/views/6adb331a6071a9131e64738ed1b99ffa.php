<?php $__env->startSection('title', 'Evaluasi On Board Kru'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?php echo e(asset('plugins/src/sweetalerts2/sweetalerts2.css')); ?>">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.css" rel="stylesheet">
<style>
    .form-section-header {
        display:flex;align-items:center;gap:.75rem;
        padding-bottom:.75rem;border-bottom:2px solid #e5e7eb;margin-bottom:1.5rem;
    }
    .step-circle {
        display:flex;align-items:center;justify-content:center;
        width:32px;height:32px;flex-shrink:0;border-radius:50%;
        background:#dbeafe;color:#1d4ed8;font-weight:700;font-size:.9rem;
    }
    /* ── CREW TABS ─────────────────────────────────── */
    .crew-tabs-bar { display:flex;gap:6px;flex-wrap:wrap;align-items:center;margin-bottom:1rem; }
    .crew-tab-btn {
        display:inline-flex;align-items:center;gap:6px;
        padding:7px 16px;border-radius:8px;font-size:.84rem;font-weight:600;
        border:2px solid #e2e8f0;background:#f8fafc;color:#64748b;
        cursor:pointer;transition:all .15s ease;white-space:nowrap;
    }
    .crew-tab-btn:hover  { border-color:#93c5fd;background:#eff6ff;color:#1d4ed8; }
    .crew-tab-btn.active { border-color:#1d4ed8;background:#1d4ed8;color:#fff; }
    .crew-tab-btn.done   { border-color:#22c55e;color:#16a34a;background:#f0fdf4; }
    .crew-tab-btn.done.active { background:#16a34a;border-color:#16a34a;color:#fff; }
    .tab-status-dot { width:8px;height:8px;border-radius:50%;background:#cbd5e1;flex-shrink:0; }
    .crew-tab-btn.active .tab-status-dot { background:#bfdbfe; }
    .crew-tab-btn.done .tab-status-dot { background:#22c55e; }
    .crew-tab-btn.done.active .tab-status-dot { background:#bbf7d0; }
    .remove-crew-tab {
        width:16px;height:16px;border-radius:50%;
        display:inline-flex;align-items:center;justify-content:center;
        font-size:.72rem;cursor:pointer;transition:all .15s;
        background:rgba(0,0,0,.12);color:inherit;margin-left:2px;
    }
    .remove-crew-tab:hover { background:#fecaca!important;color:#dc2626!important; }
    .crew-tab-btn.active .remove-crew-tab { background:rgba(255,255,255,.25);color:#fff; }
    .btn-add-crew {
        display:inline-flex;align-items:center;gap:5px;padding:7px 14px;
        border-radius:8px;font-size:.84rem;font-weight:600;
        border:2px dashed #93c5fd;background:#eff6ff;color:#1d4ed8;
        cursor:pointer;transition:all .15s ease;
    }
    .btn-add-crew:hover { border-color:#1d4ed8;background:#dbeafe; }
    /* ── CREW PANEL ─────────────────────────────────── */
    .crew-panel { display:none; }
    .crew-panel.active { display:block; }
    /* ── CRITERIA CARD ──────────────────────────────── */
    .criteria-card {
        background:#fff;border:1px solid #e2e8f0;border-radius:10px;
        padding:1rem 1.25rem;margin-bottom:.75rem;transition:box-shadow .15s ease;
    }
    .criteria-card:hover { box-shadow:0 2px 10px rgba(0,0,0,.06); }
    .criteria-card.scored-kurang { border-left:4px solid #ef4444; }
    .criteria-card.scored-cukup  { border-left:4px solid #f59e0b; }
    .criteria-card.scored-baik   { border-left:4px solid #22c55e; }
    .criteria-no {
        display:inline-flex;align-items:center;justify-content:center;
        width:26px;height:26px;border-radius:50%;background:#1e3a5f;
        color:#fff;font-size:.75rem;font-weight:700;flex-shrink:0;
    }
    /* ── SCORE BUTTONS ──────────────────────────────── */
    .score-btn-group { display:flex;gap:8px;flex-wrap:wrap; }
    .score-btn-group input[type="radio"] { display:none; }
    .score-btn-group label {
        display:inline-flex;align-items:center;justify-content:center;
        min-width:80px;height:38px;border-radius:8px;border:2px solid #cbd5e1;
        cursor:pointer;font-size:.82rem;font-weight:700;color:#64748b;
        transition:all .15s ease;user-select:none;padding:0 12px;gap:5px;
    }
    .score-btn-group input[type="radio"]:checked + label { border-color:transparent;color:#fff; }
    .score-btn-group input[value="1"]:checked + label { background:#ef4444; }
    .score-btn-group input[value="2"]:checked + label { background:#f59e0b; }
    .score-btn-group input[value="3"]:checked + label { background:#22c55e; }
    .score-btn-group label:hover { border-color:#94a3b8;background:#f8fafc; }
    /* ── SIDEBAR ─────────────────────────────────────── */
    .total-score-display { font-size:2.5rem;font-weight:800;color:#1e3a5f;line-height:1; }
    .score-category-label {
        display:inline-block;padding:4px 16px;border-radius:20px;font-size:.85rem;font-weight:700;
    }
    .cat-kurang { background:#fee2e2;color:#dc2626; }
    .cat-cukup  { background:#fef3c7;color:#d97706; }
    .cat-baik   { background:#dcfce7;color:#16a34a; }
    .cat-none   { background:#f1f5f9;color:#94a3b8; }
    /* ── SAVED BADGE ─────────────────────────────────── */
    .saved-badge {
        display:none;align-items:center;gap:6px;padding:6px 14px;
        border-radius:8px;background:#dcfce7;color:#16a34a;font-size:.82rem;font-weight:700;
    }
    .saved-badge.show { display:inline-flex; }
    /* ── DIGITAL SIGNATURE ───────────────────────────── */
    .dig-sig-preview {
        border: 1.5px solid #c7d2fe;
        border-radius: 10px;
        padding: 14px 18px;
        background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 100%);
        position: relative;
        display: inline-block;
        min-width: 220px;
        max-width: 100%;
    }
    .dig-sig-preview::before {
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
        font-size: 1.1rem; font-weight: 700;
        color: #1e3a5f; letter-spacing: -0.01em;
        margin-bottom: 3px; position: relative;
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
    /* ── CREW SUMMARY ────────────────────────────────── */
    .crew-summary-item {
        display:flex;align-items:center;justify-content:space-between;
        padding:8px 12px;border-radius:8px;margin-bottom:6px;
        background:#f8fafc;border:1px solid #e2e8f0;font-size:.83rem;
    }
    .crew-summary-item .csname { font-weight:600;color:#1e293b; }
    @media (min-width:992px) { .sticky-sidebar { position:sticky;top:1.5rem; } }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">
            <div class="col-12">

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                    <div>
                        <h3 class="fw-bold mb-0">HSSE On Board Evaluation</h3>
                        <p class="text-muted mb-0 small">Evaluasi kompetensi HSSE — bisa lebih dari 1 kru sekaligus.</p>
                    </div>
                    <a href="<?php echo e(route('hsse-evaluation.index')); ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                <div class="row g-4">

                    
                    <div class="col-lg-8">
                        <div class="d-flex flex-column gap-4">

                            
                            <div class="card shadow-sm border-0 br-8">
                                <div class="card-body p-4">
                                    <div class="form-section-header">
                                        <div class="step-circle">1</div>
                                        <div>
                                            <h5 class="mb-0 fw-bold">Identitas Kapal &amp; Assessor</h5>
                                            <small class="text-muted">Diisi sekali — berlaku untuk semua kru</small>
                                        </div>
                                    </div>
                                    <div class="row g-3">

                                        
                                        <div class="col-md-6">
                                            <?php if (\Illuminate\Support\Facades\Blade::check('hasrole', 'super-admin|hsse|user')): ?>
                                                <label class="form-label fw-bold">Perusahaan <span class="text-danger">*</span></label>
                                                <select id="company_select" class="form-select" required>
                                                    <option value="">— Pilih Perusahaan —</option>
                                                    <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            <?php else: ?>
                                                <label class="form-label fw-bold">Perusahaan</label>
                                                <input type="text" class="form-control bg-light"
                                                    value="<?php echo e($userCompany->name ?? 'N/A'); ?>" readonly>
                                                <input type="hidden" id="company_id_hidden"
                                                    value="<?php echo e(auth()->user()->company_id); ?>">
                                            <?php endif; ?>
                                        </div>

                                        
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">
                                                Kapal <span class="text-danger">*</span>
                                                <span id="vessel-loading"
                                                    class="spinner-border spinner-border-sm text-primary ms-1 d-none"></span>
                                            </label>
                                            <select id="vessel_select" class="form-select" required
                                                <?php if (\Illuminate\Support\Facades\Blade::check('hasrole', 'super-admin|hsse|user')): ?> disabled <?php endif; ?>
                                                <option value="">— Pilih kapal —</option>
                                                <?php if (! \Illuminate\Support\Facades\Blade::check('role', 'super-admin|hsse|user')): ?>
                                                    <?php $__currentLoopData = $vessels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($v->id); ?>"><?php echo e($v->name); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php endif; ?>
                                            </select>
                                        </div>

                                        
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Tanggal Evaluasi <span class="text-danger">*</span></label>
                                            <input type="date" id="global_date" class="form-control"
                                                required value="<?php echo e(date('Y-m-d')); ?>">
                                        </div>

                                        
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Nama Assessor <span class="text-danger">*</span></label>
                                            <input type="text" id="global_assessor_name" class="form-control"
                                                required value="<?php echo e(auth()->user()->name); ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Jabatan Assessor</label>
                                            <input type="text" id="global_assessor_position" class="form-control"
                                                placeholder="Contoh: HSSE Officer">
                                        </div>

                                    </div>

                                    
                                    <div class="mt-4 pt-3 border-top">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="bi bi-pen-fill text-primary"></i>
                                            <span class="fw-bold small text-muted text-uppercase" style="letter-spacing:.05em;">
                                                Tanda Tangan Digital — Preview
                                            </span>
                                        </div>
                                        <div class="dig-sig-preview">
                                            <div class="dig-sig-name" id="sig-name-preview"><?php echo e(auth()->user()->name); ?></div>
                                            <div class="dig-sig-pos" id="sig-pos-preview" style="display:none;"></div>
                                            <div class="dig-sig-date" id="sig-date-preview">
                                                <i class="bi bi-calendar3 me-1"></i><?php echo e(date('d M Y')); ?>

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

                            
                            <div class="card shadow-sm border-0 br-8">
                                <div class="card-body p-4">
                                    <div class="form-section-header">
                                        <div class="step-circle">2</div>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-0 fw-bold">Penilaian Per Kru</h5>
                                            <small class="text-muted">
                                                Klik tab untuk berpindah kru •
                                                <span class="badge bg-danger">1=Kurang</span>
                                                <span class="badge bg-warning text-dark ms-1">2=Cukup</span>
                                                <span class="badge bg-success ms-1">3=Baik</span>
                                            </small>
                                        </div>
                                    </div>

                                    <div class="crew-tabs-bar" id="crew-tabs-bar">
                                        <button type="button" class="crew-tab-btn active" data-crew-idx="0">
                                            <span class="tab-status-dot"></span>
                                            <span class="tab-label">Kru 1</span>
                                            <span class="remove-crew-tab" data-crew-idx="0">×</span>
                                        </button>
                                        <button type="button" class="btn-add-crew" id="btn-add-crew">
                                            <i class="bi bi-plus-lg"></i> Tambah Kru
                                        </button>
                                    </div>

                                    <div id="crew-panels-wrap">
                                        <div class="crew-panel active" id="crew-panel-0" data-crew-idx="0">
                                            <?php echo $__env->make('features.hsse-evaluation.partials.crew-panel-static', [
                                                'criteria' => $criteria,
                                                'idx'      => 0,
                                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    
                    <div class="col-lg-4">
                        <div class="sticky-sidebar d-flex flex-column gap-4">

                            
                            <div class="card shadow-sm border-0 br-8" style="border-left:4px solid #1e3a5f !important;">
                                <div class="card-body p-4 text-center">
                                    <div class="text-muted small fw-bold mb-1 text-uppercase">Score Kru Aktif</div>
                                    <div class="total-score-display" id="sidebar-score">—</div>
                                    <div class="mt-2">
                                        <span class="score-category-label cat-none" id="sidebar-category">Belum dinilai</span>
                                    </div>
                                    <div class="mt-3 pt-3 border-top row g-2 text-center">
                                        <div class="col-4"><div class="p-2 rounded" style="background:#fee2e2;"><div class="small fw-bold text-danger">Kurang</div><div class="small text-muted">5–8</div></div></div>
                                        <div class="col-4"><div class="p-2 rounded" style="background:#fef3c7;"><div class="small fw-bold text-warning">Cukup</div><div class="small text-muted">9–11</div></div></div>
                                        <div class="col-4"><div class="p-2 rounded" style="background:#dcfce7;"><div class="small fw-bold text-success">Baik</div><div class="small text-muted">12–15</div></div></div>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="card shadow-sm border-0 br-8">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-people me-1 text-primary"></i>
                                        Ringkasan Kru
                                        <span class="badge bg-light text-muted ms-1" id="crew-count-badge">1</span>
                                    </h6>
                                    <div id="crew-summary-list">
                                        <div class="crew-summary-item" id="summary-item-0">
                                            <span class="csname">Kru 1</span>
                                            <span class="cs-score text-muted">—</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="card shadow-sm border-0 br-8">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold mb-1">Finalisasi</h6>
                                    <p class="text-muted small mb-3">Submit semua kru sekaligus atau simpan draft.</p>
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-primary fw-bold py-2" id="btn-submit-all">
                                            <i class="bi bi-send-check me-1"></i> Submit Semua Kru
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary py-2" id="btn-draft-all">
                                            <i class="bi bi-save me-1"></i> Simpan Semua sebagai Draft
                                        </button>
                                        <a href="<?php echo e(route('hsse-evaluation.index')); ?>" class="btn btn-light text-muted py-2">Batal</a>
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
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Constants ──────────────────────────────────────────── */
    const Toast = Swal.mixin({
        toast:true, position:'top-end', showConfirmButton:false,
        timer:3500, timerProgressBar:true,
    });
    const TOTAL = <?php echo e($criteria->count()); ?>;
    const CLIST = <?php echo json_encode($criteria->map(fn($c) => ['id'=>$c->id, 'order_no'=>$c->order_no, 'aspect'=>$c->aspect])) ?>;
    const STORE = '<?php echo e(route("hsse-evaluation.store")); ?>';
    const INDEX = '<?php echo e(route("hsse-evaluation.index")); ?>';
    const CSRF  = () => document.querySelector('meta[name="csrf-token"]').content;

    let crewCount = 1;
    let activeIdx = 0;
    const crewCache = {};

    /* ── Digital Signature Live Preview ─────────────────────── */
    function updateSigPreview() {
        const name    = document.getElementById('global_assessor_name')?.value?.trim() || '—';
        const pos     = document.getElementById('global_assessor_position')?.value?.trim() || '';
        const dateVal = document.getElementById('global_date')?.value;
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

    document.getElementById('global_assessor_name')?.addEventListener('input',  updateSigPreview);
    document.getElementById('global_assessor_position')?.addEventListener('input', updateSigPreview);
    document.getElementById('global_date')?.addEventListener('change', updateSigPreview);
    updateSigPreview();

    /* ── TomSelect: Company (super-admin/hsse only) ─────────── */
    let companyTS = null;
    const companyEl = document.getElementById('company_select');
    if (companyEl) {
        companyTS = new TomSelect('#company_select', {
            plugins: ['dropdown_input'],
            placeholder: '— Pilih Perusahaan —',
            onChange: onCompanyChange,
        });
    }

    /* ── TomSelect: Vessel ──────────────────────────────────── */
    const vesselTS = new TomSelect('#vessel_select', {
        plugins: ['dropdown_input'],
        placeholder: '— Pilih kapal —',
        onChange: onVesselChange,
    });

    if (!companyEl) {
        vesselTS.enable();
        const initVessel = vesselTS.getValue();
        if (initVessel) onVesselChange(initVessel);
    }

    /* ── Company → load vessels ─────────────────────────────── */
    function onCompanyChange(companyId) {
        const spinner = document.getElementById('vessel-loading');
        spinner.classList.remove('d-none');
        vesselTS.clear(); vesselTS.clearOptions(); vesselTS.disable();
        document.querySelectorAll('.crew-panel').forEach(p => {
            setCrewOptions(parseInt(p.dataset.crewIdx), []);
        });
        if (!companyId) { spinner.classList.add('d-none'); return; }
        fetch('<?php echo e(route("hsse-evaluation.vessels-by-company")); ?>?company_id=' + companyId)
            .then(r => r.json())
            .then(data => {
                data.forEach(v => vesselTS.addOption({ value: String(v.id), text: v.name }));
                vesselTS.refreshOptions(false);
                vesselTS.enable();
            })
            .catch(() => Toast.fire({ icon:'error', title:'Gagal memuat kapal.' }))
            .finally(() => spinner.classList.add('d-none'));
    }

    /* ── Vessel → load crew ─────────────────────────────────── */
    function onVesselChange(vesselId) {
        if (!vesselId) {
            document.querySelectorAll('.crew-panel').forEach(p => {
                setCrewOptions(parseInt(p.dataset.crewIdx), []);
            });
            return;
        }
        if (crewCache[vesselId]) {
            document.querySelectorAll('.crew-panel').forEach(p => {
                setCrewOptions(parseInt(p.dataset.crewIdx), crewCache[vesselId]);
            });
            return;
        }
        document.querySelectorAll('.crew-loading-spin').forEach(s => s.classList.remove('d-none'));
        fetch('<?php echo e(route("hsse-evaluation.crew-by-vessel")); ?>?vessel_id=' + vesselId)
            .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(data => {
                crewCache[vesselId] = data;
                if (data.length === 0) Toast.fire({ icon:'warning', title:'Tidak ada kru aktif di kapal ini.' });
                document.querySelectorAll('.crew-panel').forEach(p => {
                    setCrewOptions(parseInt(p.dataset.crewIdx), data);
                });
            })
            .catch(() => Toast.fire({ icon:'error', title:'Gagal memuat kru.' }))
            .finally(() => {
                document.querySelectorAll('.crew-loading-spin').forEach(s => s.classList.add('d-none'));
            });
    }

    /* ── Set crew options (native select) ───────────────────── */
    function setCrewOptions(idx, crewList) {
        const sel = document.getElementById('crew-sel-' + idx);
        if (!sel) return;
        const currentVal = sel.value;
        sel.innerHTML = '';
        const blank = document.createElement('option');
        blank.value       = '';
        blank.textContent = crewList.length ? '— Pilih kru —' : '— Pilih kapal dahulu —';
        sel.appendChild(blank);
        crewList.forEach(c => {
            const opt = document.createElement('option');
            opt.value            = String(c.id);
            opt.textContent      = c.name;
            opt.dataset.position = c.position || '';
            sel.appendChild(opt);
        });
        if (currentVal && crewList.some(c => String(c.id) === currentVal)) sel.value = currentVal;
        sel.disabled = false;
    }

    /* ── Build criteria HTML ────────────────────────────────── */
    function esc(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function buildCriteriaHTML(idx) {
        return CLIST.map(c => `
        <div class="criteria-card" id="ccard-${idx}-${c.id}">
            <div class="d-flex align-items-start gap-3 mb-3">
                <div class="criteria-no">${c.order_no}</div>
                <div class="flex-grow-1" style="font-size:.9rem;color:#1e293b;line-height:1.5;">${esc(c.aspect)}</div>
            </div>
            <div class="score-btn-group mb-2">
                ${[1,2,3].map((v,i) => {
                    const lbl = ['Kurang','Cukup','Baik'][i];
                    return `<input type="radio" id="sc${idx}_${c.id}_${v}"
                        name="scores_${idx}[${c.id}]" value="${v}"
                        class="score-input" data-cid="${c.id}" data-cidx="${idx}">
                    <label for="sc${idx}_${c.id}_${v}">
                        <span style="font-size:1rem;font-weight:800;">${v}</span> ${lbl}
                    </label>`;
                }).join('')}
            </div>
            <input type="text" class="form-control form-control-sm ket-input"
                data-cid="${c.id}" placeholder="Keterangan (opsional)...">
        </div>`).join('');
    }

    function buildPanelHTML(idx) {
        return `
        <div class="p-3 rounded mb-3" style="background:#f8fafc;border:1px solid #e2e8f0;">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold small mb-1">
                        Kru <span class="text-danger">*</span>
                        <span class="crew-loading-spin spinner-border spinner-border-sm text-primary ms-1 d-none"></span>
                    </label>
                    <div id="crew-dropdown-wrap-${idx}">
                        <select id="crew-sel-${idx}" class="form-select crew-select">
                            <option value="">— Pilih kapal dahulu —</option>
                        </select>
                    </div>
                    <input type="text" id="crew-manual-${idx}"
                        class="form-control d-none crew-manual-input"
                        placeholder="Ketik nama kru manual">
                    <input type="hidden" id="crew-name-hid-${idx}" value="">
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox"
                            id="chk-manual-${idx}" data-cidx="${idx}">
                        <label class="form-check-label small text-muted"
                            for="chk-manual-${idx}">Ketik manual</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small mb-1">Jabatan</label>
                    <input type="text" class="form-control crew-pos-field"
                        placeholder="Terisi otomatis / manual">
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold small mb-1">Catatan / Rekomendasi</label>
                    <textarea class="form-control form-control-sm crew-notes" rows="2"
                        placeholder="Catatan untuk kru ini..."></textarea>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
                <small class="text-muted fw-bold">Progress Penilaian</small>
                <small class="text-muted crew-prog-text">0 / ${TOTAL} kriteria</small>
            </div>
            <div class="bg-light rounded" style="height:6px;">
                <div class="crew-prog-bar" style="width:0%;height:6px;border-radius:99px;
                    background:linear-gradient(90deg,#1d4ed8,#22c55e);transition:width .3s ease;"></div>
            </div>
        </div>
        ${buildCriteriaHTML(idx)}
        <div class="p-3 rounded mt-2" style="background:#f8fafc;border:1px solid #e2e8f0;">
            <div class="fw-bold small text-muted mb-1">Keterangan Skor Total:</div>
            <div class="d-flex gap-3 flex-wrap">
                <span class="small"><span class="badge bg-danger">5–8</span> Kurang</span>
                <span class="small"><span class="badge bg-warning text-dark">9–11</span> Cukup</span>
                <span class="small"><span class="badge bg-success">12–15</span> Baik</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3 mt-3 pt-3 border-top flex-wrap">
            <button type="button" class="btn btn-success btn-save-one" data-cidx="${idx}">
                <i class="bi bi-person-check me-1"></i> Simpan Kru Ini
            </button>
            <button type="button" class="btn btn-outline-secondary btn-draft-one" data-cidx="${idx}">
                <i class="bi bi-save me-1"></i> Draft Kru Ini
            </button>
            <span class="saved-badge" id="saved-badge-${idx}">
                <i class="bi bi-check-circle-fill"></i> Tersimpan
            </span>
        </div>`;
    }

    /* ── Bind panel events ──────────────────────────────────── */
    function bindPanelEvents(idx) {
        const panel = document.getElementById('crew-panel-' + idx);
        if (!panel) return;

        const crewSel = document.getElementById('crew-sel-' + idx);
        if (crewSel) {
            crewSel.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                panel.querySelector('.crew-pos-field').value = opt ? (opt.dataset.position || '') : '';
                document.getElementById('crew-name-hid-' + idx).value = opt ? opt.textContent.trim() : '';
                updateTabLabel(idx, opt && opt.value ? opt.textContent.trim() : null);
                refreshSummary(idx);
            });
        }

        const chk    = document.getElementById('chk-manual-' + idx);
        const manual = document.getElementById('crew-manual-' + idx);
        const wrap   = document.getElementById('crew-dropdown-wrap-' + idx);
        const hidden = document.getElementById('crew-name-hid-' + idx);

        if (chk) {
            chk.addEventListener('change', function () {
                if (this.checked) {
                    wrap.classList.add('d-none'); manual.classList.remove('d-none');
                    manual.required = true;
                    if (crewSel) crewSel.value = '';
                    if (hidden)  hidden.value  = '';
                } else {
                    wrap.classList.remove('d-none'); manual.classList.add('d-none');
                    manual.required = false; manual.value = '';
                    if (hidden) hidden.value = '';
                }
            });
            if (manual) {
                manual.addEventListener('input', function () {
                    if (hidden) hidden.value = this.value;
                    updateTabLabel(idx, this.value || null);
                    refreshSummary(idx);
                });
            }
        }

        panel.querySelectorAll('.score-input').forEach(r => {
            r.addEventListener('change', () => onScoreChange(idx));
        });

        panel.querySelector('.btn-save-one')?.addEventListener('click',  () => saveOne(idx, 'submit'));
        panel.querySelector('.btn-draft-one')?.addEventListener('click', () => saveOne(idx, 'draft'));
    }

    /* ── Score change ───────────────────────────────────────── */
    function onScoreChange(idx) {
        const panel = document.getElementById('crew-panel-' + idx);
        if (!panel) return;
        const checked = panel.querySelectorAll('.score-input:checked');
        let total = 0;
        checked.forEach(r => total += parseInt(r.value));

        const pct = Math.round((checked.length / TOTAL) * 100);
        const bar = panel.querySelector('.crew-prog-bar');
        const txt = panel.querySelector('.crew-prog-text');
        if (bar) bar.style.width  = pct + '%';
        if (txt) txt.textContent  = checked.length + ' / ' + TOTAL + ' kriteria';

        panel.querySelectorAll('.score-input').forEach(inp => {
            if (!inp.checked) return;
            const card = document.getElementById('ccard-' + idx + '-' + inp.dataset.cid);
            if (!card) return;
            card.classList.remove('scored-kurang','scored-cukup','scored-baik');
            card.classList.add({'1':'scored-kurang','2':'scored-cukup','3':'scored-baik'}[inp.value]||'');
        });

        const tab = document.querySelector('.crew-tab-btn[data-crew-idx="' + idx + '"]');
        if (tab) checked.length >= TOTAL ? tab.classList.add('done') : tab.classList.remove('done');

        if (idx === activeIdx) updateSidebar(idx);
        refreshSummary(idx);
    }

    /* ── Sidebar score ──────────────────────────────────────── */
    function updateSidebar(idx) {
        const panel   = document.getElementById('crew-panel-' + idx);
        const scoreEl = document.getElementById('sidebar-score');
        const catEl   = document.getElementById('sidebar-category');
        if (!panel || !scoreEl || !catEl) return;
        const checked = panel.querySelectorAll('.score-input:checked');
        let total = 0;
        checked.forEach(r => total += parseInt(r.value));
        if (checked.length < TOTAL) {
            scoreEl.textContent = total > 0 ? total : '—';
            catEl.className     = 'score-category-label cat-none';
            catEl.textContent   = 'Belum lengkap';
            return;
        }
        scoreEl.textContent = total;
        if      (total <= 8)  { catEl.className = 'score-category-label cat-kurang'; catEl.textContent = 'Kurang'; }
        else if (total <= 11) { catEl.className = 'score-category-label cat-cukup';  catEl.textContent = 'Cukup'; }
        else                  { catEl.className = 'score-category-label cat-baik';   catEl.textContent = 'Baik'; }
    }

    /* ── Summary ────────────────────────────────────────────── */
    function refreshSummary(idx) {
        const item  = document.getElementById('summary-item-' + idx);
        const panel = document.getElementById('crew-panel-' + idx);
        if (!item || !panel) return;
        const tabLbl = document.querySelector('.crew-tab-btn[data-crew-idx="' + idx + '"] .tab-label');
        item.querySelector('.csname').textContent = tabLbl ? tabLbl.textContent : 'Kru ' + (idx+1);
        const checked = panel.querySelectorAll('.score-input:checked');
        let total = 0; checked.forEach(r => total += parseInt(r.value));
        const sc = item.querySelector('.cs-score');
        if (checked.length < TOTAL) {
            sc.textContent = total > 0 ? total + '*' : '—'; sc.className = 'cs-score text-muted';
        } else {
            sc.textContent = total;
            sc.className   = 'cs-score fw-bold ' + (total<=8?'text-danger':total<=11?'text-warning':'text-success');
        }
    }

    /* ── Tab label ──────────────────────────────────────────── */
    function updateTabLabel(idx, name) {
        const lbl = document.querySelector('.crew-tab-btn[data-crew-idx="' + idx + '"] .tab-label');
        if (!lbl) return;
        lbl.textContent = (name && name.trim()) ? name.trim().split(' ').slice(0,2).join(' ') : 'Kru ' + (idx+1);
        refreshSummary(idx);
    }

    /* ── Switch tab ─────────────────────────────────────────── */
    function switchTab(idx) {
        document.querySelectorAll('.crew-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.crew-panel').forEach(p => p.classList.remove('active'));
        document.querySelector('.crew-tab-btn[data-crew-idx="' + idx + '"]')?.classList.add('active');
        document.getElementById('crew-panel-' + idx)?.classList.add('active');
        activeIdx = idx;
        updateSidebar(idx);
    }

    /* ── Tab bar delegation ─────────────────────────────────── */
    document.getElementById('crew-tabs-bar').addEventListener('click', function (e) {
        const rm = e.target.closest('.remove-crew-tab');
        if (rm) { removeCrew(parseInt(rm.dataset.crewIdx)); return; }
        const tb = e.target.closest('.crew-tab-btn');
        if (tb) switchTab(parseInt(tb.dataset.crewIdx));
    });

    /* ── Add Crew ───────────────────────────────────────────── */
    document.getElementById('btn-add-crew').addEventListener('click', function () {
        const idx    = crewCount++;
        const tabBar = document.getElementById('crew-tabs-bar');
        const addBtn = document.getElementById('btn-add-crew');
        const wrap   = document.getElementById('crew-panels-wrap');

        const tab = document.createElement('button');
        tab.type = 'button'; tab.className = 'crew-tab-btn'; tab.dataset.crewIdx = idx;
        tab.innerHTML = `<span class="tab-status-dot"></span>
                         <span class="tab-label">Kru ${idx+1}</span>
                         <span class="remove-crew-tab" data-crew-idx="${idx}">×</span>`;
        tabBar.insertBefore(tab, addBtn);

        const panel = document.createElement('div');
        panel.className = 'crew-panel'; panel.id = 'crew-panel-' + idx; panel.dataset.crewIdx = idx;
        panel.innerHTML = buildPanelHTML(idx);
        wrap.appendChild(panel);

        const si = document.createElement('div');
        si.className = 'crew-summary-item'; si.id = 'summary-item-' + idx;
        si.innerHTML = `<span class="csname">Kru ${idx+1}</span><span class="cs-score text-muted">—</span>`;
        document.getElementById('crew-summary-list').appendChild(si);
        document.getElementById('crew-count-badge').textContent =
            document.querySelectorAll('.crew-panel').length;

        bindPanelEvents(idx);

        const vesselId = vesselTS.getValue();
        if (vesselId && crewCache[vesselId]) setCrewOptions(idx, crewCache[vesselId]);

        switchTab(idx);
    });

    /* ── Remove Crew ────────────────────────────────────────── */
    function removeCrew(idx) {
        if (document.querySelectorAll('.crew-tab-btn').length <= 1) {
            Toast.fire({ icon:'warning', title:'Minimal harus ada 1 kru.' }); return;
        }
        Swal.fire({
            title:'Hapus kru ini?', icon:'question',
            showCancelButton:true, confirmButtonText:'Hapus', cancelButtonText:'Batal',
            confirmButtonColor:'#e7515a',
        }).then(r => {
            if (!r.isConfirmed) return;
            document.querySelector('.crew-tab-btn[data-crew-idx="' + idx + '"]')?.remove();
            document.getElementById('crew-panel-' + idx)?.remove();
            document.getElementById('summary-item-' + idx)?.remove();
            document.getElementById('crew-count-badge').textContent =
                document.querySelectorAll('.crew-panel').length;
            const first = document.querySelector('.crew-tab-btn');
            if (first) switchTab(parseInt(first.dataset.crewIdx));
        });
    }

    /* ── Global fields ──────────────────────────────────────── */
    function getGlobal() {
        return {
            companyId   : companyTS ? companyTS.getValue()
                        : (document.getElementById('company_id_hidden')?.value || ''),
            vesselId    : vesselTS.getValue(),
            date        : document.getElementById('global_date').value,
            assessor    : document.getElementById('global_assessor_name').value,
            assessorPos : document.getElementById('global_assessor_position').value || '',
        };
    }
    function validateGlobal() {
        const g = getGlobal();
        if (!g.companyId) { Swal.fire('Perusahaan Wajib Dipilih','','warning'); return false; }
        if (!g.vesselId)  { Swal.fire('Kapal Wajib Dipilih','Pilih kapal terlebih dahulu.','warning'); return false; }
        if (!g.date)      { Swal.fire('Tanggal Wajib Diisi','','warning'); return false; }
        if (!g.assessor)  { Swal.fire('Assessor Wajib Diisi','','warning'); return false; }
        return g;
    }

    /* ── Collect one crew ───────────────────────────────────── */
    function collectCrew(idx) {
        const panel = document.getElementById('crew-panel-' + idx);
        if (!panel) return null;
        const chk    = document.getElementById('chk-manual-' + idx);
        const manual = document.getElementById('crew-manual-' + idx);
        const sel    = document.getElementById('crew-sel-' + idx);
        const hidden = document.getElementById('crew-name-hid-' + idx);
        let crewName = '', crewMemberId = '';
        if (chk && chk.checked) {
            crewName = manual?.value?.trim() || '';
        } else {
            crewMemberId = sel?.value || '';
            crewName     = hidden?.value?.trim() || '';
            if (!crewName && sel && sel.selectedIndex > 0) {
                crewName = sel.options[sel.selectedIndex].textContent.trim();
            }
        }
        const scores = {}, keterangan = {};
        panel.querySelectorAll('.score-input:checked').forEach(r => { scores[r.dataset.cid] = r.value; });
        panel.querySelectorAll('.ket-input').forEach(inp => {
            if (inp.dataset.cid) keterangan[inp.dataset.cid] = inp.value;
        });
        return {
            crewName, crewMemberId,
            position : panel.querySelector('.crew-pos-field')?.value || '',
            notes    : panel.querySelector('.crew-notes')?.value || '',
            scores, keterangan,
        };
    }

    /* ── Build FormData ─────────────────────────────────────── */
    function buildFD(g, data, action) {
        const fd = new FormData();
        fd.append('_token',            CSRF());
        fd.append('company_id',        g.companyId);
        fd.append('vessel_id',         g.vesselId);
        fd.append('evaluated_date',    g.date);
        fd.append('assessor_name',     g.assessor);
        fd.append('assessor_position', g.assessorPos);
        fd.append('crew_member_id',    data.crewMemberId);
        fd.append('crew_name',         data.crewName);
        fd.append('crew_position',     data.position);
        fd.append('notes',             data.notes);
        fd.append('action',            action);
        Object.entries(data.scores).forEach(([k,v])     => fd.append('scores['+k+']', v));
        Object.entries(data.keterangan).forEach(([k,v]) => fd.append('keterangan['+k+']', v));
        return fd;
    }

    /* ── Save ONE crew ──────────────────────────────────────── */
    function saveOne(idx, action) {
        const g = validateGlobal(); if (!g) return;
        const d = collectCrew(idx); if (!d) return;
        if (!d.crewName) {
            Swal.fire('Nama Kru Wajib Diisi','Pilih kru dari dropdown atau centang "Ketik manual".','warning'); return;
        }
        if (Object.keys(d.scores).length < TOTAL) {
            Swal.fire('Penilaian Belum Lengkap','Isi semua ' + TOTAL + ' kriteria untuk kru ini.','warning'); return;
        }
        Swal.fire({
            title:'Menyimpan...', html:'Menyimpan <strong>' + esc(d.crewName) + '</strong>',
            allowOutsideClick:false, showConfirmButton:false, didOpen:()=>Swal.showLoading(),
        });
        fetch(STORE, { method:'POST', body:buildFD(g,d,action), headers:{'X-CSRF-TOKEN':CSRF()} })
        .then(r => r.json())
        .then(res => {
            Swal.close();
            if (res.success) {
                document.getElementById('saved-badge-' + idx)?.classList.add('show');
                document.querySelector('.crew-tab-btn[data-crew-idx="' + idx + '"]')?.classList.add('done');
                Toast.fire({ icon:'success', title:esc(d.crewName) + ' berhasil disimpan!' });
            } else {
                Swal.fire('Error', res.message || 'Terjadi kesalahan.', 'error');
            }
        })
        .catch(() => { Swal.close(); Swal.fire('Error','Gagal menghubungi server.','error'); });
    }

    /* ── Save ALL crews ─────────────────────────────────────── */
    function saveAll(action) {
        const g = validateGlobal(); if (!g) return;
        const allIndices = Array.from(document.querySelectorAll('.crew-panel'))
            .map(p => parseInt(p.dataset.crewIdx));
        const skipIndices = allIndices.filter(idx =>
            document.getElementById('saved-badge-' + idx)?.classList.contains('show')
        );
        const indices = allIndices.filter(idx =>
            !document.getElementById('saved-badge-' + idx)?.classList.contains('show')
        );

        if (indices.length === 0) {
            Swal.fire({
                icon:'info', title:'Semua Kru Sudah Tersimpan',
                html:'Semua <strong>' + skipIndices.length + ' kru</strong> sudah disimpan sebelumnya.',
                confirmButtonText:'OK',
            });
            return;
        }

        const errors = [];
        indices.forEach(idx => {
            const d = collectCrew(idx);
            if (!d || !d.crewName) errors.push('Kru '+(idx+1)+': nama kru belum diisi.');
            else if (Object.keys(d.scores).length < TOTAL)
                errors.push('Kru '+(idx+1)+' ('+esc(d.crewName)+'): penilaian belum lengkap.');
        });
        if (errors.length) {
            Swal.fire({ title:'Ada Yang Belum Lengkap', html:errors.join('<br>'), icon:'warning' }); return;
        }

        const doSave = () => {
            let saved = 0, failed = 0;
            Swal.fire({
                title:'Menyimpan ' + indices.length + ' kru...', html:'Memproses...',
                allowOutsideClick:false, showConfirmButton:false, didOpen:()=>Swal.showLoading(),
            });
            function next(i) {
                if (i >= indices.length) {
                    Swal.close();
                    if (failed === 0) {
                        Toast.fire({ icon:'success', title:saved + ' kru berhasil disimpan!' });
                        setTimeout(() => window.location.href = INDEX, 1200);
                    } else {
                        Swal.fire('Sebagian Gagal', saved+' berhasil, '+failed+' gagal.', 'warning');
                    }
                    return;
                }
                const idx = indices[i];
                const d   = collectCrew(idx);
                Swal.update({ html:'Menyimpan kru '+(i+1)+' dari '+indices.length+'...' });
                fetch(STORE, { method:'POST', body:buildFD(g,d,action), headers:{'X-CSRF-TOKEN':CSRF()} })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        saved++;
                        document.getElementById('saved-badge-' + idx)?.classList.add('show');
                        document.querySelector('.crew-tab-btn[data-crew-idx="' + idx + '"]')?.classList.add('done');
                    } else { failed++; }
                    next(i+1);
                })
                .catch(() => { failed++; next(i+1); });
            }
            next(0);
        };

        if (skipIndices.length > 0) {
            Swal.fire({
                icon:'question', title:'Konfirmasi Simpan',
                html:'<strong>' + skipIndices.length + ' kru</strong> sudah tersimpan dan akan dilewati.<br>' +
                     'Hanya <strong>' + indices.length + ' kru</strong> yang akan disimpan.',
                showCancelButton:true,
                confirmButtonText:'Ya, Simpan ' + indices.length + ' Kru',
                cancelButtonText:'Batal',
            }).then(r => { if (r.isConfirmed) doSave(); });
        } else {
            doSave();
        }
    }

    /* ── Bindings ───────────────────────────────────────────── */
    document.getElementById('btn-submit-all').addEventListener('click', () => saveAll('submit'));
    document.getElementById('btn-draft-all').addEventListener('click',  () => saveAll('draft'));

    bindPanelEvents(0);
    updateSidebar(0);

    if (!companyEl) {
        const initVessel = vesselTS.getValue();
        if (initVessel) onVesselChange(initVessel);
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/features/hsse-evaluation/create.blade.php ENDPATH**/ ?>