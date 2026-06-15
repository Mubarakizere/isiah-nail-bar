

<?php $__env->startSection('title', 'Add Team Member'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6">
    <div class="mb-8">
        <a href="<?php echo e(route('admin.team_members.index')); ?>" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition mb-4">
            <i class="ph ph-arrow-left mr-2"></i> Back to Team
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Add Team Member</h1>
        <p class="text-gray-600">Create a new profile for non-provider staff</p>
    </div>

    <div class="max-w-3xl">
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-8">
            <form action="<?php echo e(route('admin.team_members.store')); ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
                <?php echo csrf_field(); ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input type="text" name="name" value="<?php echo e(old('name')); ?>" required
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-transparent transition">
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role / Title</label>
                        <input type="text" name="role" value="<?php echo e(old('role')); ?>" required placeholder="e.g. Receptionist, Manager"
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-transparent transition">
                        <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Short Bio</label>
                    <textarea name="bio" rows="4" 
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-transparent transition"><?php echo e(old('bio')); ?></textarea>
                    <?php $__errorArgs = ['bio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Display Order</label>
                        <input type="number" name="display_order" value="<?php echo e(old('display_order', 0)); ?>"
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-transparent transition">
                        <p class="text-xs text-gray-500 mt-1">Lower numbers appear first</p>
                        <?php $__errorArgs = ['display_order'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="active" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-transparent transition">
                            <option value="1" <?php echo e(old('active', 1) == 1 ? 'selected' : ''); ?>>Active</option>
                            <option value="0" <?php echo e(old('active') == 0 ? 'selected' : ''); ?>>Inactive</option>
                        </select>
                         <?php $__errorArgs = ['active'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Photo</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition cursor-pointer bg-gray-50 relative" id="drop-zone">
                        <div class="space-y-1 text-center">
                            <i class="ph ph-image text-4xl text-gray-400 mb-2"></i>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-rose-600 hover:text-rose-500 focus-within:outline-none">
                                    <span>Upload a file</span>
                                    <input id="file-upload" name="photo" type="file" class="sr-only" accept="image/*">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                        </div>
                         
                        <img id="preview-image" src="#" alt="Preview" class="absolute inset-0 w-full h-full object-cover rounded-lg hidden">
                    </div>
                     <?php $__errorArgs = ['photo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="flex justify-end pt-6">
                    <button type="submit" class="px-8 py-3 bg-gray-900 text-white font-semibold rounded-lg hover:bg-gray-800 transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        Create Team Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    const fileInput = document.getElementById('file-upload');
    const previewImage = document.getElementById('preview-image');
    const dropZone = document.getElementById('drop-zone');

    fileInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewImage.classList.remove('hidden');
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Drag and drop functionality
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        dropZone.classList.add('border-rose-500', 'bg-rose-50');
    }

    function unhighlight(e) {
        dropZone.classList.remove('border-rose-500', 'bg-rose-50');
    }

    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files;
        
        if (files && files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewImage.classList.remove('hidden');
            }
            reader.readAsDataURL(files[0]);
        }
    }
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\admin\team_members\create.blade.php ENDPATH**/ ?>