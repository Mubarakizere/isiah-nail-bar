<?php $__env->startSection('title', 'My Notifications'); ?>

<?php $__env->startSection('content'); ?>
<div class="container my-4">
    <h2 class="text-center fw-bold mb-4">
        <i class="ph ph-bell text-primary me-2"></i> Notifications
    </h2>

    <?php if($notifications->count()): ?>
        <div class="d-flex justify-content-end mb-3">
            <form action="<?php echo e(route('notifications.markAll')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <button class="btn btn-sm btn-outline-secondary">
                    <i class="ph ph-check-circle me-1"></i> Mark All as Read
                </button>
            </form>
        </div>

        <ul class="list-group shadow-sm">
            <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1"><?php echo e($notification->data['message'] ?? 'You have a new notification.'); ?></p>
                        <small class="text-muted"><?php echo e($notification->created_at->diffForHumans()); ?></small>
                    </div>

                    <?php if(is_null($notification->read_at)): ?>
                        <form action="<?php echo e(route('notifications.mark', $notification->id)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button class="btn btn-sm btn-outline-primary">Mark as Read</button>
                        </form>
                    <?php else: ?>
                        <span class="badge bg-light text-secondary">Read</span>
                    <?php endif; ?>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>

        <div class="mt-4">
            <?php echo e($notifications->links()); ?>

        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">
            <i class="ph ph-info me-1"></i> No notifications yet.
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\dashboard\customer\notifications.blade.php ENDPATH**/ ?>