@extends('layouts.public')

@section('title', 'Frequently Asked Questions')

@section('content')

{{-- Header --}}
<div class="bg-blue-600 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-white mb-3">Frequently Asked Questions</h1>
        <p class="text-lg text-blue-100">Find answers to common questions about booking, payments, and policies</p>
    </div>
</div>

<div class="bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- First-Time Offer --}}
        <div class="bg-yellow-50 border-2 border-yellow-300 rounded-xl p-6 mb-8 text-center">
            <p class="text-lg font-bold text-yellow-900">
                🎉 <span class="text-yellow-800">First-Time Clients</span> get <span class="text-yellow-800">10% OFF</span> your first appointment!
            </p>
        </div>

        {{-- FAQ Items --}}
        <div class="space-y-4">
            {{-- Q1 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <button type="button" onclick="toggleFaq('faq1')" class="w-full px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-50 transition flex items-center justify-between">
                    <span class="flex items-center gap-3">
                        <i class="ph ph-calendar-check text-blue-600"></i>
                        Is a deposit required to book an appointment?
                    </span>
                    <i class="ph ph-caret-down transition-transform" id="icon-faq1"></i>
                </button>
                <div id="faq1" class="hidden px-6 py-4 border-t bg-gray-50">
                    <p class="text-gray-700">Yes, a <strong>non-refundable deposit</strong> of <strong>40%</strong> is required to secure your booking. This amount will be deducted from your final payment.</p>
                </div>
            </div>

            {{-- Q2 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <button type="button" onclick="toggleFaq('faq2')" class="w-full px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-50 transition flex items-center justify-between">
                    <span class="flex items-center gap-3">
                        <i class="ph ph-credit-card text-blue-600"></i>
                        What are the available payment methods?
                    </span>
                    <i class="ph ph-caret-down transition-transform" id="icon-faq2"></i>
                </button>
                <div id="faq2" class="hidden px-6 py-4 border-t bg-gray-50">
                    <p class="text-gray-700">We accept payments via <strong>MTN Mobile Money</strong>, <strong>Cash</strong>, or <strong>Bank Transfer</strong> depending on service type. Payment instructions will be provided after confirming the booking.</p>
                </div>
            </div>

            {{-- Q3 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <button type="button" onclick="toggleFaq('faq3')" class="w-full px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-50 transition flex items-center justify-between">
                    <span class="flex items-center gap-3">
                        <i class="ph ph-clock-afternoon text-blue-600"></i>
                        What happens if I'm late to my appointment?
                    </span>
                    <i class="ph ph-caret-down transition-transform" id="icon-faq3"></i>
                </button>
                <div id="faq3" class="hidden px-6 py-4 border-t bg-gray-50">
                    <p class="text-gray-700">If you are more than <strong>15 minutes late</strong>, your appointment may be canceled and your deposit forfeited. Please arrive on time to avoid disruptions.</p>
                </div>
            </div>

            {{-- Q4 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <button type="button" onclick="toggleFaq('faq4')" class="w-full px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-50 transition flex items-center justify-between">
                    <span class="flex items-center gap-3">
                        <i class="ph ph-x-circle text-blue-600"></i>
                        Can I cancel or reschedule my booking?
                    </span>
                    <i class="ph ph-caret-down transition-transform" id="icon-faq4"></i>
                </button>
                <div id="faq4" class="hidden px-6 py-4 border-t bg-gray-50">
                    <p class="text-gray-700">Yes, but cancellations must be made <strong>at least 48 hours</strong> before the appointment. Otherwise, the deposit will be forfeited. Rescheduling is allowed within the same notice period.</p>
                </div>
            </div>

            {{-- Q5 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <button type="button" onclick="toggleFaq('faq5')" class="w-full px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-50 transition flex items-center justify-between">
                    <span class="flex items-center gap-3">
                        <i class="ph ph-users-three text-blue-600"></i>
                        Can I bring someone with me to my appointment?
                    </span>
                    <i class="ph ph-caret-down transition-transform" id="icon-faq5"></i>
                </button>
                <div id="faq5" class="hidden px-6 py-4 border-t bg-gray-50">
                    <p class="text-gray-700">Yes, you may bring one guest. However, they must remain quiet and respectful to maintain a relaxing atmosphere for all clients.</p>
                </div>
            </div>
        </div>

        {{-- CTA --}}
        <div class="text-center mt-12">
            <p class="text-gray-600 mb-6">Still have questions?</p>
            <div class="flex flex-wrap gap-4 justify-center">
                <a href="{{ url('/contact') }}" class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    Contact Us
                </a>
                <a href="{{ route('booking.step1') }}" class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">
                    Book Now
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function toggleFaq(id) {
    const content = document.getElementById(id);
    const icon = document.getElementById('icon-' + id);
    content.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
}
</script>
@endpush
