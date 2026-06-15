<?php $__env->startSection('title', 'Edit Working Hours'); ?>

<?php $__env->startSection('content'); ?>
<div class="container my-4">
    <h2 class="text-center fw-bold mb-4">🕒 My Working Hours</h2>

    <form method="POST" action="<?php echo e(route('provider.working-hours.update')); ?>">
        <?php echo csrf_field(); ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Day</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $dayMap = [
                            1 => 'Monday',
                            2 => 'Tuesday',
                            3 => 'Wednesday',
                            4 => 'Thursday',
                            5 => 'Friday',
                            6 => 'Saturday',
                            0 => 'Sunday',
                        ];
                    ?>

                    <?php $__currentLoopData = $dayMap; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayIndex => $dayName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $existing = $hours->firstWhere('day_of_week', $dayIndex);
                        ?>
                        <tr>
                            <td><?php echo e($dayName); ?></td>
                            <td>
                                <input type="time" name="working_hours[<?php echo e($dayIndex); ?>][start_time]"
                                       value="<?php echo e($existing->start_time ?? '08:00'); ?>" class="form-control">
                            </td>
                            <td>
                                <input type="time" name="working_hours[<?php echo e($dayIndex); ?>][end_time]"
                                       value="<?php echo e($existing->end_time ?? '17:00'); ?>" class="form-control">
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-primary mt-3">💾 Save Working Hours</button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\dashboard\provider\working-hours\edit.blade.php ENDPATH**/ ?>