<?php $__env->startSection('title', 'Login'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">
    
    
    <div class="hidden lg:block relative overflow-hidden bg-gray-900">
        <div class="absolute inset-0 opacity-60">
            <img src="<?php echo e(asset('storage/banner.jpg')); ?>" alt="Login Background" class="w-full h-full object-cover">
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-transparent"></div>
        
        <div class="absolute bottom-0 left-0 right-0 p-12 text-white z-10">
            <h2 class="text-4xl font-serif font-bold mb-4 leading-tight">"Self-care is not a luxury. It's a necessity."</h2>
            <div class="flex items-center gap-4">
                <div class="h-px bg-rose-500 w-12"></div>
                <p class="text-rose-200 uppercase tracking-widest text-sm font-medium">Isaiah Nail Bar</p>
            </div>
        </div>
    </div>

    
    <div class="flex items-center justify-center p-8 sm:p-12 lg:p-16 bg-white">
        <div class="w-full max-w-md space-y-8">
            
            
            <div class="text-center">
                <a href="<?php echo e(url('/')); ?>" class="inline-block mb-6">
                    <img src="<?php echo e(asset('storage/logo.png')); ?>" alt="Logo" class="h-16 w-auto mx-auto object-contain">
                </a>
                <h2 class="text-3xl font-serif font-bold text-gray-900">Welcome Back</h2>
                <p class="mt-2 text-gray-500">Sign in to access your appointments</p>
            </div>

            <?php if(session('status')): ?>
                <div class="bg-green-50 text-green-700 p-4 rounded-lg text-sm border border-green-200 flex items-center gap-2">
                    <i class="ph ph-check-circle text-lg"></i>
                    <?php echo e(session('status')); ?>

                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('login')); ?>" class="space-y-6">
                <?php echo csrf_field(); ?>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ph ph-envelope text-gray-400 text-lg"></i>
                        </div>
                        <input id="email" name="email" type="email" autocomplete="email" required 
                               value="<?php echo e(old('email')); ?>"
                               class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-colors bg-gray-50 focus:bg-white placeholder-gray-400"
                               placeholder="you@example.com">
                    </div>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-rose-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <?php if(Route::has('password.request')): ?>
                            <a href="<?php echo e(route('password.request')); ?>" class="text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors">
                                Forgot password?
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ph ph-lock-key text-gray-400 text-lg"></i>
                        </div>
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                               class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-colors bg-gray-50 focus:bg-white placeholder-gray-400"
                               placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center">
                    <input id="remember_me" name="remember" type="checkbox" 
                           class="h-4 w-4 text-gray-900 focus:ring-gray-900 border-gray-300 rounded cursor-pointer">
                    <label for="remember_me" class="ml-2 block text-sm text-gray-600 cursor-pointer">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gray-900 hover:bg-rose-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition-all duration-300 transform hover:-translate-y-0.5">
                    Sign in
                    <i class="ph ph-arrow-right"></i>
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500">
                    Don't have an account? 
                    <a href="<?php echo e(route('register')); ?>" class="font-bold text-gray-900 hover:text-rose-600 transition-colors">
                        Create one
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views/auth/login.blade.php ENDPATH**/ ?>