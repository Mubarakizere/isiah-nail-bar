<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error | {{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .bg-animated {
            background: linear-gradient(-45deg, #1a0b2e, #4a154b, #2d0a31, #0f0518);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        .shaking {
            animation: shake 5s ease-in-out infinite;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        @keyframes pulse-glow {
            0%, 100% { opacity: 1; filter: brightness(1); }
            50% { opacity: 0.8; filter: brightness(1.5); }
        }
        .glowing-text {
            animation: pulse-glow 3s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-animated min-h-screen flex items-center justify-center font-sans antialiased text-white overflow-hidden">
    
    <!-- Animated background blobs -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
        <div class="absolute top-1/4 left-1/4 w-80 h-80 bg-red-600 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute top-1/3 right-1/4 w-80 h-80 bg-orange-600 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-1/4 left-1/2 w-80 h-80 bg-purple-700 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>
    </div>

    <div class="relative z-10 p-8 md:p-12 glass-card rounded-3xl shadow-2xl max-w-lg w-full text-center mx-4 border border-white/10 transform transition duration-500 hover:scale-[1.02]">
        
        <div class="mb-6 inline-block shaking">
            <svg class="w-20 h-20 text-red-400 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>

        <h1 class="text-8xl font-black tracking-tighter text-white/90 drop-shadow-lg glowing-text mb-2">
            500
        </h1>
        
        <h2 class="text-2xl font-bold text-red-200 mb-4 tracking-wide uppercase">Internal Server Error</h2>
        
        <p class="text-white/70 mb-8 text-lg font-light leading-relaxed">
            Oops! Something went wrong on our end. Our servers are having a little hiccup. Please try again later.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <button onclick="window.location.reload()" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 text-base font-semibold text-white transition-all duration-200 bg-red-500/20 border border-red-500/50 rounded-full hover:bg-red-500/40 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 focus:ring-offset-transparent hover:scale-105 active:scale-95 group">
                <svg class="w-5 h-5 mr-2 transform transition-transform group-hover:rotate-180 duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Try Again
            </button>
            <a href="{{ url('/') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 text-base font-semibold text-gray-900 transition-all duration-200 bg-white border border-transparent rounded-full shadow-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white focus:ring-offset-transparent hover:scale-105 active:scale-95">
                Go Home
            </a>
        </div>
    </div>

    <style>
        .animate-blob {
            animation: blob 10s infinite alternate;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        .animation-delay-4000 {
            animation-delay: 4s;
        }
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
    </style>
</body>
</html>
