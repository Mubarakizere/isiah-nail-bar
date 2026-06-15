<?php
    $currentStep = $currentStep ?? 1;
    $steps = [
        1 => ['label' => 'Service', 'icon' => 'ph-scissors'],
        2 => ['label' => 'Provider', 'icon' => 'ph-user'],
        3 => ['label' => 'Date & Time', 'icon' => 'ph-calendar'],
        4 => ['label' => 'Payment', 'icon' => 'ph-credit-card'],
        5 => ['label' => 'Confirm', 'icon' => 'ph-check-circle']
    ];
    $progressPercent = round((($currentStep - 1) / (count($steps) - 1)) * 100);
?>

<div class="container my-4">
    <!-- Progress Bar -->
    <div class="progress rounded-pill shadow-sm" style="height: 10px;">
        <div
            class="progress-bar bg-primary progress-bar-striped progress-bar-animated rounded-pill"
            role="progressbar"
            style="width: <?php echo e($progressPercent); ?>%;"
            aria-valuenow="<?php echo e($progressPercent); ?>"
            aria-valuemin="0"
            aria-valuemax="100"
        ></div>
    </div>

    <!-- Step Indicators -->
    <div class="d-flex justify-content-between align-items-center mt-4 px-1 flex-nowrap overflow-auto" style="gap: 10px;">
        <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="text-center flex-fill" style="min-width: 80px;">
                <div class="position-relative">
                    <i class="ph <?php echo e($data['icon']); ?> fs-4 <?php echo e($currentStep == $step ? 'text-primary scale-up' : 'text-muted'); ?>"></i>
                </div>
                <div class="small mt-1 fw-semibold <?php echo e($currentStep == $step ? 'text-primary' : 'text-muted'); ?>">
                    <?php echo e($step); ?>. <?php echo e($data['label']); ?>

                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
    .scale-up {
        transform: scale(1.2);
        transition: all 0.3s ease;
    }
</style>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\partials\booking-progress.blade.php ENDPATH**/ ?>