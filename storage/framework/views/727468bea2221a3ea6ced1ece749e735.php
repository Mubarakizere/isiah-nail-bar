<?php $__env->startComponent('mail::message'); ?>
# Booking Received

Dear <?php echo e($booking->customer->user->name ?? 'Valued Customer'); ?>,

Thank you for choosing Isaiah Nail Bar. Your appointment request has been received and your time slot has been reserved.

**Payment Required:** Please complete your payment to confirm your booking.

---

## Appointment Details

**Date:** <?php echo e(\Carbon\Carbon::parse($booking->date)->format('l, F j, Y')); ?>  
**Time:** <?php echo e(\Carbon\Carbon::parse($booking->time)->format('g:i A')); ?>  
**Service Provider:** <?php echo e($booking->provider->name ?? 'To be assigned'); ?>

<?php if($booking->is_home_service): ?>
**Home Service:** <?php echo e($booking->address); ?>

<?php elseif($booking->pickup_location_id): ?>
**Transport Pickup:** <?php echo e($booking->pickupLocation->name ?? 'Route'); ?> (<?php echo e($booking->pickup_address); ?>)
<?php endif; ?>

---

## Services Selected

<?php $__currentLoopData = $booking->services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
- <?php echo e($service->name); ?> (<?php echo e($service->duration_minutes); ?> minutes) - RWF <?php echo e(number_format($service->price, 2)); ?>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php
    $emailTotal = $booking->services->sum('price');
    if ($booking->is_home_service) $emailTotal *= 2;
    if ($booking->pickup_fee) $emailTotal += $booking->pickup_fee;
?>
**Total Amount Due:** RWF <?php echo e(number_format($emailTotal, 2)); ?>


---

## Next Steps

Your appointment slot is currently reserved. To confirm your booking, please complete the payment process.

**Status:** Awaiting Payment

<?php $__env->startComponent('mail::button', ['url' => url('/')]); ?>
Complete Payment
<?php echo $__env->renderComponent(); ?>

---

**Need Assistance?**  
Contact us on Instagram @isaiahnailbar or visit us at KG 4 Roundabout, Kigali.

Best regards,  
Isaiah Nail Bar Team
<?php echo $__env->renderComponent(); ?>
<?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\emails\booking\created.blade.php ENDPATH**/ ?>