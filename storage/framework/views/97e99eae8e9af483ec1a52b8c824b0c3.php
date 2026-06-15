

<?php $__env->startSection('title', 'Earnings Overview'); ?>

<?php $__env->startSection('content'); ?>
<div class="container my-4">
    <h2 class="text-center fw-bold mb-4">📈 Weekly Earnings Overview</h2>

    <div class="row mb-4 text-center">
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm bg-light">
                <div class="card-body">
                    <h5>Total Bookings</h5>
                    <p class="fs-4 fw-bold"><?php echo e($totalBookings); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm bg-light">
                <div class="card-body">
                    <h5>Total Revenue</h5>
                    <p class="fs-4 fw-bold">RWF <?php echo e(number_format($totalRevenue)); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm bg-light">
                <div class="card-body">
                    <h5>Completion Rate</h5>
                    <p class="fs-4 fw-bold"><?php echo e($completionRate); ?>%</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm p-4">
        <h5 class="mb-3">📊 Revenue Chart (Past 7 Days)</h5>
        <img src="data:image/png;base64,<?php echo e($chart); ?>" class="img-fluid rounded" alt="Earnings Chart">
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\dashboard\provider\earnings.blade.php ENDPATH**/ ?>