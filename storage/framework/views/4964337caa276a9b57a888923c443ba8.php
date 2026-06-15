

<?php $__env->startSection('title', 'All Bookings'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6">
    
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-1">All Bookings</h1>
                <p class="text-gray-600 text-sm">Manage customer bookings</p>
            </div>
            <div class="flex gap-2">
                <a href="<?php echo e(route('admin.emails.index')); ?>" 
                   class="px-3 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
                    Email History
                </a>
                <a href="<?php echo e(route('admin.bookings.manual.create')); ?>" 
                   class="px-3 py-2 bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">
                    + New Booking
                </a>
            </div>
        </div>
    </div>

    
    <form method="GET" action="<?php echo e(route('dashboard.admin.bookings')); ?>" class="bg-white border border-gray-300 p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Search Customer</label>
                <input type="text" 
                       name="search" 
                       value="<?php echo e(request('search')); ?>" 
                       placeholder="Search by name..." 
                       class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-blue-500">
            </div>

            
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Booking Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-blue-500">
                    <option value="">All Statuses</option>
                    <?php $__currentLoopData = ['pending', 'accepted', 'declined', 'completed', 'cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($status); ?>" <?php echo e(request('status') === $status ? 'selected' : ''); ?>>
                            <?php echo e(ucfirst($status)); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Payment Status</label>
                <select name="payment_status" class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-blue-500">
                    <option value="">All Payments</option>
                    <?php $__currentLoopData = ['paid', 'pending', 'failed', 'unpaid']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $paymentStatus): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($paymentStatus); ?>" <?php echo e(request('payment_status') === $paymentStatus ? 'selected' : ''); ?>>
                            <?php echo e(ucfirst($paymentStatus)); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            
            <div class="flex gap-2 items-end">
                <button type="submit" class="flex-1 px-3 py-2 bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
                    Filter
                </button>
                <a href="<?php echo e(route('dashboard.admin.bookings')); ?>" class="px-3 py-2 bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200">
                    Reset
                </a>
            </div>
        </div>
    </form>

    
    <div class="bg-white border border-gray-300">
        <table class="w-full">
            <thead class="bg-gray-100 border-b border-gray-300">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Customer</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Date & Time</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Payment</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Method</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $totalPaid = $booking->payments->where('status', 'paid')->sum('amount');
                        $latestPayment = $booking->payments->sortByDesc('created_at')->first();
                    ?>
                    <tr class="hover:bg-gray-50">
                        
                        <td class="px-4 py-3 text-sm text-gray-900">
                            #<?php echo e($booking->id); ?>

                        </td>
                        
                        
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <?php echo e($booking->customer->user->name ?? '—'); ?>

                        </td>
                        
                        
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <div><?php echo e(\Carbon\Carbon::parse($booking->date)->format('M d, Y')); ?></div>
                            <div class="text-xs text-gray-500"><?php echo e(\Carbon\Carbon::parse($booking->time)->format('h:i A')); ?></div>
                            <?php if($booking->is_home_service): ?>
                                <div class="text-[10px] bg-rose-50 text-rose-600 font-bold px-1.5 py-0.5 rounded mt-1 inline-flex items-center gap-1 border border-rose-100">
                                    <i class="ph ph-house"></i> <?php echo e(Str::limit($booking->address, 15)); ?>

                                </div>
                            <?php elseif($booking->pickup_location_id): ?>
                                <div class="text-[10px] bg-blue-50 text-blue-600 font-bold px-1.5 py-0.5 rounded mt-1 flex flex-col gap-0.5 border border-blue-100 mt-1">
                                    <span class="inline-flex items-center gap-1"><i class="ph ph-car"></i> <?php echo e($booking->pickupLocation->name ?? 'Pickup'); ?></span>
                                    <span class="font-normal"><?php echo e(Str::limit($booking->pickup_address, 15)); ?></span>
                                </div>
                            <?php endif; ?>
                        </td>
                        
                        
                        <td class="px-4 py-3">
                            <?php
                                $statusColors = [
                                    'pending' => 'background: #FEF3C7; color: #92400E;',
                                    'accepted' => 'background: #DBEAFE; color: #1E40AF;',
                                    'completed' => 'background: #D1FAE5; color: #065F46;',
                                    'declined' => 'background: #FEE2E2; color: #991B1B;',
                                    'cancelled' => 'background: #F3F4F6; color: #374151;',
                                ];
                            ?>
                            <span style="<?php echo e($statusColors[$booking->status] ?? $statusColors['cancelled']); ?> padding: 4px 8px; font-size: 11px; font-weight: 600; text-transform: uppercase;">
                                <?php echo e($booking->status); ?>

                            </span>
                        </td>
                        
                        
                        <td class="px-4 py-3">
                            <?php if($totalPaid > 0): ?>
                                
                                <div>
                                    <span style="background: #D1FAE5; color: #065F46; padding: 4px 8px; font-size: 11px; font-weight: 600; text-transform: uppercase;">
                                        ✓ PAID
                                    </span>
                                    <div class="text-xs text-gray-600 mt-1"><?php echo e(number_format($totalPaid)); ?> RWF</div>
                                </div>
                            <?php elseif($latestPayment && $latestPayment->status === 'failed'): ?>
                                
                                <div>
                                    <span style="background: #FEE2E2; color: #991B1B; padding: 4px 8px; font-size: 11px; font-weight: 600; text-transform: uppercase;">
                                        ✗ FAILED
                                    </span>
                                    <div class="text-xs text-gray-600 mt-1"><?php echo e(number_format($latestPayment->amount)); ?> RWF</div>
                                </div>
                            <?php elseif($latestPayment && $latestPayment->status === 'pending'): ?>
                                
                                <div>
                                    <span style="background: #FEF3C7; color: #92400E; padding: 4px 8px; font-size: 11px; font-weight: 600; text-transform: uppercase;">
                                        ⏳ PENDING
                                    </span>
                                    <div class="text-xs text-gray-600 mt-1"><?php echo e(number_format($latestPayment->amount)); ?> RWF</div>
                                </div>
                            <?php else: ?>
                                
                                <span style="background: #F3F4F6; color: #6B7280; padding: 4px 8px; font-size: 11px; font-weight: 600; text-transform: uppercase;">
                                    UNPAID
                                </span>
                            <?php endif; ?>
                        </td>
                        
                        
                        <td class="px-4 py-3">
                            <?php
                                $paidPayment = $booking->payments->where('status', 'paid')->first();
                                $paymentMethod = $paidPayment->method ?? null;
                            ?>
                            <?php if($paymentMethod): ?>
                                <?php if(strtolower($paymentMethod) === 'card'): ?>
                                    <span style="background: #DBEAFE; color: #1E40AF; padding: 4px 8px; font-size: 11px; font-weight: 600;">
                                        💳 CARD
                                    </span>
                                <?php elseif(strtolower($paymentMethod) === 'momo'): ?>
                                    <span style="background: #FEF3C7; color: #92400E; padding: 4px 8px; font-size: 11px; font-weight: 600;">
                                        📱 MOMO
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-500"><?php echo e(strtoupper($paymentMethod)); ?></span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-xs text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                        
                        
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                <?php if($booking->status === 'pending'): ?>
                                    <form method="POST" action="<?php echo e(route('dashboard.admin.update', $booking->id)); ?>" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PUT'); ?>
                                        <input type="hidden" name="status" value="accepted">
                                        <button class="px-2 py-1 bg-green-600 text-white text-xs font-medium hover:bg-green-700" title="Accept">
                                            Accept
                                        </button>
                                    </form>
                                    <form method="POST" action="<?php echo e(route('dashboard.admin.update', $booking->id)); ?>" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PUT'); ?>
                                        <input type="hidden" name="status" value="declined">
                                        <button class="px-2 py-1 bg-red-600 text-white text-xs font-medium hover:bg-red-700" title="Decline">
                                            Decline
                                        </button>
                                    </form>
                                <?php elseif($booking->status === 'accepted'): ?>
                                    <form method="POST" action="<?php echo e(route('dashboard.admin.update', $booking->id)); ?>" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PUT'); ?>
                                        <input type="hidden" name="status" value="completed">
                                        <button class="px-2 py-1 bg-blue-600 text-white text-xs font-medium hover:bg-blue-700" title="Mark Complete">
                                            Complete
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <a href="<?php echo e(route('booking.receipt', $booking->id)); ?>" 
                                   class="px-2 py-1 bg-gray-200 text-gray-700 text-xs font-medium hover:bg-gray-300" 
                                   title="View Receipt">
                                    Receipt
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                            <div class="text-4xl mb-2">📅</div>
                            <p class="font-medium">No bookings found</p>
                            <p class="text-sm text-gray-400 mt-1">Try adjusting your filters</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <?php if($bookings->hasPages()): ?>
        <div class="mt-4">
            <?php echo e($bookings->withQueryString()->links()); ?>

        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\dashboard\admin\bookings.blade.php ENDPATH**/ ?>