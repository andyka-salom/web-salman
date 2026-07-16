<?php
    $user = Auth::user();
    $isEdit = isset($report) && $report->exists;

    // LOGIKA PERMISSION KHUSUS HSSE
    $isHsseReview = $isEdit && $user->hasRole('hsse') && $report->reporter_id !== $user->id;

    $disabledAttr = $isHsseReview ? 'disabled' : '';
    $readonlyAttr = $isHsseReview ? 'readonly' : '';

    if ($isHsseReview) {
        $pageTitle = 'Review Klasifikasi Laporan #' . $report->report_number;
        $submitText = 'Simpan Klasifikasi';
    } else {
        $pageTitle = $isEdit ? 'Edit Laporan #' . $report->report_number : 'Buat Laporan CeRMAT / Teman';
        $submitText = $isEdit ? 'Simpan Perubahan' : 'Kirim Laporan';
    }

    $actionRoute = $isEdit ? route('cermat.reports.update', $report) : route('cermat.reports.store');
    $cancelRoute = $isEdit ? route('cermat.reports.show', $report) : route('cermat.reports.index');

    // Helper Data Fetching
    $getData = function($key, $default = null) use ($isEdit, $report) {
        return old($key, $isEdit ? ($report->$key ?? $default) : $default);
    };

    $getArrayData = function($relationName, $oldKey) use ($isEdit, $report) {
        if (old($oldKey) !== null) return old($oldKey);
        return $isEdit ? $report->$relationName->pluck('id')->toArray() : [];
    };

    // Supervisor Info
    $supervisorName = $supervisor ? $supervisor->name : 'Langsung ke HSSE (Tidak ada hirarki)';
    $supervisorId = $supervisor ? $supervisor->id : null;
    $supervisorNote = $supervisor
        ? 'Laporan akan diteruskan ke supervisor Anda'
        : 'Karena Anda tidak memiliki hirarki, laporan akan langsung ditinjau oleh tim HSSE';
?>



<?php $__env->startSection('title', $pageTitle); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/src/sweetalerts2/sweetalerts2.css')); ?>">

