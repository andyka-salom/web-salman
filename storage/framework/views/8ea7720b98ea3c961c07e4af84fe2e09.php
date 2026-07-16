

<?php $__env->startSection('title', 'Sign In'); ?>

<?php $__env->startSection('content'); ?>

<style>
    /* 1. Setting Background Halaman */
    .auth-bg {
        /* Gambar Oil Rig/Offshore seperti di screenshot Anda */
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

    /* 2. Overlay Gelap di Background (Opsional, agar mata lebih rileks) */
    .auth-bg::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5); /* Hitam transparan 50% */
        z-index: 0;
    }

    /* 3. Style Khusus untuk Card agar Putih Solid */
    .login-card {
        background-color: #ffffff !important; /* Wajib Putih */
        border-radius: 12px; /* Sudut agak membulat */
        box-shadow: 0 10px 30px rgba(0,0,0,0.3); /* Bayangan agar card 'melayang' */
        border: none;
        position: relative;
        z-index: 1; /* Agar berada di atas overlay */
    }

    /* Judul dan Teks agar sesuai dengan background putih */
    .login-card h2 {
        color: #333;
        font-weight: 700;
    }
    .login-card p {
        color: #666;
    }
    .btn-verify-login {
        border-color: #e5e7eb;
        color: #4b5563;
        background: #f9fafb;
        font-weight: 600;
        padding: 0.65rem 1rem;
        transition: all 0.2s ease;
        border-radius: 8px;
    }
    .btn-verify-login:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
        color: #1f2937;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
</style>

<div class="auth-container auth-bg">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xxl-4 col-xl-5 col-lg-6 col-md-8 col-12">

                
                <div class="card mt-3 mb-3 login-card">
                    <div class="card-body p-4 p-md-5">

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

                        <form method="POST" action="<?php echo e(route('login')); ?>">
                            <?php echo csrf_field(); ?>

                            <div class="text-center mb-4">
                                <h2>Sign In</h2>
                                <p>Enter your email and password to login</p>
                            </div>

                            <div class="mb-3">
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
                                       placeholder="Enter your email"
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

                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">Password</label>
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
                                       placeholder="Enter your password"
                                       required>
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
                            </div>

                            <div class="mb-4 d-flex justify-content-between align-items-center">
                                <a href="<?php echo e(route('password.request')); ?>" class="text-primary text-decoration-none fw-bold">
                                    Forgot Password?
                                </a>
                            </div>

                            <div class="d-grid mb-3">
                                
                                <button type="submit" class="btn btn-primary" style="background-color: #6f42c1; border-color: #6f42c1; padding: 0.65rem 1rem; font-weight: 600;">
                                    SIGN IN
                                </button>
                            </div>

                            <div class="d-grid mb-4">
                                <a href="<?php echo e(route('certificate-verification.index')); ?>" class="btn btn-verify-login d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-patch-check-fill text-primary"></i> Verifikasi Sertifikat Kru
                                </a>
                            </div>

                            <div class="text-center">
                                <p class="mb-0 text-secondary">
                                    Don't have an account?
                                    <a href="<?php echo e(route('register')); ?>" class="text-warning fw-bold text-decoration-none">Sign Up</a>
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

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/auth/login.blade.php ENDPATH**/ ?>