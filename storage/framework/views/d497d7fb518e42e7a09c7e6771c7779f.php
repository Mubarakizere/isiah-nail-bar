<?php $__env->startComponent('mail::message'); ?>
# New Booking Assignment

You have been assigned a new confirmed booking.

---

## Customer Information

**Name:** <?php echo e($booking->customer->user->name ?? 'N/A'); ?>  
**Phone:** <?php echo e($booking->customer->phone ?? 'Not provided'); ?>  
**Email:** <?php echo e($booking->customer->user->email ?? 'Not provided'); ?>


---

## Appointment Details

**Date:** <?php echo e(\Carbon\Carbon::parse($booking->date)->format('l, F j, Y')); ?>  
**Time:** <?php echo e(\Carbon\Carbon::parse($booking->time)->format('g:i A')); ?>  
**Assigned Provider:** <?php echo e($booking->provider->name ?? 'Not assigned'); ?>  
**Booking Reference:** <?php echo e($booking->reference); ?>


---

## Services Scheduled

<?php $__currentLoopData = $booking->services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
- <?php echo e($service->name); ?> (<?php echo e($service->duration_minutes); ?> minutes) - RWF <?php echo e(number_format($service->price, 2)); ?>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

**Total Value:** RWF <?php echo e(number_format($booking->services->sum('price'), 2)); ?>


---

## Payment Status

<?php if(isset($booking->deposit_amount)): ?>
**Deposit Received:** RWF <?php echo e(number_format($booking->deposit_amount, 2)); ?>  
**Balance Due:** RWF <?php echo e(number_format($booking->services->sum('price') - $booking->deposit_amount, 2)); ?>

<?php else: ?>
**Total Amount Paid:** RWF <?php echo e(number_format($booking->services->sum('price'), 2)); ?>

<?php endif; ?>

---

Please ensure you are available and prepared to serve this customer at the scheduled time.

Best regards,  
Isaiah Nail Bar Management
<?php echo $__env->renderComponent(); ?>
<?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\emails\booking\provider.blade.php ENDPATH**/ ?>