<style>
    /* UI Consistency */
    .widget-content-area { border-radius: 8px; }
    .form-section-header {
        display: flex; align-items: center; gap: 0.75rem; padding-bottom: 0.75rem;
        border-bottom: 1px solid #e5e7eb; margin-bottom: 1.5rem;
    }
    .step-circle {
        display: flex; align-items: center; justify-content: center;
        width: 32px; height: 32px; flex-shrink: 0;
        border-radius: 50%; background-color: #e0e7ff; color: #4338ca;
        font-weight: 700; font-size: 0.9rem;
    }

    /* TomSelect Validation Visual Fix */
    .ts-wrapper.is-invalid .ts-control { border-color: #e7515a !important; }

    /* Dropzone Area */
    .dropzone-container {
        border: 2px dashed #cbd5e1; border-radius: 0.75rem; padding: 2rem 1rem;
        text-align: center; background-color: #f8fafc; cursor: pointer;
        transition: all 0.2s ease-in-out;
    }
    .dropzone-container:hover, .dropzone-container.dragover {
        border-color: #4338ca; background-color: #eef2ff;
    }

    .attachment-item {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 0.5rem;
        padding: 0.75rem; display: flex; align-items: center; gap: 1rem; margin-bottom: 0.75rem;
    }
    .attachment-preview { width: 56px; height: 56px; border-radius: 6px; object-fit: cover; }

    /* Camera Modal */
    #camera-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0;
                     width: 100%; height: 100%; background-color: rgba(0,0,0,0.9); }
    #camera-modal.show { display: flex; align-items: center; justify-content: center; }
    #camera-container { position: relative; max-width: 90%; max-height: 90vh; }
    #camera-video { width: 100%; height: auto; border-radius: 12px; }
    #camera-canvas { display: none; }
    .camera-controls { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%);
                       display: flex; gap: 1rem; }
    .camera-btn { width: 60px; height: 60px; border-radius: 50%; border: 3px solid #fff;
                  background: rgba(255,255,255,0.2); backdrop-filter: blur(10px);
                  cursor: pointer; display: flex; align-items: center; justify-content: center;
                  transition: all 0.3s ease; }
    .camera-btn:hover { background: rgba(255,255,255,0.4); transform: scale(1.1); }
    .camera-btn i { font-size: 24px; color: #fff; }
    #capture-btn { width: 70px; height: 70px; background: #4338ca; border-color: #4338ca; }

    /* Supervisor Info Box */
    .supervisor-info-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 1.25rem;
        color: white;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .supervisor-info-box .icon-box {
        width: 48px;
        height: 48px;
        background: rgba(255,255,255,0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(10px);
    }

    @media (min-width: 992px) { .sticky-sidebar { position: sticky; top: 1.5rem; z-index: 10; } }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">
            <div class="col-12">

                
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                    <div>
                        <h3 class="fw-bold mb-0 text-dark"><?php echo e($pageTitle); ?></h3>
                        <p class="text-muted mb-0 small">
                            <?php if($isHsseReview): ?>
                                <i class="bi bi-shield-lock me-1"></i> Mode Review HSSE (Hanya klasifikasi yang dapat diubah)
                            <?php else: ?>
                                Silakan lengkapi detail temuan HSSE di bawah ini.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <form action="<?php echo e($actionRoute); ?>" method="POST" id="cermat-form" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <?php if($isEdit): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

                    
                    <input type="hidden" name="line_supervisor_id" value="<?php echo e($supervisorId); ?>">

                    
                    <?php
                        $feedbackValue = $getData('requires_feedback', '1'); // Default: Ya (1)
                    ?>
                    <input type="hidden" name="requires_feedback" value="<?php echo e($feedbackValue); ?>">

                    
                    <?php if($isHsseReview): ?>
                        <input type="hidden" name="details" value="<?php echo e($getData('details')); ?>">
                        <input type="hidden" name="report_datetime" value="<?php echo e($getData('report_datetime')); ?>">
                        <input type="hidden" name="area_id" value="<?php echo e($getData('area_id')); ?>">
                        <input type="hidden" name="location_details" value="<?php echo e($getData('location_details')); ?>">
                        <input type="hidden" name="immediate_action_taken" value="<?php echo e($getData('immediate_action_taken')); ?>">
                        <input type="hidden" name="stop_card_issued" value="<?php echo e($getData('stop_card_issued')); ?>">
                        <input type="hidden" name="statement" value="1">
                    <?php endif; ?>

                    <div class="row g-4">
                        
                        <div class="col-lg-8">
                            <div class="d-flex flex-column gap-4">

                                
                                <div class="card shadow-sm border-0 br-8">
                                    <div class="card-body p-4">
                                        <div class="form-section-header">
                                            <div class="step-circle" <?php if($isHsseReview): ?> style="background:#eee;color:#999" <?php endif; ?>>1</div>
                                            <h5 class="mb-0 fw-bold">Detail Kejadian</h5>
                                            <?php if($isHsseReview): ?> <span class="badge bg-light-dark text-dark ms-auto">Read Only</span> <?php endif; ?>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label fw-bold">Uraian / Kronologi <span class="text-danger">*</span></label>
                                            <textarea name="details" class="form-control <?php $__errorArgs = ['details'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" rows="5" <?php echo e($disabledAttr); ?> placeholder="Jelaskan secara detail temuan Anda..."><?php echo e($getData('details')); ?></textarea>
                                            <div class="invalid-feedback"><?php $__errorArgs = ['details'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo e($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                                        </div>

                                        <div class="row g-3 mb-4">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Waktu Kejadian <span class="text-danger">*</span></label>
                                                <?php
                                                    $dtVal = $getData('report_datetime');
                                                    $dtDisplay = $dtVal instanceof \Carbon\Carbon ? $dtVal->format('Y-m-d\TH:i') : ($dtVal ? \Carbon\Carbon::parse($dtVal)->format('Y-m-d\TH:i') : now()->subMinute()->format('Y-m-d\TH:i'));
                                                ?>
                                                <input type="datetime-local" name="report_datetime" id="report_datetime" class="form-control <?php $__errorArgs = ['report_datetime'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e($dtDisplay); ?>" <?php echo e($disabledAttr); ?> max="<?php echo e(now()->format('Y-m-d\TH:i')); ?>">
                                                <small class="text-muted">* Minimal 1 menit sebelum waktu sekarang</small>
                                                <div class="invalid-feedback"><?php $__errorArgs = ['report_datetime'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo e($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Area Lokasi <span class="text-danger">*</span></label>
                                                <select id="area_id" name="area_id" class="form-select <?php $__errorArgs = ['area_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" <?php echo e($disabledAttr); ?>></select>
                                                <div class="invalid-feedback"><?php $__errorArgs = ['area_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo e($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="form-label fw-bold">Lokasi Spesifik <span class="text-danger">*</span></label>
                                            <input type="text" name="location_details" class="form-control <?php $__errorArgs = ['location_details'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e($getData('location_details')); ?>" <?php echo e($disabledAttr); ?> placeholder="Misal: Deck 2, Engine Room, dsb.">
                                            <div class="invalid-feedback"><?php $__errorArgs = ['location_details'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo e($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="card shadow-sm border-0 border-start border-primary border-4 br-8">
                                    <div class="card-body p-4">
                                        <div class="form-section-header">
                                            <div class="step-circle bg-primary text-white">2</div>
                                            <h5 class="mb-0 fw-bold text-primary">Klasifikasi Observasi</h5>
                                            <?php if($isHsseReview): ?> <span class="badge bg-light-success text-success ms-auto">HSSE Edit Mode</span> <?php endif; ?>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold"><i class="bi bi-person-x text-warning"></i> Unsafe Act(s)</label>
                                                <select id="unsafe_acts" name="selectedUnsafeActs[]" multiple autocomplete="off"></select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold"><i class="bi bi-exclamation-triangle text-danger"></i> Unsafe Condition(s)</label>
                                                <select id="unsafe_conditions" name="selectedUnsafeConditions[]" multiple autocomplete="off"></select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="card shadow-sm border-0 br-8">
                                    <div class="card-body p-4">
                                        <div class="form-section-header">
                                            <div class="step-circle" <?php if($isHsseReview): ?> style="background:#eee;color:#999" <?php endif; ?>>3</div>
                                            <h5 class="mb-0 fw-bold">Tindak Lanjut & Otorisasi</h5>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label fw-bold">Tindakan Langsung (Immediate Action) <span class="text-danger">*</span></label>
                                            <textarea name="immediate_action_taken" class="form-control <?php $__errorArgs = ['immediate_action_taken'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" rows="3" <?php echo e($disabledAttr); ?> placeholder="Tindakan apa yang langsung dilakukan?"><?php echo e($getData('immediate_action_taken')); ?></textarea>
                                            <div class="invalid-feedback"><?php $__errorArgs = ['immediate_action_taken'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <?php echo e($message); ?> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                                        </div>

                                        
                                        <div class="mb-4">
                                            <label class="form-label fw-bold d-flex align-items-center gap-2">
                                                <i class="bi bi-person-check-fill text-primary"></i>
                                                Atasan Langsung (Supervisor)
                                            </label>

                                            <div class="supervisor-info-box">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="icon-box">
                                                        <i class="bi <?php echo e($supervisor ? 'bi-person-badge-fill' : 'bi-building'); ?> fs-4"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 fw-bold"><?php echo e($supervisorName); ?></h6>
                                                        <p class="mb-0 small opacity-90">
                                                            <i class="bi bi-info-circle me-1"></i>
                                                            <?php echo e($supervisorNote); ?>

                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <small class="text-muted mt-2 d-block">
                                                <i class="bi bi-lock-fill me-1"></i>
                                                Supervisor ditentukan otomatis berdasarkan hirarki organisasi Anda
                                            </small>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label d-block fw-bold">STOP Job Diterbitkan? <span class="text-danger">*</span></label>
                                                <?php $stopVal = $getData('stop_card_issued'); ?>
                                                <div class="btn-group w-100">
                                                    <input type="radio" class="btn-check" name="stop_card_issued" id="stop_yes" value="1" <?php echo e($stopVal == '1' ? 'checked' : ''); ?> <?php echo e($disabledAttr); ?>>
                                                    <label class="btn btn-outline-primary" for="stop_yes">Ya</label>
                                                    <input type="radio" class="btn-check" name="stop_card_issued" id="stop_no" value="0" <?php echo e(($stopVal === '0' || $stopVal === 0) ? 'checked' : ''); ?> <?php echo e($disabledAttr); ?>>
                                                    <label class="btn btn-outline-primary" for="stop_no">Tidak</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="card shadow-sm border-0 br-8">
                                    <div class="card-body p-4">
                                        <div class="form-section-header">
                                            <div class="step-circle" <?php if($isHsseReview): ?> style="background:#eee;color:#999" <?php endif; ?>>4</div>
                                            <h5 class="mb-0 fw-bold">Bukti Foto Dokumentasi</h5>
                                        </div>

                                        <?php if($isEdit && $report->attachments->isNotEmpty()): ?>
                                            <div class="mb-4">
                                                <p class="small fw-bold text-muted text-uppercase mb-2">Foto Tersimpan:</p>
                                                <div class="row g-2">
                                                <?php $__currentLoopData = $report->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $att): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="col-md-6">
                                                        <div class="attachment-item shadow-sm border br-8">
                                                            <?php if(!$isHsseReview): ?>
                                                            <div class="form-check m-0">
                                                                <input class="form-check-input" type="checkbox" name="delete_attachments[]" value="<?php echo e($att->id); ?>">
                                                            </div>
                                                            <?php endif; ?>
                                                            <img src="<?php echo e(Storage::url($att->file_path)); ?>" class="attachment-preview">
                                                            <div class="attachment-info overflow-hidden">
                                                                <div class="small fw-bold text-truncate"><?php echo e($att->file_name); ?></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                                <?php if(!$isHsseReview): ?> <small class="text-danger">* Centang foto jika ingin menghapus.</small> <?php endif; ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if(!$isHsseReview): ?>
                                        <div id="upload-area">
                                            <div id="new-attachments-list"></div>
                                            <input type="file" id="file-input-trigger" class="d-none" accept="image/*" multiple>

                                            
                                            <div class="d-grid gap-2 mb-3">
                                                <button type="button" id="open-camera-btn" class="btn btn-primary">
                                                    <i class="bi bi-camera-fill me-2"></i> Ambil Foto dengan Kamera
                                                </button>
                                                <button type="button" id="choose-file-btn" class="btn btn-outline-primary">
                                                    <i class="bi bi-folder2-open me-2"></i> Pilih dari Galeri
                                                </button>
                                            </div>

                                            <div id="attachment-dropzone" class="dropzone-container">
                                                <i class="bi bi-cloud-arrow-up fs-1 text-primary"></i>
                                                <h6 class="fw-bold mt-2">Atau Tarik Foto ke Sini</h6>
                                                <p class="small text-muted mb-0">Maks 5 file, @5MB (JPG, PNG, GIF)</p>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="col-lg-4">
                            <div class="sticky-sidebar">
                                <div class="card shadow-sm border-0 br-8">
                                    <div class="card-body p-4 text-center">
                                        <h5 class="fw-bold mb-3">Konfirmasi</h5>
                                        <p class="text-muted small mb-4">Pastikan data yang dimasukkan sudah benar dan akurat.</p>

                                        <?php if(!$isEdit && !$isHsseReview): ?>
                                        <div class="form-check text-start mb-4 bg-light p-3 br-8">
                                            <input class="form-check-input ms-0 me-2" type="checkbox" name="statement" value="1" id="statement" required>
                                            <label class="form-check-label small" for="statement">
                                                Saya menyatakan bahwa informasi ini adalah benar dan dapat dipertanggungjawabkan.
                                            </label>
                                        </div>
                                        <?php endif; ?>

                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary fw-bold py-3 shadow-sm" id="submit-btn">
                                                <i class="bi bi-send-fill me-2"></i> <?php echo e($submitText); ?>

                                            </button>
                                            <a href="<?php echo e($cancelRoute); ?>" class="btn btn-outline-secondary py-2">Batal</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="card shadow-sm border-0 br-8 mt-3 bg-light-info border-0">
                                    <div class="card-body p-4">
                                        <h6 class="fw-bold"><i class="bi bi-info-circle me-1"></i> Informasi</h6>
                                        <ul class="small text-muted ps-3 mb-0">
                                            <?php if($supervisor): ?>
                                            <li>Laporan akan ditinjau oleh <?php echo e($supervisor->name); ?>.</li>
                                            <?php else: ?>
                                            <li>Laporan akan langsung ditinjau oleh tim HSSE.</li>
                                            <?php endif; ?>
                                            <li>Klasifikasi teknis akan divalidasi oleh tim HSSE.</li>
                                            <li>Gunakan bahasa yang jelas dan lampirkan foto pendukung yang relevan.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<div id="camera-modal">
    <div id="camera-container">
        <video id="camera-video" autoplay playsinline></video>
        <canvas id="camera-canvas"></canvas>
        <div class="camera-controls">
            <button type="button" id="close-camera-btn" class="camera-btn">
                <i class="bi bi-x-lg"></i>
            </button>
            <button type="button" id="capture-btn" class="camera-btn">
                <i class="bi bi-camera-fill"></i>
            </button>
            <button type="button" id="switch-camera-btn" class="camera-btn">
                <i class="bi bi-arrow-repeat"></i>
            </button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?php echo e(asset('plugins/src/sweetalerts2/sweetalerts2.min.js')); ?>"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isHsseReview = <?php echo json_encode($isHsseReview, 15, 512) ?>;
    const maxFiles = 5;
    const maxSizeBytes = 5 * 1024 * 1024;

    // --- 1. INISIALISASI TOM SELECT ---
    const tsBase = { plugins: ['dropdown_input'], onInitialize: function() { this.control.classList.add('form-select', 'p-0', 'border-0'); } };

    new TomSelect('#area_id', { ...tsBase, placeholder: 'Pilih Area...', options: <?php echo json_encode($areas->map(fn($i)=>['value'=>$i->id, 'text'=>$i->name]), 512) ?>, items: [<?php echo json_encode($getData('area_id'), 15, 512) ?>].filter(Boolean) });

    const tsMulti = { plugins: ['remove_button', 'dropdown_input'], placeholder: 'Pilih klasifikasi...', onInitialize: function() { this.control.classList.add('form-control', 'p-2'); } };
    new TomSelect('#unsafe_acts', { ...tsMulti, options: <?php echo json_encode($unsafeActs->map(fn($i)=>['value'=>$i->id, 'text'=>$i->description]), 512) ?>, items: <?php echo json_encode($getArrayData('unsafeActs', 'selectedUnsafeActs'), 512) ?> });
    new TomSelect('#unsafe_conditions', { ...tsMulti, options: <?php echo json_encode($unsafeConditions->map(fn($i)=>['value'=>$i->id, 'text'=>$i->description]), 512) ?>, items: <?php echo json_encode($getArrayData('unsafeConditions', 'selectedUnsafeConditions'), 512) ?> });

    // --- 2. VALIDASI WAKTU KEJADIAN (CLIENT-SIDE) ---
    const reportDatetimeInput = document.getElementById('report_datetime');
    if (reportDatetimeInput && !isHsseReview) {
        reportDatetimeInput.addEventListener('change', function() {
            const selectedTime = new Date(this.value);
            const now = new Date();

            // Hitung selisih waktu dalam menit
            const diffMinutes = Math.floor((now - selectedTime) / (1000 * 60));

            // Cek jika waktu yang dipilih kurang dari 1 menit dari sekarang (termasuk waktu yang sama atau lebih baru)
            if (diffMinutes < 1) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Waktu Tidak Valid',
                    text: 'Waktu kejadian harus minimal 1 menit sebelum waktu sekarang. Mohon pilih waktu yang sesuai.',
                });
                // Reset ke 1 menit yang lalu
                const oneMinuteAgo = new Date(now.getTime() - 60000);
                this.value = oneMinuteAgo.toISOString().slice(0, 16);
            }
        });
    }

    // --- 3. LOGIKA UPLOAD & CAMERA ---
    if (!isHsseReview) {
        const dropzone = document.getElementById('attachment-dropzone');
        const fileTrigger = document.getElementById('file-input-trigger');
        const chooseFileBtn = document.getElementById('choose-file-btn');
        const listContainer = document.getElementById('new-attachments-list');

        // Camera Elements
        const cameraModal = document.getElementById('camera-modal');
        const cameraVideo = document.getElementById('camera-video');
        const cameraCanvas = document.getElementById('camera-canvas');
        const openCameraBtn = document.getElementById('open-camera-btn');
        const closeCameraBtn = document.getElementById('close-camera-btn');
        const captureBtn = document.getElementById('capture-btn');
        const switchCameraBtn = document.getElementById('switch-camera-btn');

        let currentStream = null;
        let facingMode = 'environment'; // 'user' for front, 'environment' for back

        // Function to add file to list
        const addFileToList = (file, dataUrl) => {
            if (listContainer.children.length >= maxFiles) {
                return Swal.fire('Peringatan', 'Maksimal 5 foto', 'warning');
            }

            const div = document.createElement('div');
            div.className = 'attachment-item shadow-sm border br-8 animate__animated animate__fadeIn';
            div.innerHTML = `
                <img src="${dataUrl}" class="attachment-preview">
                <div class="attachment-info"><div class="small fw-bold text-truncate">${file.name}</div></div>
                <button type="button" class="btn btn-sm btn-danger border-0 p-1 px-2" style="border-radius:6px">×</button>
                <input type="file" name="attachments[]" class="d-none">
            `;
            const dt = new DataTransfer();
            dt.items.add(file);
            div.querySelector('input').files = dt.files;
            div.querySelector('button').onclick = () => div.remove();
            listContainer.appendChild(div);
        };

        // Handle Files from Gallery
        const handleFiles = (files) => {
            Array.from(files).forEach(file => {
                if (file.size > maxSizeBytes) {
                    return Swal.fire('Error', 'File '+file.name+' melebihi 5MB', 'error');
                }

                const reader = new FileReader();
                reader.onload = (e) => addFileToList(file, e.target.result);
                reader.readAsDataURL(file);
            });
        };

        // Open Camera
        const startCamera = async () => {
            try {
                const constraints = {
                    video: {
                        facingMode: facingMode,
                        width: { ideal: 1920 },
                        height: { ideal: 1080 }
                    }
                };

                currentStream = await navigator.mediaDevices.getUserMedia(constraints);
                cameraVideo.srcObject = currentStream;
                cameraModal.classList.add('show');
            } catch (error) {
                console.error('Error accessing camera:', error);
                Swal.fire('Error', 'Tidak dapat mengakses kamera. Pastikan izin kamera telah diberikan.', 'error');
            }
        };

        // Close Camera
        const stopCamera = () => {
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
                currentStream = null;
            }
            cameraModal.classList.remove('show');
        };

        // Switch Camera (Front/Back)
        const switchCamera = async () => {
            facingMode = facingMode === 'user' ? 'environment' : 'user';
            stopCamera();
            await startCamera();
        };

        // Capture Photo
        const capturePhoto = () => {
            const context = cameraCanvas.getContext('2d');
            cameraCanvas.width = cameraVideo.videoWidth;
            cameraCanvas.height = cameraVideo.videoHeight;
            context.drawImage(cameraVideo, 0, 0);

            cameraCanvas.toBlob((blob) => {
                const timestamp = new Date().getTime();
                const file = new File([blob], `camera_${timestamp}.jpg`, { type: 'image/jpeg' });
                const dataUrl = cameraCanvas.toDataURL('image/jpeg');

                addFileToList(file, dataUrl);
                stopCamera();

                Swal.fire({
                    icon: 'success',
                    title: 'Foto Berhasil Diambil!',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
            }, 'image/jpeg', 0.95);
        };

        // Event Listeners
        openCameraBtn.onclick = startCamera;
        closeCameraBtn.onclick = stopCamera;
        captureBtn.onclick = capturePhoto;
        switchCameraBtn.onclick = switchCamera;

        chooseFileBtn.onclick = () => fileTrigger.click();
        fileTrigger.onchange = (e) => { handleFiles(e.target.files); e.target.value = ''; };

        // Drag & Drop
        dropzone.ondragover = (e) => { e.preventDefault(); dropzone.classList.add('dragover'); };
        dropzone.ondragleave = () => dropzone.classList.remove('dragover');
        dropzone.ondrop = (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        };
        dropzone.onclick = () => fileTrigger.click();

        // Close modal on outside click
        cameraModal.onclick = (e) => {
            if (e.target === cameraModal) {
                stopCamera();
            }
        };
    }


    // --- 4. SUBMIT WITH LOADING ALERT ---
    $('#cermat-form').on('submit', function(e) {
        // Validasi foto (wajib di create)
        const existingCount = <?php echo json_encode($isEdit ? $report->attachments->count() : 0, 15, 512) ?>;
        const deletedCount = $('input[name="delete_attachments[]"]:checked').length;
        const newCount = $('#new-attachments-list').children().length;

        if (!isHsseReview && (existingCount - deletedCount + newCount) < 1) {
            e.preventDefault();
            return Swal.fire('Foto Wajib', 'Mohon lampirkan minimal 1 foto bukti.', 'warning');
        }

        // Validasi waktu kejadian (server-side backup)
        const reportTime = new Date(reportDatetimeInput.value);
        const now = new Date();
        const diffMinutes = Math.floor((now - reportTime) / (1000 * 60));

        if (diffMinutes < 1) {
            e.preventDefault();
            return Swal.fire('Waktu Tidak Valid', 'Waktu kejadian harus minimal 1 menit sebelum waktu sekarang.', 'warning');
        }

        // Tampilkan Loading
        Swal.fire({
            title: 'Mohon Tunggu',
            html: 'Sedang memproses laporan Anda...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => { Swal.showLoading(); }
        });
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/cermat/form.blade.php ENDPATH**/ ?>