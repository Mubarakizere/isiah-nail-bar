<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title') | Isaiah Nail Bar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- SEO Meta --}}
    <meta name="description" content="Isaiah Nail Bar – Luxury Nails Booking in Kigali.">
    <meta name="keywords" content="Nail Salon Kigali, Isaiah Nail Bar, Beauty Services, Nails">
    <meta name="author" content="Isaiah Nail Bar">
    <meta property="og:title" content="Isaiah Nail Bar – Book Beauty Services in Kigali">
    <meta property="og:description" content="Luxury nails. Book online at Isaiah Nail Bar.">
    <meta property="og:image" content="{{ asset('storage/cover.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">

    {{-- Preload Critical Resources --}}
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" as="style">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style">

    {{-- CSS Libraries --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    
    {{-- Favicon --}}
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <style>
        :root {
            /* Modern Beauty Salon Color Palette */
            --primary-color: #d4717a;
            --secondary-color: #f8b5c1;
            --accent-color: #a64d79;
            --gold-accent: #d4af37;
            --dark-primary: #2c2c2c;
            --light-bg: #fefefe;
            --glass-pink: rgba(212, 113, 122, 0.1);
            --glass-white: rgba(255, 255, 255, 0.95);
            
            /* Gradients */
            --primary-gradient: linear-gradient(135deg, #d4717a 0%, #f8b5c1 100%);
            --luxury-gradient: linear-gradient(135deg, #a64d79 0%, #d4717a 50%, #d4af37 100%);
            --glass-gradient: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            
            /* Shadows */
            --shadow-soft: 0 4px 20px rgba(212, 113, 122, 0.15);
            --shadow-medium: 0 8px 30px rgba(212, 113, 122, 0.2);
            --shadow-strong: 0 15px 50px rgba(212, 113, 122, 0.25);
            --shadow-glow: 0 0 30px rgba(212, 113, 122, 0.3);
            
            --border-radius: 16px;
            --transition-fast: all 0.2s ease;
            --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
        }

        html { 
            scroll-behavior: smooth; 
            overflow-x: hidden;
        }
        
        body { 
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--dark-primary);
            background: linear-gradient(135deg, #fefefe 0%, #f8f9fa 100%);
            min-height: 100vh;
            font-display: swap;
        }

        /* Performance-optimized Navbar */
        .navbar {
            backdrop-filter: blur(15px);
            background: var(--glass-white) !important;
            border-bottom: 1px solid rgba(212, 113, 122, 0.1);
            box-shadow: var(--shadow-soft);
            transition: var(--transition-fast);
            padding: 0.75rem 0;
            will-change: transform;
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.98) !important;
            box-shadow: var(--shadow-medium);
            transform: translateZ(0);
        }

        .navbar-brand { 
            font-family: 'Playfair Display', serif;
            font-weight: 600; 
            color: var(--dark-primary) !important;
            transition: var(--transition-fast);
        }

        .navbar-brand:hover {
            color: var(--primary-color) !important;
            transform: translateY(-1px);
        }

        .navbar-nav {
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-nav .nav-link {
            font-weight: 500;
            color: var(--dark-primary) !important;
            transition: var(--transition-smooth);
            position: relative;
            padding: 0.5rem 0.75rem !important;
            border-radius: 12px;
            font-size: 0.95rem;
        }

        .navbar-nav .nav-link i {
            font-size: 0.85rem;
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--luxury-gradient);
            transition: var(--transition-smooth);
            transform: translateX(-50%);
            border-radius: 1px;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--primary-color) !important;
            background: var(--glass-pink);
            transform: translateY(-2px);
        }

        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link.active::after {
            width: 60%;
        }

      /* Premium Minimal Button */
.btn-primary {
    background: var(--primary-color) !important; /* clean solid color */
    border: none !important;
    border-radius: 10px !important; /* softer, less “bubbly” */
    font-weight: 600 !important;
    padding: 0.7rem 1.8rem !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.12) !important;
    transition: all 0.25s ease !important;
    color: #fff !important;
    position: relative;
    overflow: hidden;
}

/* Subtle hover effect */
.btn-primary:hover {
    background: color-mix(in srgb, var(--primary-color) 90%, #ffffff 10%) !important; /* slightly lighter shade */
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 16px rgba(0,0,0,0.18) !important;
}

/* Pressed/active state */
.btn-primary:active {
    transform: translateY(1px) scale(0.98) !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
}

/* Optional subtle focus ring */
.btn-primary:focus-visible {
    outline: 2px solid rgba(255,255,255,0.6) !important;
    outline-offset: 3px !important;
}


        /* Nail Polish Loader */
        #preloader {
    background: rgba(0, 0, 0, 0.6); /* semi-transparent overlay */
    z-index: 9999;
    position: fixed;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(4px); /* smooth blur effect */
}

.nail-loader {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
}

.nail-bottle {
    width: 60px;
    height: 100px;
    background: #ffffff; /* flat white */
    border-radius: 15px 15px 5px 5px;
    position: relative;
    box-shadow: 0 6px 18px rgba(0,0,0,0.15);
    animation: bottleFloat 3s ease-in-out infinite;
}

.nail-bottle::before {
    content: '';
    position: absolute;
    top: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 28px;
    height: 18px;
    background: #222; /* flat cap */
    border-radius: 8px 8px 0 0;
}

.nail-bottle::after {
    content: '';
    position: absolute;
    bottom: 12px;
    left: 50%;
    transform: translateX(-50%);
    width: 38px;
    height: 55px;
    background: var(--primary-color); /* single clean color */
    border-radius: 5px;
    animation: polish-level 2.2s ease-in-out infinite alternate;
}

.nail-drops {
    position: absolute;
    bottom: -40px;
    left: 50%;
    transform: translateX(-50%);
    width: 4px;
    height: 30px;
}

.drop {
    width: 8px;
    height: 8px;
    background: var(--primary-color);
    border-radius: 50%;
    position: absolute;
    animation: dropFall 1.8s ease-in infinite;
    opacity: 0.8;
}

.drop:nth-child(1) { animation-delay: 0s; }
.drop:nth-child(2) { animation-delay: 0.4s; }
.drop:nth-child(3) { animation-delay: 0.8s; }

.loader-text {
    color: #f5f5f5;
    font-weight: 400;
    font-size: 1rem;
    text-align: center;
    opacity: 0.8;
    letter-spacing: 0.5px;
}

@keyframes bottleFloat {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-12px); }
}

