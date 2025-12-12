@extends('layouts.public')

@section('title', 'Final Confirmation')

@section('content')

{{-- Hero Header --}}
<div class="bg-gray-900 py-12 relative overflow-hidden">
    <div class="absolute inset-0 opacity-20">
        <img src="{{ asset('storage/banner.jpg') }}" alt="" class="w-full h-full object-cover">
    </div>
    <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-transparent"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <span class="text-rose-400 font-medium tracking-widest text-xs uppercase mb-2 block">Review & Confirm</span>
        <h1 class="text-3xl md:text-4xl font-serif text-white mb-2">Your Appointment</h1>
        <p class="text-gray-400 font-light text-lg">One last look before we welcome you.</p>
    </div>
</div>

<div class="bg-white min-h-screen py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('booking.step5.submit') }}">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                {{-- Left Side: Details --}}
                <div class="lg:col-span-2 space-y-8">
                    
                    {{-- Date & Time Card --}}
                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-start gap-5">
                       <div class="w-12 h-12 rounded-full bg-rose-50 flex items-center justify-center text-rose-500 shrink-0">
                            <i class="ph ph-calendar-check text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-serif text-gray-900 mb-1">When</h3>
                            <p class="text-2xl font-bold text-gray-900 mb-1">
                                {{ \Carbon\Carbon::parse(session('booking.date'))->format('l, F j') }}
                            </p>
                            <p class="text-gray-500 font-medium">
                                at {{ \Carbon\Carbon::parse(session('booking.time'))->format('h:i A') }}
                            </p>
                        </div>
                    </div>

                    {{-- Specialist Card --}}
                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-start gap-5">
                       <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 shrink-0">
                            <i class="ph ph-sparkle text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-serif text-gray-900 mb-1">With</h3>
                            <p class="text-xl font-bold text-gray-900">{{ $provider->name }}</p>
                            @if($provider->bio)
                                <p class="text-sm text-gray-500 mt-1 line-clamp-1">{{ $provider->bio }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Services List --}}
                    <div>
                         <h3 class="text-lg font-serif text-gray-900 mb-4 flex items-center gap-2">
                             Selected Services
                        </h3>
                         <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 space-y-4">
                            @foreach($services as $service)
                                <div class="flex justify-between items-center group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-2 h-2 rounded-full bg-gray-900 group-hover:scale-125 transition-transform"></div>
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $service->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $service->duration_minutes }} mins</p>
                                        </div>
                                    </div>
                                    <div class="font-mono text-gray-900 hover:text-rose-600 transition-colors">
                                        {{ number_format($service->price) }}
                                    </div>
                                </div>
                            @endforeach

                             <div class="pt-4 mt-4 border-t border-gray-200 flex justify-between items-center">
                                <span class="font-serif">Subtotal</span>
                                <span class="font-bold text-lg">RWF {{ number_format($services->sum('price')) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Customer Info --}}
                     <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                        <h3 class="text-lg font-serif text-gray-900 mb-4">Contact Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center text-gray-400">
                                    <i class="ph ph-user"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Name</p>
                                    <p class="font-medium text-gray-900">{{ auth()->user()->name }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center text-gray-400">
                                    <i class="ph ph-phone"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Phone</p>
                                    <p class="font-medium text-gray-900">{{ auth()->user()->phone ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Salon Policies --}}
                    <div class="bg-rose-50 rounded-2xl p-6 border border-rose-100 mb-6">
                        <h3 class="text-lg font-serif text-gray-900 mb-4 flex items-center gap-2">
                            <i class="ph ph-scroll text-rose-500"></i>
                            Salon Policies
                        </h3>
                        <div class="space-y-4">
                            {{-- Deposit --}}
                            <div class="flex gap-4">
                                <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-rose-500 shrink-0 shadow-sm border border-rose-100">
                                    <i class="ph ph-credit-card"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">Deposit Required</p>
                                    <p class="text-sm text-gray-600">A non-refundable deposit of 40% is required to secure your appointment slot.</p>
                                </div>
                            </div>

                            {{-- Cancellation --}}
                            <div class="flex gap-4">
                                <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-rose-500 shrink-0 shadow-sm border border-rose-100">
                                    <i class="ph ph-calendar-x"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">Cancellation & Rescheduling</p>
                                    <p class="text-sm text-gray-600">You may reschedule with at least <span class="font-bold">48 hours' notice</span>. Otherwise, the deposit will be forfeited.</p>
                                </div>
                            </div>

                            {{-- Late Arrival --}}
                            <div class="flex gap-4">
                                <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-rose-500 shrink-0 shadow-sm border border-rose-100">
                                    <i class="ph ph-clock-countdown"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">Late Arrival</p>
                                    <p class="text-sm text-gray-600">If you are more than <span class="font-bold">15 minutes late</span>, we may need to forfeit your appointment to respect other guests.</p>
                                </div>
                            </div>

                            {{-- Guests --}}
                            <div class="flex gap-4">
                                <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-rose-500 shrink-0 shadow-sm border border-rose-100">
                                    <i class="ph ph-users-three"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">No Extra Guests</p>
                                    <p class="text-sm text-gray-600">For hygiene and space reasons, please do not bring extra guests or children.</p>
                                </div>
                            </div>

                            {{-- Silent Appointment --}}
                            <div class="flex gap-4 items-center pt-2 border-t border-rose-100/50">
                                <label class="flex items-center gap-3 cursor-pointer group w-full">
                                    <input type="checkbox" name="silent_appointment" value="1" class="w-5 h-5 text-gray-900 border-gray-300 rounded focus:ring-gray-900 cursor-pointer">
                                    <div>
                                        <span class="text-sm font-bold text-gray-900 group-hover:text-rose-600 transition-colors">Request a Silent Appointment</span>
                                        <p class="text-xs text-gray-500">No small talk, just peace and pampering.</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Terms --}}
                    <label class="group block cursor-pointer">
                        <div class="flex gap-4 items-start p-4 rounded-xl border border-rose-100 bg-rose-50/50 hover:bg-rose-50 transition-colors">
                             <input type="checkbox" name="terms_accepted" required 
                                   class="mt-1 w-5 h-5 text-gray-900 border-gray-300 rounded focus:ring-gray-900 cursor-pointer">
                             <div class="text-sm text-gray-600 leading-relaxed">
                                I confirm that all details above are correct and understand that my reservation is subject to the salon's 
                                <a href="#" class="text-gray-900 font-bold underline decoration-rose-300 hover:decoration-rose-500 underline-offset-2">cancellation policy</a>.
                             </div>
                        </div>
                    </label>

                </div>

                {{-- Right Side: Summary --}}
                <div class="lg:col-span-1">
                    <div class="sticky top-24">
                        <div class="bg-gray-900 text-white rounded-2xl p-6 shadow-xl relative overflow-hidden">
                             <div class="absolute top-0 right-0 p-4 opacity-10">
                                <i class="ph ph-check-circle text-9xl text-white"></i>
                            </div>

                            <h3 class="text-lg font-serif mb-6 relative z-10">Total to Pay</h3>
                            
                            <div class="space-y-4 mb-8 relative z-10">
                                <div class="flex justify-between items-center text-sm text-gray-400">
                                    <span>Total Duration</span>
                                    <span>{{ $services->sum('duration_minutes') }} mins</span>
                                </div>
                                <div class="flex justify-between items-center text-sm text-gray-400">
                                    <span>Service Count</span>
                                    <span>{{ $services->count() }} services</span>
                                </div>
                                 <div class="h-px bg-gray-800 my-4"></div>
                                 <div class="flex justify-between items-end">
                                    <span class="text-lg">Grand Total</span>
                                    <span class="text-3xl font-serif text-white">RWF {{ number_format($services->sum('price')) }}</span>
                                </div>
                            </div>

                            <button type="submit" class="w-full py-4 bg-white text-gray-900 font-bold rounded-xl hover:bg-rose-50 transition-all flex items-center justify-center gap-2 group relative z-10 shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                                <span>Confirm Appointment</span>
                                <i class="ph ph-arrow-right group-hover:translate-x-1 transition-transform"></i>
                            </button>

                             <p class="text-center text-xs text-gray-500 mt-4 relative z-10">
                                Secured by SSL Encryption
                            </p>
                        </div>
                        
                        <a href="{{ route('booking.step4') }}" class="block text-center mt-6 text-gray-500 hover:text-gray-900 text-sm font-medium transition-colors">
                            Change Payment Method
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
