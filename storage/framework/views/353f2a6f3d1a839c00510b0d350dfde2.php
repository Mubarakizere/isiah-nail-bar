<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt #<?php echo e($booking->id); ?> - Isaiah Nail Bar</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
            color: #333;
            padding: 30px 40px;
        }
        .text-center { text-align: center; }
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 24px; }
        .logo { height: 65px; margin-bottom: 10px; }
        .section-title {
            font-weight: bold;
            font-size: 15px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 6px;
            margin-bottom: 12px;
            margin-top: 20px;
            text-transform: uppercase;
            color: #1e40af;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }
        .table td.label {
            font-weight: bold;
            width: 40%;
            background-color: #f9fafb;
            color: #374151;
        }
        .footer {
            text-align: center;
            font-size: 11px;
            color: #6b7280;
            margin-top: 40px;
            line-height: 1.6;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-cancelled, .status-declined { background: #fee2e2; color: #991b1b; }
        .status-accepted { background: #dbeafe; color: #1e40af; }
    </style>
</head>
<body>

    
    <div class="text-center mb-4">
        <?php if($logo): ?>
            <img src="<?php echo e($logo); ?>" alt="Isaiah Nail Bar Logo" class="logo">
        <?php endif; ?>
        <h2 class="mb-2">Booking Receipt</h2>
        <p class="mb-2" style="color: #6b7280;">Isaiah Nail Bar — Kigali</p>
        <small style="color: #9ca3af;">Generated on <?php echo e(\Carbon\Carbon::now()->format('d M Y, H:i')); ?></small>
    </div>

    
    <div class="section-title">Booking Information</div>
    <table class="table">
        <tr><td class="label">Receipt ID:</td><td>#<?php echo e($booking->id); ?></td></tr>
        <tr><td class="label">Customer:</td><td><?php echo e($booking->customer->user->name ?? '-'); ?></td></tr>
        <tr><td class="label">Email:</td><td><?php echo e($booking->customer->user->email ?? '-'); ?></td></tr>
        <tr><td class="label">Provider:</td><td><?php echo e($booking->provider->name ?? '-'); ?></td></tr>
        <tr><td class="label">Date:</td><td><?php echo e(\Carbon\Carbon::parse($booking->date)->format('D, M j Y')); ?></td></tr>
        <tr><td class="label">Time:</td><td><?php echo e(\Carbon\Carbon::parse($booking->time)->format('h:i A')); ?></td></tr>
        <?php if($booking->is_home_service): ?>
        <tr><td class="label">Home Service:</td><td><?php echo e($booking->address); ?></td></tr>
        <?php elseif($booking->pickup_location_id): ?>
        <tr><td class="label">Transport Pickup:</td><td><?php echo e($booking->pickupLocation->name ?? 'Route'); ?> (<?php echo e($booking->pickup_address); ?>)</td></tr>
        <?php endif; ?>
        <tr><td class="label">Payment Option:</td><td><?php echo e(ucfirst($booking->payment_option)); ?></td></tr>
    </table>

    
    <div class="section-title">Services Booked</div>
    <table class="table">
        <tr>
            <td class="label">Service Details:</td>
            <td>
                <ul style="margin: 0; padding-left: 16px;">
                   <?php $__currentLoopData = $booking->services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <?php echo e($service->name); ?> — <?php echo e($service->category->name ?? '—'); ?> —
                            <?php echo e($service->duration_minutes); ?> mins —
                            RWF <?php echo e(number_format($service->price)); ?>

                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </td>
        </tr>
    </table>

    
    <div class="section-title">Payment Summary</div>
    <table class="table">
        <tr>
            <td class="label">Base Services Price:</td>
            <td style="font-weight: bold;">RWF <?php echo e(number_format($booking->services->sum('price'))); ?></td>
        </tr>
        <?php if($booking->is_home_service): ?>
        <tr><td class="label">Home Service Premium:</td><td style="font-weight: bold;">+100% (RWF <?php echo e(number_format($booking->services->sum('price'))); ?>)</td></tr>
        <?php elseif($booking->pickup_fee): ?>
        <tr><td class="label">Pickup Transport Fee:</td><td style="font-weight: bold;">RWF <?php echo e(number_format($booking->pickup_fee)); ?></td></tr>
        <?php endif; ?>

        <?php
            $pdfTotal = $booking->services->sum('price');
            if ($booking->is_home_service) $pdfTotal *= 2;
            if ($booking->pickup_fee) $pdfTotal += $booking->pickup_fee;
        ?>
        <tr>
            <td class="label">Grand Total:</td>
            <td style="font-weight: bold;">RWF <?php echo e(number_format($pdfTotal)); ?></td>
        </tr>
        <?php if($booking->deposit_amount): ?>
            <tr><td class="label">Deposit Paid:</td><td>RWF <?php echo e(number_format($booking->deposit_amount)); ?></td></tr>
            <tr><td class="label">Remaining Balance:</td><td>RWF <?php echo e(number_format($pdfTotal - $booking->deposit_amount)); ?></td></tr>
        <?php else: ?>
            <tr><td class="label">Paid in Full:</td><td>Yes</td></tr>
        <?php endif; ?>

        <?php if(!empty($booking->reference)): ?>
            <tr><td class="label">Transaction Ref:</td><td><?php echo e($booking->reference); ?></td></tr>
        <?php endif; ?>

        <tr>
            <td class="label">Status:</td>
            <td>
                <span class="status status-<?php echo e($booking->status); ?>">
                    <?php echo e(ucfirst($booking->status)); ?>

                </span>
            </td>
        </tr>
    </table>

    
    <div class="footer">
        This receipt is digitally generated and valid without signature.<br>
        Cancellations must be made at least 48 hours before the scheduled time.<br><br>
        &copy; <?php echo e(date('Y')); ?> Isaiah Nail Bar — All Rights Reserved.<br>
        KG 4 Roundabout, Kigali — Tel: 0788 421 063 — IG: @isaiahnailbar
    </div>
</body>
</html>
<?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\booking\receipt-pdf.blade.php ENDPATH**/ ?>