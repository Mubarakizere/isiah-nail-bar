@extends('layouts.public')

@section('title', 'Booking Successful')

@section('content')
@php
    $summary = session('last_booking');
    $bookingId = session('last_booking_id');
@endphp

<div class="min-h-screen bg-white py-12 relative overflow-hidden">
    {{-- Confetti/Decoration Background --}}
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
        <div class="absolute top-10 left-10 w-32 h-32 bg-rose-50 rounded-full blur-3xl opacity-60"></div>
        <div class="absolute bottom-10 right-10 w-64 h-64 bg-gray-50 rounded-full blur-3xl opacity-60"></div>
    </div>

    <div class="max-w-2xl mx-auto px-4 relative z-10">
        @if($summary)
            <div class="text-center mb-10">
                <div class="relative inline-block mb-6 pt-10">
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-24 h-24 bg-green-50 rounded-full animate-ping opacity-75"></div>
                    <div class="relative w-24 h-24 bg-green-100 rounded-full flex items-center justify-center text-green-600 shadow-sm mx-auto">
                        <i class="ph ph-check text-5xl"></i>
                    </div>
                </div>
                
                <h1 class="text-4xl font-serif text-gray-900 mb-4">Booking Confirmed</h1>
                <p class="text-gray-500 text-lg font-light">
                    Thank you, <span class="font-medium text-gray-900">{{ auth()->user()->name }}</span>. Your appointment has been successfully scheduled.
                </p>
                <div class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-gray-50 rounded-full border border-gray-100 text-sm text-gray-600">
                    <i class="ph ph-envelope-simple"></i>
                    <span>Check your email for the confirmation details</span>
                </div>
            </div>

            {{-- Receipt Card --}}
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 mb-8 relative">
                {{-- Decorative Receipt Teeth --}}
                <div class="absolute top-0 left-0 w-full h-2 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMCAxMCIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+PHBhdGggZD0iTTAgMTBMMTAgMEwyMCAxMEgwWiIgZmlsbD0iI2Y5ZmFZmIiLz48L3N2Zz4=')] bg-repeat-x bg-[length:20px_10px] opacity-20 transform rotate-180"></div>
                
                <div class="p-8">
                    <div class="flex justify-between items-start mb-8 border-b border-gray-100 pb-8">
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Booking Reference</p>
                            <p class="font-mono text-lg font-bold text-gray-900">#{{ str_pad($bookingId, 6, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <div class="text-right">
                             <img src="{{ asset('storage/logo.png') }}" alt="" class="h-10 w-auto ml-auto opacity-80 mix-blend-multiply">
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="flex gap-4">
                            <div class="w-12 h-12 rounded-full bg-rose-50 flex items-center justify-center text-rose-500 shrink-0">
                                <i class="ph ph-calendar-check text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Date & Time</p>
                                <p class="font-serif text-lg text-gray-900">
                                    {{ \Carbon\Carbon::parse($summary['date'])->format('l, F jS, Y') }}
                                </p>
                                <p class="text-gray-900">
                                    at {{ $summary['time'] }}
                                </p>
                            </div>
                        </div>

                         <div class="flex gap-4">
                            <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 shrink-0">
                                <i class="ph ph-users-three text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Specialist</p>
                                <p class="font-serif text-lg text-gray-900">{{ $summary['provider_name'] }}</p>
                            </div>
                        </div>

                        <div class="border-t border-dashed border-gray-200 pt-6">
                            <p class="text-sm text-gray-500 mb-4">Services Selected</p>
                            <ul class="space-y-3">
                                @foreach ($summary['service_names'] ?? [] as $service)
                                    <li class="flex items-center justify-between text-gray-900">
                                        <span class="flex items-center gap-3">
                                            <div class="w-1.5 h-1.5 rounded-full bg-gray-300"></div>
                                            {{ $service }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-8 py-6 border-t border-gray-100 flex justify-between items-center">
                    <span class="text-gray-500 font-medium">Payment Status</span>
                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold uppercase tracking-wide">
                        {{ ucfirst($summary['payment']) }}
                    </span>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
                <a href="{{ route('booking.receipt', $bookingId) }}" target="_blank" class="px-8 py-4 bg-gray-900 text-white rounded-xl font-bold hover:bg-gray-800 transition shadow-lg hover:shadow-xl hover:-translate-y-1 flex items-center justify-center gap-2">
                    <i class="ph ph-receipt"></i>
                    View Receipt
                </a>
                <a href="{{ route('download.receipt', $bookingId) }}" class="px-8 py-4 bg-white text-gray-900 border border-gray-200 rounded-xl font-bold hover:bg-gray-50 transition hover:-translate-y-1 flex items-center justify-center gap-2">
                    <i class="ph ph-download-simple"></i>
                    Download PDF
                </a>
            </div>

            <div class="border-t border-gray-100 pt-8 text-center">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-rose-500 hover:text-rose-600 font-medium transition-colors group">
                    <i class="ph ph-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                    Return to Home
                </a>
            </div>

        @else
            <div class="text-center py-20">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
                    <i class="ph ph-clock-counter-clockwise text-3xl text-gray-400"></i>
                </div>
                <h2 class="text-2xl font-serif text-gray-900 mb-2">Session Expired</h2>
                <p class="text-gray-500 mb-8">The booking details are no longer available in the session.</p>
                <a href="{{ route('booking.step1') }}" class="px-8 py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-gray-800 transition">
                    Start New Booking
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
