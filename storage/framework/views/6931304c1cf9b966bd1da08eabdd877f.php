<?php $__env->startSection('title', isset($vessel) ? 'Edit Vessel' : 'Create Vessel'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/src/tomSelect/tom-select.default.min.css')); ?>">
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/src/sweetalerts2/sweetalerts2.css')); ?>">
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/src/flatpickr/flatpickr.css')); ?>">
<style>
    .ts-wrapper.is-invalid .ts-control {
        border-color: #e7515a !important;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 shadow-sm">

                    <form id="vesselForm">
                        <?php echo csrf_field(); ?>
                        <?php if(isset($vessel)): ?>
                            <?php echo method_field('PUT'); ?>
                        <?php endif; ?>

                        
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                            <h4 class="fw-bold mb-0"><?php echo e(isset($vessel) ? 'Edit Vessel' : 'New Vessel Information'); ?></h4>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3">
                                    
                                    <div class="col-md-6">
                                        <label for="company_id" class="form-label fw-bold">Company <span class="text-danger">*</span></label>
                                        <?php if (\Illuminate\Support\Facades\Blade::check('hasrole', 'super-admin')): ?>
                                            <select class="form-select" id="company_id" name="company_id" required>
                                                <option value="">Select Company</option>
                                                <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($company->id); ?>"
                                                        <?php echo e(old('company_id', isset($vessel) ? $vessel->company_id : '') == $company->id ? 'selected' : ''); ?>>
                                                        <?php echo e($company->name); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        <?php else: ?>
                                            <input type="text" class="form-control bg-light" value="<?php echo e(auth()->user()->company->name); ?>" readonly>
                                            <input type="hidden" id="company_id" name="company_id" value="<?php echo e(auth()->user()->company_id); ?>">
                                        <?php endif; ?>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    
                                    <div class="col-md-6">
                                        <label for="coordinator_id" class="form-label fw-bold">Crew Penanggung Jawab DCU<span class="text-danger">*</span></label>
                                        <select class="form-select" id="coordinator_id" name="coordinator_id" required>
                                            <option value="">Select Coordinator</option>
                                            <?php $__currentLoopData = $coordinators; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coordinator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($coordinator->id); ?>"
                                                    <?php echo e(old('coordinator_id', isset($vessel) ? $vessel->coordinator_id : '') == $coordinator->id ? 'selected' : ''); ?>>
                                                    <?php echo e($coordinator->name); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label fw-bold">Vessel Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name"
                                               value="<?php echo e(old('name', isset($vessel) ? $vessel->name : '')); ?>"
                                               placeholder="Enter vessel name" required>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="type" class="form-label fw-bold">Vessel Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="type" name="type" required>
                                            <option value="">Select Type</option>
                                            <?php $__currentLoopData = $vesselTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($key); ?>"
                                                    <?php echo e(old('type', isset($vessel) ? $vessel->type : '') == $key ? 'selected' : ''); ?>>
                                                    <?php echo e($label); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="valid_until" class="form-label fw-bold">Valid Until</label>
                                        <input type="text" class="form-control" id="valid_until" name="valid_until"
                                               value="<?php echo e(old('valid_until', isset($vessel) && $vessel->valid_until ? $vessel->valid_until->format('Y-m-d') : '')); ?>"
                                               placeholder="Select date">
                                        <small class="text-muted">Leave empty for no expiry date</small>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                                   <?php echo e(old('is_active', isset($vessel) ? $vessel->is_active : true) ? 'checked' : ''); ?>>
                                            <label class="form-check-label fw-bold" for="is_active">Active Status</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="row mt-4">
                            <div class="col-12 text-end border-top pt-4">
                                <a href="<?php echo e(route('vessels.index')); ?>" class="btn btn-outline-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> <?php echo e(isset($vessel) ? 'Update Vessel' : 'Create Vessel'); ?>

                                </button>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('plugins/src/tomselect/tom-select.base.js')); ?>"></script>
<script src="<?php echo e(asset('plugins/src/sweetalerts2/sweetalerts2.min.js')); ?>"></script>
<script src="<?php echo e(asset('plugins/src/flatpickr/flatpickr.js')); ?>"></script>
<script>
$(document).ready(function() {

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    // --- Init Components ---
    let tsConfig = { placeholder: 'Select Option', allowEmptyOption: false, sortField: 'text' };

    let tsCompany = null;
    if(document.getElementById('company_id') && document.getElementById('company_id').tagName === 'SELECT') {
        tsCompany = new TomSelect('#company_id', tsConfig);
    }

    let tsCoordinator = new TomSelect('#coordinator_id', tsConfig);

    // Type is now REQUIRED - no empty option allowed
    let tsType = new TomSelect('#type', {
        placeholder: 'Select Type',
        allowEmptyOption: false
    });

    flatpickr('#valid_until', { dateFormat: 'Y-m-d', minDate: 'today', allowInput: true });

    // --- Form Submit ---
    $('#vesselForm').on('submit', function(e) {
        e.preventDefault();

        $('.is-invalid').removeClass('is-invalid');
        $('.ts-wrapper').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        const formData = new FormData(this);
        const isEdit = <?php echo json_encode(isset($vessel), 15, 512) ?>;
        const url = <?php echo json_encode(isset($vessel) ? route('vessels.update', $vessel->id ?? 0) : route('vessels.store'), 512) ?>;

        Swal.fire({
            title: 'Mohon Tunggu',
            html: isEdit ? 'Sedang memperbarui data kapal...' : 'Sedang menyimpan data kapal...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                Swal.close();
                if (res.success) {
                    Toast.fire({ icon: 'success', title: res.message });
                    setTimeout(() => { window.location.href = res.redirect; }, 1000);
                }
            },
            error: function(xhr) {
                Swal.close();
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        const input = $(`[name="${field}"]`);

                        if (['company_id', 'coordinator_id', 'type'].includes(field)) {
                            $(`#${field}`).siblings('.ts-wrapper').addClass('is-invalid');
                            $(`#${field}`).siblings('.invalid-feedback').text(messages[0]).show();
                        } else {
                            input.addClass('is-invalid');
                            input.siblings('.invalid-feedback').text(messages[0]);
                        }
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        text: 'Silakan periksa kembali isian Anda.',
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Kesalahan',
                        text: xhr.responseJSON?.message || 'Server Error',
                    });
                }
            }
        });
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/master/vessels/create.blade.php ENDPATH**/ ?>