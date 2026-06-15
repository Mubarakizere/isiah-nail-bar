

<?php $__env->startSection('title', $message->getSubject()); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6">
    <div class="mb-6">
        <a href="<?php echo e(route('admin.webmail.index')); ?>" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition mb-4">
            <i class="ph ph-arrow-left mr-2"></i> Back to Inbox
        </a>
        <h1 class="text-2xl font-bold text-gray-900 leading-tight"><?php echo e($message->getSubject()); ?></h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6 pb-6 border-b border-gray-100">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-rose-100 flex items-center justify-center text-rose-600">
                            <span class="font-bold text-lg"><?php echo e(strtoupper(substr($message->getFrom()[0]->personal ?? $message->getFrom()[0]->mail, 0, 1))); ?></span>
                        </div>
                        <div>
                            <span class="block font-bold text-gray-900"><?php echo e($message->getFrom()[0]->personal ?? $message->getFrom()[0]->mail); ?></span>
                            <span class="block text-sm text-gray-500"><?php echo e($message->getFrom()[0]->mail); ?></span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="block text-sm text-gray-900 font-medium"><?php echo e($message->getDate()->format('d M Y, H:i')); ?></span>
                        <span class="block text-xs text-gray-500"><?php echo e($message->getDate()->diffForHumans()); ?></span>
                    </div>
                </div>

                <div class="prose prose-rose max-w-none text-gray-800">
                    <?php if($message->hasHTMLBody()): ?>
                        <?php echo $message->getHTMLBody(); ?>

                    <?php else: ?>
                        <?php echo nl2br(e($message->getTextBody())); ?>

                    <?php endif; ?>
                </div>

                <?php if($message->hasAttachments()): ?>
                    <div class="mt-8 pt-6 border-t border-gray-100">
                        <h4 class="text-sm font-bold text-gray-900 mb-3">Attachments</h4>
                        <div class="flex flex-wrap gap-4">
                            <?php $__currentLoopData = $message->getAttachments(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="#" class="flex items-center gap-2 px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition">
                                    <i class="ph ph-paperclip text-gray-400"></i>
                                    <span class="text-sm font-medium text-gray-700"><?php echo e($attachment->getName()); ?></span>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6" id="reply-section">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Reply</h3>
                <form action="<?php echo e(route('admin.webmail.reply', $message->getUid())); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="mb-4">
                        <textarea name="body" rows="6" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-transparent transition" placeholder="Write your reply..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                         <button type="submit" class="px-6 py-2.5 bg-gray-900 text-white font-semibold rounded-lg hover:bg-gray-800 transition shadow-lg">
                            <i class="ph ph-paper-plane-right mr-2"></i>Send Reply
                        </button>
                    </div>
                </form>
            </div>
        </div>

        
        <div class="lg:col-span-1 space-y-6">
             <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Actions</h4>
                <div class="space-y-2">
                    <a href="#reply-section" class="flex items-center gap-3 px-4 py-2 text-gray-700 hover:bg-gray-50 rounded-lg transition w-full text-left">
                        <i class="ph ph-arrow-u-up-left text-lg"></i>
                        <span class="font-medium">Reply</span>
                    </a>
                    
                    <form action="<?php echo e(route('admin.webmail.delete', $message->getUid())); ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this email?');">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="flex items-center gap-3 px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition w-full text-left">
                            <i class="ph ph-trash text-lg"></i>
                            <span class="font-medium">Delete</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\admin\webmail\show.blade.php ENDPATH**/ ?>