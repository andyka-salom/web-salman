

<?php $__env->startSection('title', 'Daily Checkup Monitoring'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
/* Kondisi Kritis - Merah */
.critical-condition {
    background-color: #fee !important;
    border-left: 4px solid #dc3545 !important;
}

.text-critical {
    color: #dc3545 !important;
    font-weight: bold;
}

.badge-critical {
    background-color: #dc3545 !important;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Daily Checkup Monitoring</h2>
            <p class="text-muted mb-0">Monitor dan validasi pemeriksaan kesehatan harian crew</p>
        </div>
        <div>
            <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#exportModal">
                <i class="fas fa-download me-2"></i>Export Data
            </button>
        </div>
    </div>

    
    <?php if($companyId): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border">
                <div class="card-body">
                    <div class="text-muted small mb-1">Total Checkup</div>
                    <div class="fs-4 fw-bold"><?php echo e($stats['total']); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border">
                <div class="card-body">
                    <div class="text-muted small mb-1">Pending</div>
                    <div class="fs-4 fw-bold"><?php echo e($stats['pending']); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border">
                <div class="card-body">
                    <div class="text-muted small mb-1">Reviewed</div>
                    <div class="fs-4 fw-bold"><?php echo e($stats['reviewed']); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border">
                <div class="card-body">
                    <div class="text-muted small mb-1">Validated</div>
                    <div class="fs-4 fw-bold"><?php echo e($stats['validated']); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border border-warning">
                <div class="card-body">
                    <div class="text-muted small mb-1">Health Issues</div>
                    <div class="fs-4 fw-bold text-warning"><?php echo e($stats['warnings']); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border border-danger">
                <div class="card-body">
                    <div class="text-muted small mb-1">
                        <i class="fas fa-exclamation-triangle me-1"></i>Critical
                    </div>
                    <div class="fs-4 fw-bold text-danger"><?php echo e($stats['critical'] ?? 0); ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="card border mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('daily-checkup.index')); ?>" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Tanggal</label>
                        <input type="date" name="date" class="form-control" value="<?php echo e($selectedDate); ?>" max="<?php echo e(date('Y-m-d')); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Company <span class="text-danger">*</span></label>
                        <select name="company_id" class="form-select" id="companySelect">
                            <option value="">Pilih Company</option>
                            <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($company->id); ?>" <?php echo e($companyId == $company->id ? 'selected' : ''); ?>>
                                    <?php echo e($company->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <small class="text-muted">Wajib dipilih untuk menampilkan data</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">
                            Vessel
                            <?php if($companyId): ?>
                                <span class="text-muted small">(Opsional - All untuk semua kapal)</span>
                            <?php endif; ?>
                        </label>
                        <select name="vessel_id" class="form-select" id="vesselSelect" <?php echo e(!$companyId ? 'disabled' : ''); ?>>
                            <option value="">Pilih Vessel</option>
                            <?php if($companyId): ?>
                                <option value="all" <?php echo e($vesselId === 'all' ? 'selected' : ''); ?>>
                                    <strong>🚢 All Vessels</strong>
                                </option>
                            <?php endif; ?>
                            <?php $__currentLoopData = $vessels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vessel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($vessel->id); ?>" <?php echo e($vesselId == $vessel->id ? 'selected' : ''); ?>>
                                    <?php echo e($vessel->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php if($companyId): ?>
                            <small class="text-muted">Pilih "All Vessels" untuk melihat semua kapal</small>
                        <?php else: ?>
                            <small class="text-muted">Pilih Company terlebih dahulu</small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="all" <?php echo e($statusFilter == 'all' ? 'selected' : ''); ?>>Semua Status</option>
                            <option value="pending" <?php echo e($statusFilter == 'pending' ? 'selected' : ''); ?>>Pending</option>
                            <option value="reviewed" <?php echo e($statusFilter == 'reviewed' ? 'selected' : ''); ?>>Reviewed</option>
                            <option value="validated" <?php echo e($statusFilter == 'validated' ? 'selected' : ''); ?>>Validated</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Health Status</label>
                        <select name="health_status" class="form-select">
                            <option value="all" <?php echo e($healthFilter == 'all' ? 'selected' : ''); ?>>Semua</option>
                            <option value="normal" <?php echo e($healthFilter == 'normal' ? 'selected' : ''); ?>>Normal</option>
                            <option value="warning" <?php echo e($healthFilter == 'warning' ? 'selected' : ''); ?>>Health Issues</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-dark w-100">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                            <a href="<?php echo e(route('daily-checkup.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    
    <?php if($vesselId === 'all' && $companyId): ?>
    <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-info-circle me-3 fs-4"></i>
            <div>
                <strong>Mode: Semua Kapal</strong><br>
                <small>Menampilkan data dari <strong><?php echo e($vessels->count()); ?> kapal</strong> dalam company <strong><?php echo e($companies->find($companyId)->name ?? 'N/A'); ?></strong></small>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    
    <?php if($companyId && $healthChecks->isNotEmpty()): ?>
    <div class="mb-3">
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <?php if(auth()->user()->hasRole('nurse') || auth()->user()->hasRole('super-admin')): ?>
            <?php
                $validatableCount = $stats['pending'] + $stats['reviewed'];
            ?>
            <?php if($validatableCount > 0): ?>
            <button type="button" class="btn btn-dark" onclick="validateAll()">
                <i class="fas fa-check-double me-2"></i>Validasi Semua (<?php echo e($validatableCount); ?>)
            </button>
            <?php else: ?>
            <button type="button" class="btn btn-outline-secondary" disabled>
                <i class="fas fa-check-double me-2"></i>Semua Sudah Divalidasi
            </button>
            <?php endif; ?>
            <?php endif; ?>

            <?php if($vesselId === 'all'): ?>
            <span class="badge bg-light text-dark border">
                <i class="fas fa-layer-group me-1"></i>Multi-Vessel Mode
            </span>
            <?php endif; ?>

            <?php if(($stats['critical'] ?? 0) > 0): ?>
            <span class="badge badge-critical text-white">
                <i class="fas fa-exclamation-triangle me-1"></i><?php echo e($stats['critical']); ?> Kondisi Kritis
            </span>
            <?php endif; ?>
        </div>

        <?php if(auth()->user()->hasRole('nurse') || auth()->user()->hasRole('super-admin')): ?>
        <div class="mt-2">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                <?php if($vesselId === 'all'): ?>
                    Validasi akan diterapkan untuk <strong>pending & reviewed</strong> dari <strong>semua kapal</strong> dalam company ini
                <?php elseif($vesselId): ?>
                    Validasi akan diterapkan untuk data dengan status <strong>pending & reviewed</strong> dari vessel yang dipilih
                <?php else: ?>
                    Validasi akan diterapkan untuk data dengan status <strong>pending & reviewed</strong> dari <strong>semua kapal</strong> dalam company ini
                <?php endif; ?>
            </small>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    
    <div class="card border">
        <div class="card-body">
            <?php if(!$hasFilter): ?>
                <div class="text-center py-5">
                    <i class="fas fa-building text-muted" style="font-size: 4rem;"></i>
                    <p class="text-muted mt-3 mb-1 fw-semibold">Silakan Pilih Company</p>
                    <p class="text-muted small mb-0">Pilih company dari filter di atas untuk menampilkan data checkup</p>
                </div>
            <?php elseif($healthChecks->isEmpty()): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox text-muted" style="font-size: 4rem;"></i>
                    <p class="text-muted mt-3 mb-0">Tidak ada data checkup untuk filter yang dipilih</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th>Crew & Vessel</th>
                                <th>Company</th>
                                <th class="text-center">Suhu (°C)</th>
                                <th class="text-center">Nadi (bpm)</th>
                                <th class="text-center">Tensi (mmHg)</th>
                                <th class="text-center">Nafas (x/min)</th>
                                <th class="text-center">SpO2 (%)</th>
                                <th style="min-width: 200px;">Keluhan</th>
                                <th style="min-width: 200px;">Obat-obatan</th>
                                <th class="text-center">Tests</th>
                                
                                <th class="text-center" style="min-width: 110px;">MCU</th>
                                <th class="text-center" style="min-width: 80px;">Cat.</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Health</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $healthChecks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $abnormalVitals = $check->getAbnormalVitals();
                                $hasIssues = !empty($abnormalVitals);
                                $isCritical = $check->hasCriticalCondition();
                                $crew = $check->crewMember;
                                $mcuStatus = $crew?->mcu_status ?? 'none';
                            ?>
                            <tr class="<?php echo e($isCritical ? 'critical-condition' : ($hasIssues ? 'table-light' : '')); ?>">
                                <td class="text-center">
                                    <?php echo e($healthChecks->firstItem() + $index); ?>

                                </td>
                                <td>
                                    <div class="fw-semibold">
                                        <?php echo e(optional($check->crewMember)->name ?? $check->crew_name_snapshot ?? 'Unknown'); ?>

                                        <?php if($isCritical): ?>
                                            <span class="badge badge-critical text-white ms-1">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small text-muted"><?php echo e(optional($check->crewMember)->position ?? $check->crew_position_snapshot ?? '-'); ?></div>
                                    <div class="small">
                                        <span class="badge bg-light text-dark border">
                                            <i class="fas fa-ship me-1"></i><?php echo e(optional($check->vessel)->name ?? 'N/A'); ?>

                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="small"><?php echo e(optional(optional($check->vessel)->company)->name ?? 'N/A'); ?></div>
                                </td>
                                <td class="text-center small">
                                    <?php if(isset($abnormalVitals['temperature'])): ?>
                                        <i class="fas fa-exclamation-triangle text-danger me-1" title="<?php echo e($abnormalVitals['temperature']['message']); ?>"></i>
                                        <span class="fw-bold text-danger"><?php echo e($check->temperature ?? '-'); ?></span>
                                    <?php else: ?>
                                        <?php echo e($check->temperature ?? '-'); ?>

                                    <?php endif; ?>
                                </td>
                                <td class="text-center small">
                                    <?php if(isset($abnormalVitals['pulse_rate'])): ?>
                                        <i class="fas fa-exclamation-triangle text-danger me-1" title="<?php echo e($abnormalVitals['pulse_rate']['message']); ?>"></i>
                                        <span class="fw-bold text-danger"><?php echo e($check->pulse_rate ?? '-'); ?></span>
                                    <?php else: ?>
                                        <?php echo e($check->pulse_rate ?? '-'); ?>

                                    <?php endif; ?>
                                </td>
                                <td class="text-center small">
                                    <?php if(isset($abnormalVitals['blood_pressure'])): ?>
                                        <?php
                                            $severity = $abnormalVitals['blood_pressure']['severity'] ?? 'warning';
                                            $colorClass = $severity === 'critical' ? 'text-critical' : 'text-danger';
                                        ?>
                                        <i class="fas fa-exclamation-triangle <?php echo e($colorClass); ?> me-1" title="<?php echo e($abnormalVitals['blood_pressure']['message']); ?>"></i>
                                        <span class="fw-bold <?php echo e($colorClass); ?>"><?php echo e($check->blood_pressure ?? '-'); ?></span>
                                        <?php if($severity === 'critical'): ?>
                                            <div class="small text-critical">KRITIS</div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php echo e($check->blood_pressure ?? '-'); ?>

                                    <?php endif; ?>
                                </td>
                                <td class="text-center small">
                                    <?php if(isset($abnormalVitals['respiratory_rate'])): ?>
                                        <i class="fas fa-exclamation-triangle text-danger me-1" title="<?php echo e($abnormalVitals['respiratory_rate']['message']); ?>"></i>
                                        <span class="fw-bold text-danger"><?php echo e($check->respiratory_rate ?? '-'); ?></span>
                                    <?php else: ?>
                                        <?php echo e($check->respiratory_rate ?? '-'); ?>

                                    <?php endif; ?>
                                </td>
                                <td class="text-center small">
                                    <?php if(isset($abnormalVitals['oxygen_saturation'])): ?>
                                        <i class="fas fa-exclamation-triangle text-danger me-1" title="<?php echo e($abnormalVitals['oxygen_saturation']['message']); ?>"></i>
                                        <span class="fw-bold text-danger"><?php echo e($check->oxygen_saturation ?? '-'); ?></span>
                                    <?php else: ?>
                                        <?php echo e($check->oxygen_saturation ?? '-'); ?>

                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small text-truncate" style="max-width: 200px;" title="<?php echo e($check->illness_complaints ?? '-'); ?>">
                                        <?php if($check->illness_complaints): ?>
                                            <?php echo e($check->illness_complaints); ?>

                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="small text-truncate" style="max-width: 200px;" title="<?php echo e($check->medications_consumed ?? '-'); ?>">
                                        <?php if($check->medications_consumed): ?>
                                            <?php echo e($check->medications_consumed); ?>

                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column gap-1 small">
                                        <?php if($check->napza_test_result !== 'not_tested'): ?>
                                        <span class="badge <?php echo e($check->napza_test_result === 'negative' ? 'bg-light text-dark' : 'bg-dark'); ?>">
                                            NAPZA: <?php echo e(ucfirst($check->napza_test_result)); ?>

                                        </span>
                                        <?php endif; ?>
                                        <?php if($check->romberg_test_result !== 'not_tested'): ?>
                                        <span class="badge <?php echo e($check->romberg_test_result === 'negative' ? 'bg-light text-dark' : 'bg-dark'); ?>">
                                            Romberg: <?php echo e(ucfirst($check->romberg_test_result)); ?>

                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                
                                <td class="text-center small">
                                    <?php if($crew): ?>
                                        <?php if($mcuStatus === 'expired'): ?>
                                            <span class="badge bg-danger"
                                                  title="Expired: <?php echo e($crew->mcu_valid_until?->format('d/m/Y') ?? '-'); ?>">
                                                <i class="fas fa-times-circle me-1"></i>Expired
                                            </span>
                                        <?php elseif($mcuStatus === 'expiring_soon'): ?>
                                            <span class="badge bg-warning text-dark"
                                                  title="Expires: <?php echo e($crew->mcu_valid_until?->format('d/m/Y') ?? '-'); ?>">
                                                <i class="fas fa-exclamation-circle me-1"></i><?php echo e($crew->mcu_days_left); ?>d
                                            </span>
                                        <?php elseif($mcuStatus === 'valid'): ?>
                                            <span class="badge bg-light text-dark border"
                                                  title="Valid until: <?php echo e($crew->mcu_valid_until?->format('d/m/Y') ?? '-'); ?>">
                                                <i class="fas fa-check-circle me-1 text-success"></i>Valid
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">
                                                <i class="fas fa-minus me-1"></i>N/A
                                            </span>
                                        <?php endif; ?>
                                        <?php if($crew->mcu_valid_until): ?>
                                            <div class="text-muted" style="font-size: 0.65rem; margin-top: 2px;">
                                                <?php echo e($crew->mcu_valid_until->format('d/m/Y')); ?>

                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>

                                
                                <td class="text-center small">
                                    <?php if($crew?->health_category): ?>
                                        <?php
                                            $cat = $crew->health_category;
                                            $catColorMap = [
                                                'P1' => 'bg-success text-white',
                                                'P2' => 'bg-success text-white',
                                                'P3' => 'bg-warning text-dark',
                                                'P4' => 'bg-warning text-dark',
                                                'P5' => 'bg-danger text-white',
                                                'P6' => 'bg-secondary text-white',
                                                'P7' => 'bg-light text-dark border',
                                            ];
                                        ?>
                                        <span class="badge <?php echo e($catColorMap[$cat] ?? 'bg-light text-dark border'); ?>"
                                              title="<?php echo e($crew->health_category_label); ?>">
                                            <?php echo e($cat); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center">
                                    <?php if($check->status === 'pending'): ?>
                                        <span class="badge bg-secondary">Pending</span>
                                    <?php elseif($check->status === 'reviewed'): ?>
                                        <span class="badge bg-info">Reviewed</span>
                                    <?php elseif($check->status === 'validated'): ?>
                                        <span class="badge bg-dark">Validated</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if($isCritical): ?>
                                        <span class="badge badge-critical text-white">
                                            <i class="fas fa-exclamation-triangle me-1"></i>KRITIS
                                        </span>
                                    <?php elseif($hasIssues): ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Issues
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark">Normal</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-dark" onclick="viewDetail('<?php echo e($check->id); ?>')">
                                        Detail
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                
                <div class="mt-3">
                    <?php echo e($healthChecks->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Checkup Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <!-- Content loaded via AJAX -->
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
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">
                            Vessel
                            <?php if(!auth()->user()->hasRole('super-admin') && !auth()->user()->hasRole('nurse')): ?>
                                <span class="text-danger">*</span>
                            <?php else: ?>
                                <span class="text-muted small">(Opsional - kosongkan untuk semua kapal)</span>
                            <?php endif; ?>
                        </label>
                        <select name="vessel_id" class="form-select" <?php if(!auth()->user()->hasRole('super-admin') && !auth()->user()->hasRole('nurse')): ?> required <?php endif; ?>>
                            <option value="">
                                <?php if(auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('nurse')): ?>
                                    Pilih Vessel (atau biarkan kosong untuk semua)
                                <?php else: ?>
                                    Pilih Vessel
                                <?php endif; ?>
                            </option>
                            <?php $__currentLoopData = $vessels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vessel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($vessel->id); ?>"><?php echo e($vessel->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Akhir <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Format</label>
                        <select name="format" class="form-select" id="exportFormat" required>
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="pdf">PDF (Max 31 hari)</option>
                        </select>
                    </div>
                    <?php if(auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('nurse')): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Info:</strong> Anda dapat mengekspor data dari semua kapal dengan meninggalkan field Vessel kosong.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
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

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const HEALTH_THRESHOLDS = {
    temperature: { very_low: 36, normal_max: 37.5 },
    pulse_rate: { low: 60, normal_max: 100 },
    respiratory_rate: { low: 12, normal_max: 20 },
    blood_pressure_systolic: { low: 90, normal_max: 120, pre_hypertension_max: 140, hypertension_stage1_max: 159 },
    blood_pressure_diastolic: { low: 60, normal_max: 80 },
    oxygen_saturation: { min: 95 }
};

function validateAll() {
    let vesselId = '<?php echo e($vesselId); ?>';
    const companyId = '<?php echo e($companyId); ?>';
    const validatableCount = <?php echo e($stats['pending'] + $stats['reviewed']); ?>;

    if (!vesselId || vesselId.trim() === '') {
        vesselId = 'all';
    }

    if (validatableCount === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Tidak ada data',
            text: 'Tidak ada checkup yang perlu divalidasi',
            confirmButtonColor: '#1b2e4b'
        });
        return;
    }

    if (!companyId) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Company ID tidak ditemukan. Silakan pilih company terlebih dahulu.',
            confirmButtonColor: '#1b2e4b'
        });
        return;
    }

    const vesselMode = vesselId === 'all' ? 'semua kapal dalam company ini' : 'vessel ini';
    const statusInfo = vesselId === 'all'
        ? `<div class="small text-muted mt-2"><i class="fas fa-ship me-1"></i>Akan memvalidasi data dari <strong><?php echo e($vessels->count()); ?> kapal</strong></div>`
        : '';

    Swal.fire({
        title: 'Konfirmasi Validasi',
        html: `
            <div class="text-start">
                <div class="alert alert-light border mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Anda akan memvalidasi <strong>${validatableCount} checkup</strong> dengan status
                    <span class="badge bg-secondary">Pending</span> dan
                    <span class="badge bg-info">Reviewed</span>
                    dari ${vesselMode}.
                    ${statusInfo}
                </div>
                <div class="mb-3">
                    <label class="form-label small">Catatan Validasi (Opsional)</label>
                    <textarea id="validation_notes" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1b2e4b',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-check-double me-2"></i>Ya, Validasi',
        cancelButtonText: 'Batal',
        width: '600px',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const notes = document.getElementById('validation_notes').value;
            const payload = {
                vessel_id: vesselId,
                company_id: companyId,
                check_date: '<?php echo e($selectedDate); ?>',
                validation_notes: notes
            };

            return fetch('<?php echo e(route("daily-checkup.validate")); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
            .then(response => {
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error('Server tidak mengembalikan response JSON');
                }
                return response.json().then(data => {
                    if (!response.ok) {
                        throw new Error(data.message || 'Terjadi kesalahan pada server');
                    }
                    return data;
                });
            })
            .catch(error => {
                Swal.showValidationMessage(`Gagal memvalidasi: ${error.message}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: result.value.message || 'Data berhasil divalidasi',
                confirmButtonColor: '#1b2e4b',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        }
    });
}

function checkAbnormalVital(value, type, data) {
    if (!value) return false;
    switch(type) {
        case 'temperature':
            return value < HEALTH_THRESHOLDS.temperature.very_low || value > HEALTH_THRESHOLDS.temperature.normal_max;
        case 'pulse_rate':
            return value < HEALTH_THRESHOLDS.pulse_rate.low || value > HEALTH_THRESHOLDS.pulse_rate.normal_max;
        case 'blood_pressure':
            if (!value.includes('/')) return false;
            const [systolic, diastolic] = value.split('/').map(Number);
            return systolic < HEALTH_THRESHOLDS.blood_pressure_systolic.low ||
                   systolic > HEALTH_THRESHOLDS.blood_pressure_systolic.normal_max ||
                   diastolic < HEALTH_THRESHOLDS.blood_pressure_diastolic.low ||
                   diastolic > HEALTH_THRESHOLDS.blood_pressure_diastolic.normal_max;
        case 'respiratory_rate':
            return value < HEALTH_THRESHOLDS.respiratory_rate.low || value > HEALTH_THRESHOLDS.respiratory_rate.normal_max;
        case 'oxygen_saturation':
            return value < HEALTH_THRESHOLDS.oxygen_saturation.min;
        default:
            return false;
    }
}

function viewDetail(checkupId) {
    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
    modal.show();

    document.getElementById('detailContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border" role="status">
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
                    Gagal memuat data. Silakan coba lagi.
                </div>
            `;
        });
}

function getMcuBadgeHTML(crewMember) {
    if (!crewMember) return '<span class="text-muted">-</span>';

    const status = crewMember.mcu_status || 'none';
    const daysLeft = crewMember.mcu_days_left;
    const validUntil = crewMember.mcu_valid_until
        ? new Date(crewMember.mcu_valid_until).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
        : null;

    if (status === 'expired') {
        return `<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Expired</span>${validUntil ? `<div class="small text-muted mt-1">${validUntil}</div>` : ''}`;
    } else if (status === 'expiring_soon') {
        return `<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-circle me-1"></i>Expiring (${daysLeft} hari)</span>${validUntil ? `<div class="small text-muted mt-1">${validUntil}</div>` : ''}`;
    } else if (status === 'valid') {
        return `<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Valid</span>${validUntil ? `<div class="small text-muted mt-1">${validUntil}</div>` : ''}`;
    } else {
        return '<span class="badge bg-light text-muted border">Tidak ada data</span>';
    }
}

function getHealthCategoryBadgeHTML(crewMember) {
    if (!crewMember || !crewMember.health_category) return '<span class="text-muted">-</span>';

    const cat = crewMember.health_category;
    const label = crewMember.health_category_label || cat;
    let colorClass = 'bg-secondary text-white';

    if (['P1', 'P2'].includes(cat)) colorClass = 'bg-success text-white';
    else if (['P3', 'P4'].includes(cat)) colorClass = 'bg-warning text-dark';
    else if (cat === 'P5') colorClass = 'bg-danger text-white';
    else if (cat === 'P6') colorClass = 'bg-secondary text-white';
    else if (cat === 'P7') colorClass = 'bg-light text-dark border';

    return `<span class="badge ${colorClass}">${cat}</span><div class="small text-muted mt-1">${label.includes(' - ') ? label.split(' - ')[1] : label}</div>`;
}

function generateDetailHTML(data) {
    const hasIssue = data.has_health_issue;

    let isCritical = false;
    if (data.blood_pressure && data.blood_pressure.includes('/')) {
        const [systolic, diastolic] = data.blood_pressure.split('/').map(Number);
        if (systolic > HEALTH_THRESHOLDS.blood_pressure_systolic.pre_hypertension_max ||
            diastolic > HEALTH_THRESHOLDS.blood_pressure_diastolic.normal_max) {
            isCritical = true;
        }
    }

    const getHighlightHTML = (value, type, unit) => {
        const isAbnormal = checkAbnormalVital(value, type, data);

        if (type === 'blood_pressure' && value && value.includes('/')) {
            const [systolic, diastolic] = value.split('/').map(Number);
            let className = '';
            let icon = '';

            if (systolic > HEALTH_THRESHOLDS.blood_pressure_systolic.hypertension_stage1_max) {
                className = 'text-critical';
                icon = '<i class="fas fa-exclamation-triangle text-critical me-2"></i>';
            } else if (systolic > HEALTH_THRESHOLDS.blood_pressure_systolic.pre_hypertension_max ||
                       diastolic > HEALTH_THRESHOLDS.blood_pressure_diastolic.normal_max) {
                className = 'text-danger fw-bold';
                icon = '<i class="fas fa-exclamation-triangle text-danger me-2"></i>';
            } else if (isAbnormal) {
                className = 'text-warning fw-bold';
                icon = '<i class="fas fa-exclamation-triangle text-warning me-2"></i>';
            }

            return `<div class="${className}">${icon}${value}${unit}</div>`;
        }

        const icon = isAbnormal ? '<i class="fas fa-exclamation-triangle text-danger me-2"></i>' : '';
        const className = isAbnormal ? 'text-danger fw-bold' : '';
        return `<div class="${className}">${icon}${value || '-'}${unit}</div>`;
    };

    return `
        <div class="row g-3">
            <div class="col-12">
                <div class="border-bottom pb-3">
                    <h5 class="mb-1">${data.crew_member?.name || data.crew_name_snapshot || 'Unknown'}</h5>
                    <p class="mb-0 text-muted">${data.crew_member?.position || data.crew_position_snapshot || '-'}</p>
                    <p class="mb-0 text-muted small">Vessel: ${data.vessel.name}</p>
                    ${isCritical ? '<span class="badge badge-critical text-white mt-2"><i class="fas fa-exclamation-triangle me-1"></i>KONDISI KRITIS</span>' :
                      (hasIssue ? '<span class="badge bg-warning text-dark mt-2"><i class="fas fa-exclamation-triangle me-1"></i>Health Issue Detected</span>' :
                       '<span class="badge bg-light text-dark mt-2">Normal</span>')}
                </div>
            </div>

            <div class="col-12">
                <h6 class="border-bottom pb-2 mb-3">Vital Signs</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="small text-muted">Suhu Tubuh</label>
                        ${getHighlightHTML(data.temperature, 'temperature', ' °C')}
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">Frekuensi Nadi</label>
                        ${getHighlightHTML(data.pulse_rate, 'pulse_rate', ' bpm')}
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">Tekanan Darah</label>
                        ${getHighlightHTML(data.blood_pressure, 'blood_pressure', ' mmHg')}
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">Frekuensi Nafas</label>
                        ${getHighlightHTML(data.respiratory_rate, 'respiratory_rate', ' x/min')}
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">Gula Darah</label>
                        <div>${data.blood_sugar_level || '-'} mg/dL</div>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">Saturasi Oksigen</label>
                        ${getHighlightHTML(data.oxygen_saturation, 'oxygen_saturation', ' %')}
                    </div>
                </div>
            </div>

            
            <div class="col-12">
                <h6 class="border-bottom pb-2 mb-3">
                    <i class="fas fa-id-card me-2 text-muted"></i>MCU & Health Category
                </h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="small text-muted d-block mb-1">MCU Valid Until</label>
                        ${getMcuBadgeHTML(data.crew_member)}
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted d-block mb-1">Health Category</label>
                        ${getHealthCategoryBadgeHTML(data.crew_member)}
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted d-block mb-1">Blood Type</label>
                        <div class="fw-semibold">${data.crew_member?.blood_type || '-'}</div>
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
            </div>

            ${data.remarks ? `
            <div class="col-12">
                <h6 class="border-bottom pb-2 mb-3">Remarks</h6>
                <div class="border rounded p-3 bg-light">${data.remarks}</div>
            </div>
            ` : ''}

            <div class="col-12">
                <h6 class="border-bottom pb-2 mb-3">Status & Tracking</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="small text-muted">Status</label>
                        <div>
                            ${data.status === 'pending' ? '<span class="badge bg-secondary">Pending</span>' : ''}
                            ${data.status === 'reviewed' ? '<span class="badge bg-info">Reviewed</span>' : ''}
                            ${data.status === 'validated' ? '<span class="badge bg-dark">Validated</span>' : ''}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted">Reported By</label>
                        <div class="fw-bold">${data.reporter ? data.reporter.name : '-'}</div>
                        <div class="small text-muted">${data.checked_at || '-'}</div>
                    </div>
                </div>

                ${data.verifier ? `
                <div class="mt-3 pt-3 border-top">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="small text-muted d-block mb-2">
                                <i class="fas fa-user-check me-1"></i>Verified By
                            </label>
                            <div class="d-flex align-items-start gap-2">
                                <div class="flex-grow-1">
                                    <div class="fw-bold">${data.verifier.name}</div>
                                    <div class="small text-muted">${data.verified_at || '-'}</div>
                                </div>
                            </div>
                            ${data.verification_notes ? `
                            <div class="mt-2 p-2 bg-light border rounded">
                                <div class="small fw-semibold text-muted mb-1">Catatan Verifikasi:</div>
                                <div class="small">${data.verification_notes}</div>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
                ` : ''}

                ${data.validator ? `
                <div class="mt-3 pt-3 border-top">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="small text-muted d-block mb-2">
                                <i class="fas fa-check-double me-1"></i>Validated By
                            </label>
                            <div class="d-flex align-items-start gap-2">
                                <div class="flex-grow-1">
                                    <div class="fw-bold">${data.validator.name}</div>
                                    <div class="small text-muted">${data.validated_at || '-'}</div>
                                </div>
                            </div>
                            ${data.validation_notes ? `
                            <div class="mt-2 p-2 bg-light border rounded">
                                <div class="small fw-semibold text-muted mb-1">Catatan Validasi:</div>
                                <div class="small">${data.validation_notes}</div>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
                ` : ''}
            </div>
        </div>
    `;
}

document.getElementById('exportForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const format = document.getElementById('exportFormat').value;
    const action = format === 'excel'
        ? '<?php echo e(route("daily-checkup.export.excel")); ?>'
        : '<?php echo e(route("daily-checkup.export.pdf")); ?>';

    this.action = action;
    this.submit();
});

document.getElementById('companySelect')?.addEventListener('change', function() {
    if (this.value) {
        document.getElementById('vesselSelect').disabled = false;
    } else {
        document.getElementById('vesselSelect').disabled = true;
        document.getElementById('vesselSelect').value = '';
    }
    document.getElementById('filterForm').submit();
});

<?php if(session('success')): ?>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '<?php echo e(session('success')); ?>',
        confirmButtonColor: '#1b2e4b',
        timer: 3000,
        timerProgressBar: true
    });
<?php endif; ?>

<?php if(session('error')): ?>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '<?php echo e(session('error')); ?>',
        confirmButtonColor: '#1b2e4b'
    });
<?php endif; ?>

<?php if(session('warning')): ?>
    Swal.fire({
        icon: 'warning',
        title: 'Perhatian!',
        text: '<?php echo e(session('warning')); ?>',
        confirmButtonColor: '#1b2e4b'
    });
<?php endif; ?>
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/daily-checkup/admin/index.blade.php ENDPATH**/ ?>