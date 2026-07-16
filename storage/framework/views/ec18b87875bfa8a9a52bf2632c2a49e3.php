<?php $__env->startSection('title', '2-Step Verification'); ?>

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
    /* Style tambahan untuk input OTP agar lebih jelas di background putih */
    .opt-input {
        border: 2px solid #e0e6ed;
        color: #3b3f5c;
        font-weight: bold;
    }
    .opt-input:focus {
        border-color: #6f42c1;
        box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
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

                        <form method="POST" action="<?php echo e(route('password.verify-code')); ?>" id="verifyForm">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="email" value="<?php echo e(session('email')); ?>">

                            <div class="text-center mb-4">
                                <h2 class="text-dark fw-bold">2-Step Verification</h2>
                                <p class="text-muted">Enter the 6-digit code sent to your WhatsApp</p>
                            </div>

                            <div class="d-flex justify-content-center gap-2 mb-4">
                                <?php for($i = 0; $i < 6; $i++): ?>
                                <input type="text"
                                       class="form-control text-center opt-input <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       maxlength="1"
                                       pattern="[0-9]"
                                       data-index="<?php echo e($i); ?>"
                                       style="width: 50px; height: 50px; font-size: 24px;"
                                       <?php echo e($i == 0 ? 'autofocus' : ''); ?>>
                                <?php endfor; ?>
                                <input type="hidden" name="code" id="codeInput">
                            </div>

                            <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="alert alert-danger mb-3"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-primary" style="background-color: #6f42c1; border-color: #6f42c1;">
                                    VERIFY
                                </button>
                            </div>

                            <div class="text-center">
                                <p class="mb-0 text-muted">
                                    Didn't receive the code?
                                    <a href="#" id="resendBtn" class="text-warning fw-bold text-decoration-none">Resend</a>
                                </p>
                                <p class="mb-0 mt-3">
                                    <a href="<?php echo e(route('login')); ?>" class="text-secondary text-decoration-none">Back to Sign In</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.opt-input');
    const codeInput = document.getElementById('codeInput');

    // (Script logika OTP tetap sama seperti sebelumnya)
    inputs.forEach((input, index) => {
        input.addEventListener('input', function(e) {
            if (this.value.length === 1) {
                if (index < inputs.length - 1) inputs[index + 1].focus();
            }
            updateCode();
        });
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && this.value === '') {
                if (index > 0) inputs[index - 1].focus();
            }
        });
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text');
            const digits = pastedData.replace(/\D/g, '').slice(0, 6);
            digits.split('').forEach((digit, i) => {
                if (inputs[i]) inputs[i].value = digit;
            });
            if (inputs[digits.length]) inputs[digits.length].focus();
            else if (digits.length === 6) inputs[5].focus();
            updateCode();
        });
    });

    function updateCode() {
        let code = '';
        inputs.forEach(input => { code += input.value; });
        codeInput.value = code;
    }

    document.getElementById('resendBtn').addEventListener('click', function(e) {
        e.preventDefault();
        if (confirm('Resend verification code?')) {
            const email = '<?php echo e(session('email')); ?>';
            fetch('<?php echo e(route('password.resend-code')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                alert('Verification code has been resent!');
                inputs.forEach(input => input.value = '');
                inputs[0].focus();
            })
            .catch(error => {
                alert('Failed to resend code. Please try again.');
            });
        }
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaptensa/salman/resources/views/auth/password-verify.blade.php ENDPATH**/ ?>