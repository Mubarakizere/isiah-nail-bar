<!DOCTYPE html>
<html>
<head>
    <title>Leave a Review</title>
</head>
<body>
    <?php $__env->startComponent('mail::message'); ?>
    # Thank You for Visiting Us

    Dear <?php echo e($booking->customer->user->name ?? 'Valued Customer'); ?>,

    Thank You for choosing Isaiah Nail Bar. We hope you enjoyed your experience with us.

    ---

    ## Your Recent Visit

    **Date:** <?php echo e(\Carbon\Carbon::parse($booking->date)->format('l, F j, Y')); ?>

    **Services:** <?php echo e($booking->services->pluck('name')->implode(', ')); ?>


    ---

    ## ⭐ Leave Us a Google Review

    Your feedback helps us grow and helps other clients find us. We'd be so grateful if you could take just **30 seconds** to share your experience on Google.

    <?php $__env->startComponent('mail::button', ['url' => 'https://g.page/r/CS4QpNuz_MJkEAE/review', 'color' => 'primary']); ?>
    ⭐ Review Us on Google
    <?php echo $__env->renderComponent(); ?>

    Even a simple star rating makes a big difference! ❤️

    ---

    Thank you for your continued support.

    Best regards,
    Isaiah Nail Bar Team
    <?php echo $__env->renderComponent(); ?>
</body>
</html>

<?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\emails\booking\review-request.blade.php ENDPATH**/ ?>