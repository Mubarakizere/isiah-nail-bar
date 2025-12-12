@extends('layouts.public')

@section('title', 'Select Artist')

@section('content')


{{-- Hero Header --}}
<div class="bg-gray-900 py-12 relative overflow-hidden">
    <div class="absolute inset-0 opacity-20">
        <img src="{{ asset('storage/banner.jpg') }}" alt="" class="w-full h-full object-cover">
    </div>
    <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-transparent"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <span class="text-rose-400 font-medium tracking-widest text-xs uppercase mb-2 block">Step 2 of 4</span>
        <h1 class="text-3xl md:text-4xl font-serif text-white mb-2">Choose Your Artist</h1>
        <p class="text-gray-400 font-light text-lg">Select the specialist who will perform your services.</p>
    </div>
</div>

<div class="bg-white min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            
            {{-- Main Content --}}
            <div class="lg:col-span-8">
                <form method="POST" action="{{ route('booking.step2.submit') }}" id="providerForm" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($providers as $provider)
                            <label class="group relative cursor-pointer block">
                                <input type="radio" 
                                       name="provider_id" 
                                       value="{{ $provider->id }}" 
                                       class="peer hidden"
                                       {{ $providerId == $provider->id ? 'checked' : '' }}>
                                
                                <div class="relative bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 peer-checked:border-gray-900 peer-checked:ring-1 peer-checked:ring-gray-900 peer-checked:bg-gray-50 h-full flex flex-col items-center text-center">
                                    
                                    {{-- Status Badge --}}
                                    @if($provider->status === 'active')
                                        <div class="absolute top-4 right-4">
                                            <span class="flex h-2.5 w-2.5">
                                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                              <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                                            </span>
                                        </div>
                                    @endif

                                    {{-- Avatar --}}
                                    <div class="w-24 h-24 mb-4 relative">
                                        <div class="w-full h-full rounded-full overflow-hidden border-2 border-gray-100 group-hover:border-rose-200 transition-colors">
                                            <img src="{{ $provider->photo ? asset('storage/' . $provider->photo) : asset('images/default-user.png') }}" 
                                                 alt="{{ $provider->name }}"
                                                 class="w-full h-full object-cover">
                                        </div>
                                        <div class="absolute -bottom-1 -right-1 bg-white rounded-full p-1 opacity-0 peer-checked:opacity-100 transition-opacity shadow-sm border border-gray-100">
                                            <div class="bg-gray-900 rounded-full p-1">
                                                <i class="ph ph-check text-white text-xs"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <h3 class="text-xl font-serif text-gray-900 mb-1 group-hover:text-rose-600 transition-colors">
                                        {{ ucfirst($provider->name) }}
                                    </h3>
                                    
                                    <p class="text-xs font-medium text-rose-500 uppercase tracking-widest mb-3">Nail Specialist</p>
                                    
                                    <p class="text-sm text-gray-500 line-clamp-2 mb-4 font-light">
                                        {{ $provider->bio ?? 'Passionate about creating beautiful nail art and ensuring client satisfaction.' }}
                                    </p>
                                    
                                    <div class="mt-auto pt-4 border-t border-gray-100 w-full flex justify-between items-center text-xs text-gray-400">
                                        <span class="flex items-center gap-1"><i class="ph ph-star-fill text-yellow-400"></i> 5.0</span>
                                        <span>Highly Rated</span>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="flex justify-between items-center pt-8">
                        <a href="{{ route('booking.step1') }}" class="text-gray-500 hover:text-gray-900 font-medium flex items-center gap-2 transition-colors">
                            <i class="ph ph-arrow-left"></i> Back to Services
                        </a>
                    </div>
                </form>
            </div>

            {{-- Sidebar (Desktop) --}}
            <div class="hidden lg:block lg:col-span-4 pl-4">
                <div class="sticky top-24">
                     <div id="summaryCard" class="bg-gray-900 text-white rounded-2xl p-6 shadow-xl overflow-hidden relative">
                        <div class="absolute top-0 right-0 p-4 opacity-10">
                            <i class="ph ph-receipt text-9xl text-white"></i>
                        </div>
                        
                        <h3 class="text-lg font-serif mb-6 relative z-10">Booking Summary</h3>
                        
                        {{-- Customer --}}
                        <div class="mb-6 pb-6 border-b border-gray-700 relative z-10">
                            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Customer</p>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center text-gray-400">
                                    <i class="ph ph-user"></i>
                                </div>
                                <span class="font-medium text-white">{{ auth()->user()->name ?? 'Guest' }}</span>
                            </div>
                        </div>

                        {{-- Services --}}
                        <div class="space-y-4 mb-6 relative z-10 max-h-[30vh] overflow-y-auto custom-scrollbar pr-2">
                             @php
                                $totalPrice = $selectedServices->sum('price');
                                $totalDuration = $selectedServices->sum('duration_minutes');
                            @endphp
                            @foreach($selectedServices as $service)
                                <div class="flex justify-between items-start text-sm group">
                                    <div>
                                        <p class="text-gray-200 group-hover:text-white transition-colors">{{ $service->name }}</p>
                                        <p class="text-gray-500 text-xs">{{ $service->duration_minutes }} mins</p>
                                    </div>
                                    <span class="text-gray-300">{{ number_format($service->price) }}</span>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="border-t border-gray-700 pt-4 mb-6 relative z-10">
                            <div class="flex justify-between items-end">
                                <span class="text-gray-400 text-sm">Total</span>
                                <span class="text-2xl font-serif text-white">RWF {{ number_format($totalPrice) }}</span>
                            </div>
                            <div class="flex justify-between items-center mt-1">
                                <span class="text-gray-500 text-xs">Total Duration</span>
                                <span class="text-gray-400 text-xs">{{ $totalDuration }} mins</span>
                            </div>
                        </div>

                        <button type="submit" form="providerForm" class="w-full py-4 bg-white text-gray-900 font-bold rounded-xl hover:bg-rose-50 transition-all flex items-center justify-center gap-2 group relative z-10">
                            <span>Continue to Time</span>
                            <i class="ph ph-calendar-blank group-hover:-translate-y-0.5 transition-transform"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Mobile FAB --}}
<div class="lg:hidden fixed bottom-6 right-6 z-40">
    <button type="submit" form="providerForm" class="w-16 h-16 bg-gray-900 text-white rounded-full shadow-2xl flex items-center justify-center hover:bg-rose-600 transition-colors">
        <i class="ph ph-arrow-right text-2xl"></i>
    </button>
</div>

@endsection

@push('styles')
<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 4px;
}
</style>
@endpush
