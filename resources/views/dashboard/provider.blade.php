@extends('layouts.dashboard')

@section('title', 'Artist Studio')
@section('page-subtitle', 'Manage your schedule and track your performance.')

@section('content')
<div class="space-y-8">
    
    {{-- Personal Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        {{-- Total Bookings --}}
        <div class="bg-white rounded-2xl p-6 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.1)] border border-gray-100 group hover:border-gray-200 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-violet-50 flex items-center justify-center text-violet-500 group-hover:scale-110 transition-transform">
                    <i class="ph ph-calendar-check text-2xl"></i>
                </div>
                <span class="text-xs font-semibold text-gray-400 bg-gray-50 px-2 py-0.5 rounded-full">All Time</span>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 font-serif">Total Bookings</p>
                <h3 class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($total) }}</h3>
            </div>
        </div>

        {{-- Completion Rate --}}
        <div class="bg-white rounded-2xl p-6 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.1)] border border-gray-100 group hover:border-gray-200 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-teal-50 flex items-center justify-center text-teal-500 group-hover:scale-110 transition-transform">
                    <i class="ph ph-check-circle text-2xl"></i>
                </div>
                <span class="text-xs font-semibold text-teal-600 bg-teal-50 px-2 py-0.5 rounded-full">Great Job!</span>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 font-serif">Completion Rate</p>
                <h3 class="text-3xl font-bold text-gray-900 mt-1">{{ $rate }}%</h3>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-50">
                 <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-teal-500 h-1.5 rounded-full" style="width: {{ $rate }}%"></div>
                </div>
            </div>
        </div>

        {{-- Personal Earnings --}}
        <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl p-6 shadow-xl text-white relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-5">
                <i class="ph ph-sparkle text-9xl"></i>
            </div>
             <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center backdrop-blur-sm">
                        <i class="ph ph-wallet text-2xl text-white"></i>
                    </div>
                     <span class="text-xs font-semibold text-gray-300 bg-white/10 px-2 py-0.5 rounded-full">This Period</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-400 font-serif">Personal Earnings</p>
                    <h3 class="text-3xl font-bold text-white mt-1">RWF {{ number_format($revenue) }}</h3>
                </div>
                 <div class="mt-4 pt-4 border-t border-gray-700 flex justify-end">
                    <a href="{{ route('provider.earnings.pdf') }}" class="text-xs flex items-center gap-1 hover:text-white text-gray-300 transition-colors">
                        <i class="ph ph-file-pdf"></i> Download Statement
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- Main Section: Upcoming Agenda --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900 font-serif">Today's Agenda</h2>
                <span class="text-sm text-gray-500">{{ now()->format('l, F j') }}</span>
            </div>
            
            <div class="bg-white rounded-3xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)] border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider font-serif">Time</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider font-serif">Client</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider font-serif">Service</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider font-serif">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider font-serif">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                             @forelse($myBookings as $booking)
                                <tr class="group hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-mono font-medium text-gray-900">{{ \Carbon\Carbon::parse($booking->time)->format('H:i') }}</span>
                                        <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($booking->date)->isToday() ? 'Today' : \Carbon\Carbon::parse($booking->date)->format('M d') }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 font-bold text-xs ring-2 ring-white">
                                                {{ strtoupper(substr($booking->customer->user->name ?? 'G', 0, 1)) }}
                                            </div>
                                            <span class="font-medium text-gray-900">{{ $booking->customer->user->name ?? 'Guest' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                             @foreach($booking->services->take(1) as $service)
                                                 <span class="text-sm text-gray-700">{{ $service->name }}</span>
                                             @endforeach
                                             @if($booking->services->count() > 1)
                                                <span class="text-xs bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded-md">+{{ $booking->services->count() - 1 }} more</span>
                                             @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                         @php
                                            $statusStyles = match($booking->status) {
                                                'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
                                                'accepted' => 'bg-blue-50 text-blue-700 border-blue-100',
                                                'completed' => 'bg-green-50 text-green-700 border-green-100',
                                                'cancelled', 'declined' => 'bg-gray-50 text-gray-600 border-gray-100',
                                                default => 'bg-gray-50 text-gray-600 border-gray-100'
                                            };
                                        @endphp
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusStyles }}">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                             @if($booking->status === 'pending')
                                                <form method="POST" action="{{ route('provider.booking.action', ['id' => $booking->id, 'action' => 'accept']) }}">
                                                    @csrf
                                                    <button class="p-2 bg-white border border-gray-200 rounded-lg text-green-600 hover:bg-green-50 transition-colors" title="Accept">
                                                        <i class="ph ph-check"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('provider.booking.action', ['id' => $booking->id, 'action' => 'decline']) }}">
                                                    @csrf
                                                    <button class="p-2 bg-white border border-gray-200 rounded-lg text-red-600 hover:bg-red-50 transition-colors" title="Decline">
                                                        <i class="ph ph-x"></i>
                                                    </button>
                                                </form>
                                            @elseif($booking->status === 'accepted')
                                                <form method="POST" action="{{ route('provider.booking.complete', $booking->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="px-3 py-1.5 bg-gray-900 text-white text-xs font-bold rounded-lg hover:bg-gray-800 transition-colors">
                                                        Complete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="ph ph-calendar-blank text-4xl text-gray-300 mb-2"></i>
                                            <p>No bookings for today.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- Pagination if needed, or View All Link --}}
                <div class="p-4 border-t border-gray-100 bg-gray-50/50 text-center">
                    <a href="{{ route('provider.calendar') }}" class="text-sm font-medium text-gray-600 hover:text-rose-600 transition-colors">View Monthly Calendar &rarr;</a>
                </div>
            </div>
        </div>

        {{-- Side Section: Earnings Chart --}}
        <div class="lg:col-span-1">
             <div class="bg-white rounded-3xl p-6 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)] border border-gray-100 h-full">
                <h3 class="text-lg font-bold text-gray-900 font-serif mb-6">Revenue Trend</h3>
                
                @if($chartData->sum('total') == 0)
                     <div class="h-48 flex flex-col items-center justify-center bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                        <i class="ph ph-chart-line-up text-3xl text-gray-300 mb-2"></i>
                        <p class="text-xs text-gray-500">No data for this week</p>
                    </div>
                @else
                    <div class="h-64">
                         <canvas id="earningsChart"></canvas>
                    </div>
                @endif
                
                <div class="mt-6 space-y-3">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <span class="text-sm text-gray-600">This Week</span>
                        <span class="font-bold text-gray-900">RWF {{ number_format($chartData->sum('total')) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('earningsChart');
    if (ctx) {
         // Gradient styling
        const chartCtx = ctx.getContext('2d');
        const gradient = chartCtx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(37, 99, 235, 0.2)'); // Blue-600
        gradient.addColorStop(1, 'rgba(37, 99, 235, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData->pluck('label')) !!},
                datasets: [{
                    label: 'Revenue',
                    data: {!! json_encode($chartData->pluck('total')) !!},
                    borderColor: '#2563eb',
                    backgroundColor: gradient,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                         backgroundColor: '#111827',
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: (context) => 'RWF ' + context.raw.toLocaleString()
                        }
                    }
                },
                scales: {
                    y: {
                        display: false,
                        beginAtZero: true
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                             font: { size: 10 },
                             color: '#9ca3af'
                        }
                    }
                }
            }
        });
    }
</script>
@endpush
