<?php $__env->startSection('title', isset($user) ? 'Edit User' : 'Create User'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/src/tomSelect/tom-select.default.min.css')); ?>">
<link rel="stylesheet" type="text/css" href="<?php echo e(asset('plugins/src/sweetalerts2/sweetalerts2.css')); ?>">
<style>
    /* Checklist Area Styling */
    .checkbox-list-scroll {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #e0e6ed;
        border-radius: 6px;
        padding: 12px;
        background: #fbfbfb;
    }
    .checkbox-list-scroll .form-check {
        margin-bottom: 8px;
    }

    /* TomSelect Validation Visual Fix */
    .ts-wrapper.is-invalid .ts-control {
        border-color: #e7515a !important;
    }

    /* Photo Preview Styling */
    .profile-photo-container {
        border: 2px dashed #e0e6ed;
        border-radius: 12px;
        padding: 20px;
        background: #fbfbfb;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="layout-px-spacing">
    <div class="middle-content container-xxl p-0">

        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget-content widget-content-area br-8 shadow-sm">

                    <form id="userForm" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <?php if(isset($user)): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                            <h4 class="fw-bold mb-0"><?php echo e(isset($user) ? 'Edit User' : 'Create New User'); ?></h4>
                        </div>

                        <div class="row">
                            
                            <div class="col-md-8 border-end">
                                <h5 class="mb-4 fw-bold text-primary">Account Information</h5>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name" value="<?php echo e(old('name', $user->name ?? '')); ?>" placeholder="Enter full name" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" name="email" value="<?php echo e(old('email', $user->email ?? '')); ?>" placeholder="email@example.com" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="phone"
                                            value="<?php echo e(old('phone', $user->phone ?? '')); ?>"
                                            placeholder="+62..." required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Job Title / Jabatan</label>
                                        <input type="text" class="form-control" name="jabatan" value="<?php echo e(old('jabatan', $user->jabatan ?? '')); ?>" placeholder="e.g. Operation Manager">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Company</label>
                                        <select class="form-select" id="company_id" name="company_id">
                                            <option value="">Select Company</option>
                                            <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($c->id); ?>" <?php echo e((old('company_id', $user->company_id ?? '') == $c->id) ? 'selected' : ''); ?>><?php echo e($c->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Entity Function</label>
                                        <select class="form-select" id="entity_function_id" name="entity_function_id">
                                            <option value="">Select Function</option>
                                            <?php $__currentLoopData = $functions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($f->id); ?>" <?php echo e((old('entity_function_id', $user->entity_function_id ?? '') == $f->id) ? 'selected' : ''); ?>><?php echo e($f->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <h5 class="mb-4 mt-5 fw-bold text-primary">Roles & Permissions</h5>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Assign Roles <span class="text-danger">*</span></label>
                                        <div class="checkbox-list-scroll shadow-sm">
                                            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $isChecked = ((isset($userRoles) && in_array($role->id, $userRoles)) || (is_array(old('roles')) && in_array($role->id, old('roles'))));
                                                ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="roles[]"
                                                           value="<?php echo e($role->id); ?>" id="role_<?php echo e($role->id); ?>" <?php echo e($isChecked ? 'checked' : ''); ?>>
                                                    <label class="form-check-label" for="role_<?php echo e($role->id); ?>">
                                                        <?php echo e(ucfirst($role->name)); ?> <small class="text-muted">(<?php echo e($role->guard_name); ?>)</small>
                                                    </label>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                        <div id="roles-error" class="text-danger small mt-1"></div>
                                    </div>
                                </div>

                                <h5 class="mb-4 mt-5 fw-bold text-primary">Security</h5>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Password <?php echo e(isset($user) ? '(Empty to keep current)' : '*'); ?></label>
                                        <input type="password" class="form-control" name="password" placeholder="Min. 8 characters">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Confirm Password</label>
                                        <input type="password" class="form-control" name="password_confirmation" placeholder="Repeat password">
                                    </div>
                                </div>

                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?php echo e(old('is_active', $user->is_active ?? true) ? 'checked' : ''); ?>>
                                    <label class="form-check-label fw-bold" for="is_active">Active Account</label>
                                </div>
                            </div>

                            
                            <div class="col-md-4">
                                <h5 class="mb-4 fw-bold text-primary">Profile Photo</h5>
                                <div class="profile-photo-container text-center mb-3">
                                    <img id="photoPreview"
                                         src="<?php echo e(isset($user) && $user->photo_path ? Storage::url($user->photo_path) : asset('assets/img/profile-3.jpg')); ?>"
                                         class="img-fluid rounded-circle shadow"
                                         style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #fff;">

                                    <div class="mt-4 text-start">
                                        <label for="photo" class="form-label fw-bold">Change Photo</label>
                                        <input type="file" class="form-control" name="photo" id="photo" accept="image/*">
                                        <small class="text-muted d-block mt-1">Allowed: jpg, jpeg, png, webp. Max 2MB.</small>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="row mt-5">
                            <div class="col-12 text-end border-top pt-4">
                                <a href="<?php echo e(route('users.index')); ?>" class="btn btn-outline-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> <?php echo e(isset($user) ? 'Update User' : 'Save User'); ?>

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
<script>
$(document).ready(function() {

    // 1. Inisialisasi Toast
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    // 2. Init TomSelect
    var tsConfig = { allowEmptyOption: true, placeholder: "Select option" };
    var companySelect = new TomSelect('#company_id', tsConfig);
    var functionSelect = new TomSelect('#entity_function_id', tsConfig);

    // 3. Photo Preview Logic
    $('#photo').on('change', function(){
        const file = this.files[0];
        if (file){
            if (file.size > 2048000) {
                Toast.fire({ icon: 'error', title: 'File size must not exceed 2MB' });
                $(this).val('');
                return;
            }
            let reader = new FileReader();
            reader.onload = function(event){
                $('#photoPreview').attr('src', event.target.result);
            }
            reader.readAsDataURL(file);
        }
    });

    // 4. Form Submit AJAX
    $('#userForm').on('submit', function(e) {
        e.preventDefault();

        // Bersihkan state error
        $('.is-invalid').removeClass('is-invalid');
        $('.ts-wrapper').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#roles-error').text('');

        const formData = new FormData(this);
        const isEdit = <?php echo json_encode(isset($user), 15, 512) ?>;
        const url = <?php echo json_encode(isset($user) ? route('users.update', $user->id ?? 0) : route('users.store'), 512) ?>;

        // Spoofing PUT untuk Edit Mode via AJAX
        if(isEdit) { formData.append('_method', 'PUT'); }

        // Tampilkan Loading Alert
        Swal.fire({
            title: 'Mohon Tunggu',
            html: isEdit ? 'Sedang memperbarui data user...' : 'Sedang menyimpan user baru...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: url,
            type: 'POST', // Selalu POST karena bawa FormData
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
                if(xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        // Handle roles
                        if (field === 'roles') {
                            $('#roles-error').text(messages[0]);
                            $('.checkbox-list-scroll').css('border-color', '#e7515a');
                        }
                        // Handle TomSelect
                        else if (field === 'company_id') {
                            $(companySelect.control).parent().addClass('is-invalid');
                            $(companySelect.control).parent().siblings('.invalid-feedback').text(messages[0]).show();
                        }
                        else if (field === 'entity_function_id') {
                            $(functionSelect.control).parent().addClass('is-invalid');
                            $(functionSelect.control).parent().siblings('.invalid-feedback').text(messages[0]).show();
                        }
                        // Handle Standard Input
                        else {
                            const input = $(`[name="${field}"]`);
                            input.addClass('is-invalid');
                            input.siblings('.invalid-feedback').text(messages[0]);
                        }
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        text: 'Silakan periksa kembali inputan Anda.',
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: xhr.responseJSON?.message || 'Server Error',
                    });
                }
            }
        });
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/master/users/create.blade.php ENDPATH**/ ?>