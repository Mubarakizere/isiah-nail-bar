@extends('layouts.payment')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-lg mx-auto px-4">
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
            <h4 class="text-2xl font-bold text-gray-900 mb-4">Retry Payment for Booking #{{ $booking->id }}</h4>

            @if(session('error'))
                <div class="bg-red-50 border-2 border-red-200 rounded-lg p-4 mb-4">
                    <p class="text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('booking.retryPayment.post', $booking->id) }}">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                    <input type="tel" 
                           name="phone" 
                           required
                           placeholder="e.g. 0788123456"
                           class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Payment Method</label>
                    <select name="payment_method" 
                            required
                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                        <option value="momo">Mobile Money (MoMo)</option>
                        <option value="card">Debit/Credit Card</option>
                    </select>
                </div>

                <button type="submit" class="w-full px-6 py-4 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition">
                    Proceed to Payment
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
