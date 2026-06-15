<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?> — Isaiah Nail Bar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    
    <link rel="icon" href="<?php echo e(asset('storage/favicon.ico')); ?>" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo e(asset('apple-touch-icon.png')); ?>">
    <link rel="manifest" href="<?php echo e(asset('site.webmanifest')); ?>">

    
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    
    
    <link href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css" rel="stylesheet">

    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700|playfair-display:400,500,600,700&display=swap" rel="stylesheet">

    <style>
        [x-cloak] { display: none !important; }
        .glass-header {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(229, 231, 235, 0.5);
        }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="h-full overflow-hidden font-sans antialiased bg-gray-50 text-gray-900" x-data="{ sidebarOpen: false }">
    
    <div class="flex h-full">
        
        
        <aside class="hidden lg:flex lg:flex-shrink-0">
            <div class="flex flex-col w-72 bg-gray-900 text-white border-r border-gray-800">
                
                <div class="h-20 flex items-center px-8 border-b border-gray-800">
                    <a href="<?php echo e(url('/')); ?>" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-400 to-rose-600 flex items-center justify-center text-white shadow-lg shadow-rose-900/20 group-hover:scale-105 transition-transform duration-300">
                            <span class="font-serif font-bold text-xl">I</span>
                        </div>
                        <div>
                            <span class="block font-serif text-lg font-bold tracking-wide text-white">Isaiah</span>
                            <span class="block text-xs text-gray-400 tracking-widest uppercase">Nail Bar</span>
                        </div>
                    </a>
                </div>

                
                <nav class="flex-1 px-4 py-8 space-y-1">
                    <?php echo $__env->make('partials.sidebar-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </nav>

                
                <div class="p-4 border-t border-gray-800 bg-gray-900/50">
                    <div class="flex items-center gap-4 p-2 rounded-xl hover:bg-gray-800 transition-colors cursor-pointer">
                        <div class="relative">
                            <?php if(auth()->user()->photo): ?>
                                <img src="<?php echo e(asset('storage/' . auth()->user()->photo)); ?>" class="w-10 h-10 rounded-full object-cover ring-2 ring-rose-500/50">
                            <?php else: ?>
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-700 to-gray-800 flex items-center justify-center ring-2 ring-gray-700">
                                    <span class="font-bold text-gray-300"><?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?></span>
                                </div>
                            <?php endif; ?>
                            <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-gray-900 rounded-full"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-white truncate"><?php echo e(auth()->user()->name); ?></p>
                            <p class="text-xs text-gray-400 truncate"><?php echo e(ucfirst(auth()->user()->getRoleNames()->first())); ?> Account</p>
                        </div>
                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="text-gray-400 hover:text-white transition-colors">
                                <i class="ph ph-sign-out text-xl"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        
        <div class="flex-1 flex flex-col min-w-0 bg-gray-50/50">
            
            
            <header class="h-20 glass-header flex items-center justify-between px-6 lg:px-8 z-20 sticky top-0">
                <div class="flex items-center gap-4">
                    
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                        <i class="ph ph-list text-2xl"></i>
                    </button>

                    <div>
                        <h1 class="text-xl lg:text-2xl font-serif font-bold text-gray-900"><?php echo $__env->yieldContent('page-title', 'Dashboard'); ?></h1>
                        <?php if (! empty(trim($__env->yieldContent('page-subtitle')))): ?>
                            <p class="text-sm text-gray-500 hidden sm:block"><?php echo $__env->yieldContent('page-subtitle'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex items-center gap-3 lg:gap-6">
                    
                    <div class="flex items-center gap-2">
                        <div x-data="{ 
                            notifications: [],
                            unreadCount: 0,
                            open: false,
                            async fetchNotifications() {
                                try {
                                    const res = await fetch('<?php echo e(route('admin.notifications.unread')); ?>');
                                    const data = await res.json();
                                    this.unreadCount = data.unread_count;
                                    this.notifications = data.notifications;
                                    
                                    if(data.unread_count > 0 && data.unread_count > (localStorage.getItem('last_unread_count') || 0)) {
                                          // Play sound or show toast if count increased
                                    }
                                    localStorage.setItem('last_unread_count', data.unread_count);
                                } catch (e) {
                                    console.error('Failed to fetch notifications');
                                }
                            },
                            async markAsRead(id) {
                                await fetch(`/api/notifications/${id}/read`, {
                                    method: 'POST',
                                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                                });
                                this.fetchNotifications();
                            }
                        }" 
                        x-init="fetchNotifications(); setInterval(() => fetchNotifications(), 30000)"
                        class="relative">
                            
                            <button @click="open = !open" @click.away="open = false" class="p-2 text-gray-500 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all relative group">
                                <i class="ph ph-bell text-xl"></i>
                                <div x-show="unreadCount > 0" x-cloak class="absolute top-2 right-2 w-2 h-2 bg-rose-500 rounded-full border-2 border-white"></div>
                            </button>

                            <!-- Dropdown -->
                            <div x-show="open" x-cloak 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                                
                                <div class="px-4 py-2 border-b border-gray-100 flex justify-between items-center">
                                    <span class="font-medium text-gray-700">Notifications</span>
                                    <a href="<?php echo e(route('admin.notifications.markAllRead')); ?>" class="text-xs text-rose-500 hover:text-rose-600">Mark all read</a>
                                </div>

                                <div class="max_h-64 overflow-y-auto">
                                    <template x-for="notification in notifications" :key="notification.id">
                                        <div @click="markAsRead(notification.id); window.location.href='/dashboard/admin/bookings'" class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-50 last:border-0">
                                            <p class="text-sm text-gray-800" x-text="notification.data.message"></p>
                                            <p class="text-xs text-gray-400 mt-1" x-text="notification.created_at"></p>
                                        </div>
                                    </template>
                                    <div x-show="notifications.length === 0" class="px-4 py-4 text-center text-gray-500 text-sm">
                                        No new notifications
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        
                         <button class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all">
                            <i class="ph ph-question text-xl"></i>
                        </button>
                    </div>

                    
                    <div class="hidden md:flex items-center gap-3 px-4 py-2 bg-white border border-gray-200 rounded-full shadow-sm">
                        <i class="ph ph-calendar-blank text-rose-500"></i>
                        <span class="text-sm font-medium text-gray-700"><?php echo e(now()->format('l, M j')); ?></span>
                    </div>
                </div>
            </header>

            
            <main class="flex-1 overflow-y-auto p-4 lg:p-8 custom-scrollbar">
                <?php echo $__env->yieldContent('content'); ?>
            </main>
        </div>
    </div>

    
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/80 z-40 lg:hidden glass-backdrop"
         @click="sidebarOpen = false"
         x-cloak>
    </div>

    
    <div x-show="sidebarOpen"
         x-transition:enter="transition ease-in-out duration-300 transform"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in-out duration-300 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed inset-y-0 left-0 z-50 w-72 bg-gray-900 text-white shadow-2xl lg:hidden flex flex-col"
         x-cloak>
         
         <div class="h-20 flex items-center justify-between px-6 border-b border-gray-800">
             <span class="font-serif text-xl font-bold">Menu</span>
             <button @click="sidebarOpen = false" class="text-gray-400 hover:text-white">
                 <i class="ph ph-x text-2xl"></i>
             </button>
         </div>

         <nav class="flex-1 px-4 py-6 overflow-y-auto">
             <?php echo $__env->make('partials.sidebar-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
         </nav>

         
         <div class="p-4 border-t border-gray-800 bg-gray-900">
             <a href="<?php echo e(url('/')); ?>" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-xl transition-colors mb-2">
                 <i class="ph ph-arrow-left text-xl"></i>
                 <span class="font-medium">Back to Site</span>
             </a>

             <div class="flex items-center gap-4 px-4 py-3 rounded-xl bg-gray-800/50">
                 <?php if(auth()->user()->photo): ?>
                    <img src="<?php echo e(asset('storage/' . auth()->user()->photo)); ?>" class="w-10 h-10 rounded-full object-cover">
                <?php else: ?>
                    <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center">
                        <span class="font-bold text-gray-300"><?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?></span>
                    </div>
                <?php endif; ?>
                 <div class="flex-1 min-w-0">
                     <p class="text-sm font-medium text-white truncate"><?php echo e(auth()->user()->name); ?></p>
                     <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 flex items-center gap-1 mt-0.5">
                            Log Out
                        </button>
                    </form>
                 </div>
             </div>
         </div>
    </div>

    
    <?php if (isset($component)) { $__componentOriginalf98a32c06d8462f5513d0fb3554f9141 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf98a32c06d8462f5513d0fb3554f9141 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.toast-notification','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('toast-notification'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf98a32c06d8462f5513d0fb3554f9141)): ?>
<?php $attributes = $__attributesOriginalf98a32c06d8462f5513d0fb3554f9141; ?>
<?php unset($__attributesOriginalf98a32c06d8462f5513d0fb3554f9141); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf98a32c06d8462f5513d0fb3554f9141)): ?>
<?php $component = $__componentOriginalf98a32c06d8462f5513d0fb3554f9141; ?>
<?php unset($__componentOriginalf98a32c06d8462f5513d0fb3554f9141); ?>
<?php endif; ?>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(registration) {
                    console.log('ServiceWorker registration successful with scope: ', registration.scope);
                }, function(err) {
                    console.log('ServiceWorker registration failed: ', err);
                });
            });
        }
    </script>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\layouts\dashboard.blade.php ENDPATH**/ ?>