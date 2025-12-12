@extends('layouts.dashboard')

@section('title', 'Email Details')

@section('content')
<div class="p-6">
    <div class="mb-8">
        <a href="{{ route('admin.emails.index') }}" class="text-blue-600 hover:text-blue-800 font-semibold">
            <i class="ph ph-arrow-left mr-2"></i>Back to Email History
        </a>
    </div>

    <div class="max-w-4xl">
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-8">
            {{-- Email Header --}}
            <div class="mb-6 pb-6 border-b border-gray-200">
                <div class="flex items-start justify-between mb-4">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $emailLog->subject }}</h1>
                    @if($emailLog->status === 'sent')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text- font-bold bg-green-100 text-green-800">
                            <i class="ph ph-check-circle mr-1"></i>Sent
                        </span>
                    @elseif($emailLog->status === 'failed')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-red-100 text-red-800">
                            <i class="ph ph-x-circle mr-1"></i>Failed
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-600 mb-1">Recipient</p>
                        <p class="font-semibold text-gray-900">{{ $emailLog->recipient_name }}</p>
                        <p class="text-gray-600">{{ $emailLog->recipient_email }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 mb-1">Type</p>
                        <p class="font-semibold text-gray-900">{{ ucwords(str_replace('_', ' ', $emailLog->email_type)) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 mb-1">Sent At</p>
                        <p class="font-semibold text-gray-900">
                            {{ $emailLog->sent_at ? $emailLog->sent_at->format('F j, Y \a\t g:i A') : 'Not sent' }}
                        </p>
                    </div>
                    @if($emailLog->booking_id)
                        <div>
                            <p class="text-gray-600 mb-1">Related Booking</p>
                            <a href="{{ route('booking.receipt', $emailLog->booking_id) }}" 
                               class="font-semibold text-blue-600 hover:text-blue-800">
                                Booking #{{ $emailLog->booking_id }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Email Metadata --}}
            @if($emailLog->metadata)
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <h3 class="font-bold text-gray-900 mb-3">Email Metadata</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <pre class="text-sm text-gray-700">{{ json_encode($emailLog->metadata, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            @endif

            {{-- Error Message (if failed) --}}
            @if($emailLog->status === 'failed' && $emailLog->error_message)
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <h3 class="font-bold text-red-900 mb-3">Error Message</h3>
                    <div class="bg-red-50 border-2 border-red-200 rounded-lg p-4">
                        <p class="text-sm text-red-800">{{ $emailLog->error_message }}</p>
                    </div>
                </div>
            @endif

            {{-- Booking Details (if available) --}}
            @if($emailLog->booking)
                <div class="mb-6">
                    <h3 class="font-bold text-gray-900 mb-3">Booking Information</h3>
                    <div class="bg-blue-50 rounded-lg p-4 space-y-2 text-sm">
                        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($emailLog->booking->date)->format('F j, Y') }}</p>
                        <p><strong>Time:</strong> {{ $emailLog->booking->time }}</p>
                        <p><strong>Customer:</strong> {{ $emailLog->booking->customer->name }}</p>
                        <p><strong>Provider:</strong> {{ $emailLog->booking->provider->name }}</p>
                        <p><strong>Status:</strong> <span class="capitalize">{{ $emailLog->booking->status }}</span></p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
