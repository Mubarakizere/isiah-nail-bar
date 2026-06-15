

<?php $__env->startSection('title', 'Meet Our Team'); ?>

<?php $__env->startSection('content'); ?>


<div class="relative bg-gray-900 py-24 overflow-hidden">
    <div class="absolute inset-0 opacity-40">
        <img src="<?php echo e(asset('storage/banner.jpg')); ?>" alt="Team Banner" class="w-full h-full object-cover">
    </div>
    <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/60 to-transparent"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center pt-12">
        <span class="text-rose-400 font-medium tracking-widest text-sm uppercase mb-3 block animate-fade-in-up">The Artists</span>
        <h1 class="text-4xl md:text-6xl font-serif text-white mb-6 animate-fade-in-up delay-100">Meet the Team</h1>
        <p class="text-xl text-gray-300 font-light max-w-2xl mx-auto animate-fade-in-up delay-200">
            Dedicated professionals committed to delivering perfection in every stroke.
        </p>
    </div>
</div>


<section class="py-20 bg-white min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <?php if($providers->count() > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-12">
                <?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="group">
                        <div class="relative aspect-[3/4] mb-6 overflow-hidden rounded-lg bg-gray-100">
                            <img src="<?php echo e($provider->photo ? asset('storage/' . $provider->photo) : 'https://via.placeholder.com/400x500/f3f4f6/9ca3af?text=' . urlencode($provider->name)); ?>"
                                 alt="<?php echo e($provider->name); ?>"
                                 class="w-full h-full object-cover transition-all duration-700">
                            
                            
                            <div class="absolute inset-x-0 bottom-0 p-6 bg-gradient-to-t from-gray-900/90 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 translate-y-4 group-hover:translate-y-0 transform">
                                <?php if($provider->status === 'active'): ?>
                                    <span class="inline-block px-2 py-1 bg-green-500/20 text-green-300 text-xs font-medium rounded mb-2 border border-green-500/30">Available for Booking</span>
                                <?php endif; ?>
                                <p class="text-gray-300 text-sm line-clamp-2 mb-4"><?php echo e($provider->bio ?? 'Passionate nail artist dedicated to excellence.'); ?></p>
                                <a href="<?php echo e(route('booking.step2')); ?>" class="block w-full py-3 bg-white text-gray-900 text-center font-medium rounded-full hover:bg-rose-50 transition-colors">
                                    Book Now
                                </a>
                            </div>
                        </div>

                        <div class="text-center">
                            <h3 class="text-2xl font-serif text-gray-900 mb-1"><?php echo e($provider->name); ?></h3>
                            <p class="text-rose-600 font-medium text-sm tracking-widest uppercase">Nail Specialist</p>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div class="text-center py-24">
                <i class="ph ph-users-three text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-serif text-gray-900 mb-2">Our Team is Growing</h3>
                <p class="text-gray-500 mb-8">Check back soon to see our talented artists.</p>
                <a href="<?php echo e(url('/contact')); ?>" class="text-gray-900 underline underline-offset-4 hover:text-rose-600 transition-colors">
                    Join our team
                </a>
            </div>
        <?php endif; ?>

        
        <?php if(isset($teamMembers) && $teamMembers->count() > 0): ?>
            <div class="mt-24 mb-12 text-center">
                <span class="text-rose-400 font-medium tracking-widest text-sm uppercase mb-3 block">Support Staff</span>
                <h2 class="text-3xl font-serif text-gray-900">The Faces Behind the Service</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-12">
                <?php $__currentLoopData = $teamMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="group">
                        <div class="relative aspect-[3/4] mb-6 overflow-hidden rounded-lg bg-gray-100">
                            <img src="<?php echo e($member->photo ? asset('storage/' . $member->photo) : 'https://via.placeholder.com/400x500/f3f4f6/9ca3af?text=' . urlencode($member->name)); ?>"
                                width="400" height="500" loading="lazy"
                                alt="<?php echo e($member->name); ?>"
                                class="w-full h-full object-cover transition-all duration-700">
                            
                            
                            <div class="absolute inset-x-0 bottom-0 p-6 bg-gradient-to-t from-gray-900/90 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 translate-y-4 group-hover:translate-y-0 transform">
                                <p class="text-gray-300 text-sm line-clamp-3"><?php echo e($member->bio); ?></p>
                            </div>
                        </div>

                        <div class="text-center">
                            <h3 class="text-xl font-serif text-gray-900 mb-1"><?php echo e($member->name); ?></h3>
                            <p class="text-rose-600 font-medium text-sm tracking-widest uppercase"><?php echo e($member->role); ?></p>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

    </div>
</section>


<section class="py-24 bg-gray-50 border-t border-gray-100">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-serif text-gray-900 mb-6">Experience the Difference</h2>
        <div class="flex justify-center gap-4">
            <a href="<?php echo e(route('booking.step1')); ?>" class="px-8 py-4 bg-gray-900 text-white font-medium rounded-full hover:bg-rose-600 transition-all shadow-lg hover:shadow-xl hover:-translate-y-1">
                Book an Appointment
            </a>
        </div>
    </div>
</section>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\providers\index.blade.php ENDPATH**/ ?>