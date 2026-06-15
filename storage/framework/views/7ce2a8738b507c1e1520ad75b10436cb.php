<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['message' => session('success') ?? session('error'), 'type' => session('success') ? 'success' : 'error']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['message' => session('success') ?? session('error'), 'type' => session('success') ? 'success' : 'error']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div x-data="{ show: false, message: '<?php echo e($message); ?>', type: '<?php echo e($type); ?>' }"
     x-init="<?php if($message): ?> show = true; setTimeout(() => show = false, 3000); <?php endif; ?>"
     x-show="show"
     x-transition:enter="transform ease-out duration-300 transition"
     x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
     x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed bottom-0 right-0 m-6 w-full max-w-sm overflow-hidden rounded-lg shadow-lg border pointer-events-auto z-50"
     :class="{
         'bg-white border-green-100': type === 'success',
         'bg-white border-red-100': type === 'error'
     }"
     style="display: none;">
    <div class="p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <template x-if="type === 'success'">
                     <div class="rounded-full bg-green-100 p-1">
                        <i class="ph ph-check text-green-600"></i>
                    </div>
                </template>
                 <template x-if="type === 'error'">
                    <div class="rounded-full bg-red-100 p-1">
                        <i class="ph ph-x text-red-600"></i>
                    </div>
                </template>
            </div>
            <div class="ml-3 w-0 flex-1 pt-0.5">
                <p class="text-sm font-medium text-gray-900" x-text="message"></p>
            </div>
            <div class="ml-4 flex flex-shrink-0">
                <button type="button" @click="show = false" class="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <span class="sr-only">Close</span>
                    <i class="ph ph-x"></i>
                </button>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\components\toast-notification.blade.php ENDPATH**/ ?>