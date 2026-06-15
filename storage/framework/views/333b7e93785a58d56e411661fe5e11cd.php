<?php $__env->startSection('title', 'Providers Performance Overview'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6">
    
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Providers Performance Overview</h1>
        <p class="text-gray-600">Compare performance metrics across all providers</p>
    </div>

    
    <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-gray-800 to-gray-700 text-white">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">
                            <i class="ph ph-user mr-1"></i>Provider
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">
                            <i class="ph ph-calendar-check mr-1"></i>Total Bookings
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">
                            <i class="ph ph-check-circle mr-1"></i>Completed
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">
                            <i class="ph ph-percent mr-1"></i>Completion Rate
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">
                            <i class="ph ph-currency-circle-dollar mr-1"></i>Revenue
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider">
                            <i class="ph ph-gear mr-1"></i>Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                                        <?php echo e(substr($p->name, 0, 1)); ?>

                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900"><?php echo e($p->name); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                    <?php echo e(number_format($p->total)); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                    <?php echo e(number_format($p->completed)); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold
                                        <?php echo e($p->rate >= 75 ? 'bg-green-100 text-green-800' : ($p->rate >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')); ?>">
                                        <?php echo e($p->rate); ?>%
                                    </span>
                                    <div class="flex-1 max-w-[100px]">
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="h-2 rounded-full transition-all
                                                <?php echo e($p->rate >= 75 ? 'bg-green-500' : ($p->rate >= 50 ? 'bg-yellow-500' : 'bg-red-500')); ?>"
                                                style="width: <?php echo e($p->rate); ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-lg font-bold text-green-600">
                                    RWF <?php echo e(number_format($p->revenue)); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="<?php echo e(route('admin.providers.performance.single', $p->id)); ?>" 
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800 text-white text-sm font-semibold rounded-lg hover:bg-gray-900 transition">
                                    <i class="ph ph-chart-line"></i>
                                    <span>View Report</span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\dashboard\admin\providers-performance.blade.php ENDPATH**/ ?>