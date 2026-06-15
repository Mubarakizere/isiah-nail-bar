

<?php $__env->startSection('title', 'Verify Email'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-sm border-0 p-4 rounded-4" style="max-width: 450px; width: 100%;">
        <div class="text-center mb-4">
            <img src="<?php echo e(asset('storage/logo.png')); ?>" alt="Logo" style="width: 80px;">
            <h5 class="fw-bold mt-3">Verify Your Email</h5>
            <p class="text-muted small">
                We’ve sent a verification link to your email. If you didn’t receive it, we’ll gladly send another.
                <br>
                <span class="text-danger fw-semibold">👉 Don’t forget to check your <u>Spam or Promotions</u> folder too!</span>
            </p>
        </div>

        <?php if(session('status') == 'verification-link-sent'): ?>
            <div class="alert alert-success small text-center mb-4">
                A new verification link has been sent to your email.
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between flex-wrap gap-2">
            <form method="POST" action="<?php echo e(route('verification.send')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-dark rounded-pill">
                    <i class="fas fa-paper-plane me-1"></i> Resend Link
                </button>
            </form>

            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-outline-secondary rounded-pill">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </button>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\auth\verify-email.blade.php ENDPATH**/ ?>