@keyframes polish-level {
    0% { height: 35px; }
    100% { height: 65px; }
}

@keyframes dropFall {
    0% { 
        top: -8px; 
        opacity: 1; 
        transform: scale(1);
    }
    100% { 
        top: 28px; 
        opacity: 0; 
        transform: scale(0.6);
    }
}

        /* Enhanced Back to Top */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 998;
            display: none;
            width: 55px;
            height: 55px;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            background: var(--luxury-gradient);
            color: #fff;
            transition: var(--transition-smooth);
            box-shadow: var(--shadow-medium);
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .back-to-top:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: var(--shadow-glow);
            color: #fff;
        }

        /* Google Translate - Optimized */
        #google_translate_element {
            position: fixed;
            top: 90px;
            right: 20px;
            z-index: 9999;
            background: var(--glass-white);
            backdrop-filter: blur(15px);
            border-radius: 12px;
            box-shadow: var(--shadow-soft);
            padding: 10px 14px;
            font-size: 14px;
            border: 1px solid var(--glass-pink);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition-fast);
        }

        #google_translate_element:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        #google_translate_element select {
            border: none;
            background: transparent;
            color: var(--dark-primary);
            cursor: pointer;
            font-size: 13px;
            outline: none;
            font-weight: 500;
        }

        .goog-logo-link, 
        .goog-te-gadget span {
            display: none !important;
        }

        body > .goog-te-banner-frame.skiptranslate {
            display: none !important;
        }

        /* Enhanced Dropdown */
        .dropdown-menu {
            border: none;
            box-shadow: var(--shadow-medium);
            border-radius: var(--border-radius);
            background: var(--glass-white);
            backdrop-filter: blur(15px);
            margin-top: 0.5rem;
            border: 1px solid var(--glass-pink);
        }

        .dropdown-item {
            padding: 0.75rem 1.25rem;
            font-weight: 500;
            transition: var(--transition-fast);
            border-radius: 10px;
            margin: 0.25rem 0.5rem;
            color: var(--dark-primary);
        }

        .dropdown-item:hover {
            background: var(--glass-pink);
            color: var(--primary-color);
            transform: translateX(5px);
        }

        /* Performance optimizations */
        .navbar-toggler {
            border: none;
            padding: 0.25rem 0.5rem;
            border-radius: 8px;
            transition: var(--transition-fast);
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 2px var(--primary-color);
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-gradient);
            border-radius: 3px;
        }

        /* Responsive optimizations */
        @media (min-width: 992px) and (max-width: 1199px) {
            .navbar-nav {
                gap: 0.3rem;
            }
            
            .navbar-nav .nav-link {
                padding: 0.4rem 0.6rem !important;
                font-size: 0.9rem;
            }
            
            .btn-primary {
                padding: 0.6rem 1.5rem !important;
                font-size: 0.9rem !important;
            }
        }

        @media (max-width: 991px) {
            .navbar-nav {
                margin-top: 1rem;
                gap: 0.25rem;
            }

            .navbar-nav .nav-link {
                margin: 0;
                text-align: left;
                padding: 0.75rem 1rem !important;
                border-radius: 8px;
                font-size: 1rem;
            }

            .btn-primary {
                width: 100%;
                margin-top: 1rem;
                justify-content: center;
            }

            .dropdown-menu {
                position: static !important;
                transform: none !important;
                box-shadow: none;
                border: 1px solid var(--glass-pink);
                margin-top: 0.5rem;
                margin-left: 1rem;
                background: rgba(254, 254, 254, 0.98);
            }

            .nail-bottle {
                width: 50px;
                height: 80px;
            }

            #google_translate_element {
                top: 80px;
                right: 15px;
                padding: 8px 12px;
            }
        }

        /* Critical CSS for faster loading */
        .navbar-brand img {
            height: 45px;
            transition: var(--transition-fast);
        }

        .navbar-brand:hover img {
            transform: scale(1.05);
        }

        /* Focus states for accessibility */
        .nav-link:focus,
        .btn:focus,
        .dropdown-item:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        /* Loading performance optimization */
        .navbar,
        .btn,
        .back-to-top {
            will-change: transform;
        }

        /* Reduce layout shifts */
        .navbar-nav .nav-link i {
            display: inline-block;
            width: 1rem;
            text-align: center;
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- Google Translate --}}
<div id="google_translate_element">
    <i class="fas fa-globe me-1" style="color: var(--primary-color);"></i>
