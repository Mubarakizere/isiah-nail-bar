<?php $__env->startComponent('mail::message'); ?>
# Thank You for Your Visit

Dear <?php echo e($booking->customer->user->name ?? 'Valued Customer'); ?>,

Thank you for choosing Isaiah Nail Bar for your recent appointment on <?php echo e(\Carbon\Carbon::parse($booking->date)->format('l, F j, Y')); ?>.

We hope you enjoyed your experience with us.

---

## ⭐ Leave Us a Google Review

Your opinion is incredibly important to us and helps other clients discover Isaiah Nail Bar. We would be so grateful if you could take just **30 seconds** to leave us a review on Google.

<?php $__env->startComponent('mail::button', ['url' => 'https://g.page/r/CS4QpNuz_MJkEAE/review', 'color' => 'primary']); ?>
⭐ Review Us on Google
<?php echo $__env->renderComponent(); ?>

Even a simple star rating makes a huge difference for our small business. Thank you for supporting us!

---

Thank you for your continued support.

Best regards,  
Isaiah Nail Bar Team
<?php echo $__env->renderComponent(); ?>

<?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\emails\review-request.blade.php ENDPATH**/ ?>