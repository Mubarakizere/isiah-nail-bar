@extends('layouts.dashboard')

@section('title', 'All Bookings')

@section('content')
<div class="p-6">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-1">All Bookings</h1>
                <p class="text-gray-600 text-sm">Manage customer bookings</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.emails.index') }}" 
                   class="px-3 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
                    Email History
                </a>
                <a href="{{ route('admin.bookings.manual.create') }}" 
                   class="px-3 py-2 bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">
                    + New Booking
                </a>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('dashboard.admin.bookings') }}" class="bg-white border border-gray-300 p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            {{-- Search --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Search Customer</label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Search by name..." 
                       class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-blue-500">
            </div>

            {{-- Status Filter --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Booking Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-blue-500">
                    <option value="">All Statuses</option>
                    @foreach(['pending', 'accepted', 'declined', 'completed', 'cancelled'] as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Payment Status --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Payment Status</label>
                <select name="payment_status" class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-blue-500">
                    <option value="">All Payments</option>
                    @foreach(['paid', 'pending', 'failed', 'unpaid'] as $paymentStatus)
                        <option value="{{ $paymentStatus }}" {{ request('payment_status') === $paymentStatus ? 'selected' : '' }}>
                            {{ ucfirst($paymentStatus) }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-2 items-end">
                <button type="submit" class="flex-1 px-3 py-2 bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
                    Filter
                </button>
                <a href="{{ route('dashboard.admin.bookings') }}" class="px-3 py-2 bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white border border-gray-300">
        <table class="w-full">
            <thead class="bg-gray-100 border-b border-gray-300">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Customer</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Date & Time</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Payment</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Method</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($bookings as $booking)
                    @php
                        $totalPaid = $booking->payments->where('status', 'paid')->sum('amount');
                        $latestPayment = $booking->payments->sortByDesc('created_at')->first();
                    @endphp
                    <tr class="hover:bg-gray-50">
                        {{-- ID --}}
                        <td class="px-4 py-3 text-sm text-gray-900">
                            #{{ $booking->id }}
                        </td>
                        
                        {{-- Customer --}}
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ $booking->customer->user->name ?? '—' }}
                        </td>
                        
                        {{-- Date & Time --}}
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <div>{{ \Carbon\Carbon::parse($booking->date)->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($booking->time)->format('h:i A') }}</div>
                        </td>
                        
                        {{-- Booking Status --}}
                        <td class="px-4 py-3">
                            @php
                                $statusColors = [
                                    'pending' => 'background: #FEF3C7; color: #92400E;',
                                    'accepted' => 'background: #DBEAFE; color: #1E40AF;',
                                    'completed' => 'background: #D1FAE5; color: #065F46;',
                                    'declined' => 'background: #FEE2E2; color: #991B1B;',
                                    'cancelled' => 'background: #F3F4F6; color: #374151;',
                                ];
                            @endphp
                            <span style="{{ $statusColors[$booking->status] ?? $statusColors['cancelled'] }} padding: 4px 8px; font-size: 11px; font-weight: 600; text-transform: uppercase;">
                                {{ $booking->status }}
                            </span>
                        </td>
                        
                        {{-- Payment Status --}}
                        <td class="px-4 py-3">
                            @if ($totalPaid > 0)
                                {{-- PAID --}}
                                <div>
                                    <span style="background: #D1FAE5; color: #065F46; padding: 4px 8px; font-size: 11px; font-weight: 600; text-transform: uppercase;">
                                        ✓ PAID
                                    </span>
                                    <div class="text-xs text-gray-600 mt-1">{{ number_format($totalPaid) }} RWF</div>
                                </div>
                            @elseif ($latestPayment && $latestPayment->status === 'failed')
                                {{-- FAILED --}}
                                <div>
                                    <span style="background: #FEE2E2; color: #991B1B; padding: 4px 8px; font-size: 11px; font-weight: 600; text-transform: uppercase;">
                                        ✗ FAILED
                                    </span>
                                    <div class="text-xs text-gray-600 mt-1">{{ number_format($latestPayment->amount) }} RWF</div>
                                </div>
                            @elseif ($latestPayment && $latestPayment->status === 'pending')
                                {{-- PENDING --}}
                                <div>
                                    <span style="background: #FEF3C7; color: #92400E; padding: 4px 8px; font-size: 11px; font-weight: 600; text-transform: uppercase;">
                                        ⏳ PENDING
                                    </span>
                                    <div class="text-xs text-gray-600 mt-1">{{ number_format($latestPayment->amount) }} RWF</div>
                                </div>
                            @else
                                {{-- UNPAID --}}
                                <span style="background: #F3F4F6; color: #6B7280; padding: 4px 8px; font-size: 11px; font-weight: 600; text-transform: uppercase;">
                                    UNPAID
                                </span>
                            @endif
                        </td>
                        
                        {{-- Payment Method --}}
                        <td class="px-4 py-3">
                            @php
                                $paidPayment = $booking->payments->where('status', 'paid')->first();
                                $paymentMethod = $paidPayment->method ?? null;
                            @endphp
                            @if($paymentMethod)
                                @if(strtolower($paymentMethod) === 'card')
                                    <span style="background: #DBEAFE; color: #1E40AF; padding: 4px 8px; font-size: 11px; font-weight: 600;">
                                        💳 CARD
                                    </span>
                                @elseif(strtolower($paymentMethod) === 'momo')
                                    <span style="background: #FEF3C7; color: #92400E; padding: 4px 8px; font-size: 11px; font-weight: 600;">
                                        📱 MOMO
                                    </span>
                                @else
                                    <span class="text-xs text-gray-500">{{ strtoupper($paymentMethod) }}</span>
                                @endif
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        
                        {{-- Actions --}}
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                @if($booking->status === 'pending')
                                    <form method="POST" action="{{ route('dashboard.admin.update', $booking->id) }}" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="accepted">
                                        <button class="px-2 py-1 bg-green-600 text-white text-xs font-medium hover:bg-green-700" title="Accept">
                                            Accept
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('dashboard.admin.update', $booking->id) }}" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="declined">
                                        <button class="px-2 py-1 bg-red-600 text-white text-xs font-medium hover:bg-red-700" title="Decline">
                                            Decline
                                        </button>
                                    </form>
                                @elseif($booking->status === 'accepted')
                                    <form method="POST" action="{{ route('dashboard.admin.update', $booking->id) }}" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="completed">
                                        <button class="px-2 py-1 bg-blue-600 text-white text-xs font-medium hover:bg-blue-700" title="Mark Complete">
                                            Complete
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('booking.receipt', $booking->id) }}" 
                                   class="px-2 py-1 bg-gray-200 text-gray-700 text-xs font-medium hover:bg-gray-300" 
                                   title="View Receipt">
                                    Receipt
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                            <div class="text-4xl mb-2">📅</div>
                            <p class="font-medium">No bookings found</p>
                            <p class="text-sm text-gray-400 mt-1">Try adjusting your filters</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($bookings->hasPages())
        <div class="mt-4">
            {{ $bookings->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
