<?php $__env->startSection('title', 'Notifications'); ?>

<?php $__env->startSection('content'); ?>
<div class="container my-5">
    <h2 class="fw-bold text-center mb-4">
        <i class="ph ph-bell-ringing me-1 text-primary"></i> My Notifications
    </h2>

    <?php if($notifications->count()): ?>
    <?php if($notifications->count()): ?>
    <div class="d-flex justify-content-end mb-3">
        <form method="POST" action="<?php echo e(route('notifications.markAll')); ?>">
            <?php echo csrf_field(); ?>
            <button class="btn btn-sm btn-outline-success">
                <i class="ph ph-checks me-1"></i> Mark All as Read
            </button>
        </form>
    </div>
<?php endif; ?>

        <div class="list-group shadow-sm">
            <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="list-group-item d-flex justify-content-between align-items-start <?php echo e(is_null($notification->read_at) ? 'bg-light' : ''); ?>">
                    <div>
                        <p class="mb-1">
                            <i class="ph ph-info me-1 text-secondary"></i>
                            <?php echo e($notification->data['message'] ?? 'New notification'); ?>

                        </p>
                        <small class="text-muted">
                            <i class="ph ph-clock me-1"></i>
                            <?php echo e($notification->created_at->diffForHumans()); ?>

                        </small>
                    </div>

                    <?php if(is_null($notification->read_at)): ?>
                        <form method="POST" action="<?php echo e(route('notifications.mark', $notification->id)); ?>">
                            <?php echo csrf_field(); ?>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="ph ph-check-circle"></i> Mark as Read
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="d-flex justify-content-center mt-4">
            <?php echo e($notifications->links()); ?>

        </div>
    <?php else: ?>
        <div class="text-center text-muted mt-5">
            <i class="ph ph-bell-slash fs-1 mb-3 d-block"></i>
            You have no notifications at the moment.
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('dashboard.provider.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\dashboard\provider\notifications.blade.php ENDPATH**/ ?>