@extends('layouts.dashboard')

@section('title', 'Create Manual Booking')

@section('content')
<div class="p-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Create Manual Booking</h1>
        <p class="text-gray-600">Create a booking on behalf of a customer</p>
    </div>

    @if($errors->any())
        <div class="mb-6 bg-red-50 border-2 border-red-200 rounded-xl p-4">
            <ul class="text-sm text-red-800 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.bookings.manual.store') }}" class="max-w-4xl" id="bookingForm">
        @csrf

        <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6 space-y-6">
            {{-- Customer Selection or Creation --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Customer *</label>
                <div class="flex gap-2">
                    <select name="customer_id" id="customerSelect"
                            class="flex-1 px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                        <option value="">Select Customer</option>
                        <option value="new">➕ Create New Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->email }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- New Customer Form (Hidden by default) --}}
            <div id="newCustomerForm" class="hidden bg-blue-50 border-2 border-blue-200 rounded-xl p-4 space-y-4">
                <h3 class="font-bold text-blue-900 mb-2">New Customer Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Name *</label>
                        <input type="text" name="new_customer_name"
                               class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                        <input type="email" name="new_customer_email"
                               class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Phone</label>
                        <input type="tel" name="new_customer_phone"
                               class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Provider *</label>
                    <select name="provider_id" required
                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                        <option value="">Select Provider</option>
                        @foreach($providers as $provider)
                            <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Date *</label>
                    <input type="date" name="date" required min="{{ date('Y-m-d') }}"
                           class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Time *</label>
                    <input type="time" name="time" required
                           class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Services *</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($services as $service)
                        <label class="flex items-center gap-3 p-3 rounded-lg border-2 border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition cursor-pointer">
                            <input type="checkbox" name="services[]" value="{{ $service->id }}"
                                   class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <div class="flex-1">
                                <span class="text-sm font-medium text-gray-700">{{ $service->name }}</span>
                                <span class="text-xs text-green-600 ml-2">RWF {{ number_format($service->price) }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Payment Option *</label>
                    <select name="payment_option" required
                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                        <option value="full">Full Payment</option>
                        <option value="deposit">Deposit</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Deposit Amount</label>
                    <input type="number" name="deposit_amount" min="0" step="0.01" value="0"
                           class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Notes</label>
                <textarea name="notes" rows="3"
                          class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none resize-vertical"></textarea>
            </div>

            <div>
                <label class="flex items-center gap-3 p-4 bg-blue-50 rounded-lg border-2 border-blue-200 cursor-pointer">
                    <input type="checkbox" name="send_email" value="1" checked
                           class="w-5 h-5 text-blue-600 rounded border-blue-300 focus:ring-blue-500">
                    <div>
                        <span class="text-sm font-semibold text-blue-900">Send confirmation email to customer</span>
                        <p class="text-xs text-blue-700 mt-1">Customer will receive booking details via email</p>
                    </div>
                </label>
            </div>

            <div class="flex gap-3 pt-4 border-t">
                <a href="{{ route('dashboard.admin.bookings') }}"
                   class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    <i class="ph ph-check-circle mr-2"></i>Create Booking
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const customerSelect = document.getElementById('customerSelect');
    const newCustomerForm = document.getElementById('newCustomerForm');
    const nameInput = document.querySelector('input[name="new_customer_name"]');
    const emailInput = document.querySelector('input[name="new_customer_email"]');

    customerSelect.addEventListener('change', function() {
        if (this.value === 'new') {
            newCustomerForm.classList.remove('hidden');
            nameInput.required = true;
            emailInput.required = true;
        } else {
            newCustomerForm.classList.add('hidden');
            nameInput.required = false;
            emailInput.required = false;
        }
    });
});
</script>
@endpush
@endsection
