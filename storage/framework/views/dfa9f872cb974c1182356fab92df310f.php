<?php $__env->startSection('title', 'Riwayat Broadcast'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    /* Mengadaptasi style filter dan widget dari referensi */
    .filter-section {
        background: var(--card-bg);
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 24px;
        border: 1px solid var(--card-border-color);
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }
    .filter-section .form-label {
        font-weight: 700;
        font-size: 14px;
        color: var(--text-color);
        margin-bottom: 8px;
    }

    /* Style untuk area tabel */
    .widget-content-area {
        padding: 24px;
    }

    .table td {
        vertical-align: middle;
    }
    .badge-manual { background-color: #4361ee; color: #fff; }
    .badge-group { background-color: #805dca; color: #fff; }

    /* Memastikan tombol aksi memiliki tinggi yang sama dengan input group */
    .d-flex.align-items-end button, .d-flex.align-items-end a {
        height: 46px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">

        
        <div class="row layout-top-spacing mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h3 class="m-0 fw-bold">Riwayat Broadcast Saya</h3>
            </div>
        </div>

        
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="filter-section">
                <h5 class="mb-4 fw-bold text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-filter me-1"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                    Filter & Pencarian
                </h5>
                <form method="GET" action="<?php echo e(route('broadcast.history')); ?>">
                    <div class="row g-3 align-items-end">

                        
                        <div class="col-lg-3 col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" name="status" id="status">
                                <option value="">Semua Status</option>
                                <?php $__currentLoopData = ['draft', 'scheduled', 'processing', 'sending', 'completed', 'failed', 'cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($status); ?>" <?php echo e(request('status') == $status ? 'selected' : ''); ?>>
                                        <?php echo e(ucfirst($status)); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        
                        <div class="col-lg-3 col-md-6">
                            <label for="target_type" class="form-label">Tipe Target</label>
                            <select class="form-select" name="target_type" id="target_type">
                                <option value="">Semua Tipe</option>
                                <?php $__currentLoopData = ['manual', 'group_contact']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($type); ?>" <?php echo e(request('target_type') == $type ? 'selected' : ''); ?>>
                                        <?php echo e(ucwords(str_replace('_', ' ', $type))); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        
                        <div class="col-lg-4 col-md-8">
                            <label for="search" class="form-label">Cari Judul/Pesan</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                </span>
                                <input type="text" class="form-control" name="search" id="search" value="<?php echo e(request('search')); ?>" placeholder="Ketik kata kunci...">
                            </div>
                        </div>

                        
                        <div class="col-lg-2 col-md-4 d-flex align-items-end">
                            <div class="d-grid gap-2 d-flex w-100">
                                <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
                                <a href="<?php echo e(route('broadcast.history')); ?>" class="btn btn-outline-secondary flex-grow-1" title="Reset Filter">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget-content widget-content-area br-8">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 30%">Judul / Pesan</th>
                                <th>Target</th>
                                <th>Statistik</th>
                                <th>Status</th>
                                <th>Waktu</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $broadcasts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $broadcast): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $statusColor = match($broadcast->status) {
                                        'completed' => 'success',
                                        'failed', 'cancelled' => 'danger',
                                        'processing', 'sending' => 'primary',
                                        'scheduled' => 'info',
                                        default => 'secondary'
                                    };

                                    $isManual = $broadcast->target_type === 'manual';
                                    $targetBadgeClass = $isManual ? 'badge-manual' : 'badge-group';
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-dark" title="<?php echo e($broadcast->title); ?>">
                                                <?php echo e(Str::limit($broadcast->title ?? 'Tanpa Judul', 40)); ?>

                                            </span>
                                            <small class="text-muted text-truncate" style="max-width: 300px;">
                                                <?php echo e(Str::limit($broadcast->message, 60)); ?>

                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo e($targetBadgeClass); ?>">
                                            
                                            <?php if($isManual): ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user me-1"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                            <?php else: ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users me-1"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                            <?php endif; ?>
                                            <?php echo e(ucwords(str_replace('_', ' ', $broadcast->target_type))); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            
                                            <span class="fw-bold <?php echo e($broadcast->failed_count > 0 ? 'text-warning' : 'text-success'); ?>">
                                                <?php echo e($broadcast->sent_count); ?>

                                            </span>
                                            <span class="text-muted mx-1">/</span>
                                            
                                            <span><?php echo e($broadcast->total_recipients); ?></span>
                                        </div>
                                        <?php if($broadcast->failed_count > 0): ?>
                                            <small class="text-danger d-block mt-1" style="font-size: 10px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-circle"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                                <?php echo e($broadcast->failed_count); ?> Gagal
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo e($statusColor); ?>">
                                            <?php echo e(ucfirst($broadcast->status)); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <?php if($broadcast->scheduled_at): ?>
                                                <small class="text-info fw-bold">Jadwal:</small>
                                                <span style="font-size: 13px;"><?php echo e($broadcast->scheduled_at->format('d M Y, H:i')); ?></span>
                                            <?php else: ?>
                                                <small class="text-muted">Dibuat:</small>
                                                <span style="font-size: 13px;"><?php echo e($broadcast->created_at->format('d M Y, H:i')); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?php echo e(route('broadcast.show', $broadcast->id)); ?>" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Lihat Detail">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#e0e6ed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-inbox mb-3"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path></svg>
                                            <h6 class="text-muted">Tidak ada riwayat broadcast ditemukan.</h6>
                                            <p class="text-muted small">Coba ubah filter atau buat broadcast baru.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                
                <?php if($broadcasts->hasPages()): ?>
                    <div class="d-flex justify-content-center mt-4">
                        <?php echo e($broadcasts->appends(request()->query())->links()); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/broadcast/history.blade.php ENDPATH**/ ?>