</div>
<script>
    function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            pageLanguage: 'en',
            includedLanguages: 'en,rw,fr,ar',
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
            autoDisplay: false
        }, 'google_translate_element');
    }
</script>
<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" async defer></script>

{{-- Nail Polish Loader --}}
<div id="preloader">
    <div class="nail-loader">
        <div class="nail-bottle">
            <div class="nail-drops">
                <div class="drop"></div>
                <div class="drop"></div>
                <div class="drop"></div>
            </div>
        </div>
        <div class="loader-text">Preparing your luxury experience...</div>
    </div>
</div>

{{-- Enhanced Navbar --}}
<nav class="navbar navbar-expand-lg sticky-top" id="mainNavbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
            <img src="{{ asset('storage/logo.png') }}" alt="Isaiah Nail Bar">
            <span class="fw-bold">Isaiah Nail Bar</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">
                        Home
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/services') }}" class="nav-link {{ request()->is('services') ? 'active' : '' }}">
                       Services
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/gallery') }}" class="nav-link {{ request()->is('gallery') ? 'active' : '' }}">
                        Gallery
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('about') ? 'active' : '' }}" href="{{ route('about') }}">
                        About
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('contact') ? 'active' : '' }}" href="{{ url('/contact') }}">
                        Contact
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/providers') }}" class="nav-link {{ request()->is('providers') ? 'active' : '' }}">
                        Team
                    </a>
                </li>

                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                           Dashboard
                        </a>
                        <ul class="dropdown-menu">
                            @role('admin')
                                <li><a class="dropdown-item" href="{{ route('dashboard.admin') }}">
                                    <i class="fas fa-cog me-2"></i>Admin Panel
                                </a></li>
                            @endrole
                            @role('provider')
                                <li><a class="dropdown-item" href="{{ route('dashboard.provider') }}">
                                    <i class="fas fa-calendar-alt me-2"></i>Provider Panel
                                </a></li>
                            @endrole
                            @role('customer')
                                <li><a class="dropdown-item" href="{{ route('dashboard.customer') }}">
                                    <i class="fas fa-user me-2"></i>Customer Panel
                                </a></li>
                            @endrole
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="nav-link">
                            <i class="fas fa-sign-in-alt"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('register') }}" class="nav-link">
                            <i class="fas fa-user-plus"></i>Register
                        </a>
                    </li>
                @endauth

                <li class="nav-item ms-lg-2">
                    <a href="{{ route('booking.step1') }}" class="btn btn-primary">
                        Book Now
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

