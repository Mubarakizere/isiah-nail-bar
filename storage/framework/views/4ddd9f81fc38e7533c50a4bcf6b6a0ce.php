<?php $__env->startSection('title', 'Server Error'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-[60vh] flex items-center justify-center py-24">
    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="mb-8 flex justify-center">
            <div class="w-24 h-24 bg-rose-50 rounded-full flex items-center justify-center">
                <i class="ph ph-warning-circle text-4xl text-rose-500"></i>
            </div>
        </div>
        <h1 class="text-7xl font-bold text-gray-900 font-serif mb-4 tracking-tight">500</h1>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Internal Server Error</h2>
        <p class="text-gray-500 mb-10 text-lg">
            Oops, something went wrong on our end. We're working to fix the issue. Please try again later.
        </p>
        <a href="<?php echo e(url('/')); ?>" class="inline-flex items-center px-8 py-3.5 bg-gray-900 text-white text-base font-medium rounded-full hover:bg-rose-600 transition-all duration-300 shadow-md hover:shadow-lg hover:-translate-y-0.5">
            <i class="ph ph-arrow-left mr-2"></i>
            Back to Home
        </a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\errors\500.blade.php ENDPATH**/ ?>