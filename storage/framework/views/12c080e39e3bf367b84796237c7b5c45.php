<?php $__env->startSection('title', 'Booking Details'); ?>
<?php $__env->startSection('page-subtitle', 'Detailed view of your reservation.'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-2xl mx-auto">
    
    
    <a href="<?php echo e(route('dashboard.customer')); ?>" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 mb-6 transition-colors group">
        <i class="ph ph-arrow-left group-hover:-translate-x-1 transition-transform"></i>
        Back to My Bookings
    </a>

    <div class="bg-white rounded-3xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)] border border-gray-100 overflow-hidden relative">
        
        
        <div class="h-24 bg-gray-900 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 24px 24px;"></div>
            <div class="absolute -bottom-6 -right-6 text-white/5">
                <i class="ph ph-sparkle text-9xl"></i>
            </div>
        </div>

        <div class="px-8 pb-8 relative z-10">
            
            <div class="flex justify-center -mt-6 mb-6">
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full border-4 border-white shadow-sm bg-white text-sm font-bold uppercase tracking-wider
                <?php echo e(match($booking->status) {
                    'pending' => 'text-amber-600',
                    'accepted' => 'text-blue-600',
                    'completed' => 'text-green-600',
                    'cancelled', 'declined' => 'text-gray-500',
                    default => 'text-gray-900'
                }); ?>">
                    <?php if($booking->status === 'completed'): ?> <i class="ph ph-check-circle text-lg"></i>
                    <?php elseif($booking->status === 'pending'): ?> <i class="ph ph-clock text-lg"></i>
                    <?php elseif($booking->status === 'accepted'): ?> <i class="ph ph-calendar-check text-lg"></i>
                    <?php endif; ?>
                    <?php echo e(ucfirst($booking->status)); ?>

                </span>
            </div>

            <div class="text-center mb-8">
                <h1 class="text-2xl font-serif font-bold text-gray-900">Reservation Details</h1>
                <p class="text-sm text-gray-500 mt-1 mb-3">Order #<?php echo e($booking->id); ?></p>
                <?php if($booking->is_home_service): ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-50 border border-rose-100 text-rose-700 text-sm font-bold rounded-full shadow-sm mt-2">
                        <i class="ph ph-house"></i> Home Service: <?php echo e($booking->address); ?>

                    </span>
                <?php elseif($booking->pickup_location_id): ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 border border-blue-100 text-blue-700 text-sm font-bold rounded-full shadow-sm mt-2">
                        <i class="ph ph-car"></i> Pickup: <?php echo e($booking->pickup_address); ?> (<?php echo e($booking->pickupLocation->name ?? ''); ?>)
                    </span>
                <?php endif; ?>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                
                
                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-rose-500 shadow-sm">
                             <i class="ph ph-calendar-blank"></i>
                        </div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">When</span>
                    </div>
                    <p class="text-lg font-bold text-gray-900">
                         <?php echo e(\Carbon\Carbon::parse($booking->date)->format('l, M d')); ?>

                    </p>
                    <p class="text-gray-600">
                        at <?php echo e(\Carbon\Carbon::parse($booking->time)->format('H:i')); ?>

                    </p>
                </div>

                
                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100">
                     <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-rose-500 shadow-sm">
                             <i class="ph ph-user"></i>
                        </div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Stylist</span>
                    </div>
                    <p class="text-lg font-bold text-gray-900">
                         <?php echo e($booking->provider->name ?? 'Salon Staff'); ?>

                    </p>
                    <p class="text-gray-600 text-sm">Professional Service</p>
                </div>
            </div>

            
            <div class="space-y-4 mb-8">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest border-b border-gray-100 pb-2">Services Selected</h3>
                
                <?php $__currentLoopData = $booking->services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex justify-between items-center group">
                        <div class="flex items-center gap-3">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 group-hover:scale-150 transition-transform"></span>
                            <span class="font-medium text-gray-700"><?php echo e($service->name); ?></span>
                        </div>
                        <span class="font-mono text-gray-900"><?php echo e(number_format($service->price)); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
             <div class="bg-gray-900 text-white rounded-2xl p-6 mb-8 relative overflow-hidden">
                <div class="flex justify-between items-end relative z-10">
                    <div>
                        <p class="text-gray-400 text-sm mb-1">Total Amount</p>
                        <p class="text-3xl font-serif">RWF <?php echo e(number_format($booking->services->sum('price'))); ?></p>
                    </div>
                     <div class="text-right">
                         <?php
                             $paid = $booking->payments->where('status', 'paid')->sum('amount');
                             $total = $booking->services->sum('price');
                             $balance = $total - $paid;
                         ?>
                         
                        <?php if($balance <= 0): ?>
                            <span class="px-3 py-1 bg-green-500 text-white text-xs font-bold rounded-lg flex items-center gap-1">
                                <i class="ph ph-check-circle"></i> Paid in Full
                            </span>
                        <?php else: ?>
                             <p class="text-gray-400 text-xs mb-1">Remaining Balance</p>
                             <p class="text-xl font-bold text-white">RWF <?php echo e(number_format($balance)); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
             </div>

             
             <div class="flex flex-col sm:flex-row gap-3">
                 <a href="<?php echo e(route('booking.receipt', $booking->id)); ?>" class="flex-1 py-3 px-4 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 hover:text-gray-900 transition-colors flex items-center justify-center gap-2">
                     <i class="ph ph-download-simple"></i> Download Receipt
                 </a>

                 <?php if($booking->status === 'pending'): ?>
                    <form method="POST" action="<?php echo e(route('customer.booking.cancel', $booking->id)); ?>" class="flex-1 text-center" onsubmit="return confirm('Are you sure you want to cancel this booking? This action cannot be undone.')">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>
                        <button class="w-full py-3 px-4 bg-white border border-red-100 text-red-600 font-bold rounded-xl hover:bg-red-50 transition-colors flex items-center justify-center gap-2">
                            <i class="ph ph-x-circle"></i> Cancel Booking
                        </button>
                    </form>
                <?php endif; ?>
                
                <?php if($balance > 0): ?>
                     <a href="<?php echo e(route('customer.booking.payRemaining', $booking->id)); ?>" class="flex-1 py-3 px-4 bg-rose-600 text-white font-bold rounded-xl hover:bg-rose-700 transition-colors flex items-center justify-center gap-2 shadow-lg shadow-rose-200">
                         <i class="ph ph-wallet"></i> Pay Balance
                     </a>
                <?php endif; ?>
             </div>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\dashboard\customer\show.blade.php ENDPATH**/ ?>