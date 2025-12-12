@extends('layouts.public')

@section('title', 'Meet Our Team')

@section('content')

{{-- Team Hero --}}
<div class="relative bg-gray-900 py-24 overflow-hidden">
    <div class="absolute inset-0 opacity-40">
        <img src="{{ asset('storage/banner.jpg') }}" alt="Team Banner" class="w-full h-full object-cover">
    </div>
    <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/60 to-transparent"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center pt-12">
        <span class="text-rose-400 font-medium tracking-widest text-sm uppercase mb-3 block animate-fade-in-up">The Artists</span>
        <h1 class="text-4xl md:text-6xl font-serif text-white mb-6 animate-fade-in-up delay-100">Meet the Team</h1>
        <p class="text-xl text-gray-300 font-light max-w-2xl mx-auto animate-fade-in-up delay-200">
            Dedicated professionals committed to delivering perfection in every stroke.
        </p>
    </div>
</div>

{{-- Providers Grid --}}
<section class="py-20 bg-white min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @if($providers->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-12">
                @foreach($providers as $provider)
                    <div class="group">
                        <div class="relative aspect-[3/4] mb-6 overflow-hidden rounded-lg bg-gray-100">
                            <img src="{{ $provider->photo ? asset('storage/' . $provider->photo) : 'https://via.placeholder.com/400x500/f3f4f6/9ca3af?text=' . urlencode($provider->name) }}"
                                 alt="{{ $provider->name }}"
                                 class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-700">
                            
                            {{-- Overlay Content --}}
                            <div class="absolute inset-x-0 bottom-0 p-6 bg-gradient-to-t from-gray-900/90 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 translate-y-4 group-hover:translate-y-0 transform">
                                @if($provider->status === 'active')
                                    <span class="inline-block px-2 py-1 bg-green-500/20 text-green-300 text-xs font-medium rounded mb-2 border border-green-500/30">Available for Booking</span>
                                @endif
                                <p class="text-gray-300 text-sm line-clamp-2 mb-4">{{ $provider->bio ?? 'Passionate nail artist dedicated to excellence.' }}</p>
                                <a href="{{ route('booking.step2') }}" class="block w-full py-3 bg-white text-gray-900 text-center font-medium rounded-full hover:bg-rose-50 transition-colors">
                                    Book Now
                                </a>
                            </div>
                        </div>

                        <div class="text-center">
                            <h3 class="text-2xl font-serif text-gray-900 mb-1">{{ $provider->name }}</h3>
                            <p class="text-rose-600 font-medium text-sm tracking-widest uppercase">Nail Specialist</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-24">
                <i class="ph ph-users-three text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-serif text-gray-900 mb-2">Our Team is Growing</h3>
                <p class="text-gray-500 mb-8">Check back soon to see our talented artists.</p>
                <a href="{{ url('/contact') }}" class="text-gray-900 underline underline-offset-4 hover:text-rose-600 transition-colors">
                    Join our team
                </a>
            </div>
        @endif

    </div>
</section>

{{-- CTA --}}
<section class="py-24 bg-gray-50 border-t border-gray-100">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-serif text-gray-900 mb-6">Experience the Difference</h2>
        <div class="flex justify-center gap-4">
            <a href="{{ route('booking.step1') }}" class="px-8 py-4 bg-gray-900 text-white font-medium rounded-full hover:bg-rose-600 transition-all shadow-lg hover:shadow-xl hover:-translate-y-1">
                Book an Appointment
            </a>
        </div>
    </div>
</section>

@endsection