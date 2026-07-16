
<?php $__env->startSection('title', 'Buat Laporan Campaign Salman'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?php echo e(asset('plugins/src/sweetalerts2/sweetalerts2.css')); ?>">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.css" rel="stylesheet">

<style>
    /* --- FORM WIZARD STYLING --- */
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

    /* --- UPLOAD AREA (DRAG & DROP) --- */
    .dropzone-container {
        border: 2px dashed #cbd5e1; border-radius: 0.75rem; padding: 2rem 1rem;
        text-align: center; background-color: #f8fafc; cursor: pointer;
        transition: all 0.2s ease-in-out; position: relative;
    }
    .dropzone-container:hover, .dropzone-container.dragover {
        border-color: #4338ca; background-color: #eef2ff;
    }
    .dropzone-icon { font-size: 2.5rem; color: #94a3b8; margin-bottom: 0.5rem; }

    /* Attachment Item List */
    .attachment-item {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 0.5rem;
        padding: 0.75rem; display: flex; align-items: center; gap: 1rem;
        margin-bottom: 0.75rem; position: relative;
    }
    .attachment-preview {
        width: 56px; height: 56px; border-radius: 6px; object-fit: cover; background: #f1f5f9;
    }
    .attachment-info { flex: 1; min-width: 0; }
    .btn-delete-file {
        color: #ef4444; background: #fee2e2; border: none; padding: 0.4rem 0.6rem;
        border-radius: 6px; transition: all 0.2s; cursor: pointer;
    }
    .btn-delete-file:hover { background: #fecaca; color: #dc2626; }

    /* TomSelect Custom */
    .ts-wrapper.form-select { border: none !important; padding: 0 !important; }

    @media (min-width: 992px) {
        .sticky-sidebar { position: sticky; top: 1.5rem; z-index: 10; }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">
            <div class="col-12">

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                    <div>
                        <h3 class="fw-bold mb-0">Buat Laporan Campaign Salman</h3>
                        <p class="text-muted mb-0">Lengkapi formulir di bawah ini untuk mendokumentasikan kegiatan.</p>
                    </div>
                </div>

                <form id="createForm" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>

                    <div class="row g-4">
                        
                        <div class="col-lg-8">
                            <div class="d-flex flex-column gap-4">

                                
                                <div class="card shadow-sm border-0 br-8">
                                    <div class="card-body p-4">
                                        <div class="form-section-header">
                                            <div class="step-circle">1</div>
                                            <h5 class="mb-0 fw-bold">Informasi Dasar</h5>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Tanggal Kegiatan <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" name="tanggal" required value="<?php echo e(date('Y-m-d')); ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Tema / Judul <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="tema" required placeholder="Contoh: Sosialisasi K3">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Ringkasan Kegiatan <span class="text-danger">*</span></label>
                                            <textarea class="form-control" name="ringkasan" rows="4" required placeholder="Jelaskan secara singkat kegiatan yang dilakukan..."></textarea>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="card shadow-sm border-0 br-8">
                                    <div class="card-body p-4">
                                        <div class="form-section-header">
                                            <div class="step-circle">2</div>
                                            <h5 class="mb-0 fw-bold">Detail Pelaksanaan</h5>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Lokasi <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="lokasi" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Entitas <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="entitas" required placeholder="Contoh: Dept. Produksi">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6 mb-3">
                                                <?php if (\Illuminate\Support\Facades\Blade::check('hasrole', 'super-admin|hsse')): ?>
                                                    <label class="form-label fw-bold">Perusahaan <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="company_select" name="company_id" required>
                                                        <option value="">Pilih Perusahaan</option>
                                                        <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                <?php else: ?>
                                                    <label class="form-label fw-bold">Perusahaan</label>
                                                    <input type="text" class="form-control bg-light" value="<?php echo e($userCompany->name ?? 'N/A'); ?>" readonly>
                                                    <input type="hidden" name="company_id" value="<?php echo e(auth()->user()->company_id); ?>">
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Jumlah Peserta <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" name="jumlah_peserta" required min="1">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Pemateri <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="pemateri" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Template Cover PDF <span class="text-danger">*</span></label>
                                                <select class="form-select" id="template_select" name="cover_template_id" required>
                                                    <option value="">-- Pilih Template --</option>
                                                    <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($t->id); ?>"
                                                            <?php echo e(($defaultTemplate && $t->id == $defaultTemplate->id) ? 'selected' : ''); ?>>
                                                            <?php echo e($t->name); ?>

                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="card shadow-sm border-0 br-8">
                                    <div class="card-body p-4">
                                        <div class="form-section-header">
                                            <div class="step-circle">3</div>
                                            <h5 class="mb-0 fw-bold">Lampiran Foto</h5>
                                        </div>

                                        <div class="mb-5">
                                            <h6 class="fw-bold text-dark mb-2">A. Dokumentasi Kegiatan <span class="text-danger">*</span></h6>
                                            <div id="list-dokumentasi"></div>
                                            <input type="file" id="trigger-dokumentasi" class="d-none" accept="image/*" multiple>
                                            <div id="dropzone-dokumentasi" class="dropzone-container">
                                                <i class="bi bi-images dropzone-icon"></i>
                                                <h6 class="fw-bold text-dark mb-1">Upload Foto Kegiatan</h6>
                                                <p class="small text-muted mb-0">Klik atau Tarik foto ke sini. (Max 5MB)</p>
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <h6 class="fw-bold text-dark mb-2">B. Foto Daftar Hadir / Absensi</h6>
                                            <div id="list-absensi"></div>
                                            <input type="file" id="trigger-absensi" class="d-none" accept="image/*" multiple>
                                            <div id="dropzone-absensi" class="dropzone-container">
                                                <i class="bi bi-file-earmark-person dropzone-icon"></i>
                                                <h6 class="fw-bold text-dark mb-1">Upload Foto Absensi</h6>
                                                <p class="small text-muted mb-0">Klik atau Tarik foto absensi ke sini.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="col-lg-4">
                            <div class="sticky-sidebar">
                                <div class="card shadow-sm border-0 br-8">
                                    <div class="card-body p-4 text-center">
                                        <div class="mb-4">
                                            <i class="bi bi-info-circle text-primary" style="font-size: 3rem;"></i>
                                            <h5 class="fw-bold mt-3">Finalisasi</h5>
                                            <p class="text-muted small">Pastikan semua data dan lampiran dokumentasi sudah lengkap sebelum disimpan.</p>
                                        </div>

                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary fw-bold py-3 shadow-sm" id="btnSubmit">
                                                <i class="bi bi-save me-2"></i> Simpan Laporan
                                            </button>
                                            <a href="<?php echo e(route('campaign-salman.index')); ?>" class="btn btn-outline-secondary py-2">Batal</a>
                                        </div>
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('plugins/src/sweetalerts2/sweetalerts2.min.js')); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // --- 1. INISIALISASI TOM SELECT ---
    const tsOptions = {
        plugins: ['dropdown_input'],
        onInitialize: function() { this.control.classList.add('form-select', 'p-0', 'border-0'); }
    };

    if(document.getElementById('company_select')) {
        new TomSelect('#company_select', { ...tsOptions, placeholder: 'Pilih Perusahaan...' });
    }
    new TomSelect('#template_select', { ...tsOptions, placeholder: 'Pilih Template...' });


    // --- 2. LOGIKA UPLOAD MODULAR ---
    function initDropzone(config) {
        const dropzone = document.getElementById(config.dropzoneId);
        const fileTrigger = document.getElementById(config.triggerId);
        const listContainer = document.getElementById(config.listId);
        const inputName = config.inputName;

        const handleFiles = (files) => {
            Array.from(files).forEach(file => {
                if (!file.type.startsWith('image/')) {
                    Swal.fire('Format Salah', `File "${file.name}" harus berupa gambar.`, 'error');
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    Swal.fire('Terlalu Besar', `File "${file.name}" melebihi 5MB.`, 'error');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'attachment-item animate__animated animate__fadeIn';
                    itemDiv.innerHTML = `
                        <img src="${e.target.result}" class="attachment-preview">
                        <div class="attachment-info">
                            <div class="fw-bold text-truncate small">${file.name}</div>
                            <div class="text-muted" style="font-size:0.75rem;">${(file.size/(1024*1024)).toFixed(2)} MB</div>
                        </div>
                        <button type="button" class="btn-delete-file"><i class="bi bi-trash"></i></button>
                        <input type="file" name="${inputName}" class="d-none" hidden>
                    `;
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    itemDiv.querySelector('input').files = dt.files;
                    itemDiv.querySelector('.btn-delete-file').onclick = () => itemDiv.remove();
                    listContainer.appendChild(itemDiv);
                };
                reader.readAsDataURL(file);
            });
        };

        dropzone.onclick = () => fileTrigger.click();
        fileTrigger.onchange = (e) => { handleFiles(e.target.files); e.target.value = ''; };
        dropzone.ondragover = (e) => { e.preventDefault(); dropzone.classList.add('dragover'); };
        dropzone.ondragleave = () => dropzone.classList.remove('dragover');
        dropzone.ondrop = (e) => { e.preventDefault(); dropzone.classList.remove('dragover'); handleFiles(e.dataTransfer.files); };
    }

    initDropzone({ dropzoneId: 'dropzone-dokumentasi', triggerId: 'trigger-dokumentasi', listId: 'list-dokumentasi', inputName: 'dokumentasi[]' });
    initDropzone({ dropzoneId: 'dropzone-absensi', triggerId: 'trigger-absensi', listId: 'list-absensi', inputName: 'daftar_hadir[]' });


    // --- 3. AJAX SUBMIT DENGAN LOADING ---
    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });

    $('#createForm').on('submit', function(e) {
        e.preventDefault();

        // Validasi: Template Cover PDF wajib dipilih
        if (!$('#template_select').val()) {
            Swal.fire('Template Wajib', 'Mohon pilih template cover PDF terlebih dahulu.', 'warning');
            return;
        }

        // Validasi: Dokumentasi wajib ada
        if ($('#list-dokumentasi').children().length === 0) {
            Swal.fire('Dokumentasi Wajib', 'Mohon lampirkan minimal 1 foto dokumentasi.', 'warning');
            return;
        }

        const formData = new FormData(this);

        // Tampilkan Loading Alert
        Swal.fire({
            title: 'Mohon Tunggu',
            html: 'Sedang memproses dan mengunggah laporan...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: "<?php echo e(route('campaign-salman.store')); ?>",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                Swal.close();
                if(res.success) {
                    Toast.fire({ icon: 'success', title: res.message });
                    setTimeout(() => { window.location.href = res.redirect; }, 1000);
                }
            },
            error: function(xhr) {
                Swal.close();
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorMsg = '<ul class="text-start">';
                    $.each(errors, function(key, val) { errorMsg += `<li>${val[0]}</li>`; });
                    errorMsg += '</ul>';
                    Swal.fire({ icon: 'error', title: 'Validasi Gagal', html: errorMsg });
                } else {
                    Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                }
            }
        });
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/features/campaign-salman/create.blade.php ENDPATH**/ ?>