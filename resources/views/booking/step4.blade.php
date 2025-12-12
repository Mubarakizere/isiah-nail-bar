@extends('layouts.public')

@section('title', 'Confirm & Pay')

@section('content')


@php
    $totalPrice = $services->sum('price');
    $depositAmount = round($totalPrice * 0.4);
    $isLocal = app()->environment('local');
@endphp

{{-- Hero Header --}}
<div class="bg-gray-900 py-12 relative overflow-hidden">
    <div class="absolute inset-0 opacity-20">
        <img src="{{ asset('storage/banner.jpg') }}" alt="" class="w-full h-full object-cover">
    </div>
    <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-transparent"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <span class="text-rose-400 font-medium tracking-widest text-xs uppercase mb-2 block">Step 4 of 4</span>
        <h1 class="text-3xl md:text-4xl font-serif text-white mb-2">Final Step</h1>
        <p class="text-gray-400 font-light text-lg">Review your selection and secure your appointment.</p>
    </div>
</div>

<div class="bg-white min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @if ($isLocal)
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-8 text-center flex items-center justify-center gap-3">
                <i class="ph ph-warning text-amber-600 text-xl"></i>
                <p class="text-amber-800 text-sm font-medium">Payment simulation mode active (Local Environment).</p>
            </div>
        @endif

        <form method="POST" action="{{ route('booking.step4.submit') }}">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                
                {{-- Main Content --}}
                <div class="lg:col-span-8 space-y-8">
                    
                    {{-- Payment Options --}}
                    <div>
                        <h3 class="text-lg font-serif text-gray-900 mb-6 flex items-center gap-2">
                             <span class="w-8 h-8 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-bold">1</span>
                             Select Payment Amount
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Full Payment --}}
                            <label class="payment-option group relative cursor-pointer block">
                                <input type="radio" name="payment_option" value="full" class="peer hidden" checked>
                                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm transition-all duration-300 peer-checked:border-gray-900 peer-checked:ring-1 peer-checked:ring-gray-900 peer-checked:bg-gray-50 h-full">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center group-hover:bg-rose-50 transition-colors">
                                            <i class="ph ph-check-circle text-xl text-gray-500 group-hover:text-rose-600 transition-colors"></i>
                                        </div>
                                        <span class="text-lg font-bold text-gray-900">RWF {{ number_format($totalPrice) }}</span>
                                    </div>
                                    <h4 class="font-serif text-lg text-gray-900 mb-1">Pay in Full</h4>
                                    <p class="text-sm text-gray-500">Settle the entire amount now for a seamless experience on arrival.</p>
                                </div>
                            </label>

                            {{-- Deposit --}}
                            <label class="payment-option group relative cursor-pointer block">
                                <input type="radio" name="payment_option" value="deposit" class="peer hidden">
                                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm transition-all duration-300 peer-checked:border-gray-900 peer-checked:ring-1 peer-checked:ring-gray-900 peer-checked:bg-gray-50 h-full">
                                     <div class="flex justify-between items-start mb-4">
                                        <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center group-hover:bg-rose-50 transition-colors">
                                            <i class="ph ph-percent text-xl text-gray-500 group-hover:text-rose-600 transition-colors"></i>
                                        </div>
                                        <span class="text-lg font-bold text-gray-900">RWF {{ number_format($depositAmount) }}</span>
                                    </div>
                                    <h4 class="font-serif text-lg text-gray-900 mb-1">Deposit Only (40%)</h4>
                                    <p class="text-sm text-gray-500">Secure your slot now and pay the remaining balance at the salon.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Contact Info --}}
                    <div>
                        <h3 class="text-lg font-serif text-gray-900 mb-6 flex items-center gap-2">
                             <span class="w-8 h-8 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-bold">2</span>
                             Confirm Contact
                        </h3>
                        <div class="bg-white rounded-2xl p-6 border border-gray-200">
                            <label class="block text-sm font-medium text-gray-700 mb-2">MoMo Payment / Contact Number</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 gap-2 flex items-center border-r border-gray-200 pr-3">
                                    <i class="ph ph-phone"></i> +250
                                </span>
                                <input type="tel" 
                                       name="payment_phone" 
                                       value="{{ auth()->user()->phone ?? '' }}"
                                       required
                                       class="w-full pl-24 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-all font-medium text-gray-900"
                                       placeholder="7XX XXX XXX">
                            </div>
                            <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                <i class="ph ph-info"></i> This number will be used for payment processing and booking updates.
                            </p>
                        </div>
                    </div>

                    {{-- Payment Method --}}
                    <div>
                        <h3 class="text-lg font-serif text-gray-900 mb-6 flex items-center gap-2">
                             <span class="w-8 h-8 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-bold">3</span>
                             Payment Method
                        </h3>
                        <div class="space-y-4">
                            <label class="flex items-center gap-4 p-4 rounded-xl border border-gray-200 bg-white cursor-pointer hover:border-gray-900 transition-all group">
                                <input type="radio" name="payment_method" value="momo" required class="peer hidden" checked>
                                <div class="w-5 h-5 rounded-full border border-gray-300 flex items-center justify-center peer-checked:border-gray-900 peer-checked:bg-gray-900">
                                    <div class="w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100"></div>
                                </div>
                                <div class="flex-1">
                                    <span class="font-medium text-gray-900 block group-hover:text-rose-600 transition-colors">Mobile Money (MTN/Airtel)</span>
                                    <span class="text-xs text-gray-500">Fast and secure local payment.</span>
                                </div>
                                <i class="ph ph-device-mobile text-2xl text-gray-400"></i>
                            </label>

                             <label class="flex items-center gap-4 p-4 rounded-xl border border-gray-200 bg-white cursor-pointer hover:border-gray-900 transition-all group">
                                <input type="radio" name="payment_method" value="card" class="peer hidden">
                                <div class="w-5 h-5 rounded-full border border-gray-300 flex items-center justify-center peer-checked:border-gray-900 peer-checked:bg-gray-900">
                                    <div class="w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100"></div>
                                </div>
                                <div class="flex-1">
                                    <span class="font-medium text-gray-900 block group-hover:text-rose-600 transition-colors">Debit / Credit Card</span>
                                    <span class="text-xs text-gray-500">Secure simulated card payment (Stripe/Paypal).</span>
                                </div>
                                <div class="flex gap-2">
                                     <i class="ph ph-credit-card text-2xl text-gray-400"></i>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-8 mt-8 border-t border-gray-100">
                        <a href="{{ route('booking.step3') }}" class="text-gray-500 hover:text-gray-900 font-medium flex items-center gap-2 transition-colors">
                            <i class="ph ph-arrow-left"></i> Back to Time
                        </a>
                    </div>
                </div>

                {{-- Sidebar (Desktop) --}}
                <div class="hidden lg:block lg:col-span-4 pl-4">
                    <div class="sticky top-24">
                        <div class="bg-gray-900 text-white rounded-2xl p-6 shadow-xl overflow-hidden relative">
                            <div class="absolute top-0 right-0 p-4 opacity-10">
                                <i class="ph ph-receipt text-9xl text-white"></i>
                            </div>
                            
                            <h3 class="text-lg font-serif mb-6 relative z-10">Final Summary</h3>
                            
                            {{-- Details --}}
                            <div class="space-y-4 mb-6 pb-6 border-b border-gray-700 relative z-10 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Date</span>
                                    <span class="text-white font-medium">{{ session('booking.date') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Time</span>
                                    <span class="text-white font-medium">{{ \Carbon\Carbon::createFromFormat('H:i', session('booking.time'))->format('h:i A') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Artist</span>
                                    <span class="text-rose-400 font-medium">{{ $provider->name }}</span>
                                </div>
                            </div>

                            {{-- Services --}}
                            <div class="space-y-2 mb-6 relative z-10 max-h-[25vh] overflow-y-auto custom-scrollbar pr-2">
                                @foreach($services as $service)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-300">{{ $service->name }}</span>
                                        <span class="text-gray-500">{{ number_format($service->price) }}</span>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="border-t border-gray-700 pt-4 mb-6 relative z-10">
                                <div class="flex justify-between items-end mb-2">
                                    <span class="text-gray-400 text-sm">Total Amount</span>
                                    <span class="text-xl font-serif text-white">RWF {{ number_format($totalPrice) }}</span>
                                </div>
                                <div class="flex justify-between items-end">
                                    <span class="text-rose-400 text-sm font-medium">To Pay Now</span>
                                    <span  id="payAmountDisplay" class="text-2xl font-serif text-rose-400 font-bold">RWF {{ number_format($totalPrice) }}</span>
                                </div>
                            </div>

                            <button type="submit" 
                                    id="payNowBtn"
                                    {{ $isLocal ? 'disabled' : '' }}
                                    class="w-full py-4 bg-white text-gray-900 font-bold rounded-xl hover:bg-rose-50 transition-all flex items-center justify-center gap-2 group relative z-10 {{ $isLocal ? 'opacity-75 cursor-not-allowed' : '' }}">
                                <i class="ph ph-lock-key"></i>
                                <span>Pay Securely</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Mobile FAB --}}
<div class="lg:hidden fixed bottom-6 right-6 z-40">
    <button type="submit" id="mobilePayBtn" class="w-16 h-16 bg-gray-900 text-white rounded-full shadow-2xl flex items-center justify-center hover:bg-rose-600 transition-colors">
        <i class="ph ph-check text-2xl"></i>
    </button>
</div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('input[name="payment_option"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const val = this.value;
        const total = {{ $totalPrice }};
        const deposit = {{ $depositAmount }};
        const display = document.getElementById('payAmountDisplay');
        
        if (val === 'deposit') {
            display.textContent = 'RWF ' + deposit.toLocaleString();
        } else {
            display.textContent = 'RWF ' + total.toLocaleString();
        }
    });
});
</script>
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
