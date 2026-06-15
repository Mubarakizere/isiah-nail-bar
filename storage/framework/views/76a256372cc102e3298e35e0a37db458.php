

<?php $__env->startSection('title', 'My Services'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="fw-bold mb-0">
            <i class="ph ph-scissors me-1 text-primary"></i> My Services
        </h4>
        <a href="<?php echo e(route('provider.services.request')); ?>" class="btn btn-sm btn-outline-primary">
            <i class="ph ph-plus me-1"></i> Request New Service
        </a>
    </div>

    <!-- Alerts -->
    <?php if(session('success')): ?>
        <div class="alert alert-success text-center shadow-sm"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if($services->count()): ?>
        <!-- Services Table -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body table-responsive">
                <table class="table align-middle table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($service->id); ?></td>
                                <td>
                                    <img src="<?php echo e(asset('storage/' . ($service->image ?? 'placeholder.png'))); ?>" width="60" class="rounded shadow-sm" alt="Service Image">
                                </td>
                                <td class="fw-semibold"><?php echo e($service->name); ?></td>
                                <td><?php echo e($service->category->name); ?></td>
                                <td>RWF <?php echo e(number_format($service->price)); ?></td>
                                <td><?php echo e($service->duration_minutes); ?> min</td>
                                <td>
                                    <span class="badge bg-<?php echo e($service->approved ? 'success' : 'warning'); ?>">
                                        <?php echo e($service->approved ? 'Approved' : 'Pending'); ?>

                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                                        <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#serviceModal<?php echo e($service->id); ?>" title="View Details">
                                            <i class="ph ph-eye"></i>
                                        </button>

                                        <?php if($service->provider_id === auth()->user()->provider->id): ?>
                                            <a href="<?php echo e(route('provider.services.edit', $service->id)); ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="ph ph-pencil-simple"></i>
                                            </a>
                                            <form method="POST" action="<?php echo e(route('provider.services.destroy', $service->id)); ?>" onsubmit="return confirm('Are you sure you want to delete this service?')" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="ph ph-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted small">Assigned</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="mt-3">
                    <?php echo e($services->withQueryString()->links()); ?>

                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center shadow-sm">
            <i class="ph ph-warning-circle me-1"></i> No services assigned yet.
        </div>
    <?php endif; ?>
</div>

<!-- View Modals -->
<?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="modal fade" id="serviceModal<?php echo e($service->id); ?>" tabindex="-1" aria-labelledby="modalLabel<?php echo e($service->id); ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel<?php echo e($service->id); ?>"><?php echo e($service->name); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <img src="<?php echo e(asset('storage/' . ($service->image ?? 'placeholder.png'))); ?>" class="img-fluid rounded mb-3 shadow" alt="<?php echo e($service->name); ?>">
                    <p><strong>Category:</strong> <?php echo e($service->category->name); ?></p>
                    <p><strong>Price:</strong> RWF <?php echo e(number_format($service->price)); ?></p>
                    <p><strong>Duration:</strong> <?php echo e($service->duration_minutes); ?> minutes</p>
                    <p><strong>Description:</strong><br><?php echo e($service->description ?: '—'); ?></p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\dashboard\provider\services\index.blade.php ENDPATH**/ ?>