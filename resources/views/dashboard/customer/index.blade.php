@extends('layouts.dashboard')

@section('title', 'My Appointments')
@section('page-subtitle', 'Manage your upcoming visits and view your history.')

@section('content')
<div class="space-y-8">
    
    {{-- Filter Tabs --}}
    <div class="flex items-center gap-2 border-b border-gray-200">
        <a href="?filter=upcoming" 
           class="px-6 py-3 text-sm font-medium border-b-2 transition-colors {{ request('filter', 'upcoming') === 'upcoming' ? 'border-rose-600 text-rose-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Upcoming
        </a>
        <a href="?filter=past" 
           class="px-6 py-3 text-sm font-medium border-b-2 transition-colors {{ request('filter') === 'past' ? 'border-rose-600 text-rose-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Past History
        </a>
    </div>

    @if ($bookings->count())
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach ($bookings as $booking)
                <div class="bg-white rounded-2xl shadow-[0_2px_10px_-4px_rgba(0,0,0,0.1)] border border-gray-100 overflow-hidden group hover:border-rose-100 hover:shadow-lg transition-all duration-300">
                    
                    {{-- Card Header --}}
                    <div class="p-6 border-b border-gray-50 bg-gray-50/30">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white border border-gray-100 flex items-center justify-center text-rose-500 shadow-sm">
                                    <i class="ph ph-calendar-check text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-serif font-bold text-gray-900">
                                        {{ \Carbon\Carbon::parse($booking->date)->format('M d') }}
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        at {{ \Carbon\Carbon::parse($booking->time)->format('H:i') }}
                                    </p>
                                </div>
                            </div>
                             @php
                                $statusStyles = match($booking->status) {
                                    'pending' => 'bg-amber-50 text-amber-700',
                                    'accepted' => 'bg-blue-50 text-blue-700',
                                    'completed' => 'bg-green-50 text-green-700',
                                    'cancelled', 'declined' => 'bg-gray-100 text-gray-600',
                                    default => 'bg-gray-100 text-gray-600'
                                };
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $statusStyles }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="p-6 space-y-4">
                        
                        {{-- Services --}}
                        <div class="space-y-2">
                             @foreach ($booking->services->take(2) as $service)
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2 text-gray-900 font-medium">
                                        <i class="ph ph-sparkle text-rose-300"></i>
                                        {{ $service->name }}
                                    </div>
                                    <span class="text-gray-500">{{ $service->duration_minutes }}m</span>
                                </div>
                            @endforeach
                            @if($booking->services->count() > 2)
                                <p class="text-xs text-gray-400 pl-6">+ {{ $booking->services->count() - 2 }} more services</p>
                            @endif
                        </div>

                        {{-- Provider --}}
                        <div class="flex items-center gap-3 pt-4 border-t border-gray-50">
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-500">
                                {{ strtoupper(substr($booking->provider->name ?? 'S', 0, 1)) }}
                            </div>
                            <div class="text-sm">
                                <span class="text-gray-500">Stylist:</span>
                                <span class="font-medium text-gray-900">{{ $booking->provider->name ?? 'Salon Staff' }}</span>
                            </div>
                        </div>

                        {{-- Payment Status --}}
                        @php
                            $totalPrice = $booking->services->sum(fn($s) => $s->price);
                            $totalPaid = $booking->payments->where('status', 'paid')->sum('amount');
                            $balance = $totalPrice - $totalPaid;
                        @endphp
                        
                        <div class="bg-gray-50 rounded-xl p-3 flex items-center justify-between">
                            <div class="text-xs">
                                <p class="text-gray-500">Total</p>
                                <p class="font-bold text-gray-900">RWF {{ number_format($totalPrice) }}</p>
                            </div>
                            <div class="text-right">
                                @if($balance > 0)
                                    <span class="text-xs font-bold text-rose-600 bg-rose-50 px-2 py-1 rounded-md">
                                        Due: {{ number_format($balance) }}
                                    </span>
                                @else
                                    <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-1 rounded-md">
                                        Paid in Full
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Card Footer Actions --}}
                    <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex flex-wrap gap-2 justify-end">
                         <a href="{{ route('booking.receipt', $booking->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-colors">
                            <i class="ph ph-receipt"></i> Receipt
                        </a>

                        @if ($booking->status === 'pending')
                            <form method="POST" action="{{ route('customer.booking.cancel', $booking->id) }}" onsubmit="return confirm('Cancel this booking?')">
                                @csrf
                                @method('PATCH')
                                <button class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-600 bg-white border border-red-100 rounded-lg hover:bg-red-50 transition-colors">
                                    <i class="ph ph-x-circle"></i> Cancel
                                </button>
                            </form>

                            @if ($balance > 0)
                                @if($totalPaid > 0)
                                     <a href="{{ route('customer.booking.payRemaining', $booking->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-white bg-gray-900 rounded-lg hover:bg-gray-800 transition-colors shadow-sm">
                                        <i class="ph ph-wallet"></i> Pay Balance
                                    </a>
                                @else
                                     <a href="{{ route('booking.retryPayment', $booking->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-white bg-gray-900 rounded-lg hover:bg-gray-800 transition-colors shadow-sm">
                                        <i class="ph ph-wallet"></i> Pay Now
                                    </a>
                                @endif
                            @endif

                        @elseif($booking->status === 'accepted' && $balance > 0)
                            <a href="{{ route('customer.booking.payRemaining', $booking->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-white bg-gray-900 rounded-lg hover:bg-gray-800 transition-colors shadow-sm">
                                <i class="ph ph-wallet"></i> Pay Balance
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $bookings->withQueryString()->links() }}
        </div>

    @else
        <div class="flex flex-col items-center justify-center py-20 bg-white rounded-3xl border border-dashed border-gray-200 text-center">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                <i class="ph ph-calendar-blank text-3xl text-gray-300"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 font-serif">No {{ request('filter') === 'past' ? 'past' : 'upcoming' }} appointments</h3>
            <p class="text-gray-500 max-w-sm mx-auto mt-2">
                {{ request('filter') === 'past' 
                    ? "You haven't completed any appointments yet." 
                    : "Ready to treat yourself? Book your next luxury experience." }}
            </p>
             @if(request('filter') !== 'past')
                <a href="{{ route('booking.step1') }}" class="mt-6 inline-flex items-center gap-2 px-6 py-3 bg-gray-900 text-white font-bold rounded-xl hover:bg-gray-800 transition-all shadow-lg hover:-translate-y-0.5">
                    Book Appointment <i class="ph ph-arrow-right"></i>
                </a>
            @endif
        </div>
    @endif
</div>
@endsection
