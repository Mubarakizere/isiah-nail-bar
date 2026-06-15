<?php $__env->startComponent('mail::message'); ?>
# Payment Confirmation

Dear <?php echo e($booking->customer->user->name ?? 'Valued Customer'); ?>,

Thank you for your payment. Your booking has been confirmed and we look forward to serving you.

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
**Booking Reference:** <?php echo e($booking->reference); ?>


---

## Services Booked

<?php $__currentLoopData = $booking->services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
- <?php echo e($service->name); ?> (<?php echo e($service->duration_minutes); ?> minutes) - RWF <?php echo e(number_format($service->price, 2)); ?>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

---

## Payment Summary

<?php
    $emailTotal = $booking->services->sum('price');
    if ($booking->is_home_service) $emailTotal *= 2;
    if ($booking->pickup_fee) $emailTotal += $booking->pickup_fee;
?>
<?php if($booking->deposit_amount): ?>
**Deposit Paid:** RWF <?php echo e(number_format($booking->deposit_amount, 2)); ?>  
**Remaining Balance:** RWF <?php echo e(number_format($emailTotal - $booking->deposit_amount, 2)); ?>

<?php else: ?>
**Total Amount Paid:** RWF <?php echo e(number_format($emailTotal, 2)); ?>

<?php endif; ?>

---

Your receipt is attached to this email. You can also view it online using the button below.

<?php $__env->startComponent('mail::button', ['url' => route('booking.receipt', $booking->id)]); ?>
View Receipt Online
<?php echo $__env->renderComponent(); ?>

---

**Location:** KG 4 Roundabout, Kigali  
**Follow us:** Instagram @isaiahnailbar

We appreciate your business.

---

💬 **Love your nails?** We'd be so grateful if you could leave us a quick Google review. It takes just 30 seconds!

<?php $__env->startComponent('mail::button', ['url' => 'https://g.page/r/CS4QpNuz_MJkEAE/review']); ?>
⭐ Review Us on Google
<?php echo $__env->renderComponent(); ?>

Best regards,  
Isaiah Nail Bar Team
<?php echo $__env->renderComponent(); ?>
<?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\emails\booking\paid.blade.php ENDPATH**/ ?>