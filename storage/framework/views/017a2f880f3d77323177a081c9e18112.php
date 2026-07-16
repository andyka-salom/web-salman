<?php $__env->startSection('title', 'Edit Laporan'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/src/sweetalerts2/sweetalerts2.css')); ?>">
<style>
    .img-preview-container { position: relative; display: inline-block; margin: 5px; }
    .btn-del-img {
        position: absolute; top: -5px; right: -5px; padding: 2px 6px;
        font-size: 10px; border-radius: 50%; width: 20px; height: 20px;
        display: flex; align-items: center; justify-content: center;
        background: #ef4444; color: #fff; border: none;
    }
    .btn-del-img:hover { background: #dc2626; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 shadow-sm">

                    
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom ps-3 pt-3 pe-3">
                        <h4 class="fw-bold mb-0">Edit Laporan: <?php echo e($campaignSalman->tema); ?></h4>
                    </div>

                    <form id="editForm" enctype="multipart/form-data" class="p-3">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Tanggal Kegiatan <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="tanggal" required value="<?php echo e($campaignSalman->tanggal->format('Y-m-d')); ?>">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Tema / Judul <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="tema" required value="<?php echo e($campaignSalman->tema); ?>">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Lokasi <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="lokasi" required value="<?php echo e($campaignSalman->lokasi); ?>">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Entitas <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="entitas" required value="<?php echo e($campaignSalman->entitas); ?>">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <?php if (\Illuminate\Support\Facades\Blade::check('hasrole', 'super-admin|hsse')): ?>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Perusahaan <span class="text-danger">*</span></label>
                                        <select class="form-select" name="company_id" required>
                                            <option value="">Pilih Perusahaan</option>
                                            <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($c->id); ?>" <?php echo e($campaignSalman->company_id == $c->id ? 'selected' : ''); ?>><?php echo e($c->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                <?php else: ?>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Perusahaan</label>
                                        <input type="text" class="form-control bg-light" value="<?php echo e($campaignSalman->company->name ?? 'N/A'); ?>" readonly>
                                        <input type="hidden" name="company_id" value="<?php echo e($campaignSalman->company_id); ?>">
                                    </div>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Jumlah Peserta <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="jumlah_peserta" required min="1" value="<?php echo e($campaignSalman->jumlah_peserta); ?>">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Pemateri <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="pemateri" required value="<?php echo e($campaignSalman->pemateri); ?>">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Template Cover</label>
                                    <select class="form-select" name="cover_template_id">
                                        <option value="">-- Pilih Template --</option>
                                        <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($t->id); ?>"
                                                <?php echo e(($campaignSalman->cover_template_id == $t->id) ||
                                                   (!$campaignSalman->cover_template_id && $defaultTemplate && $t->id == $defaultTemplate->id)
                                                   ? 'selected' : ''); ?>>
                                                <?php echo e($t->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Ringkasan Kegiatan <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="ringkasan" rows="5" required><?php echo e($campaignSalman->ringkasan); ?></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light border-0 shadow-none">
                                    <div class="card-body">
                                        <label class="fw-bold mb-2">Dokumentasi</label>
                                        <?php if($campaignSalman->dokumentasi): ?>
                                        <div class="mb-3 border-bottom pb-2" id="dokumentasi-container">
                                            <?php $__currentLoopData = $campaignSalman->dokumentasi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $path): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="img-preview-container">
                                                    <img src="<?php echo e(Storage::url($path)); ?>" class="rounded border shadow-sm" style="width: 60px; height: 60px; object-fit: cover;">
                                                    <button type="button" class="btn-del-img" onclick="deleteImage('dokumentasi', '<?php echo e($path); ?>', this)">×</button>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" name="dokumentasi[]" multiple accept="image/*">
                                        <small class="text-muted d-block mt-1">Tambah foto baru. Max 5MB per file.</small>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card bg-light border-0 shadow-none">
                                    <div class="card-body">
                                        <label class="fw-bold mb-2">Daftar Hadir</label>
                                        <?php if($campaignSalman->daftar_hadir): ?>
                                        <div class="mb-3 border-bottom pb-2" id="daftar_hadir-container">
                                            <?php $__currentLoopData = $campaignSalman->daftar_hadir; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $path): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="img-preview-container">
                                                    <img src="<?php echo e(Storage::url($path)); ?>" class="rounded border shadow-sm" style="width: 60px; height: 60px; object-fit: cover;">
                                                    <button type="button" class="btn-del-img" onclick="deleteImage('daftar_hadir', '<?php echo e($path); ?>', this)">×</button>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" name="daftar_hadir[]" multiple accept="image/*">
                                        <small class="text-muted d-block mt-1">Tambah foto baru.</small>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-top pt-4 text-end">
                            <a href="<?php echo e(route('campaign-salman.index')); ?>" class="btn btn-outline-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="btnUpdate">
                                <i class="fas fa-save me-1"></i> Update Laporan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('plugins/src/sweetalerts2/sweetalerts2.min.js')); ?>"></script>
<script>
$(document).ready(function() {
    const Toast = Swal.mixin({
        toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true
    });

    // --- Update Logic ---
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Tampilkan Loading Alert
        Swal.fire({
            title: 'Mohon Tunggu',
            html: 'Sedang mengupdate data laporan dan mengunggah aset...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData(this);
        // Method spoofing PUT ditambahkan manual untuk FormData
        formData.append('_method', 'PUT');

        $.ajax({
            url: "<?php echo e(route('campaign-salman.update', $campaignSalman->id)); ?>",
            type: 'POST',
            data: formData,
            processData: false, contentType: false,
            success: function(response) {
                Swal.close();
                if(response.success) {
                    Toast.fire({ icon: 'success', title: response.message });
                    setTimeout(() => { window.location.href = response.redirect; }, 1000);
                }
            },
            error: function(xhr) {
                Swal.close();
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        // Bersihkan dot notation jika ada (e.g dokumentasi.0)
                        const baseField = field.split('.')[0];
                        const input = $(`[name="${baseField}"]`).length ? $(`[name="${baseField}"]`) : $(`[name="${field}"]`);

                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(messages[0]);
                    });

                    Toast.fire({ icon: 'error', title: 'Validasi Gagal. Mohon periksa input Anda.' });
                } else {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Gagal mengupdate laporan.', 'error');
                }
            }
        });
    });

    // --- Delete Image Logic ---
    window.deleteImage = function(type, path, btnElement) {
        Swal.fire({
            title: 'Hapus Gambar?',
            text: "Gambar akan dihapus permanen dari server.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            customClass: { confirmButton: 'btn btn-primary', cancelButton: 'btn btn-outline-danger ms-1' },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "<?php echo e(route('campaign-salman.delete-image', $campaignSalman->id)); ?>",
                    type: "POST",
                    data: { _token: "<?php echo e(csrf_token()); ?>", type: type, path: path },
                    success: function(res) {
                        if(res.success) {
                            $(btnElement).closest('.img-preview-container').fadeOut(300, function() { $(this).remove(); });
                            Toast.fire({ icon: 'success', title: 'Gambar dihapus.' });
                        }
                    },
                    error: function(xhr) {
                        Toast.fire({ icon: 'error', title: 'Gagal menghapus gambar.' });
                    }
                });
            }
        });
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/features/campaign-salman/edit.blade.php ENDPATH**/ ?>