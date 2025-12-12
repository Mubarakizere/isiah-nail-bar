<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Dashboard') — Isaiah Nail Bar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Favicons --}}
    <link rel="icon" href="{{ asset('storage/favicon.ico') }}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    {{-- Tailwind CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Phosphor Icons --}}
    <link href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css" rel="stylesheet">

    {{-- Google Fonts - Inter & Playfair Display --}}
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
    @stack('styles')
</head>
<body class="h-full overflow-hidden font-sans antialiased bg-gray-50 text-gray-900" x-data="{ sidebarOpen: false }">
    
    <div class="flex h-full">
        
        {{-- Luxury Dark Sidebar --}}
        <aside class="hidden lg:flex lg:flex-shrink-0">
            <div class="flex flex-col w-72 bg-gray-900 text-white border-r border-gray-800">
                {{-- Logo Area --}}
                <div class="h-20 flex items-center px-8 border-b border-gray-800">
                    <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-400 to-rose-600 flex items-center justify-center text-white shadow-lg shadow-rose-900/20 group-hover:scale-105 transition-transform duration-300">
                            <span class="font-serif font-bold text-xl">I</span>
                        </div>
                        <div>
                            <span class="block font-serif text-lg font-bold tracking-wide text-white">Isaiah</span>
                            <span class="block text-xs text-gray-400 tracking-widest uppercase">Nail Bar</span>
                        </div>
                    </a>
                </div>

                {{-- Navigation --}}
                <nav class="flex-1 px-4 py-8 space-y-1 overflow-y-auto custom-scrollbar">
                    @include('partials.sidebar-nav')
                </nav>

                {{-- User Profile Footer --}}
                <div class="p-4 border-t border-gray-800 bg-gray-900/50">
                    <div class="flex items-center gap-4 p-2 rounded-xl hover:bg-gray-800 transition-colors cursor-pointer">
                        <div class="relative">
                            @if(auth()->user()->photo)
                                <img src="{{ asset('storage/' . auth()->user()->photo) }}" class="w-10 h-10 rounded-full object-cover ring-2 ring-rose-500/50">
                            @else
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-700 to-gray-800 flex items-center justify-center ring-2 ring-gray-700">
                                    <span class="font-bold text-gray-300">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                </div>
                            @endif
                            <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-gray-900 rounded-full"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ ucfirst(auth()->user()->getRoleNames()->first()) }} Account</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-400 hover:text-white transition-colors">
                                <i class="ph ph-sign-out text-xl"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main Content Wrapper --}}
        <div class="flex-1 flex flex-col min-w-0 bg-gray-50/50">
            
            {{-- Top Glass Header --}}
            <header class="h-20 glass-header flex items-center justify-between px-6 lg:px-8 z-20 sticky top-0">
                <div class="flex items-center gap-4">
                    {{-- Mobile Menu Trigger --}}
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                        <i class="ph ph-list text-2xl"></i>
                    </button>

                    <div>
                        <h1 class="text-xl lg:text-2xl font-serif font-bold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                        @hasSection('page-subtitle')
                            <p class="text-sm text-gray-500 hidden sm:block">@yield('page-subtitle')</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-3 lg:gap-6">
                    {{-- Quick Actions --}}
                    <div class="flex items-center gap-2">
                        <button class="p-2 text-gray-500 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all relative group">
                            <i class="ph ph-bell text-xl"></i>
                            <span class="absolute top-2 right-2 w-2 h-2 bg-rose-500 rounded-full border-2 border-white"></span>
                        </button>
                        
                        {{-- Contextual Help --}}
                         <button class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all">
                            <i class="ph ph-question text-xl"></i>
                        </button>
                    </div>

                    {{-- Date Widget --}}
                    <div class="hidden md:flex items-center gap-3 px-4 py-2 bg-white border border-gray-200 rounded-full shadow-sm">
                        <i class="ph ph-calendar-blank text-rose-500"></i>
                        <span class="text-sm font-medium text-gray-700">{{ now()->format('l, M j') }}</span>
                    </div>
                </div>
            </header>

            {{-- Main Scroll Area --}}
            <main class="flex-1 overflow-y-auto p-4 lg:p-8 custom-scrollbar">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Mobile Sidebar Overlay --}}
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

    {{-- Mobile Sidebar --}}
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
             @include('partials.sidebar-nav')
         </nav>

         {{-- Mobile Menu Footer --}}
         <div class="p-4 border-t border-gray-800 bg-gray-900">
             <a href="{{ url('/') }}" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-gray-800 rounded-xl transition-colors mb-2">
                 <i class="ph ph-arrow-left text-xl"></i>
                 <span class="font-medium">Back to Site</span>
             </a>

             <div class="flex items-center gap-4 px-4 py-3 rounded-xl bg-gray-800/50">
                 @if(auth()->user()->photo)
                    <img src="{{ asset('storage/' . auth()->user()->photo) }}" class="w-10 h-10 rounded-full object-cover">
                @else
                    <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center">
                        <span class="font-bold text-gray-300">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    </div>
                @endif
                 <div class="flex-1 min-w-0">
                     <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                     <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 flex items-center gap-1 mt-0.5">
                            Log Out
                        </button>
                    </form>
                 </div>
             </div>
         </div>
    </div>

    {{-- Global Toast Notifications --}}
    <x-toast-notification />

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

    @stack('scripts')
</body>
</html>
