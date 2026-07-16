<?php $__env->startSection('title', 'Detail Laporan #' . $report->report_number); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="<?php echo e(asset('src/plugins/src/animate/animate.css')); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo e(asset('src/assets/css/light/components/modal.css')); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo e(asset('src/assets/css/dark/components/modal.css')); ?>" rel="stylesheet" type="text/css" />

<style>
    /* Styling dasar template */
    .detail-card {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
        border: none;
        border-radius: 8px;
    }
    .detail-card .card-header {
        background-color: var(--card-bg);
        border-bottom: 1px solid var(--bs-border-color);
    }
    .card-title i { color: var(--bs-primary); }

    /* Detail Teks */
    .detail-label {
        font-size: 0.8rem;
        color: var(--text-muted);
        display: block;
        margin-bottom: 0.25rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .detail-value {
        font-size: 1rem;
        color: var(--text-color);
        font-weight: 600;
    }
    .detail-value.long-text {
        white-space: pre-wrap;
        font-weight: 400;
        line-height: 1.6;
    }
    .detail-value .badge { font-size: 0.9rem; font-weight: 500; }

    /* Attachment Gallery */
    .attachment-gallery .attachment-item {
        display: block;
        overflow: hidden;
        border-radius: .5rem;
        position: relative;
        aspect-ratio: 1 / 1;
        background-color: var(--bs-light);
        cursor: pointer;
    }
    .attachment-gallery .attachment-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .3s ease, filter .3s ease;
    }
    .attachment-gallery .attachment-item:hover img { transform: scale(1.1); filter: brightness(0.7); }
    .attachment-gallery .attachment-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.4);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity .3s ease;
    }
    .attachment-gallery .attachment-item:hover .attachment-overlay { opacity: 1; }

    /* Action Items Accordion */
    .accordion-item-completed { opacity: 0.8; }
    .accordion-item-completed .accordion-button { background-color: var(--bs-light); }
    .completion-notes {
        margin-top: 0.75rem;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        border-left: 4px solid var(--bs-success);
        background-color: var(--bs-success-bg-subtle);
        font-style: italic;
    }
    .proof-gallery { display: flex; flex-wrap: wrap; gap: 0.75rem; }
    .proof-gallery .proof-item {
        width: 70px;
        height: 70px;
        border-radius: .5rem;
        overflow: hidden;
        border: 1px solid var(--bs-border-color);
        transition: transform .2s ease, box-shadow .2s ease;
        cursor: pointer;
    }
    .proof-gallery .proof-item:hover { transform: scale(1.05); box-shadow: 0 4px 10px rgba(0,0,0,.1); }
    .proof-gallery .proof-item img { width: 100%; height: 100%; object-fit: cover; }

    /* Modal Approval */
    .hover-bg-light:hover { background-color: var(--bs-light) !important; transition: background-color 0.2s ease; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
    use App\Models\CermatReport;
    use App\Models\ActionItem;
    use Illuminate\Support\Str;
    use Illuminate\Support\Arr;

    $statusClass = $report->getStatusBadgeClass();
    $user = Auth::user();
    $isRejected = $report->supervisor_status === CermatReport::SUPERVISOR_STATUS_REJECTED;
?>
    <div class="layout-px-spacing">
        <div class="middle-content container-xxl p-0">

            
            <div class="row layout-top-spacing">
                <div class="col-12">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                        <div>
                            <h2 class="mb-0 fw-bold d-flex align-items-center gap-2">
                                Detail Laporan
                                <span class="badge bg-<?php echo e($statusClass); ?> align-middle fs-6 fw-normal"><?php echo e(Str::of($report->status)->replace('_', ' ')->title()); ?></span>
                            </h2>
                            <p class="mb-0 text-muted">#<?php echo e($report->report_number); ?></p>
                        </div>
                        <div class="d-flex gap-2">
                            
                            <?php if(
                                !$report->isConsideredFinished() &&
                                (
                                    $report->canBeEdited() ||
                                    $report->isSupervisor(Auth::id())
                                )
                            ): ?>
                            <a href="<?php echo e(route('cermat.reports.edit', $report)); ?>" class="btn btn-outline-<?php echo e($isRejected ? 'warning' : 'secondary'); ?> rounded-pill px-3">
                                <i class="bi bi-pencil-fill me-1"></i> <?php echo e($isRejected ? 'Revisi Laporan' : 'Edit Laporan'); ?>

                            </a>
                            <?php endif; ?>

                            
                            <?php if($isRejected && $report->isReporter($user->id)): ?>
                                <form action="<?php echo e(route('cermat.reports.resubmit', $report)); ?>" method="POST" id="resubmit-form-<?php echo e($report->id); ?>" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="button" id="btn-resubmit-<?php echo e($report->id); ?>" class="btn btn-success rounded-pill px-3">
                                        <i class="bi bi-arrow-clockwise me-1"></i> Ajukan Ulang
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if($report->canUserEdit()): ?>
                            <a href="<?php echo e(route('cermat.reports.edit', $report)); ?>" class="btn btn-warning">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                                Edit Laporan
                            </a>
                            <?php endif; ?>
                            <?php if (\Illuminate\Support\Facades\Blade::check('role', 'super-admin')): ?>
            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                </svg>
                Hapus Permanen
            </button>
            <?php endif; ?>

                            <a href="<?php echo e(route('cermat.reports.index')); ?>" class="btn btn-secondary rounded-pill px-3">
                                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                            </a>

                            <a href="<?php echo e(route('cermat.reports.downloadPdf', $report)); ?>" class="btn btn-dark rounded-pill px-3" target="_blank">
                                <i class="bi bi-file-pdf me-1"></i> Download PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            
            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4 layout-spacing">
                
                <div class="col-lg-8">
                    <div class="d-flex flex-column gap-4">

                    
                    <?php if(
                        $report->isSupervisor($user->id) &&
                        $report->supervisor_status === CermatReport::SUPERVISOR_STATUS_AWAITING &&
                        !$report->isConsideredFinished()
                    ): ?>
                        <div class="card detail-card border-0 bg-primary-subtle">
                            <div class="card-body d-flex align-items-center flex-wrap gap-2 p-3">
                                <div class="me-auto">
                                    <p class="mb-0 fw-bold text-primary-emphasis">
                                        <i class="bi bi-exclamation-diamond-fill me-2"></i>Keputusan Supervisor Diperlukan
                                    </p>
                                    <small class="text-primary-emphasis opacity-75">
                                        Persetujuan Anda diperlukan untuk validasi administratif laporan ini.
                                    </small>
                                </div>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#approvalModal">
                                    <i class="bi bi-check2-square me-1"></i> Tindak Lanjuti
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                    


                    
                    <?php switch($report->status):

                        case (CermatReport::STATUS_IN_REVIEW): ?>
                            
                            <?php if (\Illuminate\Support\Facades\Blade::check('role', 'hsse')): ?>
                            <?php if($report->hsse_status !== CermatReport::HSSE_STATUS_RECOMMENDED): ?>
                            <div class="card detail-card border-0 bg-info-subtle mt-2">
                                <div class="card-body d-flex align-items-center flex-wrap gap-2 p-3">
                                    <p class="mb-0 fw-bold text-info-emphasis me-3">
                                        <i class="bi bi-info-circle-fill"></i> Laporan ini memerlukan Review & Rekomendasi HSSE.
                                    </p>
                                    <a href="<?php echo e(route('cermat.reports.review', $report)); ?>" class="btn btn-info">
                                        <i class="bi bi-journal-check me-1"></i> Review & Tindakan
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>
                            <?php break; ?>

                        <?php case (CermatReport::STATUS_ACTION_IN_PROGRESS): ?>
                            <?php if (\Illuminate\Support\Facades\Blade::check('role', 'hsse')): ?>
                            <div class="card detail-card border-0 bg-warning-subtle">
                                <div class="card-body d-flex align-items-center flex-wrap gap-2 p-3">
                                    <p class="mb-0 fw-bold text-warning-emphasis me-3">
                                        <i class="bi bi-tools"></i> Tindakan perbaikan sedang berlangsung.
                                    </p>
                                    <a href="<?php echo e(route('cermat.reports.review', $report)); ?>" class="btn btn-warning text-dark">
                                        <i class="bi bi-card-checklist me-1"></i> Kelola Tindakan
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php break; ?>

                        
                        <?php case (CermatReport::STATUS_AWAITING_CLOSEOUT): ?>
                        <?php if (\Illuminate\Support\Facades\Blade::check('role', 'hsse')): ?>
                            <div class="card detail-card border-0 bg-primary-subtle">
                                <div class="card-body d-flex align-items-center flex-wrap gap-2 p-3">
                                    <p class="mb-0 fw-bold text-primary-emphasis me-3">
                                        <i class="bi bi-exclamation-diamond-fill"></i> Laporan ini siap untuk ditutup:
                                    </p>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#closeReportModal">
                                        <i class="bi bi-lock-fill me-1"></i> Tutup Laporan
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php break; ?>

                        
                        <?php case (CermatReport::STATUS_NO_ACTION_REQUIRED): ?>
                            <div class="card detail-card border-0 bg-info-subtle">
                                <div class="card-body p-3">
                                    <p class="mb-0 fw-bold text-info-emphasis">
                                        <i class="bi bi-info-circle-fill me-1"></i>
                                        Laporan ini telah ditinjau dan tidak memerlukan tindakan lebih lanjut.
                                    </p>
                                </div>
                            </div>
                            <?php break; ?>

                        
                        <?php case (CermatReport::STATUS_CLOSED): ?>
                            <div class="card detail-card border-0 bg-success-subtle">
                                <div class="card-body p-3">
                                    <p class="mb-0 fw-bold text-success-emphasis">
                                        <i class="bi bi-check-circle-fill me-1"></i>
                                        Laporan ini telah selesai dan ditutup pada <?php echo e($report->close_out_at?->format('d M Y') ?? ''); ?>.
                                    </p>
                                </div>
                            </div>
                            <?php break; ?>
                    <?php endswitch; ?>

                        
                        <?php if($isRejected && $report->rejection_reason): ?>
                        <div class="card detail-card border-start border-4 border-danger">
                            <div class="card-header py-3">
                                <h5 class="mb-0 card-title fw-bold text-danger">
                                    <i class="bi bi-journal-x-fill me-2"></i>
                                    Alasan Penolakan Supervisor
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="detail-value long-text fst-italic">"<?php echo e($report->rejection_reason); ?>"</p>
                                <p class="text-muted small mb-0 mt-2">
                                    <i class="bi bi-clock me-1"></i> Ditolak pada: <?php echo e($report->supervisor_rejected_at?->format('d M Y, H:i') ?? '-'); ?>

                                </p>
                            </div>
                        </div>
                        <?php elseif($report->status === CermatReport::STATUS_NO_ACTION_REQUIRED && $report->supervisor_notes): ?>
                        <div class="card detail-card border-start border-4 border-info">
                            <div class="card-header py-3">
                                <h5 class="mb-0 card-title fw-bold text-info">
                                    <i class="bi bi-info-circle-fill me-2"></i>
                                    Catatan Keputusan Supervisor
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="detail-value long-text fst-italic">"<?php echo e($report->supervisor_notes); ?>"</p>
                                <p class="text-muted small mb-0 mt-2">
                                    <i class="bi bi-clock me-1"></i> Diputuskan pada: <?php echo e($report->supervisor_approved_at?->format('d M Y, H:i') ?? '-'); ?>

                                </p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- CARD DETAIL UTAMA -->
                        <div class="card detail-card widget-content widget-content-area br-8">
                            <div class="card-header py-3">
                                <h5 class="mb-0 card-title fw-bold"><i class="bi bi-info-circle-fill me-2"></i>Informasi Utama Laporan</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <span class="detail-label">Uraian Kejadian / Temuan</span>
                                        <p class="detail-value long-text"><?php echo e($report->details); ?></p>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <span class="detail-label">Tanggal & Waktu Kejadian</span>
                                        <p class="detail-value"><?php echo e($report->report_datetime->format('d M Y, H:i')); ?></p>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <span class="detail-label">Area</span>
                                        <p class="detail-value"><?php echo e($report->area->name ?? '-'); ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <span class="detail-label">Detail Lokasi Spesifik</span>
                                        <p class="detail-value"><?php echo e($report->location_details ?? '-'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CARD ANALISIS & KLASIFIKASI -->
                        <div class="card detail-card widget-content widget-content-area br-8">
                            <div class="card-header py-3">
                                <h5 class="mb-0 card-title fw-bold"><i class="bi bi-clipboard-data-fill me-2"></i>Analisis & Klasifikasi Pelapor</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <span class="detail-label">Unsafe Act(s)</span>
                                        <div class="detail-value">
                                            <?php $__empty_1 = true; $__currentLoopData = $report->unsafeActs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $act): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <span class="badge bg-warning text-dark me-1 mb-1"><?php echo e($act->description); ?></span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <span class="text-muted fst-italic small">Tidak ada</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <span class="detail-label">Unsafe Condition(s)</span>
                                        <div class="detail-value">
                                            <?php $__empty_1 = true; $__currentLoopData = $report->unsafeConditions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $condition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <span class="badge bg-danger text-white me-1 mb-1"><?php echo e($condition->description); ?></span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <span class="text-muted fst-italic small">Tidak ada</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <?php if($report->hsse_reviewed_at): ?>
                        <div class="card detail-card widget-content widget-content-area br-8">
                            <div class="card-header py-3">
                                <h5 class="mb-0 card-title fw-bold"><i class="bi bi-person-workspace me-2"></i>Klasifikasi & Review HSSE</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-md-4 col-6">
                                        <span class="detail-label">Tipe Kejadian</span>
                                        <p class="detail-value"><?php echo e(Str::of($report->event_type)->title()); ?></p>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <span class="detail-label">Klasifikasi</span>
                                        <p class="detail-value"><?php echo e(Str::of($report->classification)->replace('_', ' ')->title()); ?></p>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <span class="detail-label">Sirkulasi Terbatas</span>
                                        <p class="detail-value"><?php echo $report->is_limited_circulation ? '<span class="badge bg-danger">Ya</span>' : '<span class="badge bg-secondary">Tidak</span>'; ?></p>
                                    </div>
                                    <?php if($report->synergi_register_no): ?>
                                    <div class="col-md-4 col-6">
                                        <span class="detail-label">P-horse  </span>
                                        <p class="detail-value"><?php echo e($report->synergi_register_no); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    <div class="col-md-4 col-6">
                                        <span class="detail-label">Direview oleh</span>
                                        <p class="detail-value"><?php echo e($report->hsseOfficer->name ?? 'N/A'); ?></p>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <span class="detail-label">Tanggal Review</span>
                                        <p class="detail-value"><?php echo e($report->hsse_reviewed_at?->format('d M Y, H:i') ?? '-'); ?></p>
                                    </div>

                                    <?php if($report->short_term_mitigation): ?>
                                    <div class="col-12">
                                        <span class="detail-label">Mitigasi Jangka Pendek (HSSE)</span>
                                        <p class="detail-value long-text"><?php echo e($report->short_term_mitigation); ?></p>
                                    </div>
                                    <?php endif; ?>

                                    <?php if($report->cost_to_business_actual || $report->cost_to_business_potential): ?>
                                    <div class="col-12 border-top pt-3">
                                        <h6 class="mb-2">Dampak Finansial (USD)</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <span class="detail-label">Kerugian Aktual</span>
                                                <p class="detail-value"><?php echo e(number_format($report->cost_to_business_actual ?? 0, 2)); ?></p>
                                            </div>
                                            <div class="col-md-4">
                                                <span class="detail-label">Potensi Kerugian</span>
                                                <p class="detail-value"><?php echo e(number_format($report->cost_to_business_potential ?? 0, 2)); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- CARD PENILAIAN RISIKO -->
                        <?php if($report->riskAssessments->isNotEmpty()): ?>
                        <?php
                            $initialRisks = $report->riskAssessments->where('type', 'initial');
                            $residualRisks = $report->riskAssessments->where('type', 'residual');
                        ?>
                        <div class="card detail-card widget-content widget-content-area br-8">
                            <div class="card-header py-3">
                                <h5 class="mb-0 card-title fw-bold"><i class="bi bi-shield-shaded me-2"></i>Penilaian Risiko</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <?php if($initialRisks->isNotEmpty()): ?>
                                    <div class="col-lg-6">
                                        <h6 class="mb-3">Initial Risk</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Kategori Dampak</th>
                                                        <th class="text-center">Aktual</th>
                                                        <th class="text-center">Potensial</th>
                                                        <th class="text-center">Likelihood</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $__currentLoopData = $initialRisks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $risk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td><?php echo e(Str::of($risk->consequence_category)->replace('_', ' ')->title()); ?></td>
                                                        <td class="text-center"><?php echo e($risk->real_severity ?? '-'); ?></td>
                                                        <td class="text-center"><?php echo e($risk->potential_severity ?? '-'); ?></td>
                                                        <td class="text-center"><?php echo e($risk->likelihood ?? '-'); ?></td>
                                                    </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <?php if($residualRisks->isNotEmpty()): ?>
                                    <div class="col-lg-6">
                                        <h6 class="mb-3">Residual Risk</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Kategori Dampak</th>
                                                        <th class="text-center">Potensial</th>
                                                        <th class="text-center">Likelihood</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $__currentLoopData = $residualRisks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $risk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td><?php echo e(Str::of($risk->consequence_category)->replace('_', ' ')->title()); ?></td>
                                                        <td class="text-center"><?php echo e($risk->potential_severity ?? '-'); ?></td>
                                                        <td class="text-center"><?php echo e($risk->likelihood ?? '-'); ?></td>
                                                    </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- CARD TINDAK LANJUT & OTORISASI -->
                        <div class="card detail-card widget-content widget-content-area br-8">
                            <div class="card-header py-3">
                                <h5 class="mb-0 card-title fw-bold"><i class="bi bi-shield-fill-check me-2"></i>Tindak Lanjut & Otorisasi</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <span class="detail-label">Tindakan Langsung yang Diambil</span>
                                        <p class="detail-value long-text"><?php echo e($report->immediate_action_taken); ?></p>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <span class="detail-label">Pelapor</span>
                                        <p class="detail-value"><?php echo e($report->reporter->name ?? '-'); ?></p>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <span class="detail-label">Atasan Langsung</span>
                                        <p class="detail-value"><?php echo e($report->lineSupervisor->name ?? '-'); ?></p>
                                    </div>
                                    <div class="col-md-2 col-6">
                                        <span class="detail-label">STOP Job?</span>
                                        <p class="detail-value"><?php echo $report->stop_card_issued ? '<span class="badge bg-success">Ya</span>' : '<span class="badge bg-secondary">Tidak</span>'; ?></p>
                                    </div>
                                    <?php if($report->suggestion_for_improvement): ?>
                                    <div class="col-12">
                                        <span class="detail-label">Saran Perbaikan Pelapor</span>
                                        <p class="detail-value long-text"><?php echo e($report->suggestion_for_improvement); ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- CARD PROGRES TINDAKAN PERBAIKAN -->
                        <?php
                            $totalActions = $report->actionItems->count();
                            $completedCount = 0;
                            $progressPercentage = 0;
                            if ($totalActions > 0) {
                                $completedCount = $report->actionItems->whereIn('status', [ActionItem::STATUS_COMPLETED, ActionItem::STATUS_CANT_DO])->count();
                                $progressPercentage = ($completedCount / $totalActions) * 100;
                            }
                        ?>
                        <div class="card detail-card widget-content widget-content-area br-8">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap">
                                <h5 class="mb-0 card-title fw-bold"><i class="bi bi-kanban-fill me-2"></i>Progres Tindakan Perbaikan</h5>
                                <?php if($report->hsse_reviewed_at): ?>
                                <a href="<?php echo e(route('cermat.reports.review', $report)); ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    <i class="bi bi-card-checklist me-1"></i> Kelola Tindakan
                                </a>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <?php if($totalActions > 0): ?>
                                    
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold">Progres Penyelesaian</span>
                                            <span class="fw-bold"><?php echo e($completedCount); ?> / <?php echo e($totalActions); ?> Tuntas</span>
                                        </div>
                                        <div class="progress" role="progressbar" aria-valuenow="<?php echo e($progressPercentage); ?>" aria-valuemin="0" aria-valuemax="100" style="height: 1.25rem;">
                                            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated fw-bold" style="width: <?php echo e($progressPercentage); ?>%"><?php echo e(round($progressPercentage)); ?>%</div>
                                        </div>
                                    </div>

                                    
                                    <div class="accordion" id="actionItemsAccordion">
                                        <?php $__currentLoopData = $report->actionItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $isCompleted = in_array($item->status, [ActionItem::STATUS_COMPLETED, ActionItem::STATUS_CANT_DO]);
                                                $statusConfig = match($item->status) {
                                                    ActionItem::STATUS_DO => ['class' => 'secondary', 'icon' => 'clipboard'],
                                                    ActionItem::STATUS_IN_PROGRESS => ['class' => 'info', 'icon' => 'arrow-repeat'],
                                                    ActionItem::STATUS_COMPLETED => ['class' => 'success', 'icon' => 'check2-circle-fill'],
                                                    ActionItem::STATUS_CANT_DO => ['class' => 'danger', 'icon' => 'x-circle-fill'],
                                                    default => ['class' => 'secondary', 'icon' => 'question-circle'],
                                                };
                                            ?>
                                            <div class="accordion-item <?php if($isCompleted): ?> accordion-item-completed <?php endif; ?>">
                                                <h2 class="accordion-header" id="heading-<?php echo e($item->id); ?>">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo e($item->id); ?>" aria-expanded="false" aria-controls="collapse-<?php echo e($item->id); ?>">
                                                        <div class="d-flex w-100 align-items-center pe-2">
                                                            <i class="bi bi-<?php echo e($statusConfig['icon']); ?> fs-5 me-3 text-<?php echo e($statusConfig['class']); ?> status-icon"></i>
                                                            <span class="fw-bold flex-grow-1"><?php echo e($item->description); ?></span>
                                                            <span class="badge bg-<?php echo e($statusConfig['class']); ?>-subtle text-<?php echo e($statusConfig['class']); ?>-emphasis border border-<?php echo e($statusConfig['class']); ?>-subtle rounded-pill ms-3"><?php echo e(Str::of($item->status)->replace('_', ' ')->title()); ?></span>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="collapse-<?php echo e($item->id); ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo e($item->id); ?>" data-bs-parent="#actionItemsAccordion">
                                                    <div class="accordion-body">
                                                        <div class="d-flex flex-wrap gap-4 mb-3 pb-3 border-bottom">
                                                            <div>
                                                                <div class="detail-label">Penanggung Jawab</div>
                                                                <div class="detail-value"><i class="bi bi-person-fill me-1"></i><?php echo e($item->responsible->name ?? 'N/A'); ?></div>
                                                            </div>
                                                            <div>
                                                                <div class="detail-label">Kategori Aksi</div>
                                                                <div class="detail-value"><?php echo e($item->actionCategory->name ?? 'N/A'); ?></div>
                                                            </div>
                                                            <div>
                                                                <div class="detail-label">Target Awal</div>
                                                                <div class="detail-value"><i class="bi bi-calendar-event me-1"></i><?php echo e($item->target_date->isoFormat('DD MMMM Y')); ?></div>
                                                            </div>
                                                            <?php if($item->target_date->isPast() && !$isCompleted): ?>
                                                                <div>
                                                                    <div class="detail-label">Keterangan</div>
                                                                    <div class="detail-value text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>Terlambat</div>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>

                                                        <?php if($isCompleted && ($item->completion_notes || $item->photos->isNotEmpty())): ?>
                                                            <div class="d-flex flex-column gap-3">
                                                                <?php if($item->completion_notes): ?>
                                                                    <div>
                                                                        <div class="detail-label">Catatan Penyelesaian</div>
                                                                        <div class="completion-notes">"<?php echo e($item->completion_notes); ?>"</div>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <?php if($item->photos->isNotEmpty()): ?>
                                                                    <div>
                                                                        <div class="detail-label">Bukti Foto</div>
                                                                        <div class="proof-gallery">
                                                                            <?php $__currentLoopData = $item->photos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                            <div class="proof-item attachment-item" data-bs-toggle="modal" data-bs-target="#imageModal" data-img-src="<?php echo e(asset('storage/' . $photo->file_path)); ?>" data-img-title="Bukti Tindakan: <?php echo e(Str::limit($item->description, 40)); ?>">
                                                                                <img src="<?php echo e(asset('storage/' . $photo->file_path)); ?>" alt="Bukti Foto">
                                                                            </div>
                                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <p class="text-muted fst-italic small">Belum ada detail penyelesaian.</p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>

                                <?php else: ?>
                                    <div class="text-center text-muted p-4 bg-light rounded">
                                        <i class="bi bi-clipboard-x fs-2"></i>
                                        <p class="mb-1 mt-2 fw-bold">Belum Ada Tindakan Perbaikan</p>
                                        <p class="small mb-0">Tindakan perbaikan akan muncul di sini setelah HSSE merekomendasikannya.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        
                        <?php if($report->status === CermatReport::STATUS_CLOSED && $report->close_out_description): ?>
                        <div class="card detail-card border-start border-4 border-success widget-content widget-content-area br-8">
                            <div class="card-header py-3">
                                <h5 class="mb-0 card-title fw-bold text-success">
                                    <i class="bi bi-lock-fill me-2"></i>Informasi Penutupan
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <span class="detail-label">Ditutup oleh</span>
                                        <p class="detail-value"><?php echo e($report->closeOutPerformer->name ?? '-'); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="detail-label">Tanggal Penutupan</span>
                                        <p class="detail-value"><?php echo e($report->close_out_at?->format('d M Y, H:i') ?? '-'); ?></p>
                                    </div>
                                    <div class="col-12">
                                        <span class="detail-label">Deskripsi Penutupan</span>
                                        <p class="detail-value long-text"><?php echo e($report->close_out_description); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>

                
                <div class="col-lg-4">
                    <div class="position-sticky d-flex flex-column gap-4" style="top: 2rem;">
                        <!-- GALERI LAMPIRAN -->
                        <div class="card widget-content widget-content-area br-8">
                            <div class="card-header py-3">
                                <h5 class="mb-0 card-title fw-bold"><i class="bi bi-images me-2"></i>Galeri Lampiran</h5>
                            </div>
                            <div class="card-body">
                                <?php if($report->attachments->isNotEmpty()): ?>
                                <div class="row g-2 attachment-gallery">
                                    <?php $__currentLoopData = $report->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="col-4">
                                        <a href="#" class="attachment-item" data-bs-toggle="modal" data-bs-target="#imageModal" data-img-src="<?php echo e(asset('storage/' . $attachment->file_path)); ?>" data-img-title="<?php echo e($attachment->file_name); ?>">
                                            <img src="<?php echo e(asset('storage/' . $attachment->file_path)); ?>" alt="<?php echo e($attachment->file_name); ?>">
                                            <div class="attachment-overlay"><i class="bi bi-arrows-fullscreen fs-4"></i></div>
                                        </a>
                                    </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                                <?php else: ?>
                                <div class="text-center text-muted p-3">
                                    <i class="bi bi-paperclip fs-2"></i>
                                    <p class="mb-0 mt-2">Tidak ada lampiran.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- RIWAYAT / LOG PERUBAHAN -->
                        <div class="card widget-content widget-content-area br-8">
                            <div class="card-header py-3">
                                <h5 class="mb-0 card-title fw-bold"><i class="bi bi-clock-history me-2"></i>Riwayat Perubahan</h5>
                            </div>
                            <div class="list-group list-group-flush" style="max-height: 450px; overflow-y: auto;">
                                <?php $__empty_1 = true; $__currentLoopData = $audits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $audit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="list-group-item p-3">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div>
                                            <p class="mb-1 fw-bold"><?php echo e($audit->user->name ?? 'Sistem'); ?></p>
                                            <small class="text-muted d-block">
                                                <?php if($audit->event === 'created'): ?>
                                                    Membuat laporan ini.
                                                <?php elseif($audit->event === 'updated'): ?>
                                                    Memperbarui laporan.
                                                <?php else: ?>
                                                    Melakukan aksi: <span class="badge bg-light text-dark fw-normal"><?php echo e($audit->event); ?></span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <small class="text-muted text-nowrap ms-3"><?php echo e($audit->created_at->diffForHumans()); ?></small>
                                    </div>

                                    <?php if($audit->event === 'updated' && count($audit->getModified())): ?>
                                    <div class="mt-2 ps-2 border-start border-2 border-secondary-subtle">
                                        <ul class="list-unstyled mb-0" style="font-size: 0.8rem;">
                                            <?php $__currentLoopData = $audit->getModified(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attribute => $modified): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $formatValue = function ($value) {
                                                    if (is_null($value)) return '<em>(kosong)</em>';
                                                    if (is_bool($value)) return $value ? '<span class="badge bg-success-subtle text-success-emphasis">Ya</span>' : '<span class="badge bg-danger-subtle text-danger-emphasis">Tidak</span>';
                                                    if (is_array($value)) return '<pre class="mb-0"><code>' . e(json_encode($value, JSON_PRETTY_PRINT)) . '</code></pre>';
                                                    return e(Str::limit($value, 80));
                                                };
                                            ?>
                                            <li class="mb-1">
                                                <strong class="fw-semibold"><?php echo e(Str::of($attribute)->replace('_', ' ')->title()); ?></strong> diubah dari
                                                <span class="text-danger"><?php echo $formatValue(Arr::get($modified, 'old')); ?></span> menjadi
                                                <span class="text-success fw-bold"><?php echo $formatValue(Arr::get($modified, 'new')); ?></span>.
                                            </li>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="list-group-item text-center text-muted p-4">
                                    <p class="mb-0">Belum ada riwayat perubahan.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>


<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Lihat Lampiran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid" alt="Preview">
            </div>
        </div>
    </div>
</div>

<?php if (\Illuminate\Support\Facades\Blade::check('role', 'super-admin')): ?>
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold" id="deleteModalLabel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    Konfirmasi Penghapusan Permanen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger mb-3" role="alert">
                    <strong>PERINGATAN!</strong> Tindakan ini tidak dapat dibatalkan.
                </div>
                <p class="mb-3">
                    Anda akan <strong>menghapus permanen</strong> laporan berikut:
                </p>
                <div class="card bg-light">
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="fw-bold" style="width: 40%">No. Laporan:</td>
                                <td><?php echo e($report->report_number); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Pelapor:</td>
                                <td><?php echo e($report->reporter->name ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Tanggal:</td>
                                <td><?php echo e($report->report_datetime->format('d M Y, H:i')); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Area:</td>
                                <td><?php echo e($report->area->name ?? '-'); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-muted small">
                    Data yang akan dihapus meliputi: laporan utama, lampiran file, foto tindakan, riwayat audit, dan semua data terkait.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    Batal
                </button>
                <form id="deleteForm" action="<?php echo e(route('cermat.reports.destroy', $report)); ?>" method="POST" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-danger" id="confirmDeleteBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                        Ya, Hapus Permanen
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($report->isSupervisor(Auth::id()) && $report->supervisor_status === CermatReport::SUPERVISOR_STATUS_AWAITING): ?>
<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="<?php echo e(route('cermat.reports.submitApproval', $report)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="approvalModalLabel">Keputusan Laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Pilih tindakan yang akan diambil untuk laporan ini:</p>

                    <?php if($errors->any()): ?>
                        
                        <?php if(old('decision') && (old('decision') === 'reject' || old('decision') === 'no_action')): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="mb-3">
                        
                        <div class="form-check p-3 border rounded mb-3 hover-bg-light" style="cursor: pointer;">
                            <input class="form-check-input" type="radio" name="decision" id="decisionApprove"
                                   value="approve" required <?php echo e(old('decision') == 'approve' ? 'checked' : ''); ?>>
                            <label class="form-check-label w-100" for="decisionApprove" style="cursor: pointer;">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
                                    <div>
                                        <strong class="d-block">Setujui & Lanjutkan</strong>
                                        <small class="text-muted">Laporan akan diteruskan ke HSSE untuk review lebih lanjut.</small>
                                    </div>
                                </div>
                            </label>
                        </div>

                        
                        <div class="form-check p-3 border rounded mb-3 hover-bg-light" style="cursor: pointer;">
                            <input class="form-check-input" type="radio" name="decision" id="decisionNoAction"
                                   value="no_action" <?php echo e(old('decision') == 'no_action' ? 'checked' : ''); ?>>
                            <label class="form-check-label w-100" for="decisionNoAction" style="cursor: pointer;">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-info-circle-fill text-info fs-4 me-3"></i>
                                    <div>
                                        <strong class="d-block">Tidak Perlu Tindakan</strong>
                                        <small class="text-muted">Laporan diterima, tetapi tidak memerlukan tindakan perbaikan lebih lanjut.</small>
                                    </div>
                                </div>
                            </label>
                        </div>

                        
                        <div class="form-check p-3 border rounded hover-bg-light" style="cursor: pointer;">
                            <input class="form-check-input" type="radio" name="decision" id="decisionReject"
                                   value="reject" <?php echo e(old('decision') == 'reject' ? 'checked' : ''); ?>>
                            <label class="form-check-label w-100" for="decisionReject" style="cursor: pointer;">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-x-circle-fill text-danger fs-4 me-3"></i>
                                    <div>
                                        <strong class="d-block">Tolak Laporan</strong>
                                        <small class="text-muted">Laporan tidak valid. Reporter dapat merevisi dan mengajukan ulang.</small>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    
                    <div class="mb-3">
                        <label for="supervisor_notes" class="form-label fw-semibold" id="notesLabel">
                            Catatan (Opsional)
                        </label>
                        <textarea class="form-control <?php $__errorArgs = ['supervisor_notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                  id="supervisor_notes"
                                  name="supervisor_notes"
                                  rows="4"
                                  placeholder="Berikan alasan atau detail tambahan..."><?php echo e(old('supervisor_notes')); ?></textarea>
                        <small class="form-text text-muted" id="notesHint">
                            Catatan ini akan terlihat oleh reporter dan tim terkait.
                        </small>
                        <?php $__errorArgs = ['supervisor_notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send-fill me-1"></i> Kirim Keputusan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>


<?php if($report->status === CermatReport::STATUS_AWAITING_CLOSEOUT): ?>
<div class="modal fade" id="closeReportModal" tabindex="-1" aria-labelledby="closeReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?php echo e(route('cermat.reports.submitClose', $report)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="closeReportModalLabel">
                        <i class="bi bi-lock-fill me-2"></i>Tutup Laporan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Semua tindakan perbaikan telah selesai. Anda dapat menutup laporan ini sekarang.
                    </div>

                    <?php if($errors->any() && old('close_out_description')): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="close_out_description" class="form-label fw-semibold">
                            Deskripsi Penutupan <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control <?php $__errorArgs = ['close_out_description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                  id="close_out_description"
                                  name="close_out_description"
                                  rows="4"
                                  required
                                  placeholder="Berikan ringkasan penyelesaian dan konfirmasi bahwa semua tindakan telah selesai..."><?php echo e(old('close_out_description')); ?></textarea>
                        <small class="form-text text-muted">
                            Jelaskan bahwa laporan ini telah diselesaikan dengan baik.
                        </small>
                        <?php $__errorArgs = ['close_out_description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-lock-fill me-1"></i> Tutup Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'super-admin')): ?>
function confirmDelete() {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

// Handle form submission via AJAX for better UX
document.getElementById('deleteForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const btn = document.getElementById('confirmDeleteBtn');
    const originalBtnHtml = btn.innerHTML;

    // Disable button and show loading
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menghapus...';

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            _method: 'DELETE'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            const alertHtml = `
                <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <strong>Berhasil!</strong><span class="ms-1">${data.message}</span>
                    </div>
                </div>
            `;

            // Redirect to index page after short delay
            setTimeout(() => {
                window.location.href = '<?php echo e(route("cermat.reports.index")); ?>';
            }, 1000);
        } else {
            throw new Error(data.message || 'Terjadi kesalahan');
        }
    })
    .catch(error => {
        // Show error message
        btn.disabled = false;
        btn.innerHTML = originalBtnHtml;

        alert('Gagal menghapus laporan: ' + error.message);
        console.error('Delete error:', error);
    });
});
<?php endif; ?>
    // Image Modal Handler
    document.addEventListener('DOMContentLoaded', function() {
        // --- Image Modal ---
        const imageModal = document.getElementById('imageModal');
        if (imageModal) {
            imageModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const imgSrc = button.getAttribute('data-img-src');
                const imgTitle = button.getAttribute('data-img-title');

                const modalImage = imageModal.querySelector('#modalImage');
                const modalTitle = imageModal.querySelector('#imageModalLabel');

                modalImage.src = imgSrc;
                modalTitle.textContent = imgTitle || 'Lihat Lampiran';
            });
        }

        // --- Approval Modal - Dynamic Notes Label & Hint ---
        const approvalModal = document.getElementById('approvalModal');
        if (approvalModal) {
            const radioButtons = approvalModal.querySelectorAll('input[name="decision"]');
            const notesLabel = document.getElementById('notesLabel');
            const notesHint = document.getElementById('notesHint');
            const notesTextarea = document.getElementById('supervisor_notes');

            const updateNotesState = (decisionValue) => {
                if (decisionValue === 'reject') {
                    notesLabel.innerHTML = 'Alasan Penolakan <span class="text-danger">*</span>';
                    notesTextarea.required = true;
                    notesTextarea.placeholder = 'Jelaskan alasan penolakan dan apa yang perlu diperbaiki...';
                    notesHint.textContent = 'Catatan wajib diisi untuk penolakan. Berikan panduan yang jelas untuk perbaikan.';
                } else if (decisionValue === 'no_action') {
                    notesLabel.innerHTML = 'Catatan Keputusan <span class="text-danger">*</span>';
                    notesTextarea.required = true;
                    notesTextarea.placeholder = 'Jelaskan mengapa laporan ini tidak memerlukan tindakan lanjut...';
                    notesHint.textContent = 'Catatan wajib diisi untuk keputusan "Tidak Perlu Tindakan Lanjut".';
                } else {
                    notesLabel.innerHTML = 'Catatan (Opsional)';
                    notesTextarea.required = false;
                    notesTextarea.placeholder = 'Berikan komentar atau instruksi tambahan jika diperlukan...';
                    notesHint.textContent = 'Catatan ini akan terlihat oleh reporter dan tim terkait.';
                }
            };

            radioButtons.forEach(radio => {
                radio.addEventListener('change', function() {
                    updateNotesState(this.value);
                });
            });

            // Trigger change event on page load if there's an old value or initialize default
            const checkedRadio = approvalModal.querySelector('input[name="decision"]:checked');
            if (checkedRadio) {
                updateNotesState(checkedRadio.value);
            } else {
                 // Initialize default state ('approve') if no decision selected
                 updateNotesState('approve');
            }
        }

        // --- Auto-reopen modals if there are validation errors ---
        <?php if($errors->any() && old('decision')): ?>
            const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
            modal.show();
        <?php endif; ?>

        <?php if($errors->any() && old('close_out_description')): ?>
            const closeModal = new bootstrap.Modal(document.getElementById('closeReportModal'));
            closeModal.show();
        <?php endif; ?>

        // --- Resubmit Confirmation ---
        const resubmitButton = document.getElementById('btn-resubmit-<?php echo e($report->id); ?>');

        if (resubmitButton) {
            resubmitButton.addEventListener('click', function (e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Ajukan Ulang Laporan?',
                    text: "Pastikan Anda telah merevisi laporan sesuai alasan penolakan. Laporan akan dikirim kembali untuk review.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Ajukan Ulang!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        const resubmitForm = document.getElementById('resubmit-form-<?php echo e($report->id); ?>');
                        if(resubmitForm) {
                            resubmitButton.innerHTML = `
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Mengajukan...
                            `;
                            resubmitButton.disabled = true;

                            resubmitForm.submit();
                        }
                    }
                });
            });
        }
    });

</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/cermat/show.blade.php ENDPATH**/ ?>