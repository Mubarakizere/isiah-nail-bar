<?php $__env->startComponent('mail::message'); ?>
# New Booking Notification

A new booking has been received and requires your attention.

**Customer:** <?php echo e($booking->customer->user->name ?? 'Guest Customer'); ?>  
**Booking Reference:** <?php echo e($booking->reference); ?>  
**Status:** <?php echo e(ucfirst($booking->status)); ?>


---

## Booking Details

**Date & Time**  
<?php echo e(\Carbon\Carbon::parse($booking->date)->format('l, F j, Y')); ?> at <?php echo e(\Carbon\Carbon::parse($booking->time)->format('g:i A')); ?>


**Assigned Provider**  
<?php echo e($booking->provider->name ?? 'Not assigned'); ?>


<?php if($booking->is_home_service): ?>
**Home Service Location**  
<?php echo e($booking->address); ?>

<?php elseif($booking->pickup_location_id): ?>
**Pickup Requested**  
<?php echo e($booking->pickupLocation->name ?? 'Route'); ?> (<?php echo e($booking->pickup_address); ?>)
<?php endif; ?>

**Selected Services**
<?php $__currentLoopData = $booking->services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
- <?php echo e($service->name); ?> (<?php echo e($service->duration_minutes); ?> minutes) - RWF <?php echo e(number_format($service->price, 2)); ?>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php
    $emailTotal = $booking->services->sum('price');
    if ($booking->is_home_service) $emailTotal *= 2;
    if ($booking->pickup_fee) $emailTotal += $booking->pickup_fee;
?>
**Total Amount:** RWF <?php echo e(number_format($emailTotal, 2)); ?>


---

## Payment Information

**Payment Method:** <?php echo e(ucfirst($booking->payment_option)); ?>

<?php if($booking->deposit_amount): ?>
**Deposit Paid:** RWF <?php echo e(number_format($booking->deposit_amount, 2)); ?>

<?php endif; ?>

---

<?php $__env->startComponent('mail::button', ['url' => url('/dashboard')]); ?>
View Booking Details
<?php echo $__env->renderComponent(); ?>

This is an automated notification from your booking management system.

Best regards,  
Isaiah Nail Bar Team
<?php echo $__env->renderComponent(); ?>
<?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\emails\booking\admin.blade.php ENDPATH**/ ?>