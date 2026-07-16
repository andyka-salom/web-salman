

<?php $__env->startSection('title', 'Input Daily Checkup - Coordinator'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    
    <div class="mb-4">
        <a href="<?php echo e(route('daily-checkup.index')); ?>" class="btn btn-outline-secondary mb-3">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
        <h2 class="mb-1">Daily Health Checkup</h2>
        <p class="text-muted mb-2"><?php echo e($vessel->name); ?> - <?php echo e($today->translatedFormat('l, d F Y')); ?></p>
        <div class="alert alert-info border">
            <i class="fas fa-info-circle me-2"></i>Info: Data yang Anda input akan otomatis terverifikasi sebagai Koordinator.
        </div>
    </div>

    
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-users text-secondary fs-2"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Total Crew</div>
                            <div class="fs-3 fw-bold"><?php echo e($stats['total_crew']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-secondary fs-2"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Completed</div>
                            <div class="fs-3 fw-bold"><?php echo e($stats['completed']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock text-secondary fs-2"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Pending</div>
                            <div class="fs-3 fw-bold"><?php echo e($stats['pending']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <?php
        $progress = $stats['total_crew'] > 0 ? round(($stats['completed'] / $stats['total_crew']) * 100) : 0;
    ?>
    <div class="card border mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Daily Progress</h5>
                <span class="fs-4 fw-bold"><?php echo e($progress); ?>%</span>
            </div>
            <div class="progress" style="height: 12px;">
                <div class="progress-bar bg-dark"
                     role="progressbar"
                     style="width: <?php echo e($progress); ?>%"
                     aria-valuenow="<?php echo e($progress); ?>"
                     aria-valuemin="0"
                     aria-valuemax="100">
                </div>
            </div>
        </div>
    </div>

    
    <div class="row g-3">
        <?php $__currentLoopData = $crewMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $crew): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $isChecked = in_array($crew->id, $existingCheckups);
            ?>

            <div class="col-md-6">
                <div class="card border h-100 <?php echo e($isChecked ? 'bg-light' : ''); ?>">
                    <div class="card-body">
                        
                        <div class="d-flex align-items-start mb-3">
                            <div class="flex-shrink-0">
                                <div class="rounded-circle bg-light p-3">
                                    <i class="fas fa-user text-secondary fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1"><?php echo e($crew->name); ?></h5>
                                <p class="text-muted small mb-0"><?php echo e($crew->position); ?></p>
                            </div>
                            <?php if($isChecked): ?>
                                <span class="badge bg-dark">
                                    <i class="fas fa-check me-1"></i>Verified
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-clock me-1"></i>Pending
                                </span>
                            <?php endif; ?>
                        </div>

                        
                        <div class="row g-2 mb-3 small">
                            <div class="col-6">
                                <div class="text-muted">NIK</div>
                                <div class="fw-semibold"><?php echo e($crew->nik); ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted">Blood Type</div>
                                <div class="fw-semibold"><?php echo e($crew->blood_type ?? '-'); ?></div>
                            </div>
                        </div>

                        
                        <div class="row g-2 mb-3 small">
                            <div class="col-6">
                                <div class="text-muted">MCU Valid Until</div>
                                <?php if($crew->mcu_valid_until): ?>
                                    <div class="fw-semibold"><?php echo e($crew->mcu_valid_until->format('d/m/Y')); ?></div>
                                    <?php if($crew->mcu_status === 'expired'): ?>
                                        <span class="badge bg-danger mt-1">
                                            <i class="fas fa-times-circle me-1"></i>Expired
                                        </span>
                                    <?php elseif($crew->mcu_status === 'expiring_soon'): ?>
                                        <span class="badge bg-warning text-dark mt-1">
                                            <i class="fas fa-exclamation-circle me-1"></i>Exp. <?php echo e($crew->mcu_days_left); ?>d
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success mt-1">
                                            <i class="fas fa-check-circle me-1"></i>Valid
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="text-muted">-</div>
                                <?php endif; ?>
                            </div>
                            <div class="col-6">
                                <div class="text-muted">Health Category</div>
                                <?php if($crew->health_category): ?>
                                    <?php
                                        $cat = $crew->health_category;
                                        $catBadgeClass = match(true) {
                                            in_array($cat, ['P1', 'P2']) => 'bg-success text-white',
                                            in_array($cat, ['P3', 'P4']) => 'bg-warning text-dark',
                                            $cat === 'P5'                => 'bg-danger text-white',
                                            $cat === 'P6'                => 'bg-secondary text-white',
                                            default                      => 'bg-light text-dark border',
                                        };
                                        $catDescription = Str::after($crew->health_category_label, ' - ');
                                    ?>
                                    <div>
                                        <span class="badge <?php echo e($catBadgeClass); ?>" title="<?php echo e($crew->health_category_label); ?>">
                                            <?php echo e($cat); ?>

                                        </span>
                                    </div>
                                    <div class="text-muted mt-1" style="font-size: 0.7rem; line-height: 1.3;">
                                        <?php echo e($catDescription); ?>

                                    </div>
                                <?php else: ?>
                                    <div class="text-muted">-</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if($isChecked): ?>
                            <button type="button" class="btn btn-secondary btn-sm w-100" disabled>
                                <i class="fas fa-check-circle me-2"></i>Already Checked & Verified
                            </button>
                        <?php else: ?>
                            <button type="button"
                                    class="btn btn-dark btn-sm w-100 btn-input-checkup"
                                    data-crew-id="<?php echo e($crew->id); ?>"
                                    data-crew-name="<?php echo e($crew->name); ?>">
                                <i class="fas fa-plus-circle me-2"></i>Input Checkup
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <?php if($stats['pending'] > 0): ?>
    <div class="mt-4">
        <button type="button" class="btn btn-lg btn-dark w-100" id="btnBulkCheckup">
            <i class="fas fa-clipboard-list me-2"></i>
            Bulk Input for All Pending (<?php echo e($stats['pending']); ?> crew)
        </button>
    </div>
    <?php endif; ?>
</div>


<div class="modal fade" id="singleCheckupModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Daily Checkup - <span id="crewNameSingle"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSingleCheckup" method="POST" action="<?php echo e(route('daily-checkup.coordinator.store')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="vessel_id" value="<?php echo e($vessel->id); ?>">
                <input type="hidden" name="check_date" value="<?php echo e($today->format('Y-m-d')); ?>">
                <input type="hidden" name="crew_member_id" id="crewMemberId">

                <div class="modal-body">
                    <div class="alert alert-info border">
                        <i class="fas fa-info-circle me-2"></i>Data akan otomatis terverifikasi
                    </div>

                    
                    <h6 class="mb-3 border-bottom pb-2">Vital Signs</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Suhu Tubuh (°C)</label>
                            <input type="number" step="0.1" class="form-control" name="temperature" placeholder="36.5" min="34" max="43">
                            <div class="form-text">Normal: ≤ 37.5°C</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Frekuensi Nadi (bpm) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="pulse_rate" placeholder="70" required min="30" max="200">
                            <div class="form-text">Normal: 60-120 bpm</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tekanan Darah <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="blood_pressure" placeholder="120/80" required pattern="\d{2,3}/\d{2,3}">
                            <div class="form-text">Normal: 90-140 / 60-90 mmHg</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Frekuensi Nafas (x/min)</label>
                            <input type="number" class="form-control" name="respiratory_rate" placeholder="18" min="10" max="60">
                            <div class="form-text">Normal: 16-24 x/min</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gula Darah (mg/dL)</label>
                            <input type="number" class="form-control" name="blood_sugar_level" placeholder="100" min="30" max="500">
                            <div class="form-text">Normal: 70-200 mg/dL</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Saturasi Oksigen (%)</label>
                            <input type="number" class="form-control" name="oxygen_saturation" placeholder="98" min="70" max="100">
                            <div class="form-text">Normal: ≥ 95%</div>
                        </div>
                    </div>

                    
                    <h6 class="mb-3 border-bottom pb-2">Health Status</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label">Keluhan Kesehatan <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="illness_complaints" rows="3" placeholder="Tidak ada keluhan / Sebutkan keluhan jika ada" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Obat-obatan yang Dikonsumsi <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="medications_consumed" rows="3" placeholder="Tidak ada / Sebutkan obat yang dikonsumsi" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tingkat Kelelahan</label>
                            <input type="text" class="form-control" name="fatigue_level" placeholder="Contoh: Ringan, Sedang, Berat">
                        </div>
                    </div>

                    
                    <h6 class="mb-3 border-bottom pb-2">Medical Tests</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">NAPZA Test</label>
                            <select class="form-select" name="napza_test_result">
                                <option value="not_tested">Tidak Dites</option>
                                <option value="negative">Negatif</option>
                                <option value="non-negative">Non-Negatif</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Romberg Test</label>
                            <select class="form-select" name="romberg_test_result">
                                <option value="not_tested">Tidak Dites</option>
                                <option value="negative">Negatif</option>
                                <option value="positive">Positif</option>
                            </select>
                        </div>
                    </div>

                    
                    <div class="mb-3">
                        <label class="form-label">Catatan Tambahan</label>
                        <textarea class="form-control" name="remarks" rows="2" placeholder="Catatan tambahan jika diperlukan..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-dark">
                        <i class="fas fa-save me-2"></i>Simpan & Verifikasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="bulkCheckupModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white border-0">
                <div>
                    <h5 class="modal-title mb-1">
                        <i class="fas fa-clipboard-list me-2"></i>Bulk Daily Checkup
                    </h5>
                    <small class="opacity-75"><?php echo e($vessel->name); ?> - <?php echo e($today->translatedFormat('d F Y')); ?></small>
                </div>
                <button type="button" class="btn-close btn-close-white" id="bulkModalCloseBtn"></button>
            </div>
            <form method="POST" action="<?php echo e(route('daily-checkup.coordinator.store-bulk')); ?>" id="formBulkCheckup">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="vessel_id" value="<?php echo e($vessel->id); ?>">
                <input type="hidden" name="check_date" value="<?php echo e($today->format('Y-m-d')); ?>">

                <div class="modal-body p-3 p-md-4 bg-light">
                    <div class="alert alert-info border-0 shadow-sm mb-3 mb-md-4">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-info-circle me-3 mt-1"></i>
                            <div>
                                <strong>Informasi:</strong>
                                <ul class="mb-0 mt-2 small">
                                    <li>Data akan otomatis terverifikasi sebagai Koordinator</li>
                                    <li>Field yang ditandai dengan <span class="text-danger fw-bold">*</span> wajib diisi</li>
                                    <li>Kolom <strong>MCU</strong> dan <strong>Cat.</strong> hanya tampilan — tidak dapat diubah di sini</li>
                                    <li><strong>Geser tabel ke kanan/kiri</strong> untuk melihat semua kolom pada perangkat kecil</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="bulk-table-wrapper">
                        <table class="table table-hover table-bordered bg-white mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-nowrap sticky-first-col" style="min-width: 200px;">
                                        <i class="fas fa-user me-2"></i>Crew Member
                                    </th>
                                    
                                    <th class="text-center text-nowrap" style="min-width: 110px;">
                                        <div>MCU</div>
                                        <small class="fw-normal opacity-75">Status</small>
                                    </th>
                                    <th class="text-center text-nowrap" style="min-width: 90px;">
                                        <div>Cat.</div>
                                        <small class="fw-normal opacity-75">Health</small>
                                    </th>
                                    
                                    <th class="text-center text-nowrap" style="min-width: 120px;">
                                        <div>Suhu Tubuh</div>
                                        <small class="fw-normal opacity-75">(°C)</small>
                                    </th>
                                    <th class="text-center text-nowrap" style="min-width: 140px;">
                                        <div>Frekuensi Nadi <span class="text-danger">*</span></div>
                                        <small class="fw-normal opacity-75">(bpm)</small>
                                    </th>
                                    <th class="text-center text-nowrap" style="min-width: 140px;">
                                        <div>Tekanan Darah <span class="text-danger">*</span></div>
                                        <small class="fw-normal opacity-75">(mmHg)</small>
                                    </th>
                                    <th class="text-center text-nowrap" style="min-width: 140px;">
                                        <div>Frekuensi Nafas</div>
                                        <small class="fw-normal opacity-75">(x/min)</small>
                                    </th>
                                    <th class="text-center text-nowrap" style="min-width: 130px;">
                                        <div>Gula Darah</div>
                                        <small class="fw-normal opacity-75">(mg/dL)</small>
                                    </th>
                                    <th class="text-center text-nowrap" style="min-width: 140px;">
                                        <div>Saturasi Oksigen</div>
                                        <small class="fw-normal opacity-75">(%)</small>
                                    </th>
                                    <th class="text-center text-nowrap" style="min-width: 220px;">
                                        <div>Keluhan Kesehatan <span class="text-danger">*</span></div>
                                    </th>
                                    <th class="text-center text-nowrap" style="min-width: 220px;">
                                        <div>Obat-obatan <span class="text-danger">*</span></div>
                                    </th>
                                    <th class="text-center text-nowrap" style="min-width: 160px;">
                                        <div>Tingkat Kelelahan</div>
                                    </th>
                                    <th class="text-center text-nowrap" style="min-width: 140px;">
                                        <div>NAPZA Test</div>
                                    </th>
                                    <th class="text-center text-nowrap" style="min-width: 140px;">
                                        <div>Romberg Test</div>
                                    </th>
                                    <th class="text-center text-nowrap" style="min-width: 220px;">
                                        <div>Catatan Tambahan</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="bulkCheckupTable">
                                
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top shadow-sm">
                    <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
                        <div class="text-muted">
                            <i class="fas fa-users me-2"></i>
                            <strong class="text-dark" id="crewCount">0</strong> crew pending
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-secondary" id="bulkModalCancelBtn">
                                <i class="fas fa-times me-2"></i>Batal
                            </button>
                            <button type="submit" class="btn btn-dark" id="bulkCheckupSubmitBtn">
                                <i class="fas fa-save me-2"></i>Simpan & Verifikasi Semua
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Data from Laravel — accessor appends (mcu_status, mcu_days_left, mcu_valid_until,
// health_category, health_category_label) are included automatically via $appends.
const pendingCrew = <?php echo json_encode($crewMembers->filter(fn($c) => !in_array($c->id, $existingCheckups))->values(), 512) ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Single Checkup
    document.querySelectorAll('.btn-input-checkup').forEach(btn => {
        btn.addEventListener('click', function() {
            const crewId   = this.dataset.crewId;
            const crewName = this.dataset.crewName;

            document.getElementById('crewMemberId').value      = crewId;
            document.getElementById('crewNameSingle').textContent = crewName;
            document.getElementById('formSingleCheckup').reset();

            new bootstrap.Modal(document.getElementById('singleCheckupModal')).show();
        });
    });

    // Bulk Checkup - Open Modal and Generate Rows
    document.getElementById('btnBulkCheckup')?.addEventListener('click', function() {
        openBulkCheckupForm();
    });

    // Handle bulk form cancel with confirmation
    document.getElementById('bulkModalCancelBtn').addEventListener('click', function() {
        const form      = document.getElementById('formBulkCheckup');
        const allInputs = form.querySelectorAll('input:not([type="hidden"]), textarea, select');

        const hasInput = Array.from(allInputs).some(el => {
            if (el.tagName === 'SELECT') return el.value !== 'not_tested' && el.value !== '';
            return el.value && el.value.trim() !== '';
        });

        if (hasInput) {
            if (!confirm('Apakah Anda yakin ingin menutup modal? Data yang belum disimpan akan hilang.')) {
                return false;
            }
        }

        const modalEl = document.getElementById('bulkCheckupModal');
        const modal   = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    });

    // Close button handler
    document.getElementById('bulkModalCloseBtn').addEventListener('click', function() {
        document.getElementById('bulkModalCancelBtn').click();
    });

    // Blood pressure pattern validation
    document.addEventListener('input', function(e) {
        if (e.target.name && e.target.name.includes('blood_pressure')) {
            const pattern = /^\d{2,3}\/\d{2,3}$/;
            if (e.target.value && !pattern.test(e.target.value)) {
                e.target.setCustomValidity('Format harus xxx/xxx (contoh: 120/80)');
            } else {
                e.target.setCustomValidity('');
            }
        }
    });
});

// ─── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Returns HTML badge string for MCU status (read-only display in bulk table).
 */
function getMcuBadgeHTML(crew) {
    const status   = crew.mcu_status   || 'none';
    const daysLeft = crew.mcu_days_left;
    const validUntil = crew.mcu_valid_until
        ? new Date(crew.mcu_valid_until).toLocaleDateString('id-ID', {
              day: '2-digit', month: '2-digit', year: 'numeric'
          })
        : null;

    const dateNote = validUntil
        ? `<div style="font-size:0.65rem;color:#6c757d;margin-top:2px;">${validUntil}</div>`
        : '';

    if (status === 'expired') {
        return `<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Expired</span>${dateNote}`;
    }
    if (status === 'expiring_soon') {
        return `<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-circle me-1"></i>${daysLeft}d left</span>${dateNote}`;
    }
    if (status === 'valid') {
        return `<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Valid</span>${dateNote}`;
    }
    return `<span class="badge bg-light text-muted border" style="font-size:0.7rem;">N/A</span>`;
}

/**
 * Returns HTML badge string for health category (read-only display in bulk table).
 */
function getHealthCategoryBadgeHTML(crew) {
    if (!crew.health_category) return '<span class="text-muted small">-</span>';

    const cat   = crew.health_category;
    const label = crew.health_category_label || cat;
    const desc  = label.includes(' - ') ? label.split(' - ')[1] : '';

    let colorClass = 'bg-secondary text-white';
    if      (['P1', 'P2'].includes(cat)) colorClass = 'bg-success text-white';
    else if (['P3', 'P4'].includes(cat)) colorClass = 'bg-warning text-dark';
    else if (cat === 'P5')               colorClass = 'bg-danger text-white';
    else if (cat === 'P7')               colorClass = 'bg-light text-dark border';

    const descNote = desc
        ? `<div style="font-size:0.65rem;color:#6c757d;margin-top:2px;">${desc}</div>`
        : '';

    return `<span class="badge ${colorClass}" title="${label}">${cat}</span>${descNote}`;
}

// ─── Bulk Form ────────────────────────────────────────────────────────────────

function openBulkCheckupForm() {
    const tableBody = document.getElementById('bulkCheckupTable');
    tableBody.innerHTML = '';

    document.getElementById('crewCount').textContent = pendingCrew.length;

    if (pendingCrew.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="16" class="text-center py-5">
                    <i class="fas fa-check-circle fa-4x text-success mb-3 d-block"></i>
                    <h5 class="mb-0">Semua crew sudah melakukan checkup hari ini</h5>
                </td>
            </tr>
        `;
    } else {
        pendingCrew.forEach((crew, index) => {
            const row = `
                <tr class="align-middle">
                    
                    <td class="sticky-first-col">
                        <input type="hidden" name="crew_members[${index}]" value="${crew.id}">
                        <div class="d-flex align-items-center">
                            <div class="bg-white rounded-circle p-2 me-2 flex-shrink-0"
                                 style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-user text-secondary"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-bold small text-truncate">${crew.name}</div>
                                <div class="small text-muted text-truncate">${crew.position}</div>
                                <div class="small text-muted">NIK: ${crew.nik}</div>
                            </div>
                        </div>
                    </td>

                    
                    <td class="text-center readonly-col">
                        ${getMcuBadgeHTML(crew)}
                    </td>

                    
                    <td class="text-center readonly-col">
                        ${getHealthCategoryBadgeHTML(crew)}
                    </td>

                    
                    <td>
                        <input type="number"
                               step="0.1"
                               class="form-control form-control-sm"
                               name="temperature[${index}]"
                               placeholder="36.5"
                               min="34"
                               max="43">
                        <small class="text-muted d-block mt-1">Normal: ≤37.5</small>
                    </td>
                    <td>
                        <input type="number"
                               class="form-control form-control-sm"
                               name="pulse_rate[${index}]"
                               placeholder="70"
                               min="30"
                               max="200"
                               required>
                        <small class="text-muted d-block mt-1">Normal: 60-120</small>
                    </td>
                    <td>
                        <input type="text"
                               class="form-control form-control-sm"
                               name="blood_pressure[${index}]"
                               placeholder="120/80"
                               pattern="\\d{2,3}/\\d{2,3}"
                               required>
                        <small class="text-muted d-block mt-1">Normal: 90-140/60-90</small>
                    </td>
                    <td>
                        <input type="number"
                               class="form-control form-control-sm"
                               name="respiratory_rate[${index}]"
                               placeholder="18"
                               min="10"
                               max="60">
                        <small class="text-muted d-block mt-1">Normal: 16-24</small>
                    </td>
                    <td>
                        <input type="number"
                               class="form-control form-control-sm"
                               name="blood_sugar_level[${index}]"
                               placeholder="100"
                               min="30"
                               max="500">
                        <small class="text-muted d-block mt-1">Normal: 70-200</small>
                    </td>
                    <td>
                        <input type="number"
                               class="form-control form-control-sm"
                               name="oxygen_saturation[${index}]"
                               placeholder="98"
                               min="70"
                               max="100">
                        <small class="text-muted d-block mt-1">Normal: ≥95</small>
                    </td>

                    
                    <td>
                        <textarea name="illness_complaints[${index}]"
                                  class="form-control form-control-sm"
                                  rows="2"
                                  required
                                  placeholder="Tidak ada keluhan"></textarea>
                    </td>
                    <td>
                        <textarea name="medications_consumed[${index}]"
                                  class="form-control form-control-sm"
                                  rows="2"
                                  required
                                  placeholder="Tidak ada"></textarea>
                    </td>
                    <td>
                        <input type="text"
                               name="fatigue_level[${index}]"
                               class="form-control form-control-sm"
                               placeholder="Ringan/Sedang/Berat">
                    </td>

                    
                    <td>
                        <select name="napza_test_result[${index}]"
                                class="form-select form-select-sm">
                            <option value="not_tested">Tidak Dites</option>
                            <option value="negative">Negatif</option>
                            <option value="non-negative">Non-Negatif</option>
                        </select>
                    </td>
                    <td>
                        <select name="romberg_test_result[${index}]"
                                class="form-select form-select-sm">
                            <option value="not_tested">Tidak Dites</option>
                            <option value="negative">Negatif</option>
                            <option value="positive">Positif</option>
                        </select>
                    </td>
                    <td>
                        <textarea name="remarks[${index}]"
                                  class="form-control form-control-sm"
                                  rows="2"
                                  placeholder="Catatan tambahan..."></textarea>
                    </td>
                </tr>
            `;
            tableBody.insertAdjacentHTML('beforeend', row);
        });
    }

    const modal = new bootstrap.Modal(document.getElementById('bulkCheckupModal'));
    modal.show();
}

// ─── Bulk Form Submit ─────────────────────────────────────────────────────────

document.getElementById('formBulkCheckup').addEventListener('submit', async function(e) {
    e.preventDefault();

    const form        = this;
    const submitBtn   = document.getElementById('bulkCheckupSubmitBtn');
    const originalText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';

    try {
        const formData = new FormData(form);

        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (response.ok && result.success) {
            showAlert('success', result.message);

            setTimeout(() => {
                const modalEl = document.getElementById('bulkCheckupModal');
                const modal   = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                location.reload();
            }, 1500);
        } else {
            const errorMessage = result.message || 'Gagal menyimpan data';
            const errors       = result.errors  || {};

            let errorHtml = errorMessage;
            if (Object.keys(errors).length > 0) {
                errorHtml += '<ul class="mt-2 mb-0">';
                Object.values(errors).forEach(errArray => {
                    errArray.forEach(err => { errorHtml += `<li>${err}</li>`; });
                });
                errorHtml += '</ul>';
            }

            showAlert('danger', errorHtml);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// ─── Alert Utility ────────────────────────────────────────────────────────────

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-3`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    const modalBody = document.querySelector('#bulkCheckupModal .modal-body');
    if (modalBody) {
        modalBody.insertBefore(alertDiv, modalBody.firstChild);
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alertDiv);
            bsAlert.close();
        }, 5000);
    }
}
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    /* General Card Styles */
    .card {
        transition: all 0.2s ease;
    }

    .card:hover {
        box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.1);
    }

    /* Single Checkup Modal */
    .modal-dialog-scrollable .modal-body {
        max-height: calc(100vh - 200px);
        overflow-y: auto;
    }

    /* ── Bulk Checkup Modal ──────────────────────────────────────────────────── */
    #bulkCheckupModal .modal-header {
        position: sticky;
        top: 0;
        z-index: 1050;
        backdrop-filter: blur(10px);
    }

    #bulkCheckupModal .modal-body {
        background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
        min-height: calc(100vh - 180px);
        padding: 1rem;
        overflow-y: auto;
    }

    #bulkCheckupModal .modal-footer {
        position: sticky;
        bottom: 0;
        z-index: 1050;
        backdrop-filter: blur(10px);
    }

    /* Table Wrapper */
    #bulkCheckupModal .bulk-table-wrapper {
        width: 100%;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        background: white;
        max-height: calc(100vh - 350px);
        overflow-y: auto;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scroll-behavior: smooth;
    }

    #bulkCheckupModal .table {
        margin-bottom: 0;
        width: 100%;
        table-layout: auto;
    }

    /* Sticky Header */
    #bulkCheckupModal .table thead.table-dark {
        position: sticky;
        top: 0;
        z-index: 20;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    #bulkCheckupModal .table thead.table-dark th {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        border: none;
        padding: 1rem 0.75rem;
        font-weight: 600;
        font-size: 0.85rem;
        vertical-align: middle;
        white-space: nowrap;
    }

    /* Sticky First Column */
    #bulkCheckupModal .sticky-first-col {
        position: sticky;
        left: 0;
        z-index: 10;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.05);
    }

    #bulkCheckupModal .table thead.table-dark .sticky-first-col {
        z-index: 25;
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.15);
    }

    /* Read-only MCU / Cat. cells — subtle background to signal non-editable */
    #bulkCheckupModal .table tbody td.readonly-col {
        background-color: #f8f9fa;
        vertical-align: middle;
    }

    #bulkCheckupModal .table tbody tr:hover td.readonly-col {
        background-color: #f1f3f5;
    }

    /* Table Body */
    #bulkCheckupModal .table tbody tr {
        transition: all 0.2s ease;
        background: white;
    }

    #bulkCheckupModal .table tbody tr:hover {
        background: #f8f9fa;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    #bulkCheckupModal .table tbody tr:hover .sticky-first-col {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
    }

    #bulkCheckupModal .table tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-color: #e9ecef;
    }

    /* Form Controls */
    #bulkCheckupModal .form-control-sm,
    #bulkCheckupModal .form-select-sm {
        font-size: 0.875rem;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        transition: all 0.2s ease;
        width: 100%;
    }

    #bulkCheckupModal .form-control-sm:focus,
    #bulkCheckupModal .form-select-sm:focus {
        border-color: #495057;
        box-shadow: 0 0 0 0.2rem rgba(52, 58, 64, 0.15);
        outline: none;
    }

    #bulkCheckupModal .form-control-sm:required:valid {
        border-color: #198754;
    }

    #bulkCheckupModal .form-control-sm:required:invalid:not(:placeholder-shown) {
        border-color: #dc3545;
    }

    #bulkCheckupModal small.text-muted {
        font-size: 0.7rem;
        display: block;
        margin-top: 0.25rem;
    }

    /* Scrollbar */
    #bulkCheckupModal .bulk-table-wrapper::-webkit-scrollbar {
        width: 12px;
        height: 12px;
    }

    #bulkCheckupModal .bulk-table-wrapper::-webkit-scrollbar-track {
        background: #f1f3f5;
        border-radius: 10px;
    }

    #bulkCheckupModal .bulk-table-wrapper::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #868e96 0%, #495057 100%);
        border-radius: 10px;
        border: 2px solid #f1f3f5;
    }

    #bulkCheckupModal .bulk-table-wrapper::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #495057 0%, #343a40 100%);
    }

    #bulkCheckupModal .bulk-table-wrapper::-webkit-scrollbar-corner {
        background: #f1f3f5;
    }

    /* Alert */
    #bulkCheckupModal .alert-info {
        background: linear-gradient(135deg, #e7f5ff 0%, #d0ebff 100%);
        border: 1px solid #74c0fc;
        border-radius: 10px;
    }

    /* Empty State */
    #bulkCheckupModal tbody tr td[colspan] {
        background: white;
    }

    /* Submit button disabled */
    #bulkCheckupModal .btn[type="submit"]:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Smooth scrolling */
    #bulkCheckupModal .bulk-table-wrapper {
        scroll-behavior: smooth;
    }

    /* Focus */
    #bulkCheckupModal textarea:focus,
    #bulkCheckupModal input:focus,
    #bulkCheckupModal select:focus {
        outline: none;
    }

    /* Helpers */
    .text-truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .min-w-0 { min-width: 0; }

    /* ── Responsive ─────────────────────────────────────────────────────────── */
    @media (max-width: 1200px) {
        #bulkCheckupModal .table thead.table-dark th {
            font-size: 0.75rem;
            padding: 0.75rem 0.5rem;
        }
        #bulkCheckupModal .table tbody td {
            padding: 0.75rem 0.5rem;
        }
        #bulkCheckupModal .sticky-first-col {
            min-width: 180px;
        }
    }

    @media (max-width: 768px) {
        #bulkCheckupModal .modal-body { padding: 1rem !important; }
        #bulkCheckupModal .alert-info { font-size: 0.85rem; }
        #bulkCheckupModal .form-control-sm,
        #bulkCheckupModal .form-select-sm { font-size: 0.8rem; padding: 0.4rem 0.35rem; }
        #bulkCheckupModal small.text-muted { font-size: 0.65rem; }
        #bulkCheckupModal .modal-footer { padding: 0.75rem !important; }
        #bulkCheckupModal .modal-footer .btn { font-size: 0.85rem; padding: 0.5rem 0.75rem; }
        #bulkCheckupModal .table thead.table-dark th { font-size: 0.7rem; padding: 0.6rem 0.4rem; }
        #bulkCheckupModal .table tbody td { padding: 0.6rem 0.4rem; }
        #bulkCheckupModal .sticky-first-col { min-width: 160px; }
        #bulkCheckupModal .bulk-table-wrapper { -webkit-overflow-scrolling: touch; }
        #bulkCheckupModal .bulk-table-wrapper::-webkit-scrollbar { width: 10px; height: 10px; }
    }

    @media (max-width: 576px) {
        #bulkCheckupModal .table thead.table-dark th { font-size: 0.65rem; padding: 0.5rem 0.3rem; }
        #bulkCheckupModal .table tbody td { padding: 0.5rem 0.3rem; }
        #bulkCheckupModal .sticky-first-col { min-width: 140px; }
        #bulkCheckupModal .form-control-sm,
        #bulkCheckupModal .form-select-sm { font-size: 0.75rem; padding: 0.35rem 0.3rem; }
        #bulkCheckupModal small.text-muted { font-size: 0.6rem; margin-top: 0.15rem; }
        #bulkCheckupModal .bulk-table-wrapper::-webkit-scrollbar { width: 12px; height: 12px; }
        #bulkCheckupModal .bulk-table-wrapper::-webkit-scrollbar-thumb { border: 1px solid #f1f3f5; }
    }

    /* Show scrollbar thumb only on hover (desktop) */
    @media (hover: hover) {
        #bulkCheckupModal .bulk-table-wrapper::-webkit-scrollbar-thumb {
            background: transparent;
        }
        #bulkCheckupModal .bulk-table-wrapper:hover::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #868e96 0%, #495057 100%);
        }
    }
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/daily-checkup/crew/index.blade.php ENDPATH**/ ?>