<?php $__env->startSection('title', 'Reset Password'); ?>

<?php $__env->startSection('content'); ?>


<style>
    .auth-bg {
        background-image: url('<?php echo e(asset('phm.jpg')); ?>');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    .auth-bg::before {
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 0;
    }
    .auth-card {
        background-color: #ffffff !important;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        border: none;
        position: relative;
        z-index: 1;
    }
</style>

<div class="auth-container auth-bg">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xxl-4 col-xl-5 col-lg-6 col-md-8 col-12">

                <div class="card mt-3 mb-3 auth-card">
                    <div class="card-body p-4 p-md-5">

                        <?php if(session('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle me-2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            <?php echo e(session('success')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo e(route('password.update')); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="token" value="<?php echo e($token); ?>">
                            <input type="hidden" name="email" value="<?php echo e($email); ?>">

                            <div class="text-center mb-4">
                                <h2 class="text-dark fw-bold">Set New Password</h2>
                                <p class="text-muted">Enter your new password</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">New Password</label>
                                <input type="password"
                                       name="password"
                                       class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       placeholder="Enter new password"
                                       required
                                       autofocus>
                                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <small class="form-text text-muted">
                                    Min 8 characters, include uppercase, lowercase, number & symbol
                                </small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-dark fw-bold">Confirm Password</label>
                                <input type="password"
                                       name="password_confirmation"
                                       class="form-control"
                                       placeholder="Confirm new password"
                                       required>
                            </div>

                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-primary" style="background-color: #6f42c1; border-color: #6f42c1;">
                                    RESET PASSWORD
                                </button>
                            </div>

                            <div class="text-center">
                                <p class="mb-0">
                                    <a href="<?php echo e(route('login')); ?>" class="text-warning fw-bold text-decoration-none">Back to Sign In</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/auth/password-reset.blade.php ENDPATH**/ ?>