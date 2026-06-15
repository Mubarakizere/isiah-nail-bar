

<?php $__env->startSection('title', 'Revenue Reports'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6">
    
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Revenue Reports</h1>
                <p class="text-gray-600">Analyze revenue and bookings over time</p>
            </div>
            <a href="<?php echo e(route('dashboard.admin')); ?>" class="px-4 py-2 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">
                ← Back
            </a>
        </div>
    </div>

    
    <form method="GET" class="bg-white rounded-2xl p-6 shadow-md border border-gray-200 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-7 gap-4 items-end">
            <div class="md:col-span-3">
                <label class="block text-sm font-semibold text-gray-700 mb-2">From Date</label>
                <input type="date" 
                       name="from" 
                       value="<?php echo e($from); ?>" 
                       class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
            </div>
            <div class="md:col-span-3">
                <label class="block text-sm font-semibold text-gray-700 mb-2">To Date</label>
                <input type="date" 
                       name="to" 
                       value="<?php echo e($to); ?>" 
                       class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
            </div>
            <div class="md:col-span-1 flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    <i class="ph ph-magnifying-glass"></i>
                </button>
                <a href="<?php echo e(route('dashboard.admin.reports.pdf', ['from' => $from, 'to' => $to])); ?>" 
                   class="flex-1 px-4 py-2.5 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition text-center">
                    <i class="ph ph-file-pdf"></i>
                </a>
            </div>
        </div>
    </form>

    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="ph ph-currency-circle-dollar text-2xl"></i>
                </div>
            </div>
            <p class="text-green-100 text-sm mb-1">Total Revenue</p>
            <h3 class="text-3xl font-bold">RWF <?php echo e(number_format($total)); ?></h3>
        </div>

        
        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="ph ph-calendar-check text-2xl"></i>
                </div>
            </div>
            <p class="text-blue-100 text-sm mb-1">Total Bookings</p>
            <h3 class="text-3xl font-bold"><?php echo e(number_format(count($bookings))); ?></h3>
        </div>

        
        <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="ph ph-chart-line text-2xl"></i>
                </div>
            </div>
            <p class="text-purple-100 text-sm mb-1">Avg. per Booking</p>
            <h3 class="text-3xl font-bold">
                RWF <?php echo e(count($bookings) > 0 ? number_format($total / count($bookings)) : '0'); ?>

            </h3>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-bold text-gray-900">Booking Details</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">#</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Services</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Amount Paid</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #<?php echo e($booking->id); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo e($booking->customer->user->name ?? '-'); ?>

                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php if($booking->services && $booking->services->count()): ?>
                                    <div class="space-y-1">
                                        <?php $__currentLoopData = $booking->services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="flex items-center gap-1">
                                                <i class="ph ph-scissors text-gray-400 text-xs"></i>
                                                <span><?php echo e($service->name); ?></span>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400">No Services</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo e(\Carbon\Carbon::parse($booking->date)->format('M d, Y')); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                                    <?php echo e(match($booking->status) {
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'accepted' => 'bg-blue-100 text-blue-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'declined', 'cancelled' => 'bg-gray-100 text-gray-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    }); ?>">
                                    <?php echo e(ucfirst($booking->status)); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <?php
                                    $paid = $booking->payments->where('status', 'paid')->sum('amount');
                                ?>

                                <?php if($paid > 0): ?>
                                    <span class="font-semibold text-green-600">RWF <?php echo e(number_format($paid)); ?></span>
                                <?php else: ?>
                                    <span class="text-gray-400">Unpaid</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <i class="ph ph-chart-line-down text-5xl text-gray-300 mb-3"></i>
                                <p class="text-gray-500 font-semibold">No bookings found in this range</p>
                                <p class="text-gray-400 text-sm mt-1">Try adjusting your date filters</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\dashboard\admin\reports.blade.php ENDPATH**/ ?>