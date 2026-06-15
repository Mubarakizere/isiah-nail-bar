

<?php $__env->startSection('title', 'Manage Time Slots'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6">
    
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Time Slots</h1>
        <p class="text-gray-600">Manage provider availability and block/unblock time slots</p>
    </div>

    
    <form method="GET" action="<?php echo e(route('admin.slots.index')); ?>" class="bg-white rounded-2xl p-6 shadow-md border border-gray-200 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-1">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Select Provider</label>
                <select name="provider_id" required
                        class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                    <option value="">-- Choose Provider --</option>
                    <?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($prov->id); ?>" <?php echo e($providerId == $prov->id ? 'selected' : ''); ?>>
                            <?php echo e($prov->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Choose Date</label>
                <input type="date" 
                       name="date" 
                       value="<?php echo e($selectedDate); ?>"
                       class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
            </div>

            <div class="flex items-end">
                <button type="submit" 
                        class="w-full px-6 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    <i class="ph ph-magnifying-glass mr-2"></i>View Slots
                </button>
            </div>
        </div>
    </form>

    
    <?php if($provider && count($slots)): ?>
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
            
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="font-bold text-gray-900">Time Slots for <span class="text-blue-600"><?php echo e($provider->name); ?></span></h2>
                <p class="text-sm text-gray-600 mt-1"><?php echo e(\Carbon\Carbon::parse($selectedDate)->format('l, M j, Y')); ?></p>
            </div>

            
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    <?php $__currentLoopData = $slots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="rounded-xl border-2 p-4 text-center transition
                            <?php echo e($slot['status'] === 'blocked' ? 'bg-red-50 border-red-200' :
                               ($slot['status'] === 'fully_booked' ? 'bg-gray-50 border-gray-200' : 'bg-white border-gray-200 hover:border-blue-300')); ?>">
                            
                            <h3 class="font-bold text-lg mb-2 <?php echo e($slot['status'] === 'blocked' ? 'text-red-600' : 'text-gray-900'); ?>">
                                <?php echo e($slot['formatted']); ?>

                            </h3>
                            
                            <div class="mb-3">
                                <?php if($slot['status'] === 'blocked'): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                        <i class="ph ph-lock mr-1"></i>Blocked
                                    </span>
                                <?php elseif($slot['status'] === 'fully_booked'): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-700">
                                        <i class="ph ph-users-three mr-1"></i>Fully Booked
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                        <i class="ph ph-check-circle mr-1"></i><?php echo e(3 - $slot['count']); ?> spot(s)
                                    </span>
                                <?php endif; ?>
                            </div>

                            
                            <?php if($slot['status'] === 'blocked'): ?>
                                <form method="POST" action="<?php echo e(route('admin.slots.unblock')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="provider_id" value="<?php echo e($provider->id); ?>">
                                    <input type="hidden" name="date" value="<?php echo e($selectedDate); ?>">
                                    <input type="hidden" name="time" value="<?php echo e($slot['time']); ?>">
                                    <button type="submit" class="w-full px-3 py-1.5 bg-red-600 text-white text-xs font-semibold rounded-lg hover:bg-red-700 transition">
                                        <i class="ph ph-lock-open mr-1"></i>Unblock
                                    </button>
                                </form>
                            <?php elseif($slot['status'] !== 'fully_booked'): ?>
                                <form method="POST" action="<?php echo e(route('admin.slots.block')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="provider_id" value="<?php echo e($provider->id); ?>">
                                    <input type="hidden" name="date" value="<?php echo e($selectedDate); ?>">
                                    <input type="hidden" name="time" value="<?php echo e($slot['time']); ?>">
                                    <button type="submit" class="w-full px-3 py-1.5 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-lg hover:bg-yellow-200 transition">
                                        <i class="ph ph-prohibit mr-1"></i>Block
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    <?php elseif($provider): ?>
        <div class="bg-yellow-50 border-2 border-yellow-200 rounded-xl p-6 text-center">
            <i class="ph ph-warning text-5xl text-yellow-500 mb-3"></i>
            <p class="text-yellow-900 font-semibold">No working hours set for <strong><?php echo e($provider->name); ?></strong></p>
            <p class="text-yellow-700 text-sm mt-1">This day is marked as off/holiday or working hours are not configured</p>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\admin\slots\index.blade.php ENDPATH**/ ?>