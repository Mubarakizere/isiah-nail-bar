@extends('layouts.dashboard')

@section('title', 'Settings')
@section('page-title', 'Site Settings')
@section('page-subtitle', 'Manage global configuration options')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900">Email Notifications</h2>
            <p class="text-sm text-gray-500 mt-1">Configure additional email addresses that should receive booking alerts.</p>
        </div>

        <form action="{{ route('admin.settings.store') }}" method="POST" class="p-6 space-y-6">
            @csrf
            
            <div>
                <label for="booking_alert_emails" class="block text-sm font-medium text-gray-700 mb-2">Additional Booking Alert Emails</label>
                <textarea 
                    id="booking_alert_emails" 
                    name="booking_alert_emails" 
                    rows="3" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-rose-500 focus:border-rose-500 @error('booking_alert_emails') border-red-500 @enderror"
                    placeholder="e.g. admin2@isaiahnailbar.com, manager@isaiahnailbar.com"
                >{{ old('booking_alert_emails', $settings['booking_alert_emails'] ?? '') }}</textarea>
                <p class="text-xs text-gray-500 mt-2">Enter multiple email addresses separated by commas (,). These emails will receive an alert whenever a new booking is made.</p>
                @error('booking_alert_emails')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-100">
                <button type="submit" class="px-6 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-xl shadow-sm shadow-rose-600/20 transition-colors flex items-center gap-2">
                    <i class="ph ph-check-circle text-lg"></i>
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
