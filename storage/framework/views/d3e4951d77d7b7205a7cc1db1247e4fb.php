<?php $__env->startSection('title', 'Customer Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6">
    
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            👋 Welcome back, <?php echo e(Auth::user()->name); ?>!
        </h1>
        <p class="text-gray-600">Here are your <?php echo e($status); ?> bookings.</p>
    </div>

    
    <?php if(session('success')): ?>
        <div class="bg-green-50 border-2 border-green-200 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <i class="ph ph-check-circle text-2xl text-green-600 mt-0.5"></i>
                <div class="flex-1">
                    <p class="text-green-800 font-medium"><?php echo e(session('success')); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if($bookings->isEmpty()): ?>
        
        <div class="bg-blue-50 border-2 border-blue-200 rounded-2xl p-12 text-center">
            <i class="ph ph-calendar-plus text-6xl text-blue-300 mb-4"></i>
            <h3 class="text-2xl font-bold text-blue-900 mb-2">No bookings yet!</h3>
            <p class="text-blue-700 mb-6">Start your journey by booking your first service</p>
            <a href="<?php echo e(route('booking.step1')); ?>" 
               class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                <i class="ph ph-plus-circle mr-2"></i>Book a Service
            </a>
        </div>
    <?php else: ?>
        
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-900 text-white">
                        <tr>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">#</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">Service</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">Provider</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">Time</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="text-center hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    #<?php echo e($booking->id); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-medium text-gray-900"><?php echo e($booking->service->name ?? 'N/A'); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-gray-700"><?php echo e($booking->provider->name ?? 'N/A'); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php echo e($booking->date); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php echo e($booking->time); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if($booking->status == 'pending'): ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    <?php elseif($booking->status == 'confirmed'): ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Confirmed
                                        </span>
                                    <?php elseif($booking->status == 'completed'): ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Completed
                                        </span>
                                    <?php elseif($booking->status == 'cancelled'): ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            Cancelled
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="<?php echo e(route('customer.booking.show', $booking->id)); ?>" 
                                           class="px-3 py-1.5 bg-blue-100 text-blue-700 text-sm font-semibold rounded-lg hover:bg-blue-200 transition">
                                            View
                                        </a>

                                        <?php if($booking->status == 'pending'): ?>
                                            <form action="<?php echo e(route('customer.booking.cancel', $booking->id)); ?>" 
                                                  method="POST" 
                                                  class="inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <button type="submit" 
                                                        class="px-3 py-1.5 bg-red-100 text-red-700 text-sm font-semibold rounded-lg hover:bg-red-200 transition"
                                                        onclick="return confirm('Are you sure you want to cancel this booking?')">
                                                    Cancel
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            
            <?php if($bookings->hasPages()): ?>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <?php echo e($bookings->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\dashboard\customer.blade.php ENDPATH**/ ?>