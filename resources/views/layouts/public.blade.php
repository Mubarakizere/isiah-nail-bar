<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <title>@yield('title') | Isaiah Nail Bar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- SEO Meta --}}
    <meta name="description" content="@yield('meta_description', 'Isaiah Nail Bar – The Best Nail Salon in Rwanda. Luxury Manicure, Pedicure, and Nail Art Services in Kigali.')">
    <meta name="keywords" content="@yield('meta_keywords', 'Nail Salon Rwanda, Best Nails Kigali, Isaiah Nail Bar, Luxury Manicure Kigali, Gel Polish Rwanda, Acrylic Nails Kigali, Beauty Salon Gisementi')">
    <meta name="author" content="Isaiah Nail Bar">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Isaiah Nail Bar') | Best Nail Salon in Rwanda">
    <meta property="og:description" content="@yield('meta_description', 'Experience luxury nail care at Isaiah Nail Bar. Top-rated manicure and pedicure services in Kigali.')">
    <meta property="og:image" content="@yield('og_image', asset('storage/cover.jpg'))">
    <meta property="og:site_name" content="Isaiah Nail Bar">
    <meta property="og:locale" content="en_US">

    {{-- Twitter --}}
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="@yield('title', 'Isaiah Nail Bar') | Best Nail Salon in Rwanda">
    <meta property="twitter:description" content="@yield('meta_description', 'Experience luxury nail care at Isaiah Nail Bar. Top-rated manicure and pedicure services in Kigali.')">
    <meta property="twitter:image" content="@yield('og_image', asset('storage/cover.jpg'))">

    {{-- Favicons --}}
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    
    @stack('meta')

    {{-- Schema.org JSON-LD --}}
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "NailSalon",
      "name": "Isaiah Nail Bar",
      "image": "{{ asset('storage/logo.png') }}",
      "description": "Kigali's premier nail salon offering luxury manicure, pedicure, and nail art services.",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "KG 4 Roundabout",
        "addressLocality": "Gisementi, Kigali",
        "addressRegion": "Kigali City",
        "postalCode": "00000",
        "addressCountry": "RW"
      },
      "geo": {
        "@type": "GeoCoordinates",
        "latitude": -1.954736,
        "longitude": 30.108678
      },
      "url": "{{ url('/') }}",
      "telephone": "+250788421063",
      "priceRange": "$$",
      "openingHoursSpecification": [
        {
          "@type": "OpeningHoursSpecification",
          "dayOfWeek": [
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday"
          ],
          "opens": "08:00",
          "closes": "20:00"
        },
        {
          "@type": "OpeningHoursSpecification",
          "dayOfWeek": "Sunday",
          "opens": "10:00",
          "closes": "18:00"
        }
      ],
      "sameAs": [
        "https://www.instagram.com/isaiahnailbar",
        "https://www.facebook.com/isaiahnailbar"
      ]
    }
    </script>

    {{-- Tailwind CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Phosphor Icons --}}
    <link href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css" rel="stylesheet">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet">
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,600,700&display=swap" rel="stylesheet">

    <style>
        /* CustomScrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
    </style>

    @stack('styles')
</head>
<body class="min-h-screen bg-white font-sans antialiased text-gray-900 selection:bg-rose-100 selection:text-rose-900">

    {{-- Navigation --}}
    <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-100 transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                {{-- Logo --}}
                <div class="flex items-center">
                    <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                        <img src="{{ asset('storage/logo.png') }}" alt="Isaiah Nail Bar" class="h-10 w-auto group-hover:scale-105 transition-transform duration-300">
                        <span class="text-xl font-bold text-gray-900 tracking-tight font-serif hidden sm:block">Isaiah Nail Bar</span>
                    </a>
                </div>

                {{-- Desktop Navigation --}}
                <div class="hidden lg:flex lg:items-center lg:gap-10">
                    <a href="{{ url('/') }}" class="text-sm font-medium {{ request()->is('/') ? 'text-rose-600' : 'text-gray-600 hover:text-rose-600' }} transition-colors">
                        Home
                    </a>
                    <a href="{{ url('/services') }}" class="text-sm font-medium {{ request()->is('services') ? 'text-rose-600' : 'text-gray-600 hover:text-rose-600' }} transition-colors">
                        Services
                    </a>
                    <a href="{{ url('/gallery') }}" class="text-sm font-medium {{ request()->is('gallery') ? 'text-rose-600' : 'text-gray-600 hover:text-rose-600' }} transition-colors">
                        Gallery
                    </a>
                    <a href="{{ route('about') }}" class="text-sm font-medium {{ request()->is('about') ? 'text-rose-600' : 'text-gray-600 hover:text-rose-600' }} transition-colors">
                        About
                    </a>
                    <a href="{{ url('/contact') }}" class="text-sm font-medium {{ request()->is('contact') ? 'text-rose-600' : 'text-gray-600 hover:text-rose-600' }} transition-colors">
                        Contact
                    </a>
                    <a href="{{ url('/providers') }}" class="text-sm font-medium {{ request()->is('providers') ? 'text-rose-600' : 'text-gray-600 hover:text-rose-600' }} transition-colors">
                        Team
                    </a>

                    @auth
                        <div class="relative group">
                            <button class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-rose-600 transition-colors">
                                <span>Dashboard</span>
                                <i class="ph ph-caret-down text-xs"></i>
                            </button>
                            <div class="absolute right-0 mt-4 w-56 bg-white rounded-2xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform origin-top-right scale-95 group-hover:scale-100">
                                <div class="p-2">
                                    @role('admin')
                                        <a href="{{ route('dashboard.admin') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-rose-50 hover:text-rose-700 rounded-xl transition-colors">
                                            Admin Panel
                                        </a>
                                    @endrole
                                    @role('provider')
                                        <a href="{{ route('dashboard.provider') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-rose-50 hover:text-rose-700 rounded-xl transition-colors">
                                            Provider Panel
                                        </a>
                                    @endrole
                                    @role('customer')
                                        <a href="{{ route('dashboard.customer') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-rose-50 hover:text-rose-700 rounded-xl transition-colors">
                                            My Bookings
                                        </a>
                                    @endrole
                                    <hr class="my-2 border-gray-100">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-4">
                            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-rose-600 transition-colors">
                                Login
                            </a>
                            <span class="text-gray-300 h-4 w-px bg-gray-300"></span>
                            <a href="{{ route('register') }}" class="text-sm font-medium text-gray-700 hover:text-rose-600 transition-colors">
                                Register
                            </a>
                        </div>
                    @endauth

                    <a href="{{ route('booking.step1') }}" class="inline-flex items-center px-6 py-2.5 bg-gray-900 text-white text-sm font-medium rounded-full hover:bg-rose-600 transition-all duration-300 shadow-md hover:shadow-lg hover:-translate-y-0.5">
                        Book Now
                    </a>
                </div>

                {{-- Mobile Menu Button --}}
                <button id="mobile-menu-btn" type="button" class="lg:hidden inline-flex items-center justify-center p-2 rounded-lg text-gray-600 hover:bg-gray-50 focus:outline-none">
                    <i class="ph ph-list text-2xl"></i>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div id="mobile-menu" class="hidden lg:hidden border-t border-gray-100 bg-white absolute w-full left-0 shadow-lg">
            <div class="px-4 py-6 space-y-3">
                <a href="{{ url('/') }}" class="block px-4 py-3 text-base font-medium {{ request()->is('/') ? 'text-rose-600 bg-rose-50' : 'text-gray-600' }} rounded-xl hover:bg-gray-50">
                    Home
                </a>
                <a href="{{ url('/services') }}" class="block px-4 py-3 text-base font-medium {{ request()->is('services') ? 'text-rose-600 bg-rose-50' : 'text-gray-600' }} rounded-xl hover:bg-gray-50">
                    Services
                </a>
                <a href="{{ url('/gallery') }}" class="block px-4 py-3 text-base font-medium {{ request()->is('gallery') ? 'text-rose-600 bg-rose-50' : 'text-gray-600' }} rounded-xl hover:bg-gray-50">
                    Gallery
                </a>
                <a href="{{ route('about') }}" class="block px-4 py-3 text-base font-medium {{ request()->is('about') ? 'text-rose-600 bg-rose-50' : 'text-gray-600' }} rounded-xl hover:bg-gray-50">
                    About
                </a>
                <a href="{{ url('/contact') }}" class="block px-4 py-3 text-base font-medium {{ request()->is('contact') ? 'text-rose-600 bg-rose-50' : 'text-gray-600' }} rounded-xl hover:bg-gray-50">
                    Contact
                </a>
                <a href="{{ url('/providers') }}" class="block px-4 py-3 text-base font-medium {{ request()->is('providers') ? 'text-rose-600 bg-rose-50' : 'text-gray-600' }} rounded-xl hover:bg-gray-50">
                    Team
                </a>

                @auth
                    <hr class="my-3 border-gray-100">
                    @role('admin')
                        <a href="{{ route('dashboard.admin') }}" class="block px-4 py-3 text-base font-medium text-gray-600 rounded-xl hover:bg-gray-50">
                            Admin Panel
                        </a>
                    @endrole
                    @role('provider')
                        <a href="{{ route('dashboard.provider') }}" class="block px-4 py-3 text-base font-medium text-gray-600 rounded-xl hover:bg-gray-50">
                            Provider Panel
                        </a>
                    @endrole
                    @role('customer')
                        <a href="{{ route('dashboard.customer') }}" class="block px-4 py-3 text-base font-medium text-gray-600 rounded-xl hover:bg-gray-50">
                            My Bookings
                        </a>
                    @endrole
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="w-full text-left px-4 py-3 text-base font-medium text-red-600 rounded-xl hover:bg-gray-50">
                            Logout
                        </button>
                    </form>
                @else
                    <hr class="my-3 border-gray-100">
                    <a href="{{ route('login') }}" class="block px-4 py-3 text-base font-medium text-gray-600 rounded-xl hover:bg-gray-50">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="block px-4 py-3 text-base font-medium text-gray-600 rounded-xl hover:bg-gray-50">
                        Register
                    </a>
                @endauth

                <div class="pt-4">
                    <a href="{{ route('booking.step1') }}" class="block w-full text-center px-6 py-4 bg-gray-900 text-white text-base font-medium rounded-xl hover:bg-gray-800 transition shadow-lg">
                        Book Now
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Hero Section (if provided) --}}
    @yield('hero')

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-950 text-gray-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
                {{-- About --}}
                <div class="col-span-1 lg:col-span-2">
                    <div class="flex items-center gap-3 mb-6">
                        <img src="{{ asset('storage/logo.png') }}" alt="Logo" class="h-10 w-auto opacity-90 grayscale hover:grayscale-0 transition-all duration-300">
                        <span class="text-2xl font-bold text-white font-serif tracking-tight">Isaiah Nail Bar</span>
                    </div>
                    <p class="text-base leading-relaxed text-gray-400 mb-6 max-w-md">
                        Experience the epitome of luxury nail care in Kigali. We blend artistic excellence with premium products to ensure your hands and feet look their absolute best.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-white hover:bg-rose-600 transition-all duration-300">
                            <i class="ph ph-facebook-logo text-xl"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-white hover:bg-rose-600 transition-all duration-300">
                            <i class="ph ph-instagram-logo text-xl"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-white hover:bg-rose-600 transition-all duration-300">
                            <i class="ph ph-twitter-logo text-xl"></i>
                        </a>
                    </div>
                </div>

                {{-- Quick Links --}}
                <div>
                    <h3 class="text-white font-semibold text-lg mb-6 tracking-tight">Quick Links</h3>
                    <ul class="space-y-4">
                        <li><a href="{{ url('/') }}" class="text-sm hover:text-rose-400 transition-colors">Home</a></li>
                        <li><a href="{{ url('/services') }}" class="text-sm hover:text-rose-400 transition-colors">Our Services</a></li>
                        <li><a href="{{ url('/gallery') }}" class="text-sm hover:text-rose-400 transition-colors">Gallery</a></li>
                        <li><a href="{{ route('about') }}" class="text-sm hover:text-rose-400 transition-colors">About Us</a></li>
                        <li><a href="{{ url('/contact') }}" class="text-sm hover:text-rose-400 transition-colors">Contact</a></li>
                    </ul>
                </div>

                {{-- Contact Info --}}
                <div>
                    <h3 class="text-white font-semibold text-lg mb-6 tracking-tight">Visit Us</h3>
                    <ul class="space-y-6 text-sm">
                        <li class="flex items-start gap-3">
                            <i class="ph ph-map-pin text-xl text-rose-500 flex-shrink-0 mt-0.5"></i>
                            <span class="leading-relaxed">KG 4 Roundabout<br>Gisementi, Kigali</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="ph ph-phone text-xl text-rose-500 flex-shrink-0"></i>
                            <a href="tel:0788421063" class="hover:text-white transition-colors">0788 421 063</a>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="ph ph-envelope text-xl text-rose-500 flex-shrink-0"></i>
                            <a href="mailto:info@isaiahnailbar.com" class="hover:text-white transition-colors">info@isaiahnailbar.com</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-16 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-gray-500">
                    &copy; {{ date('Y') }} Isaiah Nail Bar. All rights reserved.
                </p>
                <div class="flex gap-6 text-sm text-gray-500">
                    <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                    <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    {{-- Back to Top Button --}}
    <button id="back-to-top" class="hidden fixed bottom-8 right-8 z-40 p-4 bg-gray-900 text-white rounded-full shadow-lg hover:bg-rose-600 hover:-translate-y-1 transition-all duration-300">
        <i class="ph ph-arrow-up text-xl"></i>
    </button>

    {{-- Toast Notifications --}}
    <div id="toast-container" class="fixed top-24 right-4 z-50 space-y-4">
        @if(session('success'))
            <div class="max-w-sm bg-white shadow-xl rounded-xl border border-green-100 pointer-events-auto overflow-hidden animate-slide-in-right">
                <div class="p-4 flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-green-50 rounded-full flex items-center justify-center">
                        <i class="ph ph-check-circle text-xl text-green-600"></i>
                    </div>
                    <div class="flex-1 pt-1">
                        <p class="text-sm font-semibold text-gray-900">Success</p>
                        <p class="text-sm text-gray-600 mt-1">{{ session('success') }}</p>
                    </div>
                    <button onclick="this.closest('.max-w-sm').remove()" class="text-gray-400 hover:text-gray-500">
                        <i class="ph ph-x"></i>
                    </button>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="max-w-sm bg-white shadow-xl rounded-xl border border-red-100 pointer-events-auto overflow-hidden animate-slide-in-right">
                <div class="p-4 flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-red-50 rounded-full flex items-center justify-center">
                        <i class="ph ph-warning-circle text-xl text-red-600"></i>
                    </div>
                    <div class="flex-1 pt-1">
                        <p class="text-sm font-semibold text-gray-900">Error</p>
                        <p class="text-sm text-gray-600 mt-1">{{ session('error') }}</p>
                    </div>
                    <button onclick="this.closest('.max-w-sm').remove()" class="text-gray-400 hover:text-gray-500">
                        <i class="ph ph-x"></i>
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- Scripts --}}
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
        
        // Use Intersection Observer for navbar scroll effect
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 0) {
                navbar.classList.add('shadow-sm');
                navbar.classList.replace('bg-white/80', 'bg-white/95');
            } else {
                navbar.classList.remove('shadow-sm');
                navbar.classList.replace('bg-white/95', 'bg-white/80');
            }
        });

        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        
        mobileMenuBtn?.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
            const icon = mobileMenuBtn.querySelector('i');
            if (mobileMenu.classList.contains('hidden')) {
                icon.classList.replace('ph-x', 'ph-list');
            } else {
                icon.classList.replace('ph-list', 'ph-x');
            }
        });

        // Back to top button
        const backToTop = document.getElementById('back-to-top');
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 400) {
                backToTop.classList.remove('hidden');
                backToTop.classList.add('flex');
            } else {
                backToTop.classList.add('hidden');
                backToTop.classList.remove('flex');
            }
        });

        backToTop?.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Auto-dismiss toasts
        setTimeout(() => {
            document.querySelectorAll('#toast-container > div').forEach(toast => {
                toast.style.transition = 'all 0.5s ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 500);
            });
        }, 5000);
    </script>
    
    <style>
        @keyframes slide-in-right {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .animate-slide-in-right {
            animation: slide-in-right 0.3s ease-out forwards;
        }
    </style>

    @stack('scripts')
</body>
</html>