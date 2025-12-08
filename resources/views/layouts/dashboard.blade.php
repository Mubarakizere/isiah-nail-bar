<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Dashboard') — Isaiah Nail Bar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('storage/favicon.ico') }}" type="image/x-icon">

    {{-- CSS Libraries --}}
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css" rel="stylesheet">
    {{-- Favicon --}}
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
<link rel="manifest" href="{{ asset('site.webmanifest') }}">


    <style>
        .sidebar {
            width: 240px;
            background-color: #212529;
            color: #fff;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        .sidebar a.nav-link {
            color: #ccc;
            transition: 0.2s;
            padding: 0.6rem 1rem;
            border-radius: 4px;
        }

        .sidebar a.nav-link:hover,
        .sidebar a.nav-link.active {
            color: #fff;
            background-color: #343a40;
            font-weight: 600;
        }

        .sidebar small {
            font-size: 0.72rem;
            letter-spacing: 0.5px;
            color: #aaa;
        }

        .sidebar i {
            width: 18px;
            display: inline-block;
        }

        .profile-avatar {
            width: 32px;
            height: 32px;
            object-fit: cover;
            border-radius: 50%;
        }

        #google_translate_element select {
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        .goog-logo-link,
        .goog-te-gadget span {
            display: none !important;
        }

        .goog-te-gadget {
            font-family: inherit !important;
            font-size: 14px !important;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100%;
                z-index: 1040;
                overflow-y: auto;
                transform: translateX(-100%);
            }

            .sidebar.collapsed {
                transform: translateX(0);
            }

            .backdrop {
                display: none;
            }

            .backdrop.active {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1030;
            }
        }

        .sidebar-toggler {
            border: none;
            background: none;
            font-size: 1.5rem;
        }

        .pagination .page-link {
            border-radius: 30px;
            margin: 0 2px;
            transition: all 0.2s ease;
        }

        .pagination .page-link:hover {
            background-color: #000;
            color: #fff;
        }

        /* ✅ Toast Container */
        .toast-container {
            z-index: 1080;
        }
    </style>
</head>
<body>
    {{-- 🌐 Google Translate --}}
    <div id="google_translate_element" class="position-fixed top-0 end-0 m-3 z-3 shadow-sm bg-white rounded"></div>
    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'en,rw,fr,ar',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE
            }, 'google_translate_element');
        }
    </script>
    <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

    {{-- 🔷 Main Layout --}}
    <div class="d-flex flex-column flex-md-row" style="min-height: 100vh;">
        <div id="sidebar-backdrop" class="backdrop" onclick="toggleSidebar()"></div>

        {{-- Sidebar --}}
        @include('partials.sidebar')

        {{-- Main --}}
        <div class="flex-grow-1 d-flex flex-column">
            {{-- Topbar --}}
            @include('partials.topbar')

            {{-- Page Content --}}
            <main class="p-4 bg-light flex-grow-1">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- ✅ Toast Notifications --}}
    <div class="toast-container position-fixed top-0 end-0 p-3">
        @foreach (['success', 'error', 'info', 'warning'] as $type)
            @if(session($type))
                <div class="toast align-items-center text-white bg-{{ $type == 'error' ? 'danger' : $type }} border-0 show mb-2 shadow" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body fw-semibold">
                            {{ session($type) }}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    {{-- JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('sidebar-backdrop').classList.toggle('active');
        }

        // 🚀 Auto-dismiss Bootstrap Toasts after 5 seconds
        document.addEventListener('DOMContentLoaded', function () {
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(toastEl => {
                const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
                toast.show();
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
