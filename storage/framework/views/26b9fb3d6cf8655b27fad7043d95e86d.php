

<?php $__env->startSection('title', 'Leave a Review'); ?>

<?php $__env->startSection('content'); ?>
<div class="container my-5" style="max-width: 700px;">
    <h2 class="fw-bold text-center mb-4">
        <i class="ph ph-pencil-line text-primary me-1"></i> Leave a Review
    </h2>

    <?php if(session('success')): ?>
        <div class="alert alert-success text-center"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if($booking): ?>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="fw-bold mb-2">
                    <i class="ph ph-scissors me-1 text-muted"></i> 
                    Services You Booked:
                </h5>
                <ul class="list-unstyled">
                    <?php $__currentLoopData = $booking->services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><i class="ph ph-check-circle text-success me-1"></i> <?php echo e($service->name); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        </div>

        <form action="<?php echo e(route('customer.reviews.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="booking_id" value="<?php echo e($booking->id); ?>">
            
            <div class="mb-3">
                <label for="service_id" class="form-label">Select Service</label>
                <select name="service_id" id="service_id" class="form-select" required>
                    <option value="">Choose one</option>
                    <?php $__currentLoopData = $booking->services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($service->id); ?>"><?php echo e($service->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="rating" class="form-label">Rating (1 to 5)</label>
                <select name="rating" id="rating" class="form-select" required>
                    <?php for($i = 5; $i >= 1; $i--): ?>
                        <option value="<?php echo e($i); ?>"><?php echo e($i); ?> Star<?php echo e($i > 1 ? 's' : ''); ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="comment" class="form-label">Your Feedback</label>
                <textarea name="comment" id="comment" rows="4" class="form-control" placeholder="Write your review..." required></textarea>
            </div>

            <button type="submit" class="btn btn-dark w-100 rounded-pill">
                <i class="ph ph-paper-plane me-1"></i> Submit Review
            </button>
        </form>
    <?php else: ?>
        <div class="alert alert-warning text-center">
            Booking not found or unauthorized access.
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\dashboard\reviews\create.blade.php ENDPATH**/ ?>