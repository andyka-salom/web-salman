<?php $__env->startSection('title', 'Password Reset'); ?>

<?php $__env->startSection('content'); ?>


<style>
    /* 1. Background Image & Overlay */
    .auth-bg {
        /* Gambar yang sama dengan login agar transisi halus */
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
        background: rgba(0, 0, 0, 0.5); /* Overlay gelap */
        z-index: 0;
    }

    /* 2. Card Putih Solid */
    .reset-card {
        background-color: #ffffff !important;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        border: none;
        position: relative;
        z-index: 1;
    }

    .reset-card h2 { color: #333; font-weight: 700; }
    .reset-card p { color: #666; }
</style>

<div class="auth-container auth-bg">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xxl-4 col-xl-5 col-lg-6 col-md-8 col-12">

                
                <div class="card mt-3 mb-3 reset-card">
                    <div class="card-body p-4 p-md-5">

                        <?php if(session('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle me-2">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <span><?php echo e(session('success')); ?></span>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo e(route('password.send-code')); ?>">
                            <?php echo csrf_field(); ?>

                            <div class="text-center mb-4">
                                <h2>Password Reset</h2>
                                <p class="text-muted">Enter your email to receive verification code via WhatsApp</p>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-dark fw-bold">Email</label>
                                <input type="email"
                                       name="email"
                                       class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       value="<?php echo e(old('email')); ?>"
                                       placeholder="Enter your registered email"
                                       required
                                       autofocus>
                                <?php $__errorArgs = ['email'];
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

                            <div class="d-grid mb-4">
                                
                                <button type="submit" class="btn btn-primary" style="background-color: #6f42c1; border-color: #6f42c1;">
                                    SEND VERIFICATION CODE
                                </button>
                            </div>

                            <div class="text-center">
                                <p class="mb-0 text-secondary">
                                    Remember your password?
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

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/auth/password-request.blade.php ENDPATH**/ ?>