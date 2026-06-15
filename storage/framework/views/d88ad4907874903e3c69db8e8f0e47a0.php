

<?php $__env->startSection('title', 'Email Details'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6">
    <div class="mb-8">
        <a href="<?php echo e(route('admin.emails.index')); ?>" class="text-blue-600 hover:text-blue-800 font-semibold">
            <i class="ph ph-arrow-left mr-2"></i>Back to Email History
        </a>
    </div>

    <div class="max-w-4xl">
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-8">
            
            <div class="mb-6 pb-6 border-b border-gray-200">
                <div class="flex items-start justify-between mb-4">
                    <h1 class="text-2xl font-bold text-gray-900"><?php echo e($emailLog->subject); ?></h1>
                    <?php if($emailLog->status === 'sent'): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text- font-bold bg-green-100 text-green-800">
                            <i class="ph ph-check-circle mr-1"></i>Sent
                        </span>
                    <?php elseif($emailLog->status === 'failed'): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-red-100 text-red-800">
                            <i class="ph ph-x-circle mr-1"></i>Failed
                        </span>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-600 mb-1">Recipient</p>
                        <p class="font-semibold text-gray-900"><?php echo e($emailLog->recipient_name); ?></p>
                        <p class="text-gray-600"><?php echo e($emailLog->recipient_email); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600 mb-1">Type</p>
                        <p class="font-semibold text-gray-900"><?php echo e(ucwords(str_replace('_', ' ', $emailLog->email_type))); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600 mb-1">Sent At</p>
                        <p class="font-semibold text-gray-900">
                            <?php echo e($emailLog->sent_at ? $emailLog->sent_at->format('F j, Y \a\t g:i A') : 'Not sent'); ?>

                        </p>
                    </div>
                    <?php if($emailLog->booking_id): ?>
                        <div>
                            <p class="text-gray-600 mb-1">Related Booking</p>
                            <a href="<?php echo e(route('booking.receipt', $emailLog->booking_id)); ?>" 
                               class="font-semibold text-blue-600 hover:text-blue-800">
                                Booking #<?php echo e($emailLog->booking_id); ?>

                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            
            <?php if($emailLog->metadata): ?>
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <h3 class="font-bold text-gray-900 mb-3">Email Metadata</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <pre class="text-sm text-gray-700"><?php echo e(json_encode($emailLog->metadata, JSON_PRETTY_PRINT)); ?></pre>
                    </div>
                </div>
            <?php endif; ?>

            
            <?php if($emailLog->status === 'failed' && $emailLog->error_message): ?>
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <h3 class="font-bold text-red-900 mb-3">Error Message</h3>
                    <div class="bg-red-50 border-2 border-red-200 rounded-lg p-4">
                        <p class="text-sm text-red-800"><?php echo e($emailLog->error_message); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            
            <?php if($emailLog->booking): ?>
                <div class="mb-6">
                    <h3 class="font-bold text-gray-900 mb-3">Booking Information</h3>
                    <div class="bg-blue-50 rounded-lg p-4 space-y-2 text-sm">
                        <p><strong>Date:</strong> <?php echo e(\Carbon\Carbon::parse($emailLog->booking->date)->format('F j, Y')); ?></p>
                        <p><strong>Time:</strong> <?php echo e($emailLog->booking->time); ?></p>
                        <p><strong>Customer:</strong> <?php echo e($emailLog->booking->customer->name); ?></p>
                        <p><strong>Provider:</strong> <?php echo e($emailLog->booking->provider->name); ?></p>
                        <p><strong>Status:</strong> <span class="capitalize"><?php echo e($emailLog->booking->status); ?></span></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\admin\emails\show.blade.php ENDPATH**/ ?>