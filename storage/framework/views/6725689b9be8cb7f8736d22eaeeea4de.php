<?php $__env->startSection('title', 'Pending Services'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6">
    
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Pending Services</h1>
        <p class="text-gray-600">Review and approve/reject service submissions from providers</p>
    </div>

    <?php if($pendingServices->count()): ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <?php $__currentLoopData = $pendingServices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white rounded-2xl shadow-md border-2 border-yellow-200 overflow-hidden hover:shadow-xl transition">
                    
                    <div class="p-6 pb-4">
                        <div class="flex items-start justify-between mb-3">
                            <h3 class="text-lg font-bold text-gray-900 flex-1"><?php echo e($service->name); ?></h3>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 flex-shrink-0 ml-2">
                                <i class="ph ph-clock mr-1"></i>Pending
                            </span>
                        </div>

                        
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <i class="ph ph-folder text-gray-400"></i>
                                <span><?php echo e($service->category->name ?? '—'); ?></span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <i class="ph ph-timer text-gray-400"></i>
                                <span><?php echo e($service->duration_minutes); ?> minutes</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm font-semibold text-green-600">
                                <i class="ph ph-currency-circle-dollar text-green-500"></i>
                                <span>RWF <?php echo e(number_format($service->price)); ?></span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <i class="ph ph-user text-gray-400"></i>
                                <span>By <strong><?php echo e($service->provider->user->name ?? 'Unknown'); ?></strong></span>
                            </div>
                        </div>

                        
                        <p class="text-sm text-gray-600 leading-relaxed">
                            <?php echo e(\Illuminate\Support\Str::limit($service->description, 120)); ?>

                        </p>
                    </div>

                    
                    <div class="p-6 pt-0 flex gap-2">
                        <form action="<?php echo e(route('admin.services.approve', $service->id)); ?>" method="POST" class="flex-1">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="w-full px-4 py-2.5 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                                <i class="ph ph-check-circle mr-1"></i>Approve
                            </button>
                        </form>
                        <form action="<?php echo e(route('admin.services.reject', $service->id)); ?>" method="POST" class="flex-1">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="w-full px-4 py-2.5 bg-red-100 text-red-700 font-semibold rounded-lg hover:bg-red-200 transition">
                                <i class="ph ph-x-circle mr-1"></i>Reject
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <?php if($pendingServices->hasPages()): ?>
            <div class="flex justify-center">
                <?php echo e($pendingServices->links()); ?>

            </div>
        <?php endif; ?>
    <?php else: ?>
        
        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-12 text-center">
            <i class="ph ph-check-circle text-6xl text-blue-300 mb-4"></i>
            <p class="text-blue-900 font-semibold text-lg">All caught up!</p>
            <p class="text-blue-700 text-sm mt-2">No pending services to review at the moment</p>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\dashboard\admin\services\pending.blade.php ENDPATH**/ ?>