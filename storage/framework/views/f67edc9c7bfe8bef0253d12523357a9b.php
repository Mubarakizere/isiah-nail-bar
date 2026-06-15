

<?php $__env->startSection('title', 'Select Date & Time'); ?>

<?php $__env->startSection('content'); ?>



<div class="bg-gray-900 py-12 relative overflow-hidden">
    <div class="absolute inset-0 opacity-20">
        <img src="<?php echo e(asset('storage/banner.jpg')); ?>" alt="" class="w-full h-full object-cover">
    </div>
    <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-transparent"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <span class="text-rose-400 font-medium tracking-widest text-xs uppercase mb-2 block">Step 3 of 4</span>
        <h1 class="text-3xl md:text-4xl font-serif text-white mb-2">Schedule Your Visit</h1>
        <p class="text-gray-400 font-light text-lg">Find the perfect time for your luxury experience.</p>
    </div>
</div>

<div class="bg-white min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="<?php echo e(route('booking.step3.submit')); ?>" id="timeForm">
            <?php echo csrf_field(); ?>
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                
                
                <div class="lg:col-span-8">
                    
                    
                    <div class="mb-10">
                        <h3 class="text-lg font-serif text-gray-900 mb-4 flex items-center gap-2">
                             <span class="w-8 h-8 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-bold">1</span>
                             Select Date
                        </h3>
                        <div class="flex gap-3 overflow-x-auto pb-4 scrollbar-hide snap-x">
                            <?php for($i = 0; $i < 14; $i++): ?>
                                <?php
                                    $day = \Carbon\Carbon::today()->addDays($i);
                                    $isSelected = $selectedDate === $day->toDateString();
                                ?>
                                <a href="<?php echo e(route('booking.step3', ['booking_date' => $day->toDateString()])); ?>"
                                   class="snap-start flex-shrink-0 w-24 p-3 rounded-2xl border text-center transition-all duration-300 group
                                   <?php echo e($isSelected 
                                      ? 'bg-gray-900 border-gray-900 text-white shadow-lg scale-105' 
                                      : 'bg-white border-gray-200 text-gray-500 hover:border-gray-900 hover:text-gray-900'); ?>">
                                    <span class="block text-xs font-medium uppercase tracking-wider opacity-60 mb-1"><?php echo e($day->format('M')); ?></span>
                                    <span class="block text-2xl font-serif font-bold mb-1 <?php echo e($isSelected ? 'text-white' : 'text-gray-900'); ?>"><?php echo e($day->format('j')); ?></span>
                                    <span class="block text-xs <?php echo e($isSelected ? 'text-rose-400' : ''); ?>"><?php echo e($day->format('D')); ?></span>
                                </a>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="booking_date" value="<?php echo e($selectedDate); ?>">
                    </div>

                    
                    <div>
                        <h3 class="text-lg font-serif text-gray-900 mb-4 flex items-center gap-2">
                             <span class="w-8 h-8 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-bold">2</span>
                             Select Time
                        </h3>
                        
                        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                             <div class="flex items-center justify-between mb-6">
                                <span class="text-sm text-gray-500">Available slots for <span class="font-bold text-gray-900"><?php echo e(\Carbon\Carbon::parse($selectedDate)->format('l, F jS')); ?></span></span>
                                <div class="flex gap-4 text-xs">
                                    <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-white border border-gray-300"></span> Available</div>
                                    <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-gray-900"></span> Selected</div>
                                    <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-gray-200"></span> Taken</div>
                                </div>
                            </div>

                            <?php if(empty($slotsWithStatus)): ?>
                                <div class="text-center py-12">
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-200 mb-4">
                                        <i class="ph ph-calendar-x text-2xl text-gray-400"></i>
                                    </div>
                                    <h4 class="text-gray-900 font-medium mb-1">No availability</h4>
                                    <p class="text-gray-500 text-sm">Please select another date.</p>
                                </div>
                            <?php else: ?>
                                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                                    <?php $__currentLoopData = $slotsWithStatus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="relative">
                                            <input type="radio"
                                                   name="booking_time"
                                                   id="time_<?php echo e($slot['time']); ?>"
                                                   value="<?php echo e($slot['time']); ?>"
                                                   class="peer hidden"
                                                   <?php echo e(old('booking_time') == $slot['time'] ? 'checked' : ''); ?>

                                                   <?php echo e($slot['status'] !== 'available' ? 'disabled' : ''); ?>>
                                            
                                            <label for="time_<?php echo e($slot['time']); ?>"
                                                   class="flex flex-col items-center justify-center py-3 rounded-xl border text-sm font-medium transition-all duration-200
                                                   <?php echo e($slot['status'] === 'available' 
                                                      ? 'bg-white border-gray-200 text-gray-700 hover:border-gray-900 hover:shadow-md cursor-pointer peer-checked:bg-gray-900 peer-checked:border-gray-900 peer-checked:text-white peer-checked:shadow-lg' 
                                                      : 'bg-gray-100 border-transparent text-gray-400 cursor-not-allowed decoration-slice line-through'); ?>">
                                                <?php echo e(\Carbon\Carbon::createFromFormat('H:i', $slot['time'])->format('H:i')); ?>

                                            </label>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                                <?php $__errorArgs = ['booking_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="text-red-500 text-sm mt-3 flex items-center gap-1">
                                        <i class="ph ph-warning-circle"></i> Please select a time slot.
                                    </p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="mt-10">
                        <h3 class="text-lg font-serif text-gray-900 mb-4 flex items-center gap-2">
                             <span class="w-8 h-8 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-bold">3</span>
                             Select Location
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <label class="relative cursor-pointer block">
                                <input type="radio" name="location_type" value="salon" class="peer hidden" checked onchange="toggleAddressField()">
                                <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm transition-all duration-300 peer-checked:border-gray-900 peer-checked:ring-1 peer-checked:ring-gray-900 peer-checked:bg-gray-50 h-full flex flex-col justify-center">
                                    <h4 class="font-bold text-gray-900 mb-1 flex items-center gap-2">
                                        <i class="ph ph-storefront text-xl"></i> In-Salon Service
                                    </h4>
                                    <p class="text-sm text-gray-500">Standard pricing at our luxury studio.</p>
                                </div>
                            </label>

                            <label class="relative cursor-pointer block">
                                <input type="radio" name="location_type" value="home" class="peer hidden" onchange="toggleAddressField()">
                                <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm transition-all duration-300 peer-checked:border-gray-900 peer-checked:ring-1 peer-checked:ring-gray-900 peer-checked:bg-gray-50 h-full flex flex-col justify-center">
                                    <h4 class="font-bold text-gray-900 mb-1 flex items-center gap-2">
                                        <i class="ph ph-house text-xl"></i> Home Service
                                    </h4>
                                    <p class="text-sm text-rose-500 font-medium">+100% (Double Total Price)</p>
                                </div>
                            </label>
                        </div>
                        <?php $__errorArgs = ['location_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                        <div id="address_field_container" class="hidden animate-fade-in-up bg-white p-4 rounded-xl border border-gray-200">
                            <label class="block text-sm font-medium text-gray-900 mb-2">Service Address <span class="text-rose-500">*</span></label>
                            <textarea name="address" id="address" rows="2" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-colors" placeholder="Enter your full address (Street, House number, Area)..."><?php echo e(old('address')); ?></textarea>
                            <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <p class="text-sm text-gray-500 mt-2"><i class="ph ph-info"></i> Please ensure the location is accessible.</p>
                        </div>

                        
                        <div id="pickup_container" class="mt-4 animate-fade-in-up bg-white p-4 rounded-xl border border-gray-200">
                            <label class="flex items-center cursor-pointer mb-3 relative">
                                <input type="checkbox" name="wants_pickup" id="wants_pickup" value="1" class="peer hidden" onchange="togglePickupFields()" <?php echo e(old('wants_pickup') ? 'checked' : ''); ?>>
                                <div class="w-5 h-5 rounded border border-gray-300 flex items-center justify-center mr-3 peer-checked:bg-gray-900 peer-checked:border-gray-900 transition-colors">
                                    <i class="ph ph-check text-white opacity-0 peer-checked:opacity-100 text-xs"></i>
                                </div>
                                <span class="font-medium text-gray-900">Need Transport / Pickup?</span>
                                <span class="ml-2 text-xs text-rose-500 bg-rose-50 px-2 py-1 rounded-full">New</span>
                            </label>

                            <div id="pickup_fields" class="<?php echo e(old('wants_pickup') ? '' : 'hidden'); ?> space-y-4 pt-2 border-t border-gray-100">
                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-1">Pickup Location <span class="text-rose-500">*</span></label>
                                    <select name="pickup_location_id" id="pickup_location_id" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-colors">
                                        <option value="">Select a location</option>
                                        <?php $__currentLoopData = $pickupLocations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($loc->id); ?>" <?php echo e(old('pickup_location_id') == $loc->id ? 'selected' : ''); ?>>
                                                <?php echo e($loc->name); ?> (+<?php echo e(number_format($loc->fee)); ?> RWF)
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['pickup_location_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-900 mb-1">Exact Address <span class="text-rose-500">*</span></label>
                                    <textarea name="pickup_address" id="pickup_address" rows="2" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-colors" placeholder="Where should we pick you up?"><?php echo e(old('pickup_address')); ?></textarea>
                                    <?php $__errorArgs = ['pickup_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-8 mt-8 border-t border-gray-100">
                        <a href="<?php echo e(route('booking.step2')); ?>" class="text-gray-500 hover:text-gray-900 font-medium flex items-center gap-2 transition-colors">
                            <i class="ph ph-arrow-left"></i> Back to Artist
                        </a>
                    </div>
                </div>

                
                <div class="hidden lg:block lg:col-span-4 pl-4">
                    <div class="sticky top-24">
                        <div id="summaryCard" class="bg-gray-900 text-white rounded-2xl p-6 shadow-xl overflow-hidden relative">
                            <div class="absolute top-0 right-0 p-4 opacity-10">
                                <i class="ph ph-receipt text-9xl text-white"></i>
                            </div>
                            
                            <h3 class="text-lg font-serif mb-6 relative z-10">Booking Summary</h3>
                            
                            
                            <div class="space-y-4 mb-6 pb-6 border-b border-gray-700 relative z-10">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-400">Customer</span>
                                    <span class="text-white"><?php echo e(auth()->user()->name ?? 'Guest'); ?></span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-400">Artist</span>
                                    <span class="text-rose-400"><?php echo e($provider->name); ?></span>
                                </div>
                                 <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-400">Date</span>
                                    <span class="text-white"><?php echo e(\Carbon\Carbon::parse($selectedDate)->format('M j, Y')); ?></span>
                                </div>
                            </div>

                            
                            <div class="space-y-2 mb-6 relative z-10 max-h-[25vh] overflow-y-auto custom-scrollbar pr-2">
                                <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-300"><?php echo e($service->name); ?></span>
                                        <span class="text-gray-500"><?php echo e(number_format($service->price)); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            
                            <div class="border-t border-gray-700 pt-4 mb-6 relative z-10">
                                <div class="flex justify-between items-end">
                                    <span class="text-gray-400 text-sm">Total</span>
                                    <span class="text-2xl font-serif text-white">RWF <?php echo e(number_format($services->sum('price'))); ?></span>
                                </div>
                            </div>

                            <button type="submit" form="timeForm" class="w-full py-4 bg-white text-gray-900 font-bold rounded-xl hover:bg-rose-50 transition-all flex items-center justify-center gap-2 group relative z-10">
                                <span>Complete Booking</span>
                                <i class="ph ph-check-circle group-hover:text-green-600 transition-colors"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<div class="lg:hidden fixed bottom-6 right-6 z-40">
    <button type="submit" form="timeForm" class="w-16 h-16 bg-gray-900 text-white rounded-full shadow-2xl flex items-center justify-center hover:bg-rose-600 transition-colors">
        <i class="ph ph-arrow-right text-2xl"></i>
    </button>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.animate-fade-in-up {
    animation: fadeInUp 0.3s ease-out forwards;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
function toggleAddressField() {
    const isHome = document.querySelector('input[name="location_type"]:checked').value === 'home';
    const container = document.getElementById('address_field_container');
    const addressInput = document.getElementById('address');
    
    // Pickup elements
    const pickupContainer = document.getElementById('pickup_container');
    const wantsPickupCheckbox = document.getElementById('wants_pickup');
    
    if (isHome) {
        container.classList.remove('hidden');
        addressInput.setAttribute('required', 'required');
        
        // Hide pickup section if home service
        pickupContainer.classList.add('hidden');
        wantsPickupCheckbox.checked = false;
        togglePickupFields();
    } else {
        container.classList.add('hidden');
        addressInput.removeAttribute('required');
        addressInput.value = '';
        
        // Show pickup section if in-salon
        pickupContainer.classList.remove('hidden');
    }
}

function togglePickupFields() {
    const isPickup = document.getElementById('wants_pickup').checked;
    const fields = document.getElementById('pickup_fields');
    const locationSelect = document.getElementById('pickup_location_id');
    const pickupAddressInput = document.getElementById('pickup_address');
    
    if (isPickup) {
        fields.classList.remove('hidden');
        locationSelect.setAttribute('required', 'required');
        pickupAddressInput.setAttribute('required', 'required');
    } else {
        fields.classList.add('hidden');
        locationSelect.removeAttribute('required');
        pickupAddressInput.removeAttribute('required');
        locationSelect.value = '';
        pickupAddressInput.value = '';
    }
}

// Initial state on page load (in case of old inputs)
document.addEventListener('DOMContentLoaded', function() {
    toggleAddressField();
    togglePickupFields();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\booking\step3.blade.php ENDPATH**/ ?>