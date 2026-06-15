

<?php $__env->startSection('title', 'Booking Receipt'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4">
        
        <div class="mb-6">
            <?php if(auth()->check() && auth()->user()->role === 'admin'): ?>
                <a href="<?php echo e(route('dashboard.admin.bookings')); ?>" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Bookings
                </a>
            <?php elseif(auth()->check()): ?>
                <a href="<?php echo e(route('dashboard.customer')); ?>" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to My Bookings
                </a>
            <?php else: ?>
                <a href="<?php echo e(route('home')); ?>" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Home
                </a>
            <?php endif; ?>
        </div>

        
        <div class="bg-white border-2 border-gray-300 shadow-sm">
            
            <div class="border-b-2 border-gray-300 p-8 text-center">
                <img src="<?php echo e(asset('storage/logo.png')); ?>" alt="Logo" class="h-16 mx-auto mb-4">
                <h1 class="text-2xl font-bold text-gray-900 mb-1">BOOKING RECEIPT</h1>
                <p class="text-gray-600">Isaiah Nail Bar – Kigali</p>
                <p class="text-sm text-gray-500 mt-2">Reference: #<?php echo e($booking->id); ?></p>
            </div>

            
            <div class="p-8">
                
                <div class="mb-6">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase mb-3">Customer Information</h3>
                    <div class="bg-gray-50 border border-gray-200 p-4">
                        <p class="font-semibold text-gray-900 mb-1"><?php echo e($booking->customer->user->name ?? '-'); ?></p>
                        <p class="text-sm text-gray-600"><?php echo e($booking->customer->user->email ?? '-'); ?></p>
                        <?php if($booking->customer && $booking->customer->user && $booking->customer->user->phone): ?>
                            <p class="text-sm text-gray-600"><?php echo e($booking->customer->user->phone); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="mb-6">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase mb-3">Appointment Details</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 border border-gray-200 p-4">
                            <p class="text-xs text-gray-500 mb-1">Date</p>
                            <p class="font-semibold text-gray-900"><?php echo e(\Carbon\Carbon::parse($booking->date)->format('D, M j, Y')); ?></p>
                        </div>
                        <div class="bg-gray-50 border border-gray-200 p-4">
                            <p class="text-xs text-gray-500 mb-1">Time</p>
                            <p class="font-semibold text-gray-900"><?php echo e(\Carbon\Carbon::parse($booking->time)->format('h:i A')); ?></p>
                        </div>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 p-4 mt-4">
                        <p class="text-xs text-gray-500 mb-1">Provider</p>
                        <p class="font-semibold text-gray-900"><?php echo e($booking->provider->name ?? '-'); ?></p>
                    </div>
                    <?php if($booking->is_home_service): ?>
                        <div class="bg-rose-50 border border-rose-200 p-4 mt-4">
                            <p class="text-xs text-rose-700 mb-1 font-bold">Location (Home Service)</p>
                            <p class="font-semibold text-gray-900"><?php echo e($booking->address); ?></p>
                        </div>
                    <?php elseif($booking->pickup_location_id): ?>
                        <div class="bg-blue-50 border border-blue-200 p-4 mt-4">
                            <p class="text-xs text-blue-700 mb-1 font-bold">Transport / Pickup Request</p>
                            <p class="font-semibold text-gray-900">Pickup Area: <?php echo e($booking->pickupLocation->name ?? 'Configured Route'); ?></p>
                            <p class="text-sm text-gray-600 mt-1">Exact Address: <?php echo e($booking->pickup_address); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                
                <div class="mb-6">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase mb-3">Services</h3>
                    <table class="w-full border border-gray-300">
                        <thead class="bg-gray-100 border-b border-gray-300">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Service</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700">Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php $__currentLoopData = $booking->services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900"><?php echo e($service->name); ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right font-medium">RWF <?php echo e(number_format($service->price)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if($booking->is_home_service): ?>
                                <tr>
                                    <td class="px-4 py-3 text-sm text-rose-600 font-bold">Home Service Premium</td>
                                    <td class="px-4 py-3 text-sm text-rose-600 text-right font-medium">+100%</td>
                                </tr>
                            <?php elseif($booking->pickup_location_id): ?>
                                <tr>
                                    <td class="px-4 py-3 text-sm text-blue-600 font-bold">Pickup: <?php echo e($booking->pickupLocation->name ?? 'Route'); ?></td>
                                    <td class="px-4 py-3 text-sm text-blue-600 text-right font-medium">RWF <?php echo e(number_format($booking->pickup_fee)); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="bg-gray-50 border-t-2 border-gray-300">
                            <tr>
                                <td class="px-4 py-4 text-sm font-bold text-gray-900">TOTAL</td>
                                <?php
                                    $receiptTotal = $booking->services->sum('price');
                                    if ($booking->is_home_service) $receiptTotal *= 2;
                                    if ($booking->pickup_fee) $receiptTotal += $booking->pickup_fee;
                                ?>
                                <td class="px-4 py-4 text-right text-lg font-bold text-gray-900">RWF <?php echo e(number_format($receiptTotal)); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                
                <div class="mb-6">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase mb-3">Payment Information</h3>
                    <div class="bg-gray-50 border border-gray-200 p-4">
                        <?php
                            $totalPaid = $booking->payments->where('status', 'paid')->sum('amount');
                            $latestPayment = $booking->payments->sortByDesc('created_at')->first();
                            $totalAmount = $booking->services->sum('price');
                            if ($booking->is_home_service) $totalAmount *= 2;
                            if ($booking->pickup_fee) $totalAmount += $booking->pickup_fee;
                        ?>
                        
                        <div class="grid grid-cols-2 gap-4 mb-3">
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Payment Option</p>
                                <p class="font-semibold text-gray-900 capitalize"><?php echo e($booking->payment_option); ?></p>
                            </div>
                            <?php if($latestPayment): ?>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Payment Method</p>
                                    <p class="font-semibold text-gray-900 uppercase"><?php echo e($latestPayment->method); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if($totalPaid > 0): ?>
                            <div class="pt-3 border-t border-gray-300">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-600">Amount Paid:</span>
                                    <span class="font-bold text-green-600">RWF <?php echo e(number_format($totalPaid)); ?></span>
                                </div>
                                <?php if($totalPaid < $totalAmount): ?>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Balance Due:</span>
                                        <span class="font-bold text-red-600">RWF <?php echo e(number_format($totalAmount - $totalPaid)); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php elseif($latestPayment && $latestPayment->status === 'failed'): ?>
                            <div class="pt-3 border-t border-gray-300">
                                <div class="bg-red-50 border border-red-200 p-3 text-center">
                                    <p class="text-sm font-medium text-red-800">Payment Failed</p>
                                    <p class="text-xs text-red-600 mt-1">Please retry payment to confirm your booking</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="pt-3 border-t border-gray-300">
                                <div class="bg-yellow-50 border border-yellow-200 p-3 text-center">
                                    <p class="text-sm font-medium text-yellow-800">Payment Pending</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="text-center">
                    <p class="text-xs text-gray-500 mb-2">BOOKING STATUS</p>
                    <?php
                        $statusStyles = [
                            'pending' => 'background: #FEF3C7; color: #92400E; border: 2px solid #FCD34D;',
                            'accepted' => 'background: #DBEAFE; color: #1E40AF; border: 2px solid #60A5FA;',
                            'completed' => 'background: #D1FAE5; color: #065F46; border: 2px solid #34D399;',
                            'declined' => 'background: #FEE2E2; color: #991B1B; border: 2px solid #F87171;',
                            'cancelled' => 'background: #F3F4F6; color: #374151; border: 2px solid #9CA3AF;',
                        ];
                    ?>
                    <span style="<?php echo e($statusStyles[$booking->status] ?? $statusStyles['cancelled']); ?> padding: 8px 24px; font-size: 14px; font-weight: 700; text-transform: uppercase; display: inline-block;">
                        <?php echo e($booking->status); ?>

                    </span>
                </div>
            </div>

            
            <div class="border-t-2 border-gray-300 p-6 bg-gray-50 text-center">
                <p class="text-sm text-gray-600 mb-4">Thank you for choosing Isaiah Nail Bar!</p>
                <a href="<?php echo e(route('download.receipt', $booking->id)); ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-800 text-white text-sm font-medium hover:bg-gray-900 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download PDF Receipt
                </a>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\booking\receipt.blade.php ENDPATH**/ ?>