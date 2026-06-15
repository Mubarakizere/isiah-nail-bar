<?php $__env->startSection('title', 'My Services'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-2 flex items-center justify-center gap-3">
            <i class="ph ph-sparkle text-blue-600"></i>
            <span>My Services</span>
        </h2>
    </div>

    <?php if($services->isEmpty()): ?>
        <div class="bg-white rounded-2xl shadow-lg p-16 text-center border border-gray-200">
            <div class="w-24 h-24 mx-auto mb-6 bg-blue-50 rounded-full flex items-center justify-center">
                <i class="ph ph-sparkle text-5xl text-blue-600"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">No Services Yet</h3>
            <p class="text-gray-600 mb-6">You haven't added any services yet. Start building your service portfolio!</p>
            <a href="<?php echo e(route('provider.services.request')); ?>" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-indigo-700 transition">
                <i class="ph ph-plus"></i>
                <span>Add Service</span>
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 hover:border-blue-200">
                    <div class="h-48 overflow-hidden bg-gray-100">
                        <img src="<?php echo e(asset('storage/' . ($service->image ?? 'default.jpg'))); ?>" 
                             alt="<?php echo e($service->name); ?>"
                             class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
                    </div>
                    <div class="p-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-2"><?php echo e($service->name); ?></h3>
                        <p class="text-blue-600 font-bold mb-3">RWF <?php echo e(number_format($service->price)); ?></p>
                        <p class="text-gray-600 text-sm line-clamp-2"><?php echo e($service->description); ?></p>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="mt-8">
            <?php echo e($services->links()); ?>

        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\providers\services\index.blade.php ENDPATH**/ ?>