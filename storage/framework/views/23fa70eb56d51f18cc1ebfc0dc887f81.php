<?php $__env->startSection('title', 'Add Provider'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6">
    
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Add New Provider</h1>
                <p class="text-gray-600">Create a new service provider account</p>
            </div>
            <a href="<?php echo e(route('admin.providers.index')); ?>" 
               class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">
                <i class="ph ph-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>

    
    <?php if($errors->any()): ?>
        <div class="bg-red-50 border-2 border-red-200 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <i class="ph ph-warning text-2xl text-red-600 mt-0.5"></i>
                <div class="flex-1">
                    <h3 class="font-bold text-red-900 mb-2">Please fix the following errors:</h3>
                    <ul class="list-disc list-inside space-y-1">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="text-red-700 text-sm"><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    
    <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4 mb-6">
        <div class="flex items-start gap-3">
            <i class="ph ph-info text-2xl text-blue-600 mt-0.5"></i>
            <div>
                <h3 class="font-bold text-blue-900 mb-1">🔐 Password & Login Credentials</h3>
                <p class="text-blue-700 text-sm">
                    A secure random password will be automatically generated for this provider. 
                    Login credentials will be sent to their email address immediately after creation.
                </p>
            </div>
        </div>
    </div>

    
    <form method="POST" action="<?php echo e(route('admin.providers.store')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Full Name <span class="text-red-600">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               value="<?php echo e(old('name')); ?>"
                               required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>

                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Email <span class="text-red-600">*</span>
                        </label>
                        <input type="email" 
                               name="email" 
                               value="<?php echo e(old('email')); ?>"
                               required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <p class="text-xs text-gray-500 mt-1">Login credentials will be sent to this email</p>
                    </div>

                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Phone
                        </label>
                        <input type="text" 
                               name="phone" 
                               value="<?php echo e(old('phone')); ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>

                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Photo
                        </label>
                        <input type="file" 
                               name="photo" 
                               accept="image/*"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-gray-500 mt-1">Maximum file size: 30MB</p>
                    </div>

                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Bio
                        </label>
                        <textarea name="bio" 
                                  rows="4"
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"><?php echo e(old('bio')); ?></textarea>
                    </div>

                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            Assign Services
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                                    <input type="checkbox" 
                                           name="services[]" 
                                           value="<?php echo e($service->id); ?>"
                                           id="service-<?php echo e($service->id); ?>" 
                                           <?php echo e(in_array($service->id, old('services', [])) ? 'checked' : ''); ?>

                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                                    <label for="service-<?php echo e($service->id); ?>" 
                                           class="ml-3 text-sm text-gray-700 cursor-pointer flex-1">
                                        <?php echo e($service->name); ?>

                                    </label>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <a href="<?php echo e(route('admin.providers.index')); ?>" 
                   class="px-6 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">
                    <i class="ph ph-arrow-left mr-2"></i>Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    <i class="ph ph-floppy-disk mr-2"></i>Save Provider
                </button>
            </div>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\admin\providers\create.blade.php ENDPATH**/ ?>