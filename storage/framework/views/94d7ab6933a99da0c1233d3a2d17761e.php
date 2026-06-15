

<?php $__env->startSection('title', 'My Reviews'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <h2 class="fw-bold text-center mb-4 text-dark">
        <i class="ph ph-star text-warning me-1"></i> My Reviews
    </h2>

    <?php if(session('success')): ?>
        <div class="alert alert-success text-center shadow-sm rounded"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php $__empty_1 = true; $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">
                            <i class="ph ph-scissors me-1 text-secondary"></i> <?php echo e($review->service->name); ?>

                        </h5>

                        <div class="mb-1">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="ph <?php echo e($i <= $review->rating ? 'ph-star-fill text-warning' : 'ph-star text-muted'); ?>"></i>
                            <?php endfor; ?>
                            <small class="text-muted ms-2">(<?php echo e($review->rating); ?> / 5)</small>
                        </div>

                        <?php if($review->booking && $review->booking->provider): ?>
                            <p class="text-muted mb-0">
                                <i class="ph ph-user me-1"></i> With <?php echo e($review->booking->provider->name); ?>

                            </p>
                        <?php endif; ?>

                        <p class="text-muted small mb-0">
                            <i class="ph ph-calendar me-1"></i> <?php echo e($review->created_at->format('F j, Y')); ?>

                        </p>
                    </div>

                    <form method="POST" action="<?php echo e(route('customer.reviews.destroy', $review->id)); ?>" onsubmit="return confirm('Are you sure you want to delete this review?')" class="ms-3">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Review">
                            <i class="ph ph-trash"></i>
                        </button>
                    </form>
                </div>

                <hr class="my-3">
                <p class="mb-0"><?php echo e($review->comment); ?></p>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="text-center text-muted mt-5">
            <i class="ph ph-star text-secondary fs-1 mb-3 d-block"></i>
            <p class="mb-0">You haven’t left any reviews yet.</p>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-center mt-4">
        <?php echo e($reviews->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\dashboard\customer\reviews\index.blade.php ENDPATH**/ ?>