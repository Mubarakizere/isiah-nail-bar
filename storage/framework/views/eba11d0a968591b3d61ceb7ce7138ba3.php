<?php $__env->startComponent('mail::message'); ?>
# Appointment Reminder

Dear <?php echo e($booking->provider->name ?? 'Service Provider'); ?>,

This is a reminder about your upcoming appointment scheduled for tomorrow.

---

## Appointment Details

**Customer:** <?php echo e($booking->customer->user->name ?? 'Client'); ?>  
**Date:** <?php echo e(\Carbon\Carbon::parse($booking->date)->format('l, F j, Y')); ?>  
**Time:** <?php echo e(\Carbon\Carbon::parse($booking->time)->format('g:i A')); ?>


---

## Services to Provide

<?php echo e($booking->services->pluck('name')->implode(', ')); ?>


---

Please ensure you are prepared and arrive on time to provide excellent service to our customer.

Thank you for your dedication to quality service.

Best regards,  
Isaiah Nail Bar Management
<?php echo $__env->renderComponent(); ?>
<?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\emails\booking\provider-reminder.blade.php ENDPATH**/ ?>