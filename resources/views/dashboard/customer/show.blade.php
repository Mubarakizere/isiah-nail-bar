@extends('layouts.dashboard')

@section('title', 'Booking Details')
@section('page-subtitle', 'Detailed view of your reservation.')

@section('content')
<div class="max-w-2xl mx-auto">
    
    {{-- Back Link --}}
    <a href="{{ route('dashboard.customer') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 mb-6 transition-colors group">
        <i class="ph ph-arrow-left group-hover:-translate-x-1 transition-transform"></i>
        Back to My Bookings
    </a>

    <div class="bg-white rounded-3xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)] border border-gray-100 overflow-hidden relative">
        
        {{-- Luxury Header Pattern --}}
        <div class="h-24 bg-gray-900 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 24px 24px;"></div>
            <div class="absolute -bottom-6 -right-6 text-white/5">
                <i class="ph ph-sparkle text-9xl"></i>
            </div>
        </div>

        <div class="px-8 pb-8 relative z-10">
            {{-- Status Badge --}}
            <div class="flex justify-center -mt-6 mb-6">
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full border-4 border-white shadow-sm bg-white text-sm font-bold uppercase tracking-wider
                {{ match($booking->status) {
                    'pending' => 'text-amber-600',
                    'accepted' => 'text-blue-600',
                    'completed' => 'text-green-600',
                    'cancelled', 'declined' => 'text-gray-500',
                    default => 'text-gray-900'
                } }}">
                    @if($booking->status === 'completed') <i class="ph ph-check-circle text-lg"></i>
                    @elseif($booking->status === 'pending') <i class="ph ph-clock text-lg"></i>
                    @elseif($booking->status === 'accepted') <i class="ph ph-calendar-check text-lg"></i>
                    @endif
                    {{ ucfirst($booking->status) }}
                </span>
            </div>

            <div class="text-center mb-8">
                <h1 class="text-2xl font-serif font-bold text-gray-900">Reservation Details</h1>
                <p class="text-sm text-gray-500 mt-1">Order #{{ $booking->id }}</p>
            </div>

            {{-- Main Details Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                
                {{-- Date & Time --}}
                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-rose-500 shadow-sm">
                             <i class="ph ph-calendar-blank"></i>
                        </div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">When</span>
                    </div>
                    <p class="text-lg font-bold text-gray-900">
                         {{ \Carbon\Carbon::parse($booking->date)->format('l, M d') }}
                    </p>
                    <p class="text-gray-600">
                        at {{ \Carbon\Carbon::parse($booking->time)->format('H:i') }}
                    </p>
                </div>

                {{-- Provider --}}
                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100">
                     <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-rose-500 shadow-sm">
                             <i class="ph ph-user"></i>
                        </div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Stylist</span>
                    </div>
                    <p class="text-lg font-bold text-gray-900">
                         {{ $booking->provider->name ?? 'Salon Staff' }}
                    </p>
                    <p class="text-gray-600 text-sm">Professional Service</p>
                </div>
            </div>

            {{-- Service List --}}
            <div class="space-y-4 mb-8">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest border-b border-gray-100 pb-2">Services Selected</h3>
                
                @foreach($booking->services as $service)
                    <div class="flex justify-between items-center group">
                        <div class="flex items-center gap-3">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 group-hover:scale-150 transition-transform"></span>
                            <span class="font-medium text-gray-700">{{ $service->name }}</span>
                        </div>
                        <span class="font-mono text-gray-900">{{ number_format($service->price) }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Payment Info --}}
             <div class="bg-gray-900 text-white rounded-2xl p-6 mb-8 relative overflow-hidden">
                <div class="flex justify-between items-end relative z-10">
                    <div>
                        <p class="text-gray-400 text-sm mb-1">Total Amount</p>
                        <p class="text-3xl font-serif">RWF {{ number_format($booking->services->sum('price')) }}</p>
                    </div>
                     <div class="text-right">
                         @php
                             $paid = $booking->payments->where('status', 'paid')->sum('amount');
                             $total = $booking->services->sum('price');
                             $balance = $total - $paid;
                         @endphp
                         
                        @if($balance <= 0)
                            <span class="px-3 py-1 bg-green-500 text-white text-xs font-bold rounded-lg flex items-center gap-1">
                                <i class="ph ph-check-circle"></i> Paid in Full
                            </span>
                        @else
                             <p class="text-gray-400 text-xs mb-1">Remaining Balance</p>
                             <p class="text-xl font-bold text-white">RWF {{ number_format($balance) }}</p>
                        @endif
                    </div>
                </div>
             </div>

             {{-- Actions --}}
             <div class="flex flex-col sm:flex-row gap-3">
                 <a href="{{ route('booking.receipt', $booking->id) }}" class="flex-1 py-3 px-4 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 hover:text-gray-900 transition-colors flex items-center justify-center gap-2">
                     <i class="ph ph-download-simple"></i> Download Receipt
                 </a>

                 @if ($booking->status === 'pending')
                    <form method="POST" action="{{ route('customer.booking.cancel', $booking->id) }}" class="flex-1 text-center" onsubmit="return confirm('Are you sure you want to cancel this booking? This action cannot be undone.')">
                        @csrf
                        @method('PATCH')
                        <button class="w-full py-3 px-4 bg-white border border-red-100 text-red-600 font-bold rounded-xl hover:bg-red-50 transition-colors flex items-center justify-center gap-2">
                            <i class="ph ph-x-circle"></i> Cancel Booking
                        </button>
                    </form>
                @endif
                
                @if ($balance > 0)
                     <a href="{{ route('customer.booking.payRemaining', $booking->id) }}" class="flex-1 py-3 px-4 bg-rose-600 text-white font-bold rounded-xl hover:bg-rose-700 transition-colors flex items-center justify-center gap-2 shadow-lg shadow-rose-200">
                         <i class="ph ph-wallet"></i> Pay Balance
                     </a>
                @endif
             </div>

        </div>
    </div>
</div>
@endsection
