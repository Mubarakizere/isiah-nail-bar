<?php $__env->startComponent('mail::message'); ?>
# Appointment Reminder

Dear <?php echo e($booking->customer->user->name ?? 'Valued Customer'); ?>,

This is a reminder that you have an appointment scheduled with Isaiah Nail Bar tomorrow.

---

## Appointment Details

**Date:** <?php echo e(\Carbon\Carbon::parse($booking->date)->format('l, F j, Y')); ?>  
**Time:** <?php echo e(\Carbon\Carbon::parse($booking->time)->format('g:i A')); ?>  
**Service Provider:** <?php echo e($booking->provider->name ?? 'To be assigned'); ?>


---

## Scheduled Services

<?php $__currentLoopData = $booking->services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
- <?php echo e($service->name); ?> (<?php echo e($service->duration_minutes); ?> minutes)
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

---

## Important Information

**Cancellation Policy:** If you need to reschedule or cancel your appointment, please contact us at least 24 hours in advance.

**Location:** KG 4 Roundabout, Kigali  
**Contact:** Instagram @isaiahnailbar

We look forward to seeing you.

---

💅 **After your visit**, we'd love to hear about your experience! You can leave us a review here: [Review on Google](https://g.page/r/CS4QpNuz_MJkEAE/review)

Best regards,  
Isaiah Nail Bar Team
<?php echo $__env->renderComponent(); ?>
<?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\emails\booking\reminder.blade.php ENDPATH**/ ?>