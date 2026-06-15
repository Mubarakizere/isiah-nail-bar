<!DOCTYPE html>
<html>
<head>
    <title>Provider Earnings Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h2>Earnings Report</h2>
    <p>Provider: <?php echo e($provider->name); ?></p>
    <p>Date: <?php echo e($today); ?></p>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Service</th>
                <th>Amount (RWF)</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e(\Carbon\Carbon::parse($row->date)->format('M d, Y')); ?></td>
                    <td><?php echo e(\Carbon\Carbon::parse($row->time)->format('H:i')); ?></td>
                    <td><?php echo e($row->name); ?></td>
                    <td><?php echo e(number_format($row->price)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <h4 style="text-align: right; margin-top: 20px;">Total Revenue: RWF <?php echo e(number_format($total)); ?></h4>
</body>
</html>
<?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\dashboard\provider\earnings-pdf.blade.php ENDPATH**/ ?>