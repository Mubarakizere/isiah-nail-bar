

<?php $__env->startSection('title', 'Manage Services'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6">
    
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Services</h1>
                <p class="text-gray-600">Manage all services</p>
            </div>
            <a href="<?php echo e(route('admin.services.create')); ?>" 
               class="px-6 py-3 bg-gray-900 text-white font-semibold rounded-lg hover:bg-gray-800 transition">
                <i class="ph ph-plus-circle mr-2"></i>Add Service
            </a>
        </div>
    </div>

    
    <form method="GET" class="bg-white rounded-2xl p-6 shadow-md border border-gray-200 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
            <div class="lg:col-span-2">
                <input type="text" 
                       name="search" 
                       value="<?php echo e(request('search')); ?>" 
                       placeholder="Search services..."
                       class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
            </div>
            <div>
                <select name="category_id" class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                    <option value="">All Categories</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($cat->id); ?>" <?php echo e(request('category_id') == $cat->id ? 'selected' : ''); ?>>
                            <?php echo e($cat->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <input type="number" 
                       name="min_price" 
                       value="<?php echo e(request('min_price')); ?>" 
                       placeholder="Min Price"
                       class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
            </div>
            <div>
                <input type="number" 
                       name="max_price" 
                       value="<?php echo e(request('max_price')); ?>" 
                       placeholder="Max Price"
                       class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
            </div>
            <div>
                <button type="submit" 
                        class="w-full px-4 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    <i class="ph ph-funnel mr-1"></i>Filter
                </button>
            </div>
        </div>
    </form>

    
    <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Service</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Category</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Price</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Created</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-semibold text-gray-900"><?php echo e($service->name); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo e($service->category->name ?? '—'); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-semibold text-green-600">RWF <?php echo e(number_format($service->price)); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                                    <?php echo e(match($service->status) {
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'approved' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    }); ?>">
                                    <?php echo e(ucfirst($service->status)); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo e($service->created_at->diffForHumans()); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?php echo e(route('admin.services.edit', $service->id)); ?>" 
                                       class="px-3 py-1.5 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>

                                    <?php if($service->status === 'pending'): ?>
                                        <form action="<?php echo e(route('admin.services.approve', $service->id)); ?>" method="POST" class="inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <button class="px-3 py-1.5 bg-green-100 text-green-700 text-sm font-semibold rounded-lg hover:bg-green-200 transition">
                                                <i class="ph ph-check"></i>
                                            </button>
                                        </form>

                                        <form action="<?php echo e(route('admin.services.decline', $service->id)); ?>" method="POST" class="inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <button class="px-3 py-1.5 bg-red-100 text-red-700 text-sm font-semibold rounded-lg hover:bg-red-200 transition">
                                                <i class="ph ph-x"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <button type="button"
                                            class="px-3 py-1.5 bg-red-100 text-red-700 text-sm font-semibold rounded-lg hover:bg-red-200 transition btn-delete"
                                            data-url="<?php echo e(route('admin.services.destroy', $service->id)); ?>"
                                            data-name="<?php echo e($service->name); ?>">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <i class="ph ph-scissors text-5xl text-gray-300 mb-3"></i>
                                <p class="text-gray-500 font-semibold">No services found</p>
                                <p class="text-gray-400 text-sm mt-1">Try adjusting your filters or add a new service</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <?php if($services->hasPages()): ?>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <?php echo e($services->withQueryString()->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>


<div id="confirmDeleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="ph ph-warning text-2xl text-red-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Confirm Deletion</h3>
                    <p class="text-sm text-gray-600">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-gray-700 mb-6">
                Are you sure you want to delete <strong id="delete-item-name" class="text-gray-900"></strong>?
            </p>
            <div class="flex gap-3">
                <button type="button" 
                        onclick="closeDeleteModal()"
                        class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">
                    Cancel
                </button>
                <form method="POST" id="deleteForm" class="flex-1">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" 
                            class="w-full px-4 py-2.5 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition">
                        <i class="ph ph-trash mr-1"></i>Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const modal = document.getElementById('confirmDeleteModal');
const deleteForm = document.getElementById('deleteForm');
const itemNameSpan = document.getElementById('delete-item-name');

document.querySelectorAll('.btn-delete').forEach(button => {
    button.addEventListener('click', function () {
        const action = this.getAttribute('data-url');
        const name = this.getAttribute('data-name');
        
        itemNameSpan.textContent = name;
        deleteForm.setAttribute('action', action);
        
        modal.classList.remove('hidden');
    });
});

function closeDeleteModal() {
    modal.classList.add('hidden');
}

modal.addEventListener('click', function(e) {
    if (e.target === modal) {
        closeDeleteModal();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
        closeDeleteModal();
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\admin\services\index.blade.php ENDPATH**/ ?>