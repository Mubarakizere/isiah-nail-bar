@extends('layouts.public')

@section('title', 'Booking Receipt')

@section('content')
<div class="bg-gray-50 py-8">
    <div class="max-w-3xl mx-auto px-4">
        {{-- Header --}}
        <div class="text-center mb-8">
            <img src="{{ asset('storage/logo.png') }}" alt="Logo" class="h-12 mx-auto mb-4">
            <h3 class="text-2xl font-bold text-gray-900">Booking Receipt</h3>
            <p class="text-gray-600">Isaiah Nail Bar – Kigali</p>
        </div>

        {{-- Receipt Card --}}
        <div class="bg-white rounded-xl shadow-lg border border-gray-200">
            <div class="p-6 space-y-4">
                <div class="flex justify-between py-3 border-b">
                    <span class="text-gray-600">Booking ID:</span>
                    <span class="font-bold text-gray-900">#{{ $booking->id }}</span>
                </div>

                <div class="flex justify-between py-3 border-b">
                    <span class="text-gray-600">Customer:</span>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900">{{ $booking->customer->user->name ?? '-' }}</p>
                        <p class="text-sm text-gray-500">{{ $booking->customer->user->email ?? '-' }}</p>
                    </div>
                </div>

                <div class="py-3 border-b">
                    <p class="text-gray-600 mb-3">Services:</p>
                    <div class="space-y-2">
                        @foreach($booking->services as $service)
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="font-semibold text-gray-900">{{ $service->name }}</span>
                                <span class="font-bold text-blue-600">RWF {{ number_format($service->price) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-between py-3 border-b">
                    <span class="text-gray-600">Provider:</span>
                    <span class="font-semibold text-gray-900">{{ $booking->provider->name ?? '-' }}</span>
                </div>

                <div class="flex justify-between py-3 border-b">
                    <span class="text-gray-600">Date:</span>
                    <span class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($booking->date)->format('D, M j, Y') }}</span>
                </div>

                <div class="flex justify-between py-3 border-b">
                    <span class="text-gray-600">Time:</span>
                    <span class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($booking->time)->format('h:i A') }}</span>
                </div>

                <div class="flex justify-between py-3 border-b">
                    <span class="text-gray-600">Payment Option:</span>
                    <span class="font-semibold text-gray-900 capitalize">{{ $booking->payment_option }}</span>
                </div>

                @if($booking->deposit_amount)
                    <div class="flex justify-between py-3 border-b">
                        <span class="text-gray-600">Deposit Paid:</span>
                        <span class="font-semibold text-green-600">RWF {{ number_format($booking->deposit_amount) }}</span>
                    </div>
                @endif

                <div class="flex justify-between py-3 border-b">
                    <span class="text-gray-600">Total Amount:</span>
                    <span class="text-xl font-bold text-blue-600">RWF {{ number_format($booking->services->sum('price')) }}</span>
                </div>

                <div class="flex justify-between items-center py-3">
                    <span class="text-gray-600">Status:</span>
                    <span class="px-4 py-2 rounded-full text-sm font-bold
                        {{ match($booking->status) {
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'accepted' => 'bg-blue-100 text-blue-800',
                            'completed' => 'bg-green-100 text-green-800',
                            'cancelled', 'declined' => 'bg-red-100 text-red-800',
                            default => 'bg-gray-100 text-gray-800'
                        } }}">
                        {{ ucfirst($booking->status) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Download Button --}}
        <div class="text-center mt-6">
            <a href="{{ route('download.receipt', $booking->id) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-700 text-white font-semibold rounded-lg hover:bg-gray-800 transition">
                <i class="ph ph-file-arrow-down"></i>
                <span>Download PDF</span>
            </a>
        </div>
    </div>
</div>

@endsection
