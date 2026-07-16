<?php $__env->startSection('title', 'Daily Checkup - ' . $vessel->name); ?>

<?php $__env->startPush('styles'); ?>
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- Font Awesome CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" >
<style>
/* Style minimalis untuk badge */
.badge {
    font-weight: 500;
}
/* Spinner di modal detail */
.spinner-border.text-dark {
    color: #343a40 !important;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?php echo e(route('daily-checkup.index')); ?>">Daily Checkup</a>
                </li>
                <li class="breadcrumb-item active"><?php echo e($vessel->name); ?></li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1"><?php echo e($vessel->name); ?></h2>
                <p class="text-muted mb-0"><?php echo e($vessel->company->name); ?></p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <input type="date"
                       id="dateFilter"
                       class="form-control"
                       value="<?php echo e($selectedDate); ?>"
                       max="<?php echo e(date('Y-m-d')); ?>">
                <button type="button"
                        class="btn btn-outline-dark"
                        data-bs-toggle="modal"
                        data-bs-target="#exportModal">
                    <i class="fas fa-download me-2"></i>Export
                </button>
            </div>
        </div>
    </div>

    
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">Total Crew</div>
                    <div class="fs-4 fw-bold"><?php echo e($stats['crew_total'] ?? 0); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">Completed</div>
                    <div class="fs-4 fw-bold"><?php echo e($stats['completed'] ?? 0); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">Pending</div>
                    <div class="fs-4 fw-bold"><?php echo e($stats['pending'] ?? 0); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">Reviewed</div>
                    <div class="fs-4 fw-bold"><?php echo e($stats['reviewed'] ?? 0); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">Validated</div>
                    <div class="fs-4 fw-bold"><?php echo e($stats['validated'] ?? 0); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">Warnings</div>
                    <div class="fs-4 fw-bold text-danger"><?php echo e($stats['warnings'] ?? 0); ?></div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card border mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex gap-2">
                    <div class="btn-group" role="group">
                        <a href="<?php echo e(route('daily-checkup.vessel.show', $vessel)); ?>?date=<?php echo e($selectedDate); ?>&filter=all"
                           class="btn btn-sm <?php echo e($filter == 'all' ? 'btn-dark' : 'btn-outline-dark'); ?>">
                            All (<?php echo e($healthChecks->count()); ?>)
                        </a>
                        <a href="<?php echo e(route('daily-checkup.vessel.show', $vessel)); ?>?date=<?php echo e($selectedDate); ?>&filter=warnings"
                           class="btn btn-sm <?php echo e($filter == 'warnings' ? 'btn-danger' : 'btn-outline-danger'); ?>">
                            Warnings (<?php echo e($stats['warnings'] ?? 0); ?>)
                        </a>
                    </div>
                </div>

                <?php if(auth()->user()->hasRole('koordinator')): ?>
                    <?php if(($stats['pending'] ?? 0) > 0): ?>
                    <button type="button"
                            class="btn btn-dark"
                            onclick="verifyAll()">
                        <i class="fas fa-check-circle me-2"></i>Verify All Pending (<?php echo e($stats['pending']); ?>)
                    </button>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if(auth()->user()->hasRole('ners') || auth()->user()->hasRole('super-admin')): ?>
                    <?php if($healthChecks->isNotEmpty()): ?>
                    <button type="button"
                            class="btn btn-dark"
                            onclick="validateAll()">
                        <i class="fas fa-check-double me-2"></i>Validate All (<?php echo e($healthChecks->count()); ?>)
                    </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="card border">
        <div class="card-body">
            <?php if($healthChecks->isEmpty()): ?>
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list text-muted" style="font-size: 4rem;"></i>
                    <p class="text-muted mt-3 mb-0">
                        <?php if($filter == 'warnings'): ?>
                            Tidak ada data dengan warning
                        <?php else: ?>
                            Belum ada pemeriksaan pada tanggal ini
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Crew</th>
                                <th class="text-center">Suhu (°C)</th>
                                <th class="text-center">Nadi (bpm)</th>
                                <th class="text-center">Tensi (mmHg)</th>
                                <th class="text-center">SpO2 (%)</th>
                                <th class="text-center">Tests</th>
                                <th class="text-center">Fatigue</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $healthChecks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                // START: Logic Consistency Fix
                                // We use $check->warnings for specific flags (e.g., vital signs)
                                $warnings = $check->warnings ?? [];

                                // We determine $hasIssues by checking all potential sources of warning
                                $isFatigueWarning = $check->fatigue_level === 'sedang' || $check->fatigue_level === 'berat';
                                $isNapzaWarning = $check->napza_test_result !== 'not_tested' && $check->napza_test_result !== 'negative';
                                $hasIssues = !empty($warnings) || $isFatigueWarning || $isNapzaWarning;
                                // END: Logic Consistency Fix
                            ?>
                            <tr class="<?php echo e($hasIssues ? 'table-light' : ''); ?>">
                                <td class="text-center"><?php echo e($index + 1); ?></td>
                                <td>
                                    <div class="fw-semibold"><?php echo e($check->crewMember->name); ?></div>
                                    <div class="small text-muted"><?php echo e($check->crewMember->position); ?></div>
                                    <div class="small text-secondary">NIK: <?php echo e($check->crewMember->nik); ?></div>
                                </td>

                                
                                <td class="text-center small">
                                    <?php if(isset($warnings['temperature'])): ?>
                                        <i class="fas fa-exclamation-triangle text-danger me-1" title="Suhu abnormal"></i>
                                        <span class="fw-bold text-danger"><?php echo e($check->temperature ?? '-'); ?></span>
                                    <?php else: ?>
                                        <?php echo e($check->temperature ?? '-'); ?>

                                    <?php endif; ?>
                                </td>
                                
                                <td class="text-center small">
                                    <?php if(isset($warnings['pulse_rate'])): ?>
                                        <i class="fas fa-exclamation-triangle text-danger me-1" title="Nadi abnormal"></i>
                                        <span class="fw-bold text-danger"><?php echo e($check->pulse_rate ?? '-'); ?></span>
                                    <?php else: ?>
                                        <?php echo e($check->pulse_rate ?? '-'); ?>

                                    <?php endif; ?>
                                </td>
                                
                                <td class="text-center small">
                                    <?php if(isset($warnings['blood_pressure'])): ?>
                                        <i class="fas fa-exclamation-triangle text-danger me-1" title="Tekanan darah abnormal"></i>
                                        <span class="fw-bold text-danger"><?php echo e($check->blood_pressure ?? '-'); ?></span>
                                    <?php else: ?>
                                        <?php echo e($check->blood_pressure ?? '-'); ?>

                                    <?php endif; ?>
                                </td>
                                
                                <td class="text-center small">
                                    <?php if(isset($warnings['oxygen_saturation'])): ?>
                                        <i class="fas fa-exclamation-triangle text-danger me-1" title="Saturasi oksigen abnormal"></i>
                                        <span class="fw-bold text-danger"><?php echo e($check->oxygen_saturation ?? '-'); ?></span>
                                    <?php else: ?>
                                        <?php echo e($check->oxygen_saturation ?? '-'); ?>

                                    <?php endif; ?>
                                </td>

                                <td class="text-center">
                                    <div class="d-flex flex-column gap-1">
                                        
                                        <?php if($check->napza_test_result !== 'not_tested'): ?>
                                        <span class="badge <?php echo e($check->napza_test_result === 'negative' ? 'bg-light text-dark' : 'bg-dark'); ?>">
                                            NAPZA: <?php echo e($check->napza_test_result === 'negative' ? 'Negatif' : 'Non-Negatif'); ?>

                                        </span>
                                        <?php endif; ?>
                                        
                                        <?php if($check->romberg_test_result !== 'not_tested'): ?>
                                        <span class="badge <?php echo e($check->romberg_test_result === 'negative' ? 'bg-light text-dark' : 'bg-dark'); ?>">
                                            Romberg: <?php echo e($check->romberg_test_result === 'negative' ? 'Negatif' : 'Positif'); ?>

                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    
                                    <?php if($check->fatigue_level): ?>
                                        <span class="badge <?php echo e($check->fatigue_level === 'ringan' ? 'bg-secondary' : ($check->fatigue_level === 'sedang' ? 'bg-warning text-dark' : 'bg-danger')); ?>">
                                            <?php echo e(ucfirst($check->fatigue_level)); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    
                                    <?php if($check->status === 'pending'): ?>
                                        <span class="badge bg-secondary">Pending</span>
                                    <?php elseif($check->status === 'reviewed'): ?>
                                        <span class="badge bg-secondary">Reviewed</span>
                                    <?php elseif($check->status === 'validated'): ?>
                                        <span class="badge bg-dark">Validated</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    
                                    <button type="button"
                                            class="btn btn-sm btn-outline-dark"
                                            onclick="viewDetail('<?php echo e($check->id); ?>')">
                                        Detail
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Detail Pemeriksaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-dark" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Export Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="exportForm" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="vessel_id" value="<?php echo e($vessel->id); ?>">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control" value="<?php echo e($selectedDate); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" name="end_date" class="form-control" value="<?php echo e($selectedDate); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Format</label>
                        <select name="format" class="form-select" id="exportFormat" required>
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-dark">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php if(auth()->user()->hasRole('koordinator')): ?>
<div class="modal fade" id="verifyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Verify Checkups</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo e(route('daily-checkup.verify')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="vessel_id" value="<?php echo e($vessel->id); ?>">
                <input type="hidden" name="check_date" value="<?php echo e($selectedDate); ?>">

                <div class="modal-body">
                    <div class="alert alert-light border mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Anda akan memverifikasi <strong><?php echo e($stats['pending'] ?? 0); ?> pemeriksaan</strong>
                        yang berstatus pending pada tanggal
                        <strong><?php echo e(\Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y')); ?></strong>.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan Verifikasi (Opsional)</label>
                        <textarea name="verification_notes"
                                  class="form-control"
                                  rows="4"
                                  placeholder="Tambahkan catatan verifikasi jika diperlukan..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-dark">
                        <i class="fas fa-check-circle me-2"></i>Verify
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>


<?php if(auth()->user()->hasRole('ners') || auth()->user()->hasRole('super-admin')): ?>
<div class="modal fade" id="validateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Validate Checkups</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo e(route('daily-checkup.validate')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="vessel_id" value="<?php echo e($vessel->id); ?>">
                <input type="hidden" name="check_date" value="<?php echo e($selectedDate); ?>">

                <div class="modal-body">
                    <div class="alert alert-light border mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Anda akan memvalidasi <strong><?php echo e($healthChecks->count()); ?> pemeriksaan</strong>
                        pada tanggal <strong><?php echo e(\Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y')); ?></strong>.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan Validasi (Opsional)</label>
                        <textarea name="validation_notes"
                                  class="form-control"
                                  rows="4"
                                  placeholder="Tambahkan catatan validasi jika diperlukan..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-dark">
                        <i class="fas fa-check-double me-2"></i>Validate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Health thresholds from backend (Needed for Detail Modal logic)
const HEALTH_THRESHOLDS = {
    temperature: { max: 37.5 },
    pulse_rate: { min: 60, max: 120 },
    respiratory_rate: { min: 16, max: 24 },
    blood_pressure_systolic: { min: 90, max: 140 },
    blood_pressure_diastolic: { min: 60, max: 90 },
    blood_sugar: { min: 70, max: 200 },
    oxygen_saturation: { min: 95 }
};

// Date filter
document.getElementById('dateFilter').addEventListener('change', function() {
    window.location.href = `<?php echo e(route('daily-checkup.vessel.show', $vessel)); ?>?date=${this.value}`;
});

// Helper function to check abnormality (Client-side implementation)
function checkAbnormalVital(value, type, data) {
    if (!value) return false;

    switch(type) {
        case 'temperature':
            return value > HEALTH_THRESHOLDS.temperature.max;
        case 'pulse_rate':
            return value < HEALTH_THRESHOLDS.pulse_rate.min || value > HEALTH_THRESHOLDS.pulse_rate.max;
        case 'blood_pressure':
            if (!value.includes('/')) return false;

            const [systolic, diastolic] = value.split('/').map(Number);
            return systolic < HEALTH_THRESHOLDS.blood_pressure_systolic.min ||
                   systolic > HEALTH_THRESHOLDS.blood_pressure_systolic.max ||
                   diastolic < HEALTH_THRESHOLDS.blood_pressure_diastolic.min ||
                   diastolic > HEALTH_THRESHOLDS.blood_pressure_diastolic.max;
        case 'respiratory_rate':
            return value < HEALTH_THRESHOLDS.respiratory_rate.min || value > HEALTH_THRESHOLDS.respiratory_rate.max;
        case 'blood_sugar_level': // Using the key used in the data object
            return value < HEALTH_THRESHOLDS.blood_sugar.min || value > HEALTH_THRESHOLDS.blood_sugar.max;
        case 'oxygen_saturation':
            return value < HEALTH_THRESHOLDS.oxygen_saturation.min;
        default:
            return false;
    }
}


// View detail
function viewDetail(checkupId) {
    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
    modal.show();

    // Reset content to loading spinner
    document.getElementById('detailContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-dark" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;

    fetch(`/daily-checkup/${checkupId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('detailContent').innerHTML = generateDetailHTML(data);
        })
        .catch(error => {
            document.getElementById('detailContent').innerHTML = `
                <div class="alert alert-light border">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Gagal memuat data. Silakan coba lagi.
                </div>
            `;
        });
}

function generateDetailHTML(data) {

    // Client-side warning determination for vital signs in the modal
    let warnings = {};
    if (checkAbnormalVital(data.temperature, 'temperature', data)) warnings.temperature = true;
    if (checkAbnormalVital(data.pulse_rate, 'pulse_rate', data)) warnings.pulse_rate = true;
    if (checkAbnormalVital(data.blood_pressure, 'blood_pressure', data)) warnings.blood_pressure = true;
    if (checkAbnormalVital(data.oxygen_saturation, 'oxygen_saturation', data)) warnings.oxygen_saturation = true;
    if (checkAbnormalVital(data.respiratory_rate, 'respiratory_rate', data)) warnings.respiratory_rate = true;
    if (checkAbnormalVital(data.blood_sugar_level, 'blood_sugar_level', data)) warnings.blood_sugar_level = true;

    // Check if the primary backend flag or our determined vital warnings exist
    const hasWarning = data.has_health_issue || Object.keys(warnings).length > 0;

    // Helper function to show icon if warning exists
    const warningIcon = (key) => warnings[key] ? '<i class="fas fa-exclamation-triangle text-danger me-2"></i>' : '';
    const warningClass = (key) => warnings[key] ? 'text-danger' : '';

    // Helper function untuk status badge netral (sesuai Daily Checkup Monitoring)
    const getStatusBadge = (status) => {
        if (status === 'pending') return '<span class="badge bg-secondary">Pending</span>';
        if (status === 'reviewed') return '<span class="badge bg-secondary">Reviewed</span>';
        if (status === 'validated') return '<span class="badge bg-dark">Validated</span>';
        return '-';
    };

    // Helper untuk badge tes (sesuai Daily Checkup Monitoring)
    const getTestBadge = (result) => {
        if (result === 'negative') return 'bg-light text-dark';
        if (result === 'positive' || result === 'non_negative') return 'bg-dark';
        return 'bg-secondary';
    };

    // Helper untuk badge fatigue (sesuai gaya minimalis)
    const getFatigueBadge = (level) => {
        if (level === 'ringan') return 'bg-secondary';
        if (level === 'sedang') return 'bg-warning text-dark';
        if (level === 'berat') return 'bg-danger';
        return 'bg-secondary';
    };


    return `
        <div class="row g-3">
            <div class="col-12">
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="mb-1">${data.crew_member.name}</h5>
                            <p class="mb-0 text-muted">${data.crew_member.position}</p>
                            <p class="mb-0 text-muted small">Vessel: ${data.crew_member.vessel.name}</p>
                        </div>
                        ${hasWarning ? '<span class="badge bg-dark mt-2"><i class="fas fa-exclamation-triangle me-1"></i>Health Issue Detected</span>' : '<span class="badge bg-light text-dark mt-2">Normal</span>'}
                    </div>
                </div>
            </div>

            <div class="col-12">
                <h6 class="border-bottom pb-2 mb-3">Vital Signs</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="small text-muted">Suhu Tubuh</label>
                        <div class="fw-bold ${warningClass('temperature')}">
                            ${warningIcon('temperature')} ${data.temperature || '-'} °C
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">Frekuensi Nadi</label>
                        <div class="fw-bold ${warningClass('pulse_rate')}">
                            ${warningIcon('pulse_rate')} ${data.pulse_rate || '-'} bpm
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">Tekanan Darah</label>
                        <div class="fw-bold ${warningClass('blood_pressure')}">
                            ${warningIcon('blood_pressure')} ${data.blood_pressure || '-'} mmHg
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">Frekuensi Nafas</label>
                        <div class="fw-bold ${warningClass('respiratory_rate')}">
                            ${warningIcon('respiratory_rate')} ${data.respiratory_rate || '-'} x/min
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">Gula Darah</label>
                        <div class="fw-bold ${warningClass('blood_sugar_level')}">
                            ${warningIcon('blood_sugar_level')} ${data.blood_sugar_level || '-'} mg/dL
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">Saturasi Oksigen</label>
                        <div class="fw-bold ${warningClass('oxygen_saturation')}">
                            ${warningIcon('oxygen_saturation')} ${data.oxygen_saturation || '-'} %
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <h6 class="border-bottom pb-2 mb-3">Health Status</h6>
                <div class="mb-3">
                    <label class="small text-muted">Keluhan Kesehatan</label>
                    <div class="border rounded p-2">${data.illness_complaints || '-'}</div>
                </div>
                <div class="mb-3">
                    <label class="small text-muted">Obat-obatan</label>
                    <div class="border rounded p-2">${data.medications_consumed || '-'}</div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="small text-muted">Tingkat Kelelahan</label>
                        <div class="fw-bold">
                            <span class="badge ${getFatigueBadge(data.fatigue_level)}">
                                ${data.fatigue_level ? data.fatigue_level.charAt(0).toUpperCase() + data.fatigue_level.slice(1) : '-'}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">Area Kerja</label>
                        <div class="fw-bold">${data.work_area || '-'}</div>
                    </div>
                </div>
            </div>

            ${data.napza_test_result !== 'not_tested' || data.romberg_test_result !== 'not_tested' ? `
            <div class="col-12">
                <h6 class="border-bottom pb-2 mb-3">Medical Tests</h6>
                <div class="row g-3">
                    ${data.napza_test_result !== 'not_tested' ? `
                    <div class="col-md-6">
                        <label class="small text-muted">NAPZA Test</label>
                        <div>
                            <span class="badge ${getTestBadge(data.napza_test_result === 'non_negative' ? 'positive' : data.napza_test_result)}">
                                NAPZA: ${data.napza_test_result === 'negative' ? 'Negatif' : 'Non-Negatif'}
                            </span>
                        </div>
                    </div>
                    ` : ''}
                    ${data.romberg_test_result !== 'not_tested' ? `
                    <div class="col-md-6">
                        <label class="small text-muted">Romberg Test</label>
                        <div>
                            <span class="badge ${getTestBadge(data.romberg_test_result === 'positive' ? 'positive' : data.romberg_test_result)}">
                                Romberg: ${data.romberg_test_result === 'negative' ? 'Negatif' : 'Positif'}
                            </span>
                        </div>
                    </div>
                    ` : ''}
                </div>
            </div>
            ` : ''}

            ${data.remarks ? `
            <div class="col-12">
                <h6 class="border-bottom pb-2 mb-3">Remarks</h6>
                <div class="border rounded p-3 bg-light">${data.remarks}</div>
            </div>
            ` : ''}

            <div class="col-12">
                <h6 class="border-bottom pb-2 mb-3">Status & Timeline</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="small text-muted">Status</label>
                        <div>${getStatusBadge(data.status)}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted">Reported By</label>
                        <div class="fw-bold">${data.reporter ? data.reporter.name : '-'}</div>
                        <div class="small text-muted">${new Date(data.checked_at).toLocaleString('id-ID')}</div>
                    </div>
                    ${data.verifier ? `
                    <div class="col-md-4">
                        <label class="small text-muted">Verified By</label>
                        <div class="fw-bold">${data.verifier.name}</div>
                    </div>
                    ` : ''}
                    ${data.validator ? `
                    <div class="col-md-4">
                        <label class="small text-muted">Validated By</label>
                        <div class="fw-bold">${data.validator.name}</div>
                    </div>
                    ` : ''}
                </div>
            </div>
        </div>
    `;
}

// Verify all
function verifyAll() {
    const modal = new bootstrap.Modal(document.getElementById('verifyModal'));
    modal.show();
}

// Validate all
function validateAll() {
    const modal = new bootstrap.Modal(document.getElementById('validateModal'));
    modal.show();
}

// Export
document.getElementById('exportForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const format = document.getElementById('exportFormat').value;
    const action = format === 'excel'
        ? '<?php echo e(route("daily-checkup.export.excel")); ?>'
        : '<?php echo e(route("daily-checkup.export.pdf")); ?>';

    this.action = action;
    this.submit();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/daily-checkup/vessel-detail.blade.php ENDPATH**/ ?>