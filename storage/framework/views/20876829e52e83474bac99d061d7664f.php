<?php $__env->startSection('title', 'Add Hero Slide'); ?>
<?php $__env->startSection('page-title', 'Add Hero Slide'); ?>
<?php $__env->startSection('page-subtitle', 'Create a new carousel slide for the homepage'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        
        <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50">
            <div class="flex items-center gap-3">
                <a href="<?php echo e(route('admin.hero-slides.index')); ?>" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-white transition-colors">
                    <i class="ph ph-arrow-left text-xl"></i>
                </a>
                <div>
                    <h3 class="font-serif text-lg font-bold text-gray-900">New Hero Slide</h3>
                    <p class="text-sm text-gray-500">Fill in the details for the carousel slide</p>
                </div>
            </div>
        </div>

        <form method="POST" action="<?php echo e(route('admin.hero-slides.store')); ?>" enctype="multipart/form-data" class="p-8 space-y-6">
            <?php echo csrf_field(); ?>

            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Slide Image <span class="text-red-500">*</span></label>
                <div class="relative" x-data="{ preview: null }">
                    <div class="border-2 border-dashed border-gray-200 rounded-2xl hover:border-rose-300 transition-colors cursor-pointer overflow-hidden"
                         :class="{ 'border-rose-400': preview }">
                        <template x-if="preview">
                            <div class="relative aspect-[16/9]">
                                <img :src="preview" class="w-full h-full object-cover">
                                <button type="button" @click="preview = null; $refs.imageInput.value = ''"
                                        class="absolute top-3 right-3 p-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors shadow-lg">
                                    <i class="ph ph-x text-lg"></i>
                                </button>
                            </div>
                        </template>
                        <template x-if="!preview">
                            <label class="flex flex-col items-center justify-center p-12 cursor-pointer">
                                <div class="w-16 h-16 bg-rose-50 rounded-2xl flex items-center justify-center mb-4">
                                    <i class="ph ph-cloud-arrow-up text-3xl text-rose-400"></i>
                                </div>
                                <p class="text-gray-900 font-medium mb-1">Click to upload image</p>
                                <p class="text-gray-400 text-sm">JPG, PNG or WebP • Max 5MB</p>
                                <p class="text-gray-400 text-xs mt-1">Recommended: 1920×1080px or larger</p>
                            </label>
                        </template>
                    </div>
                    <input type="file" name="image" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" x-ref="imageInput"
                           @change="const file = $event.target.files[0]; if(file) { const reader = new FileReader(); reader.onload = e => preview = e.target.result; reader.readAsDataURL(file); }">
                </div>
                <?php $__errorArgs = ['image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div>
                <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-2">Tagline / Subtitle</label>
                <input type="text" name="subtitle" id="subtitle" value="<?php echo e(old('subtitle')); ?>"
                       placeholder="e.g. Kigali's Premier Nail Salon"
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 text-sm">
                <p class="text-gray-400 text-xs mt-1">Appears as a small badge above the title</p>
                <?php $__errorArgs = ['subtitle'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" value="<?php echo e(old('title')); ?>"
                       placeholder="e.g. Artistry at Your Fingertips"
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 text-sm">
                <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" id="description" rows="3"
                          placeholder="e.g. Experience the perfect blend of luxury, hygiene, and style."
                          class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 text-sm resize-none"><?php echo e(old('description')); ?></textarea>
                <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="button_text" class="block text-sm font-medium text-gray-700 mb-2">Primary Button Text</label>
                    <input type="text" name="button_text" id="button_text" value="<?php echo e(old('button_text', 'Book Appointment')); ?>"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 text-sm">
                </div>
                <div>
                    <label for="button_url" class="block text-sm font-medium text-gray-700 mb-2">Primary Button URL</label>
                    <input type="text" name="button_url" id="button_url" value="<?php echo e(old('button_url', '/booking')); ?>"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="secondary_button_text" class="block text-sm font-medium text-gray-700 mb-2">Secondary Button Text</label>
                    <input type="text" name="secondary_button_text" id="secondary_button_text" value="<?php echo e(old('secondary_button_text', 'View Services')); ?>"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 text-sm">
                </div>
                <div>
                    <label for="secondary_button_url" class="block text-sm font-medium text-gray-700 mb-2">Secondary Button URL</label>
                    <input type="text" name="secondary_button_url" id="secondary_button_url" value="<?php echo e(old('secondary_button_url', '/services')); ?>"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 text-sm">
                </div>
            </div>

            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order" value="<?php echo e(old('sort_order', 0)); ?>" min="0"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 text-sm">
                    <p class="text-gray-400 text-xs mt-1">Lower numbers appear first</p>
                </div>
                <div class="flex items-end pb-1">
                    <label class="relative inline-flex items-center cursor-pointer gap-3">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" <?php echo e(old('is_active', true) ? 'checked' : ''); ?>

                               class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-rose-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
                        <span class="text-sm font-medium text-gray-700">Active</span>
                    </label>
                </div>
            </div>

            
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="<?php echo e(route('admin.hero-slides.index')); ?>" class="px-6 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-8 py-2.5 bg-gray-900 text-white rounded-xl text-sm font-medium hover:bg-rose-600 transition-all shadow-sm">
                    <i class="ph ph-plus-circle mr-1"></i>
                    Create Slide
                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\admin\hero-slides\create.blade.php ENDPATH**/ ?>