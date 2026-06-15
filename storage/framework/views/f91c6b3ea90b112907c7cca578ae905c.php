<?php $__env->startComponent('mail::message'); ?>
# Manual Booking Confirmation

Dear <?php echo e($booking->customer->user->name ?? 'Valued Customer'); ?>,

Your booking has been manually confirmed by our team. We look forward to serving you.

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

<?php $__env->startComponent('mail::button', ['url' => route('booking.receipt', $booking->id)]); ?>
View Receipt
<?php echo $__env->renderComponent(); ?>

We look forward to seeing you!

---

💬 **After your visit**, we'd love a quick Google review! It helps other clients discover us.

<?php $__env->startComponent('mail::button', ['url' => 'https://g.page/r/CS4QpNuz_MJkEAE/review']); ?>
⭐ Review Us on Google
<?php echo $__env->renderComponent(); ?>

Thanks,<br>
<?php echo e(config('app.name')); ?>

<?php echo $__env->renderComponent(); ?>
<?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\emails\booking-manual.blade.php ENDPATH**/ ?>