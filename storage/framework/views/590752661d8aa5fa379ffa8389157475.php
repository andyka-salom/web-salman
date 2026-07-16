


<div class="p-3 rounded mb-3" style="background:#f8fafc;border:1px solid #e2e8f0;">
    <div class="row g-3">

        
        <div class="col-md-6">
            <label class="form-label fw-bold small mb-1">
                Kru <span class="text-danger">*</span>
                <span class="crew-loading-spin spinner-border spinner-border-sm text-primary ms-1 d-none"></span>
            </label>
            
            <div id="crew-dropdown-wrap-<?php echo e($idx); ?>">
                <select id="crew-sel-<?php echo e($idx); ?>" class="form-select">
                    <option value="">— Pilih kapal dahulu —</option>
                </select>
            </div>
            <input type="text"
                id="crew-manual-<?php echo e($idx); ?>"
                class="form-control d-none crew-manual-input"
                placeholder="Ketik nama kru manual">
            <input type="hidden" id="crew-name-hid-<?php echo e($idx); ?>" value="">
            <div class="form-check mt-1">
                <input class="form-check-input" type="checkbox"
                    id="chk-manual-<?php echo e($idx); ?>" data-cidx="<?php echo e($idx); ?>">
                <label class="form-check-label small text-muted"
                    for="chk-manual-<?php echo e($idx); ?>">Ketik manual</label>
            </div>
        </div>

        
        <div class="col-md-6">
            <label class="form-label fw-bold small mb-1">Jabatan</label>
            <input type="text" class="form-control crew-pos-field"
                placeholder="Terisi otomatis / ketik manual">
        </div>

        
        <div class="col-md-6">
            <label class="form-label fw-bold small mb-1">Catatan / Rekomendasi</label>
            <textarea class="form-control form-control-sm crew-notes" rows="2"
                placeholder="Catatan untuk kru ini..."></textarea>
        </div>
    </div>
</div>


<div class="mb-3">
    <div class="d-flex justify-content-between mb-1">
        <small class="text-muted fw-bold">Progress Penilaian</small>
        <small class="text-muted crew-prog-text">0 / <?php echo e($criteria->count()); ?> kriteria</small>
    </div>
    <div class="bg-light rounded" style="height:6px;">
        <div class="crew-prog-bar"
            style="width:0%;height:6px;border-radius:99px;
                   background:linear-gradient(90deg,#1d4ed8,#22c55e);
                   transition:width .3s ease;"></div>
    </div>
</div>


<?php $__currentLoopData = $criteria; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="criteria-card" id="ccard-<?php echo e($idx); ?>-<?php echo e($c->id); ?>">
    <div class="d-flex align-items-start gap-3 mb-3">
        <div class="criteria-no"><?php echo e($c->order_no); ?></div>
        <div class="flex-grow-1" style="font-size:.9rem;color:#1e293b;line-height:1.5;">
            <?php echo e($c->aspect); ?>

        </div>
    </div>
    <div class="score-btn-group mb-2">
        <?php $__currentLoopData = [1 => 'Kurang', 2 => 'Cukup', 3 => 'Baik']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <input type="radio"
            id="sc<?php echo e($idx); ?>_<?php echo e($c->id); ?>_<?php echo e($val); ?>"
            name="scores_<?php echo e($idx); ?>[<?php echo e($c->id); ?>]"
            value="<?php echo e($val); ?>"
            class="score-input"
            data-cid="<?php echo e($c->id); ?>"
            data-cidx="<?php echo e($idx); ?>">
        <label for="sc<?php echo e($idx); ?>_<?php echo e($c->id); ?>_<?php echo e($val); ?>">
            <span style="font-size:1rem;font-weight:800;"><?php echo e($val); ?></span> <?php echo e($label); ?>

        </label>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <input type="text"
        class="form-control form-control-sm ket-input"
        data-cid="<?php echo e($c->id); ?>"
        placeholder="Keterangan (opsional)...">
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


<div class="p-3 rounded mt-2" style="background:#f8fafc;border:1px solid #e2e8f0;">
    <div class="fw-bold small text-muted mb-1">Keterangan Skor Total:</div>
    <div class="d-flex gap-3 flex-wrap">
        <span class="small"><span class="badge bg-danger">5–8</span> Kurang</span>
        <span class="small"><span class="badge bg-warning text-dark">9–11</span> Cukup</span>
        <span class="small"><span class="badge bg-success">12–15</span> Baik</span>
    </div>
</div>


<div class="d-flex align-items-center gap-3 mt-3 pt-3 border-top flex-wrap">
    <button type="button" class="btn btn-success btn-save-one" data-cidx="<?php echo e($idx); ?>">
        <i class="bi bi-person-check me-1"></i> Simpan Kru Ini
    </button>
    <button type="button" class="btn btn-outline-secondary btn-draft-one" data-cidx="<?php echo e($idx); ?>">
        <i class="bi bi-save me-1"></i> Draft Kru Ini
    </button>
    <span class="saved-badge" id="saved-badge-<?php echo e($idx); ?>">
        <i class="bi bi-check-circle-fill"></i> Tersimpan
    </span>
</div>
<?php /**PATH /home/kaptensa/salman/resources/views/features/hsse-evaluation/partials/crew-panel-static.blade.php ENDPATH**/ ?>