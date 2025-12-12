@extends('layouts.dashboard')

@section('title', 'All Bookings')

@section('content')
<div class="p-6">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">All Bookings</h1>
                <p class="text-gray-600">Manage and track all customer bookings</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.emails.index') }}" 
                   class="px-4 py-2.5 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition">
                    <i class="ph ph-envelope-simple mr-2"></i>Email History
                </a>
                <a href="{{ route('admin.bookings.manual.create') }}" 
                   class="px-4 py-2.5 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                    <i class="ph ph-plus-circle mr-2"></i>Manual Booking
                </a>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('dashboard.admin.bookings') }}" class="bg-white rounded-2xl p-6 shadow-md border border-gray-200 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            {{-- Search --}}
            <div class="lg:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="ph ph-magnifying-glass mr-1"></i>Search
                </label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Customer or Service..." 
                       class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
            </div>

            {{-- Status Filter --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="ph ph-funnel mr-1"></i>Status
                </label>
                <select name="status" class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                    <option value="">All</option>
                    @foreach(['pending', 'accepted', 'declined', 'completed', 'cancelled'] as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Payment Status --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="ph ph-wallet mr-1"></i>Payment
                </label>
                <select name="payment_status" class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                    <option value="">All</option>
                    @foreach(['paid', 'pending', 'failed', 'unpaid'] as $paymentStatus)
                        <option value="{{ $paymentStatus }}" {{ request('payment_status') === $paymentStatus ? 'selected' : '' }}>
                            {{ ucfirst($paymentStatus) }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    <i class="ph ph-funnel mr-1"></i>Filter
                </button>
                <a href="{{ route('dashboard.admin.bookings') }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition flex items-center justify-center">
                    <i class="ph ph-arrow-counter-clockwise"></i>
                </a>
            </div>
        </div>

        {{-- Fetch Time --}}
        @if(isset($fetchTime))
            <div class="mt-3 text-right text-sm text-gray-500">
                <i class="ph ph-clock mr-1"></i>Loaded in {{ $fetchTime }}s
            </div>
        @endif
    </form>

    {{-- Table Card --}}
    <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">#</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="ph ph-user mr-1"></i>Customer
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="ph ph-scissors mr-1"></i>Services
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="ph ph-calendar-blank mr-1"></i>Date
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="ph ph-clock mr-1"></i>Time
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="ph ph-flag mr-1"></i>Status
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="ph ph-currency-circle-dollar mr-1"></i>Payment
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <i class="ph ph-gear mr-1"></i>Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($bookings as $booking)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #{{ $booking->id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $booking->customer->user->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                @if($booking->services && $booking->services->count())
                                    <div class="space-y-1">
                                        @foreach($booking->services as $s)
                                            <div class="flex items-center gap-1">
                                                <i class="ph ph-scissors text-gray-400 text-xs"></i>
                                                <span>{{ $s->name }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($booking->date)->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($booking->time)->format('h:i A') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                                    {{ match($booking->status) {
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'accepted' => 'bg-blue-100 text-blue-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'declined', 'cancelled' => 'bg-gray-100 text-gray-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    } }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $totalPaid = $booking->payments->where('status', 'paid')->sum('amount');
                                    $latestPayment = $booking->payments->sortByDesc('created_at')->first();
                                @endphp

                                @if ($totalPaid > 0)
                                    <div class="space-y-1">
                                        <div class="font-semibold text-gray-900 uppercase">{{ $latestPayment->method ?? '—' }}</div>
                                        <div class="text-gray-600">{{ number_format($totalPaid) }} RWF</div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">Paid</span>
                                    </div>
                                @elseif ($latestPayment)
                                    <div class="space-y-1">
                                        <div class="font-semibold text-gray-900 uppercase">{{ $latestPayment->method }}</div>
                                        <div class="text-gray-600">{{ number_format($latestPayment->amount) }} RWF</div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">{{ ucfirst($latestPayment->status) }}</span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800">Unpaid</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($booking->status === 'pending')
                                        <form method="POST" action="{{ route('dashboard.admin.update', $booking->id) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="accepted">
                                            <button class="px-3 py-1.5 bg-green-600 text-white text-xs font-semibold rounded-lg hover:bg-green-700 transition">
                                                <i class="ph ph-check mr-1"></i>Accept
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('dashboard.admin.update', $booking->id) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="declined">
                                            <button class="px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-200 transition">
                                                <i class="ph ph-x mr-1"></i>Decline
                                            </button>
                                        </form>
                                    @elseif($booking->status === 'accepted')
                                        <form method="POST" action="{{ route('dashboard.admin.update', $booking->id) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="completed">
                                            <button class="px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition">
                                                <i class="ph ph-check-circle mr-1"></i>Complete
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('booking.receipt', $booking->id) }}" 
                                       class="px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-200 transition"
                                       title="View Receipt">
                                        <i class="ph ph-receipt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <i class="ph ph-calendar-x text-5xl text-gray-300 mb-3"></i>
                                <p class="text-gray-500 font-semibold">No bookings found</p>
                                <p class="text-gray-400 text-sm mt-1">Try adjusting your filters</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($bookings->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $bookings->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
