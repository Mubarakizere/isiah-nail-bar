<?php $__env->startSection('title', 'Booking Receipt'); ?>

<?php $__env->startSection('content'); ?>
<div class="container my-5">
    <h2 class="fw-bold mb-4">📄 Admin View: Booking #<?php echo e($booking->id); ?></h2>

    <div class="card shadow-sm p-4">
        <ul class="list-group list-group-flush mb-4">
            <li class="list-group-item"><strong>Customer:</strong> <?php echo e($booking->customer->user->name); ?></li>
            <li class="list-group-item"><strong>Service:</strong> <?php echo e($booking->service->name); ?></li>
            <li class="list-group-item"><strong>Provider:</strong> <?php echo e($booking->provider->user->name); ?></li>
            <li class="list-group-item"><strong>Date:</strong> <?php echo e(\Carbon\Carbon::parse($booking->date)->format('D, M j Y')); ?></li>
            <li class="list-group-item"><strong>Time:</strong> <?php echo e(\Carbon\Carbon::parse($booking->time)->format('H:i')); ?></li>
            <li class="list-group-item"><strong>Status:</strong> <?php echo e(ucfirst($booking->status)); ?></li>
            <li class="list-group-item"><strong>Payment:</strong>
                <?php echo e($booking->payment_option === 'deposit' ? 'Deposit (RWF 10,000)' : 'Full Payment'); ?>

            </li>
        </ul>

        <div class="d-flex justify-content-center gap-3">
            <a href="<?php echo e(route('booking.receipt', $booking->id)); ?>" target="_blank" class="btn btn-outline-secondary">View as Customer</a>
            <a href="<?php echo e(route('download.receipt', $booking->id)); ?>" class="btn btn-success">Download PDF</a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\admin\bookings\receipt.blade.php ENDPATH**/ ?>