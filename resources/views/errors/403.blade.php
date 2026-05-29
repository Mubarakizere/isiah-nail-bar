<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Forbidden | {{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .bg-animated {
            background: linear-gradient(-45deg, #0f172a, #1e293b, #334155, #0f172a);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        @keyframes lock-bounce {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-10px) scale(1.05); }
        }
        .bouncing-lock {
            animation: lock-bounce 3s ease-in-out infinite;
        }
        .glass-card {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body class="bg-animated min-h-screen flex items-center justify-center font-sans antialiased text-white overflow-hidden">
    
    <!-- Animated background blobs -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-blue-900 rounded-full mix-blend-screen filter blur-[100px] opacity-40 animate-blob"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-indigo-900 rounded-full mix-blend-screen filter blur-[100px] opacity-40 animate-blob animation-delay-4000"></div>
    </div>

    <div class="relative z-10 p-8 md:p-12 glass-card rounded-3xl max-w-lg w-full text-center mx-4 border-t border-white/20 transform transition duration-500 hover:shadow-indigo-500/20">
        
        <div class="mb-8 inline-block bouncing-lock relative">
            <div class="absolute inset-0 bg-indigo-500 blur-2xl opacity-20 rounded-full"></div>
            <svg class="w-24 h-24 text-indigo-400 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
        </div>

        <h1 class="text-7xl font-extrabold tracking-tight text-white/90 drop-shadow-md mb-2">
            403
        </h1>
        
        <h2 class="text-2xl font-semibold text-indigo-300 mb-4 uppercase tracking-widest">Access Denied</h2>
        
        <p class="text-gray-300 mb-8 text-lg font-light leading-relaxed">
            You don't have permission to access this resource. It looks like this area is restricted.
        </p>

        <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-8 py-3.5 text-base font-semibold text-white transition-all duration-300 bg-indigo-600 hover:bg-indigo-500 border border-transparent rounded-full shadow-lg shadow-indigo-500/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 focus:ring-offset-gray-900 hover:scale-105 active:scale-95 group">
            <svg class="w-5 h-5 mr-2 transform transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Return to Safety
        </a>
    </div>

    <style>
        .animate-blob {
            animation: blob 15s infinite alternate;
        }
        .animation-delay-4000 {
            animation-delay: 4s;
        }
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(50px, -50px) scale(1.2); }
            66% { transform: translate(-40px, 40px) scale(0.8); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
    </style>
</body>
</html>
