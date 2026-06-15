

<?php $__env->startSection('title', $service->name . ' | Nail Service in Kigali - Isaiah Nail Bar'); ?>

<?php $__env->startSection('meta_description', Str::limit(($service->description ?? $service->name . ' service at Isaiah Nail Bar') . ' - RWF ' . number_format($service->price) . '. Book your ' . $service->name . ' appointment at Kigali\'s #1 nail salon. Expert technicians, premium products.', 160)); ?>
<?php $__env->startSection('meta_keywords', strtolower($service->name) . ' Kigali, ' . strtolower($service->name) . ' Rwanda, ' . strtolower($service->name) . ' price, nail salon ' . strtolower($service->category->name ?? 'services') . ' Kigali, Isaiah Nail Bar ' . strtolower($service->name)); ?>

<?php if($service->image): ?>
<?php $__env->startSection('og_image', asset('storage/' . $service->image)); ?>
<?php endif; ?>

<?php $__env->startPush('schema'); ?>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Service",
  "name": "<?php echo e($service->name); ?>",
  "description": "<?php echo e($service->description ?? $service->name . ' service at Isaiah Nail Bar, Kigali'); ?>",
  <?php if($service->image): ?>
  "image": "<?php echo e(asset('storage/' . $service->image)); ?>",
  <?php endif; ?>
  "provider": {
    "@type": "NailSalon",
    "@id": "<?php echo e(url('/')); ?>/#business"
  },
  "areaServed": {
    "@type": "City",
    "name": "Kigali"
  },
  "offers": {
    "@type": "Offer",
    "price": "<?php echo e($service->price); ?>",
    "priceCurrency": "RWF",
    "availability": "https://schema.org/InStock",
    "url": "<?php echo e(route('booking.step1')); ?>"
  }
  <?php if($service->reviews->count() > 0): ?>
  ,"aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "<?php echo e(round($service->reviews->avg('rating'), 1)); ?>",
    "reviewCount": "<?php echo e($service->reviews->count()); ?>"
  }
  <?php endif; ?>
}
</script>


<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem", "position": 1, "name": "Home", "item": "<?php echo e(url('/')); ?>"},
    {"@type": "ListItem", "position": 2, "name": "Services", "item": "<?php echo e(url('/services')); ?>"},
    {"@type": "ListItem", "position": 3, "name": "<?php echo e($service->name); ?>", "item": "<?php echo e(url('/services/' . $service->id)); ?>"}
  ]
}
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-start">
            
            
            <div>
                <?php if($service->image): ?>
                    <img src="<?php echo e(asset('storage/' . $service->image)); ?>" 
                         alt="<?php echo e($service->name); ?>"
                         class="w-full rounded-2xl shadow-xl hover:scale-105 transition-transform duration-300">
                <?php else: ?>
                    <div class="w-full h-96 bg-gray-200 rounded-2xl flex items-center justify-center">
                        <i class="ph ph-image text-6xl text-gray-400"></i>
                    </div>
                <?php endif; ?>
            </div>

            
            <div>
                <h2 class="text-4xl font-bold text-gray-900 mb-4"><?php echo e($service->name); ?></h2>
                
                <p class="text-lg text-gray-600 mb-6"><?php echo e($service->description); ?></p>

                <?php if($service->category): ?>
                    <span class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 rounded-full text-sm font-semibold mb-6">
                        <i class="ph ph-tag"></i>
                        <?php echo e($service->category->name); ?>

                    </span>
                <?php endif; ?>

                <div class="bg-white rounded-xl p-6 shadow-md border border-gray-200 mb-6">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-clock text-2xl text-blue-600"></i>
                            <div>
                                <p class="text-sm text-gray-600">Duration</p>
                                <p class="font-bold text-gray-900"><?php echo e($service->duration_minutes); ?> minutes</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            <i class="ph ph-currency-circle-dollar text-2xl text-blue-600"></i>
                            <div>
                                <p class="text-sm text-gray-600">Price</p>
                                <p class="text-2xl font-bold text-blue-600">RWF <?php echo e(number_format($service->price)); ?></p>
                            </div>
                        </div>

                        <?php if($service->provider): ?>
                            <div class="flex items-center gap-3">
                                <i class="ph ph-user text-2xl text-blue-600"></i>
                                <div>
                                    <p class="text-sm text-gray-600">Provider</p>
                                    <p class="font-bold text-gray-900"><?php echo e($service->provider->name); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <a href="<?php echo e(route('booking.step1', ['service_id' => $service->id])); ?>" 
                   class="block w-full px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-center font-bold rounded-xl hover:from-blue-700 hover:to-indigo-700 shadow-lg hover:shadow-xl transition-all">
                    <i class="ph ph-calendar-check mr-2"></i>Book This Service
                </a>
            </div>
        </div>

        
        <?php if($service->reviews->count()): ?>
            <div class="mt-16">
                <h4 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <i class="ph ph-chat-circle-text text-blue-600"></i>
                    Customer Reviews
                </h4>

                <div class="space-y-4">
                    <?php $__currentLoopData = $service->reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
                            <div class="flex justify-between items-center mb-3">
                                <strong class="text-gray-900"><?php echo e($review->user->name ?? 'Anonymous'); ?></strong>
                                <small class="text-gray-500"><?php echo e($review->created_at->diffForHumans()); ?></small>
                            </div>
                            <p class="text-gray-700"><?php echo e($review->comment); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if(auth()->guard()->check()): ?>
            <?php if(auth()->user()->hasBookedService($service->id)): ?>
                <div class="mt-12 bg-white rounded-xl p-8 shadow-md border border-gray-200">
                    <h5 class="text-xl font-bold text-gray-900 mb-6">Leave a Review</h5>

                    <form method="POST" action="<?php echo e(route('reviews.store')); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="service_id" value="<?php echo e($service->id); ?>">

                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Rating (1 to 5)</label>
                            <select name="rating" required class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                                <option value="">Select rating</option>
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?php echo e($i); ?>"><?php echo e($i); ?> ★</option>
                                <?php endfor; ?>
                            </select>
                            <?php $__errorArgs = ['rating'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-red-600"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Comment</label>
                            <textarea name="comment" required rows="4" class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none resize-vertical"></textarea>
                            <?php $__errorArgs = ['comment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="text-red-600"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <button type="submit" class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                            <i class="ph ph-paper-plane-tilt mr-2"></i>Submit Review
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\services\show.blade.php ENDPATH**/ ?>