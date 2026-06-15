<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Booking Details</h2>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><?php echo e($booking->service->name); ?></h5>
            <p><strong>Provider:</strong> <?php echo e($booking->provider->name); ?></p>
            <p><strong>Date:</strong> <?php echo e($booking->date); ?> at <?php echo e($booking->time); ?></p>
            <p><strong>Status:</strong> <?php echo e(ucfirst($booking->status)); ?></p>
            <p><strong>Payment:</strong> <?php echo e(ucfirst($booking->payment_status)); ?></p>

            <a href="<?php echo e(route('dashboard.customer')); ?>" class="btn btn-secondary">Back to Bookings</a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\dashboard\customer-show.blade.php ENDPATH**/ ?>