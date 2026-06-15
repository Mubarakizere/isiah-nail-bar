<?php $__env->startSection('title', 'Edit Provider'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6">
    
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Edit Provider</h1>
        <p class="text-gray-600">Update provider profile information</p>
    </div>

    
    <?php if($errors->any()): ?>
        <div class="mb-6 bg-red-50 border-2 border-red-200 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <i class="ph ph-warning-circle text-2xl text-red-600 flex-shrink-0"></i>
                <div class="flex-1">
                    <h3 class="font-bold text-red-900 mb-2">Please fix the following errors:</h3>
                    <ul class="text-sm text-red-800 space-y-1">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>• <?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    
    <div class="max-w-4xl">
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6">
            <form action="<?php echo e(route('admin.providers.update', $provider->id)); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="space-y-6 mb-6">
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Provider Name</label>
                        <input type="text" 
                               name="name" 
                               value="<?php echo e(old('name', $provider->name)); ?>" 
                               required
                               class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                    </div>

                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Phone</label>
                            <input type="text" 
                                   name="phone" 
                                   value="<?php echo e(old('phone', $provider->phone)); ?>"
                                   placeholder="+250 XXX XXX XXX"
                                   class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                            <input type="email" 
                                   name="email" 
                                   value="<?php echo e(old('email', $provider->email)); ?>"
                                   placeholder="provider@example.com"
                                   class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                        </div>
                    </div>

                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Bio</label>
                        <textarea name="bio" 
                                  rows="4"
                                  placeholder="Tell us about this provider..."
                                  class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none resize-vertical"><?php echo e(old('bio', $provider->bio)); ?></textarea>
                    </div>

                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Provider Photo</label>
                        <?php if($provider->photo): ?>
                            <img src="<?php echo e(asset('storage/'.$provider->photo)); ?>" 
                                 alt="Provider Photo" 
                                 class="mb-3 rounded-lg border-2 border-gray-200 max-h-40 object-cover">
                        <?php else: ?>
                            <p class="text-gray-500 text-sm mb-3">No photo uploaded</p>
                        <?php endif; ?>
                        <input type="file" 
                               name="photo"
                               accept="image/*"
                               class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                        <p class="text-xs text-gray-500 mt-1">Maximum file size: 30MB</p>
                    </div>

                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Assign Services</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="flex items-center gap-3 p-3 rounded-lg border-2 border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition cursor-pointer">
                                    <input class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500" 
                                           type="checkbox" 
                                           name="services[]" 
                                           value="<?php echo e($service->id); ?>"
                                           id="service-<?php echo e($service->id); ?>"
                                           <?php echo e(in_array($service->id, old('services', $provider->services->pluck('id')->toArray())) ? 'checked' : ''); ?>>
                                    <span class="text-sm font-medium text-gray-700"><?php echo e($service->name); ?></span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>

                
                <div class="flex gap-3">
                    <a href="<?php echo e(route('admin.providers.index')); ?>" 
                       class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition text-center">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-gray-900 text-white font-semibold rounded-lg hover:bg-gray-800 transition">
                        <i class="ph ph-check-circle mr-2"></i>Update Provider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\admin\providers\edit.blade.php ENDPATH**/ ?>