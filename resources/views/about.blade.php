@extends('layouts.public')

@section('title', 'About Isaiah Nail Bar')

@section('content')

{{-- Cinematic Hero --}}
<div class="relative bg-gray-900 py-32 overflow-hidden">
    <div class="absolute inset-0 opacity-30">
        <img src="{{ asset('storage/banner.jpg') }}" alt="About Us Banner" class="w-full h-full object-cover">
    </div>
    <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/60 to-transparent"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="text-rose-400 font-medium tracking-widest text-sm uppercase mb-4 block animate-fade-in-up">Kigali's Premier Destination</span>
        <h1 class="text-5xl md:text-7xl font-serif text-white mb-6 animate-fade-in-up delay-100">
            The Art of <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-200 to-white">Nail Perfection</span>
        </h1>
    </div>
</div>

{{-- Story Section --}}
<section class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div class="space-y-8">
                <div>
                    <h2 class="text-4xl font-serif text-gray-900 mb-6 leading-tight">
                        Redefining Luxury Nail Care in Rwanda
                    </h2>
                    <div class="w-16 h-1 bg-rose-500 mb-8"></div>
                </div>
                
                <div class="prose prose-lg text-gray-600 font-light leading-relaxed">
                    <p>
                        Isaiah Nail Bar began with a simple yet ambitious vision: to bring world-class nail artistry and hygiene standards to Kigali. We felt that getting your nails done shouldn't just be a routine maintenance task—it should be an experience of leisure and luxury.
                    </p>
                    <p>
                        Our salon was designed as a sanctuary from the bustling city. From the moment you step in, every detail—from the playlist to the scent, from the ergonomic chairs to the premium sterilizers—has been curated to ensure your absolute comfort and safety.
                    </p>
                </div>
                
                {{-- Stats Grid --}}
                <div class="grid grid-cols-3 gap-8 pt-8 border-t border-gray-100">
                    <div>
                        <span class="block text-3xl font-serif text-gray-900 mb-1">3+</span>
                        <span class="text-sm text-gray-500 uppercase tracking-widest">Years</span>
                    </div>
                    <div>
                        <span class="block text-3xl font-serif text-gray-900 mb-1">500+</span>
                        <span class="text-sm text-gray-500 uppercase tracking-widest">Clients</span>
                    </div>
                    <div>
                        <span class="block text-3xl font-serif text-gray-900 mb-1">10k+</span>
                        <span class="text-sm text-gray-500 uppercase tracking-widest">Manicures</span>
                    </div>
                </div>
            </div>
            
            <div class="relative">
                <div class="absolute -inset-4 bg-gray-50 rounded-full blur-3xl opacity-50 z-0"></div>
                <div class="relative z-10 grid grid-cols-2 gap-4">
                    <div class="space-y-4 translate-y-8">
                        <div class="aspect-[3/4] rounded-2xl overflow-hidden bg-gray-100">
                            <img src="{{ asset('images/about-1.jpg') }}" onerror="this.src='https://images.unsplash.com/photo-1604654894610-df63bc536371?auto=format&fit=crop&q=80'" alt="Salon Interior" class="w-full h-full object-cover">
                        </div>
                        <div class="p-6 bg-gray-900 rounded-2xl text-white">
                            <i class="ph ph-sparkle text-3xl text-rose-400 mb-3"></i>
                            <p class="font-serif italic">"Where hygiene meets high fashion."</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="p-6 bg-rose-50 rounded-2xl">
                            <i class="ph ph-heart text-3xl text-rose-500 mb-3"></i>
                            <p class="text-gray-900 font-medium">Uncompromising Hygiene Standards</p>
                        </div>
                        <div class="aspect-[3/4] rounded-2xl overflow-hidden bg-gray-100">
                             <img src="{{ asset('images/about-2.jpg') }}" onerror="this.src='https://images.unsplash.com/photo-1632345031435-8727f68979a6?auto=format&fit=crop&q=80'" alt="Nail Detail" class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Values --}}
<section class="py-24 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <h2 class="text-3xl font-serif text-gray-900 mb-4">Our Core Philosophy</h2>
            <p class="text-gray-500">We don't just paint nails; we treat them.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white p-10 rounded-2xl text-center hover:-translate-y-1 transition-transform duration-300 shadow-sm border border-gray-100">
                <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-rose-50 flex items-center justify-center text-rose-500">
                    <i class="ph ph-shield-check text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Health First</h3>
                <p class="text-gray-500 leading-relaxed font-light">
                    We use only top-tier, non-toxic products and hospital-grade sterilization for every tool. Your health is our non-negotiable priority.
                </p>
            </div>
            
            <div class="bg-white p-10 rounded-2xl text-center hover:-translate-y-1 transition-transform duration-300 shadow-sm border border-gray-100">
                <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-rose-50 flex items-center justify-center text-rose-500">
                    <i class="ph ph-diamond text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Precision Artistry</h3>
                <p class="text-gray-500 leading-relaxed font-light">
                    Our technicians are true artists. Whether you want a clean minimalist look or intricate nail art, we deliver perfection.
                </p>
            </div>

            <div class="bg-white p-10 rounded-2xl text-center hover:-translate-y-1 transition-transform duration-300 shadow-sm border border-gray-100">
                <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-rose-50 flex items-center justify-center text-rose-500">
                    <i class="ph ph-armchair text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Absolute Comfort</h3>
                <p class="text-gray-500 leading-relaxed font-light">
                    Sit back, relax, and enjoy a complimentary beverage. We've created an environment where you can truly unwind.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-24 bg-gray-900 text-center">
    <div class="max-w-4xl mx-auto px-4">
        <h2 class="text-4xl font-serif text-white mb-8">Ready to experience the difference?</h2>
        <a href="{{ route('booking.step1') }}" class="inline-block px-10 py-4 bg-white text-gray-900 font-medium rounded-full hover:bg-rose-50 transition-all transform hover:-translate-y-1">
            Book Appointment
        </a>
    </div>
</section>

@endsection