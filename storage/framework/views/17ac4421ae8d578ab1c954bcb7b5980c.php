<?php $__env->startSection('title', 'Settings'); ?>
<?php $__env->startSection('page-title', 'Site Settings'); ?>
<?php $__env->startSection('page-subtitle', 'Manage global configuration options'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto">
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900">Email Notifications</h2>
            <p class="text-sm text-gray-500 mt-1">Configure additional email addresses that should receive booking alerts.</p>
        </div>

        <form action="<?php echo e(route('admin.settings.store')); ?>" method="POST" class="p-6 space-y-6">
            <?php echo csrf_field(); ?>
            
            <div>
                <label for="booking_alert_emails" class="block text-sm font-medium text-gray-700 mb-2">Additional Booking Alert Emails</label>
                <textarea 
                    id="booking_alert_emails" 
                    name="booking_alert_emails" 
                    rows="3" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-rose-500 focus:border-rose-500 <?php $__errorArgs = ['booking_alert_emails'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    placeholder="e.g. admin2@isaiahnailbar.com, manager@isaiahnailbar.com"
                ><?php echo e(old('booking_alert_emails', $settings['booking_alert_emails'] ?? '')); ?></textarea>
                <p class="text-xs text-gray-500 mt-2">Enter multiple email addresses separated by commas (,). These emails will receive an alert whenever a new booking is made.</p>
                <?php $__errorArgs = ['booking_alert_emails'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-100">
                <button type="submit" class="px-6 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-xl shadow-sm shadow-rose-600/20 transition-colors flex items-center gap-2">
                    <i class="ph ph-check-circle text-lg"></i>
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\dashboard\admin\settings\index.blade.php ENDPATH**/ ?>