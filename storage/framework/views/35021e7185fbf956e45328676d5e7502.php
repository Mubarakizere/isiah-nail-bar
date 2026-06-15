<?php $__env->startSection('title', 'Forgot Password'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-sm border-0 p-4 rounded-4" style="max-width: 420px; width: 100%;">
        <div class="text-center mb-4">
            <img src="<?php echo e(asset('storage/logo.png')); ?>" alt="Logo" style="width: 80px;">
            <h5 class="fw-bold mt-3">Forgot Password?</h5>
            <p class="text-muted small">Enter your email and we’ll send you a reset link.</p>
        </div>

        <?php if(session('status')): ?>
            <div class="alert alert-success small"><?php echo e(session('status')); ?></div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('password.email')); ?>">
            <?php echo csrf_field(); ?>

            <div class="mb-3">
                <label class="form-label">Email address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" value="<?php echo e(old('email')); ?>" class="form-control" required autofocus>
                </div>
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="d-grid">
                <button class="btn btn-dark rounded-pill" type="submit">
                    <i class="fas fa-paper-plane me-1"></i> Send Reset Link
                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\auth\forgot-password.blade.php ENDPATH**/ ?>