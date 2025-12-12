@extends('layouts.public')

@section('title', 'Pay Remaining Balance')

@section('content')
<div class="bg-gray-50 py-8 min-h-screen">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
            <h4 class="text-2xl font-bold text-gray-900 mb-2 flex items-center gap-2">
                <i class="ph ph-wallet text-blue-600"></i>
                Pay Remaining Balance
            </h4>
            <p class="text-gray-600 mb-6">
                Please review your booking details and complete your remaining payment.
            </p>

            {{-- Summary --}}
            <div class="mb-6">
                <div class="space-y-3">
                    @foreach ($booking->services as $service)
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-700">{{ $service->name }}</span>
                            <span class="font-semibold">RWF {{ number_format($service->price) }}</span>
                        </div>
                    @endforeach

                    <div class="flex justify-between py-2 border-b font-semibold">
                        <span>Total</span>
                        <span>RWF {{ number_format($booking->services->sum('price')) }}</span>
                    </div>

                    <div class="flex justify-between py-2 border-b text-green-600">
                        <span>Deposit Paid</span>
                        <span>RWF {{ number_format($booking->deposit_amount) }}</span>
                    </div>

                    <div class="flex justify-between py-3 text-red-600 font-bold text-lg">
                        <span>Remaining Balance</span>
                        <span>RWF {{ number_format($remainingAmount) }}</span>
                    </div>
                </div>
            </div>

            {{-- Payment Form --}}
            <form method="POST" action="{{ route('customer.booking.payRemainingPost', $booking->id) }}">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Payment Phone Number</label>
                    <input type="tel" 
                           name="phone" 
                           value="{{ $phone }}" 
                           required
                           placeholder="e.g. 0788xxxxxx"
                           class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Payment Method</label>
                    <div class="space-y-2">
                        <label class="block p-4 rounded-lg border-2 border-gray-200 cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition">
                            <input type="radio" name="payment_method" value="momo" required class="mr-3">
                            <span class="font-semibold">Mobile Money (MoMo)</span>
                        </label>
                        <label class="block p-4 rounded-lg border-2 border-gray-200 cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition">
                            <input type="radio" name="payment_method" value="card" class="mr-3">
                            <span class="font-semibold">Debit/Credit Card</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full px-6 py-4 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition">
                    <i class="ph ph-arrow-circle-right mr-2"></i>Pay Now
                </button>
            </form>
        </div>
    </div>
</div>

@endsection
