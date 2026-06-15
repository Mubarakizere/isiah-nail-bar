

<?php $__env->startSection('title', 'Manage Providers'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6">
    
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Providers</h1>
                <p class="text-gray-600">Manage service providers and staff</p>
            </div>
            <a href="<?php echo e(route('admin.providers.create')); ?>" 
               class="px-6 py-3 bg-gray-900 text-white font-semibold rounded-lg hover:bg-gray-800 transition">
                <i class="ph ph-plus-circle mr-2"></i>Add Provider
            </a>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Provider</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-semibold text-gray-900"><?php echo e($provider->name); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo e($provider->phone ?? '—'); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                                    <?php echo e($provider->active ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                    <?php echo e($provider->active ? 'Approved' : 'Pending'); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?php echo e(route('admin.providers.edit', $provider->id)); ?>" 
                                       class="px-3 py-1.5 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition"
                                       title="Edit">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>

                                    <a href="<?php echo e(route('admin.providers.hours.edit', $provider->id)); ?>" 
                                       class="px-3 py-1.5 bg-blue-100 text-blue-700 text-sm font-semibold rounded-lg hover:bg-blue-200 transition"
                                       title="Working Hours">
                                        <i class="ph ph-clock"></i>
                                    </a>

                                    <a href="<?php echo e(route('admin.providers.performance.single', $provider->id)); ?>" 
                                       class="px-3 py-1.5 bg-purple-100 text-purple-700 text-sm font-semibold rounded-lg hover:bg-purple-200 transition"
                                       title="Performance">
                                        <i class="ph ph-chart-line"></i>
                                    </a>

                                    <?php if(!$provider->active): ?>
                                        <form action="<?php echo e(route('admin.providers.approve', $provider->id)); ?>" method="POST" class="inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <button class="px-3 py-1.5 bg-green-100 text-green-700 text-sm font-semibold rounded-lg hover:bg-green-200 transition"
                                                    title="Approve">
                                                <i class="ph ph-check-circle"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form action="<?php echo e(route('admin.providers.decline', $provider->id)); ?>" method="POST" class="inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <button class="px-3 py-1.5 bg-orange-100 text-orange-700 text-sm font-semibold rounded-lg hover:bg-orange-200 transition"
                                                    title="Decline">
                                                <i class="ph ph-x-circle"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <button type="button"
                                            class="px-3 py-1.5 bg-red-100 text-red-700 text-sm font-semibold rounded-lg hover:bg-red-200 transition btn-delete"
                                            data-url="<?php echo e(route('admin.providers.destroy', $provider->id)); ?>"
                                            data-name="<?php echo e($provider->name); ?>"
                                            title="Delete">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <i class="ph ph-users-three text-5xl text-gray-300 mb-3"></i>
                                <p class="text-gray-500 font-semibold">No providers found</p>
                                <p class="text-gray-400 text-sm mt-1">Add your first provider to get started</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\admin\providers\index.blade.php ENDPATH**/ ?>