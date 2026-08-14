@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
<div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">
    
    {{-- Left: Luxury Background --}}
    <div class="hidden lg:block relative overflow-hidden bg-gray-900">
        <div class="absolute inset-0 opacity-60">
            <img src="{{ asset('storage/banner.jpg') }}" alt="Isaiah Nail Bar" class="w-full h-full object-cover">
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-transparent"></div>
        
        <div class="absolute bottom-0 left-0 right-0 p-12 text-white z-10">
            <h2 class="text-4xl font-serif font-bold mb-4 leading-tight">"Elegance is the only beauty that never fades."</h2>
            <div class="flex items-center gap-4">
                <div class="h-px bg-rose-500 w-12"></div>
                <p class="text-rose-200 uppercase tracking-widest text-sm font-medium">Isaiah Nail Bar</p>
            </div>
        </div>
    </div>

    {{-- Right: Content Card --}}
    <div class="flex items-center justify-center p-8 sm:p-12 lg:p-16 bg-white">
        <div class="w-full max-w-md space-y-8 text-center">
            
            {{-- Logo & Animated Icon --}}
            <div>
                <a href="{{ url('/') }}" class="inline-block mb-6">
                    <img src="{{ asset('storage/logo.png') }}" alt="Isaiah Nail Bar Logo" class="h-16 w-auto mx-auto object-contain">
                </a>
                
                <div class="relative w-20 h-20 mx-auto mb-6 flex items-center justify-center rounded-full bg-rose-50 text-rose-600">
                    <span class="absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-20 animate-ping"></span>
                    <i class="ph ph-envelope-open text-4xl relative z-10"></i>
                </div>

                <h2 class="text-3xl font-serif font-bold text-gray-900">Verify Your Email</h2>
                <p class="mt-3 text-gray-600 leading-relaxed text-sm">
                    Thanks for joining us! Before getting started, please verify your email address by clicking the link we just sent to your inbox.
                </p>
            </div>

            {{-- Spam Warning Alert --}}
            <div class="bg-amber-50 border border-amber-200 text-amber-800 p-4 rounded-2xl text-xs text-left flex items-start gap-3">
                <i class="ph ph-warning-circle text-amber-600 text-lg flex-shrink-0 mt-0.5"></i>
                <div>
                    <span class="font-bold">Can't find the email?</span> Check your <span class="underline font-semibold">Spam or Promotions</span> folder if it doesn't appear in your main inbox within a few moments.
                </div>
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-2xl text-xs flex items-center gap-2">
                    <i class="ph ph-check-circle text-emerald-600 text-lg flex-shrink-0"></i>
                    <span>A new verification link has been sent to your email address!</span>
                </div>
            @endif

            {{-- Auto-refresh indicator --}}
            <div class="flex items-center justify-center gap-2 text-xs text-gray-400">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span>Waiting for verification... Page auto-redirects when verified.</span>
            </div>

            {{-- Actions --}}
            <div class="space-y-4 pt-2">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="w-full flex justify-center items-center gap-2 py-3 px-4 rounded-xl shadow-lg text-sm font-bold text-white bg-gray-900 hover:bg-rose-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition-all duration-300 transform hover:-translate-y-0.5">
                        <i class="ph ph-paper-plane-tilt text-lg"></i>
                        Resend Verification Email
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs font-medium text-gray-500 hover:text-gray-900 transition-colors inline-flex items-center gap-1">
                        <i class="ph ph-sign-out text-sm"></i>
                        Log out of this account
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Auto Polling Script --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkStatusUrl = "{{ route('email.check-status') }}";
        
        setInterval(function() {
            fetch(checkStatusUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.verified) {
                    window.location.href = data.redirect || "{{ url('/dashboard') }}";
                }
            })
            .catch(err => console.log('Polling status check...', err));
        }, 3000);
    });
</script>
@endsection

