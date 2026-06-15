<?php $__env->startComponent('mail::message'); ?>
# Booking Confirmation

Dear <?php echo e($booking->customer->user->name ?? 'Valued Customer'); ?>,

Thank you for choosing Isaiah Nail Bar. Your payment has been successfully processed and your appointment is confirmed.

---

## Appointment Details

**Date:** <?php echo e(\Carbon\Carbon::parse($booking->date)->format('l, F j, Y')); ?>  
**Time:** <?php echo e(\Carbon\Carbon::parse($booking->time)->format('g:i A')); ?>  
**Service Provider:** <?php echo e($booking->provider->name ?? 'To be assigned'); ?>


---

## Services Booked

<?php $__currentLoopData = $booking->services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
- <?php echo e($service->name); ?> (<?php echo e($service->duration_minutes); ?> minutes) - RWF <?php echo e(number_format($service->price, 2)); ?>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

**Total Amount:** RWF <?php echo e(number_format($booking->services->sum('price'), 2)); ?>


---

## Payment Information

**Payment Method:** <?php echo e(ucfirst($booking->payment_option)); ?>

<?php if($booking->deposit_amount): ?>
**Deposit Paid:** RWF <?php echo e(number_format($booking->deposit_amount, 2)); ?>

<?php endif; ?>

---

## Important Information

**Cancellation Policy:** Changes or cancellations must be made at least 48 hours in advance.

**Location:** KG 4 Roundabout, Kigali  
**Contact:** Follow us on Instagram @isaiahnailbar

We look forward to serving you.

---

💬 **Enjoyed your experience?** We'd love to hear from you! Leave us a quick review on Google — it means the world to us.

<?php $__env->startComponent('mail::button', ['url' => 'https://g.page/r/CS4QpNuz_MJkEAE/review']); ?>
⭐ Review Us on Google
<?php echo $__env->renderComponent(); ?>

Best regards,  
Isaiah Nail Bar Team
<?php echo $__env->renderComponent(); ?>
<?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\emails\booking\confirmed.blade.php ENDPATH**/ ?>