{{-- Content --}}
<main>
    @yield('hero')
    @yield('content')
</main>

{{-- Footer --}}
@include('partials.footer')

{{-- Back to Top --}}
<a href="#" class="back-to-top d-flex" aria-label="Back to top">
    <i class="fas fa-arrow-up"></i>
</a>

{{-- JS Libraries - Optimized Loading --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" defer></script>

<script>
    // Performance optimized JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        // Fast AOS initialization
        if (typeof AOS !== 'undefined') {
            AOS.init({ 
                duration: 800, 
                once: true,
                offset: 50
            });
        }

        // Optimized scroll handling with throttling
        let ticking = false;
        const navbar = document.getElementById('mainNavbar');
        const backToTop = document.querySelector('.back-to-top');
        
        function updateScroll() {
            const scrollTop = window.pageYOffset;
            
            if (scrollTop > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
            
            backToTop.style.display = scrollTop > 300 ? 'flex' : 'none';
            ticking = false;
        }

        function requestTick() {
            if (!ticking) {
                requestAnimationFrame(updateScroll);
                ticking = true;
            }
        }

        window.addEventListener('scroll', requestTick, { passive: true });

        // Fast back to top
        backToTop.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Quick preloader removal
        const preloader = document.getElementById('preloader');
        setTimeout(() => {
            preloader.style.opacity = '0';
            preloader.style.transform = 'scale(0.9)';
            setTimeout(() => preloader.remove(), 300);
        }, 800);

        // Optimized toastr
        if (typeof toastr !== 'undefined') {
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "4000",
                "showDuration": "200",
                "hideDuration": "200"
            };
        }

        // Session notifications
        @if(session('success')) 
            toastr.success("{{ session('success') }}"); 
        @endif
        @if(session('error')) 
            toastr.error("{{ session('error') }}"); 
        @endif
        @if(session('info')) 
            toastr.info("{{ session('info') }}"); 
        @endif
        @if(session('warning')) 
            toastr.warning("{{ session('warning') }}"); 
        @endif
    });

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });
</script>

@stack('scripts')

{{-- Live Chat - Optimized Loading --}}
<script>
    window.addEventListener('load', function() {
        var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
        (function(){
            var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
            s1.async = true;
            s1.src = 'https://embed.tawk.to/684e87f88f51131912365960/1itpc699h';
            s1.charset = 'UTF-8';
            s1.setAttribute('crossorigin', '*');
            s0.parentNode.insertBefore(s1, s0);
        })();
    });
</script>

</body>
</html>