@extends('layouts.dashboard')

@section('title', 'Booking Receipt')

@section('content')
<div class="p-6 max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="ph ph-receipt"></i> Booking #{{ $booking->id }}
            </h1>
            <p class="text-gray-500 text-sm mt-1">Admin view of the receipt</p>
        </div>
        <a href="{{ route('dashboard.admin.bookings') }}" class="text-gray-500 hover:text-gray-800 text-sm font-medium flex items-center gap-1 transition">
            <i class="ph ph-arrow-left"></i> Back to Bookings
        </a>
    </div>

    {{-- Receipt Card --}}
    <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
        {{-- Card Header --}}
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <span class="font-semibold text-gray-700">Receipt Details</span>
            @php
                $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                    'accepted' => 'bg-blue-100 text-blue-800 border-blue-200',
                    'completed' => 'bg-green-100 text-green-800 border-green-200',
                    'declined' => 'bg-red-100 text-red-800 border-red-200',
                    'cancelled' => 'bg-gray-100 text-gray-800 border-gray-200',
                ];
                $colorClass = $statusColors[$booking->status] ?? $statusColors['cancelled'];
            @endphp
            <span class="px-3 py-1 text-xs font-bold uppercase rounded-full border {{ $colorClass }}">
                {{ $booking->status }}
            </span>
        </div>

        {{-- Details List --}}
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-12">
                {{-- Customer Info --}}
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Customer</p>
                    <p class="text-gray-900 font-semibold text-lg">{{ $booking->customer->user->name ?? 'N/A' }}</p>
                    @if($booking->customer && $booking->customer->user && $booking->customer->user->email)
                        <p class="text-gray-500 text-sm">{{ $booking->customer->user->email }}</p>
                    @endif
                </div>

                {{-- Provider Info --}}
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Provider</p>
                    <p class="text-gray-900 font-semibold text-lg">{{ $booking->provider->user->name ?? 'N/A' }}</p>
                </div>

                {{-- Date & Time --}}
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Schedule</p>
                    <p class="text-gray-900 font-semibold">{{ \Carbon\Carbon::parse($booking->date)->format('D, M j Y') }}</p>
                    <p class="text-gray-600 text-sm">{{ \Carbon\Carbon::parse($booking->time)->format('h:i A') }}</p>
                </div>

                {{-- Services --}}
                <div class="md:col-span-2">
                    <p class="text-sm font-medium text-gray-500 mb-2">Services Requested</p>
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                        <ul class="space-y-2">
                            @forelse($booking->services as $service)
                                <li class="flex justify-between items-center">
                                    <span class="text-gray-800 font-medium">{{ $service->name }}</span>
                                    <span class="text-gray-600 font-semibold">RWF {{ number_format($service->price) }}</span>
                                </li>
                            @empty
                                <li class="text-gray-500 italic">No services listed</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                {{-- Extras (Home Service / Pickup) --}}
                @if($booking->is_home_service)
                <div class="md:col-span-2">
                    <p class="text-sm font-medium text-gray-500 mb-2">Location (Home Service)</p>
                    <div class="bg-rose-50 rounded-lg p-4 border border-rose-100 flex items-start gap-3">
                        <i class="ph ph-house text-rose-500 text-xl mt-0.5"></i>
                        <div>
                            <span class="text-rose-900 font-medium">{{ $booking->address }}</span>
                            <p class="text-rose-700 text-sm mt-1">Premium rate applied (+100%)</p>
                        </div>
                    </div>
                </div>
                @elseif($booking->pickup_location_id)
                <div class="md:col-span-2">
                    <p class="text-sm font-medium text-gray-500 mb-2">Transport / Pickup</p>
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-100 flex items-start gap-3">
                        <i class="ph ph-car text-blue-500 text-xl mt-0.5"></i>
                        <div>
                            <span class="text-blue-900 font-medium">{{ $booking->pickupLocation->name ?? 'Configured Route' }}</span>
                            <p class="text-blue-700 text-sm mt-1">Exact Address: {{ $booking->pickup_address }}</p>
                            <p class="text-blue-700 text-sm font-semibold mt-1">Fee: RWF {{ number_format($booking->pickup_fee) }}</p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Payment Details --}}
                <div class="md:col-span-2 pt-4 border-t border-gray-100">
                    <p class="text-sm font-medium text-gray-500 mb-2">Payment Details</p>
                    @php
                        $totalPaid = $booking->payments->where('status', 'paid')->sum('amount');
                        $totalAmount = $booking->services->sum('price');
                        if ($booking->is_home_service) $totalAmount *= 2;
                        if ($booking->pickup_fee) $totalAmount += $booking->pickup_fee;
                    @endphp
                    <div class="flex flex-col sm:flex-row gap-4 justify-between bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Payment Option</p>
                            <p class="font-semibold text-gray-800 capitalize">{{ $booking->payment_option === 'deposit' ? 'Deposit (RWF 10,000)' : 'Full Payment' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Total Amount</p>
                            <p class="font-bold text-gray-900 text-lg">RWF {{ number_format($totalAmount) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Amount Paid</p>
                            <p class="font-bold text-green-600 text-lg">RWF {{ number_format($totalPaid) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Card Footer / Actions --}}
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex flex-wrap justify-end gap-3">
            <a href="{{ route('booking.receipt', $booking->id) }}" target="_blank" 
               class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition flex items-center gap-2">
                <i class="ph ph-eye"></i> View as Customer
            </a>
            <a href="{{ route('download.receipt', $booking->id) }}" 
               class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition shadow-sm flex items-center gap-2">
                <i class="ph ph-download-simple"></i> Download PDF
            </a>
        </div>
    </div>
</div>
@endsection
