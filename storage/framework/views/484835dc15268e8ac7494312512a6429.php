 

<?php $__env->startSection('title', 'Provider Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 bg-white shadow-sm px-0 position-sticky min-vh-100" style="top: 0;">
            <div class="d-flex flex-column p-3">
                <h5 class="fw-bold mb-4 text-center text-primary">📋 My Dashboard</h5>

                <ul class="nav flex-column nav-pills gap-2">
                    <li class="nav-item">
                        <a href="<?php echo e(route('dashboard.provider')); ?>" class="nav-link <?php echo e(request()->routeIs('dashboard.provider') ? 'active' : ''); ?>">
                            📊 Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo e(route('provider.services.index')); ?>" class="nav-link <?php echo e(request()->routeIs('provider.services.*') ? 'active' : ''); ?>">
                            💼 My Services
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo e(route('provider.services.request')); ?>" class="nav-link <?php echo e(request()->routeIs('provider.services.request') ? 'active' : ''); ?>">
                            ➕ Request Service
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo e(route('provider.reviews.index')); ?>" class="nav-link <?php echo e(request()->routeIs('provider.reviews.index') ? 'active' : ''); ?>">
                            ⭐ My Reviews
                        </a>
                    </li>
                    <li class="nav-item mt-3">
                        <form action="<?php echo e(route('logout')); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button class="btn btn-outline-danger w-100">🚪 Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <div class="col-md-9 col-lg-10 py-4 px-4">
            <?php echo $__env->yieldContent('provider-content'); ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\dashboard\provider\layout.blade.php ENDPATH**/ ?>