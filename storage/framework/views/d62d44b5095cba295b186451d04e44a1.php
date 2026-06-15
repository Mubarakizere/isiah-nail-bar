

<?php $__env->startSection('title', 'Instagram Gallery'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6">
    
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            <i class="ph ph-instagram-logo text-pink-600 mr-2"></i>Instagram Gallery
        </h1>
        <p class="text-gray-600">Manage Instagram posts displayed on your website</p>
    </div>

    
    <?php if(session('success')): ?>
        <div class="mb-6 bg-green-50 border-2 border-green-200 rounded-xl p-4 flex items-center gap-3">
            <i class="ph ph-check-circle text-2xl text-green-600"></i>
            <p class="text-green-900 font-semibold"><?php echo e(session('success')); ?></p>
        </div>
    <?php endif; ?>

    
    <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Add New Instagram Post</h2>
        <form method="POST" action="<?php echo e(route('admin.gallery-instagram.store')); ?>">
            <?php echo csrf_field(); ?>
            <div class="flex gap-3">
                <div class="flex-1">
                    <input type="url" 
                           name="url" 
                           required
                           placeholder="https://www.instagram.com/p/POST_ID/"
                           class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                    <?php $__errorArgs = ['url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <button type="submit" 
                        class="px-6 py-3 bg-pink-600 text-white font-semibold rounded-lg hover:bg-pink-700 transition flex-shrink-0">
                    <i class="ph ph-plus-circle mr-2"></i>Add Post
                </button>
            </div>
        </form>
    </div>

    
    <?php if($posts->count()): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden hover:shadow-xl transition">
                    <div class="p-4">
                        <blockquote class="instagram-media"
                                    data-instgrm-permalink="<?php echo e($post->url); ?>"
                                    data-instgrm-version="14"
                                    style="background:#FFF; border:0; margin:0 auto; max-width:100%;">
                        </blockquote>
                    </div>
                    <div class="p-4 pt-0 flex items-center justify-between border-t border-gray-100">
                        <a href="<?php echo e($post->url); ?>" 
                           target="_blank"
                           class="text-sm text-blue-600 hover:text-blue-800 transition">
                            <i class="ph ph-arrow-square-out mr-1"></i>View on Instagram
                        </a>
                        <form method="POST" action="<?php echo e(route('admin.gallery-instagram.destroy', $post->id)); ?>">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit"
                                    class="px-3 py-1.5 bg-red-100 text-red-700 text-sm font-semibold rounded-lg hover:bg-red-200 transition"
                                    onclick="return confirm('Delete this Instagram post from gallery?')">
                                <i class="ph ph-trash mr-1"></i>Delete
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php else: ?>
        <div class="bg-pink-50 border-2 border-pink-200 rounded-xl p-12 text-center">
            <i class="ph ph-instagram-logo text-6xl text-pink-300 mb-4"></i>
            <p class="text-pink-900 font-semibold text-lg">No Instagram posts yet</p>
            <p class="text-pink-700 text-sm mt-2">Add your first Instagram post to showcase your work</p>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script async src="//www.instagram.com/embed.js"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\admin\gallery\index.blade.php ENDPATH**/ ?>