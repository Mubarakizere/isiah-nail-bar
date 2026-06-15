<?php $__env->startSection('page-title', 'Customer Reviews'); ?>
<?php $__env->startSection('page-subtitle', 'Manage all reviews from customers and external sources'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto">
    
    <?php if(session('success')): ?>
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl flex items-center gap-3">
            <i class="ph ph-check-circle text-2xl"></i>
            <p class="font-medium"><?php echo e(session('success')); ?></p>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-center gap-3">
            <i class="ph ph-warning-circle text-2xl"></i>
            <p class="font-medium"><?php echo e(session('error')); ?></p>
        </div>
    <?php endif; ?>

    
    <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <p class="text-sm text-gray-500">
                Showing <span class="font-medium text-gray-900"><?php echo e($reviews->total()); ?></span> reviews
                · <span class="font-medium text-blue-600"><?php echo e(\App\Models\Review::where('source', 'google')->count()); ?> from Google</span>
            </p>
        </div>
        <a href="<?php echo e(route('admin.reviews.create')); ?>" 
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-900 text-white font-medium rounded-xl hover:bg-rose-600 transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5 text-sm">
            <i class="ph ph-google-logo"></i>
            Add Google Review
        </a>
    </div>

    
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <?php if($reviews->count()): ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Service</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Rating</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Comment</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Source</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <?php if($review->customer && $review->customer->user): ?>
                                            
                                            <?php if($review->customer->user->photo): ?>
                                                <img src="<?php echo e(asset('storage/' . $review->customer->user->photo)); ?>" 
                                                     class="w-10 h-10 rounded-full object-cover">
                                            <?php else: ?>
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-rose-400 to-rose-600 flex items-center justify-center text-white font-bold">
                                                    <?php echo e(strtoupper(substr($review->customer->user->name, 0, 1))); ?>

                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <p class="font-medium text-gray-900"><?php echo e($review->customer->user->name); ?></p>
                                                <p class="text-xs text-gray-500"><?php echo e($review->customer->user->email); ?></p>
                                            </div>
                                        <?php else: ?>
                                            
                                            <?php if($review->avatar_url): ?>
                                                <img src="<?php echo e($review->avatar_url); ?>" 
                                                     class="w-10 h-10 rounded-full object-cover">
                                            <?php else: ?>
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold">
                                                    <?php echo e(strtoupper(substr($review->reviewer_name ?? 'G', 0, 1))); ?>

                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <p class="font-medium text-gray-900"><?php echo e($review->reviewer_name ?? 'Anonymous'); ?></p>
                                                <p class="text-xs text-gray-500">External Review</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                
                                <td class="px-6 py-4">
                                    <?php if($review->service): ?>
                                        <span class="inline-flex items-center gap-2 px-3 py-1 bg-rose-50 text-rose-700 rounded-full text-sm font-medium">
                                            <i class="ph ph-scissors"></i>
                                            <?php echo e($review->service->name); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-sm">General Review</span>
                                    <?php endif; ?>
                                </td>

                                
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <?php if($i <= $review->rating): ?>
                                                <i class="ph-fill ph-star text-yellow-400 text-lg"></i>
                                            <?php else: ?>
                                                <i class="ph ph-star text-gray-300 text-lg"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                        <span class="ml-2 text-sm font-medium text-gray-600"><?php echo e($review->rating); ?>/5</span>
                                    </div>
                                </td>

                                
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-700 max-w-md truncate">
                                        <?php echo e($review->comment ?? '—'); ?>

                                    </p>
                                </td>

                                
                                <td class="px-6 py-4">
                                    <?php if($review->source === 'google'): ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 rounded-md text-xs font-medium">
                                            <i class="ph ph-google-logo"></i>
                                            Google
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-50 text-green-700 rounded-md text-xs font-medium">
                                            <i class="ph ph-check-circle"></i>
                                            Internal
                                        </span>
                                    <?php endif; ?>
                                </td>

                                
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-600"><?php echo e($review->created_at->format('M d, Y')); ?></p>
                                    <p class="text-xs text-gray-400"><?php echo e($review->created_at->diffForHumans()); ?></p>
                                </td>

                                
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="<?php echo e(route('admin.reviews.edit', $review->id)); ?>" 
                                           class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-50 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium transition-colors">
                                            <i class="ph ph-pencil-simple"></i>
                                            Edit
                                        </a>
                                        <form action="<?php echo e(route('admin.reviews.destroy', $review->id)); ?>" method="POST" 
                                              onsubmit="return confirm('Are you sure you want to delete this review?');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" 
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg text-sm font-medium transition-colors">
                                                <i class="ph ph-trash"></i>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <?php echo e($reviews->links()); ?>

            </div>
        <?php else: ?>
            
            <div class="py-16 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                    <i class="ph ph-star text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Reviews Yet</h3>
                <p class="text-gray-500 max-w-sm mx-auto mb-6">
                    Start by adding your Google Business reviews to showcase on your homepage.
                </p>
                <a href="<?php echo e(route('admin.reviews.create')); ?>" 
                   class="inline-flex items-center gap-2 px-6 py-3 bg-gray-900 text-white font-medium rounded-xl hover:bg-rose-600 transition-all shadow-lg">
                    <i class="ph ph-google-logo"></i>
                    Add Your First Google Review
                </a>
            </div>
        <?php endif; ?>
    </div>

    
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-rose-100 rounded-lg flex items-center justify-center">
                    <i class="ph ph-star text-2xl text-rose-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Reviews</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo e($reviews->total()); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="ph ph-trend-up text-2xl text-yellow-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Average Rating</p>
                    <p class="text-2xl font-bold text-gray-900">
                        <?php echo e($reviews->count() > 0 ? number_format($reviews->avg('rating'), 1) : '0.0'); ?>/5
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="ph ph-google-logo text-2xl text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Google Reviews</p>
                    <p class="text-2xl font-bold text-gray-900">
                        <?php echo e(\App\Models\Review::where('source', 'google')->count()); ?>

                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\dashboard\admin\reviews\index.blade.php ENDPATH**/ ?>