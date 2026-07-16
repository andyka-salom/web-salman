<?php $__env->startSection('title', 'Detail Laporan: ' . $campaignSalman->tema); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .detail-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        transition: transform 0.2s;
    }
    .detail-label {
        font-size: 0.85rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }
    .detail-value {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2c3e50;
    }
    .gallery-img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        transition: opacity 0.2s;
    }
    .gallery-img:hover { opacity: 0.8; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">

        
        <div class="row layout-top-spacing mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h3 class="m-0 text-truncate" style="max-width: 70%;"><?php echo e($campaignSalman->tema); ?></h3>
                <div class="d-flex gap-2">
                    <a href="<?php echo e(route('campaign-salman.edit', $campaignSalman->id)); ?>" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    <a href="<?php echo e(route('campaign-salman.index')); ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>

                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-file-pdf me-1"></i> Export PDF
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="<?php echo e(route('campaign-salman.preview', $campaignSalman->id)); ?>" target="_blank">
                                    <i class="fas fa-eye me-2"></i> Preview
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo e(route('campaign-salman.download', $campaignSalman->id)); ?>">
                                    <i class="fas fa-download me-2"></i> Download
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            
            <div class="col-xl-8 col-lg-8 col-md-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 p-4 h-100">
                    <h5 class="mb-4 pb-2 border-bottom">Informasi Kegiatan</h5>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="detail-label">Tanggal</div>
                            <div class="detail-value"><?php echo e($campaignSalman->tanggal->format('d F Y')); ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Lokasi</div>
                            <div class="detail-value"><?php echo e($campaignSalman->lokasi); ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Entitas</div>
                            <div class="detail-value"><?php echo e($campaignSalman->entitas); ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Perusahaan</div>
                            <div class="detail-value"><?php echo e($campaignSalman->company->name ?? '-'); ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Pemateri</div>
                            <div class="detail-value"><?php echo e($campaignSalman->pemateri); ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Jumlah Peserta</div>
                            <div class="detail-value"><?php echo e($campaignSalman->jumlah_peserta); ?> Orang</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="detail-label mb-2">Ringkasan Kegiatan</div>
                        <div class="p-3 bg-light rounded text-justify" style="white-space: pre-line; line-height: 1.6;">
                            <?php echo e($campaignSalman->ringkasan); ?>

                        </div>
                    </div>
                </div>
            </div>

            
            <div class="col-xl-4 col-lg-4 col-md-12 layout-spacing">
                
                <div class="widget-content widget-content-area br-8 p-4 mb-4">
                    <h5 class="mb-3">Cover Template</h5>
                    <?php if($campaignSalman->coverTemplate): ?>
                        <div class="text-center border rounded p-2 bg-light">
                            <img src="<?php echo e(asset('storage/' . $campaignSalman->coverTemplate->cover_image_path)); ?>" class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                            <p class="mt-2 mb-0 fw-bold"><?php echo e($campaignSalman->coverTemplate->name); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mb-0">Tidak ada template cover yang dipilih.</div>
                    <?php endif; ?>
                </div>

                
                <div class="widget-content widget-content-area br-8 p-4">
                    <h5 class="mb-3">Meta Data</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Dibuat Oleh</span>
                            <span class="fw-bold"><?php echo e($campaignSalman->creator->name ?? 'System'); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Dibuat Pada</span>
                            <span><?php echo e($campaignSalman->created_at->format('d M Y H:i')); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Terakhir Update</span>
                            <span><?php echo e($campaignSalman->updated_at->format('d M Y H:i')); ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        
        <div class="row layout-top-spacing">

            
            <div class="col-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 p-4">
                    <h5 class="mb-4 pb-2 border-bottom">Dokumentasi Kegiatan</h5>
                    <?php if(!empty($campaignSalman->dokumentasi)): ?>
                        <div class="row g-3">
                            <?php $__currentLoopData = $campaignSalman->dokumentasi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $path): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <a href="<?php echo e(asset('storage/' . $path)); ?>" target="_blank">
                                        <img src="<?php echo e(asset('storage/' . $path)); ?>" class="gallery-img border">
                                    </a>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">Tidak ada dokumentasi.</p>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="col-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 p-4">
                    <h5 class="mb-4 pb-2 border-bottom">Daftar Hadir / Absensi</h5>
                    <?php if(!empty($campaignSalman->daftar_hadir)): ?>
                        <div class="row g-3">
                            <?php $__currentLoopData = $campaignSalman->daftar_hadir; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $path): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <a href="<?php echo e(asset('storage/' . $path)); ?>" target="_blank">
                                        <img src="<?php echo e(asset('storage/' . $path)); ?>" class="gallery-img border">
                                    </a>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">Tidak ada foto daftar hadir.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/features/campaign-salman/show.blade.php ENDPATH**/ ?>