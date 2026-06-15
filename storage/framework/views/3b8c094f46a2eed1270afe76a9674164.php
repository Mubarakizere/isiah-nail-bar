<?php $__env->startSection('title', 'Customer Reviews'); ?>

<?php $__env->startSection('content'); ?>
<div class="container my-5">
    <h2 class="fw-bold text-center mb-4">
        <i class="ph ph-chat-circle-dots me-1 text-primary"></i> Customer Reviews
    </h2>

    <?php if($reviews->count()): ?>
        <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h5 class="fw-bold mb-1">
                                <i class="ph ph-user me-1 text-muted"></i>
                                <?php echo e($review->customer->user->name ?? 'Unknown Customer'); ?>

                            </h5>
                            <p class="text-muted mb-1">
                                <i class="ph ph-scissors me-1"></i>
                                <strong>Service:</strong> <?php echo e($review->service->name ?? '—'); ?>

                            </p>
                            <p class="mb-2"><?php echo e($review->message); ?></p>

                            <div class="small text-muted">
                                <i class="ph ph-star-fill text-warning me-1"></i>
                                <?php echo e($review->rating); ?> / 5
                                &middot;
                                <i class="ph ph-clock me-1"></i>
                                <?php echo e($review->created_at->diffForHumans()); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <div class="d-flex justify-content-center mt-4">
            <?php echo e($reviews->links()); ?>

        </div>
    <?php else: ?>
        <div class="text-center text-muted">
            <i class="ph ph-chat-centered-dots fs-1 mb-3 d-block"></i>
            You haven’t received any reviews yet.
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('dashboard.provider.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\dashboard\provider\reviews\index.blade.php ENDPATH**/ ?>