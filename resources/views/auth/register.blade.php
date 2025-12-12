@extends('layouts.auth')

@section('title', 'Register')

@section('content')
<div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">
    
    {{-- Left: Artistic Image --}}
    <div class="hidden lg:block relative overflow-hidden bg-gray-900">
        <div class="absolute inset-0 opacity-60">
            {{-- Use a different image if available, or the same brand banner --}}
            <img src="{{ asset('storage/banner.jpg') }}" alt="Register Background" class="w-full h-full object-cover">
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-transparent"></div>
        
        <div class="absolute bottom-0 left-0 right-0 p-12 text-white z-10">
            <h2 class="text-4xl font-serif font-bold mb-4 leading-tight">"Join our community of elegance."</h2>
             <div class="flex items-center gap-4">
                <div class="h-px bg-rose-500 w-12"></div>
                <p class="text-rose-200 uppercase tracking-widest text-sm font-medium">Isaiah Nail Bar</p>
            </div>
        </div>
    </div>

    {{-- Right: Form --}}
    <div class="flex items-center justify-center p-8 sm:p-12 lg:p-16 bg-white">
        <div class="w-full max-w-md space-y-8">
            
            {{-- Header --}}
            <div class="text-center">
                 <a href="{{ url('/') }}" class="inline-block mb-6">
                    <img src="{{ asset('storage/logo.png') }}" alt="Logo" class="h-16 w-auto mx-auto object-contain">
                </a>
                <h2 class="text-3xl font-serif font-bold text-gray-900">Create Account</h2>
                <p class="mt-2 text-gray-500">Sign up to book appointments easily</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ph ph-user text-gray-400 text-lg"></i>
                        </div>
                        <input type="text" name="name" value="{{ old('name') }}" required autofocus
                               class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-colors bg-gray-50 focus:bg-white placeholder-gray-400"
                               placeholder="Jane Doe">
                    </div>
                    @error('name') <p class="mt-1 text-sm text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ph ph-envelope text-gray-400 text-lg"></i>
                        </div>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-colors bg-gray-50 focus:bg-white placeholder-gray-400"
                               placeholder="you@example.com">
                    </div>
                    @error('email') <p class="mt-1 text-sm text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                     <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ph ph-phone text-gray-400 text-lg"></i>
                        </div>
                        <input type="text" name="phone" value="{{ old('phone') }}" required
                               class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-colors bg-gray-50 focus:bg-white placeholder-gray-400"
                               placeholder="+250 7XX XXX XXX">
                    </div>
                    @error('phone') <p class="mt-1 text-sm text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ph ph-lock-key text-gray-400 text-lg"></i>
                        </div>
                        <input type="password" name="password" required
                               class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-colors bg-gray-50 focus:bg-white placeholder-gray-400"
                               placeholder="••••••••">
                    </div>
                    @error('password') <p class="mt-1 text-sm text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                    <div class="relative">
                         <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ph ph-check-circle text-gray-400 text-lg"></i>
                        </div>
                        <input type="password" name="password_confirmation" required
                               class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-colors bg-gray-50 focus:bg-white placeholder-gray-400"
                               placeholder="••••••••">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gray-900 hover:bg-rose-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition-all duration-300 transform hover:-translate-y-0.5">
                        Register
                        <i class="ph ph-user-plus"></i>
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500">
                    Already have an account? 
                    <a href="{{ route('login') }}" class="font-bold text-gray-900 hover:text-rose-600 transition-colors">
                        Sign in
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
