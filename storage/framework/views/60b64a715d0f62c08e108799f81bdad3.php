

<?php $__env->startSection('title', 'Secure Payment'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-3 py-5" style="max-width: 960px; margin: auto;">
    <div class="text-center mb-4">
        <h2 class="fw-bold">💳 Complete Your Payment</h2>
        <p class="text-muted">Please don’t close this page. The payment will process securely in the frame below.</p>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="responsive-iframe-wrapper">
            <iframe src="<?php echo e($iframeUrl); ?>" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>

    <div class="text-center mt-4 text-muted small">
        If the payment fails or you close this page, your booking will remain pending. You can retry from your dashboard.
    </div>
</div>

<style>
    .responsive-iframe-wrapper {
        position: relative;
        width: 100%;
        padding-bottom: 75vh; /* Use height-based ratio to give more space on mobile */
        height: 0;
    }

    .responsive-iframe-wrapper iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
    }

    @media (min-width: 768px) {
        .responsive-iframe-wrapper {
            padding-bottom: 60vh; /* reduce height on larger screens */
        }
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\payment\iframe.blade.php ENDPATH**/ ?>