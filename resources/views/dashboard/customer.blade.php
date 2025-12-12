@extends('layouts.dashboard')

@section('title', 'Customer Dashboard')

@section('content')
<div class="p-6">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            👋 Welcome back, {{ Auth::user()->name }}!
        </h1>
        <p class="text-gray-600">Here are your {{ $status }} bookings.</p>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="bg-green-50 border-2 border-green-200 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <i class="ph ph-check-circle text-2xl text-green-600 mt-0.5"></i>
                <div class="flex-1">
                    <p class="text-green-800 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($bookings->isEmpty())
        {{-- Empty State --}}
        <div class="bg-blue-50 border-2 border-blue-200 rounded-2xl p-12 text-center">
            <i class="ph ph-calendar-plus text-6xl text-blue-300 mb-4"></i>
            <h3 class="text-2xl font-bold text-blue-900 mb-2">No bookings yet!</h3>
            <p class="text-blue-700 mb-6">Start your journey by booking your first service</p>
            <a href="{{ route('booking.step1') }}" 
               class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                <i class="ph ph-plus-circle mr-2"></i>Book a Service
            </a>
        </div>
    @else
        {{-- Bookings Table --}}
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-900 text-white">
                        <tr>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">#</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">Service</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">Provider</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">Time</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($bookings as $booking)
                            <tr class="text-center hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    #{{ $booking->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-medium text-gray-900">{{ $booking->service->name ?? 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-gray-700">{{ $booking->provider->name ?? 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $booking->date }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $booking->time }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($booking->status == 'pending')
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @elseif($booking->status == 'confirmed')
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Confirmed
                                        </span>
                                    @elseif($booking->status == 'completed')
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Completed
                                        </span>
                                    @elseif($booking->status == 'cancelled')
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            Cancelled
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('customer.booking.show', $booking->id) }}" 
                                           class="px-3 py-1.5 bg-blue-100 text-blue-700 text-sm font-semibold rounded-lg hover:bg-blue-200 transition">
                                            View
                                        </a>

                                        @if($booking->status == 'pending')
                                            <form action="{{ route('customer.booking.cancel', $booking->id) }}" 
                                                  method="POST" 
                                                  class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="px-3 py-1.5 bg-red-100 text-red-700 text-sm font-semibold rounded-lg hover:bg-red-200 transition"
                                                        onclick="return confirm('Are you sure you want to cancel this booking?')">
                                                    Cancel
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($bookings->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
