@extends('layouts.dashboard')

@section('title', 'Provider Performance Report')

@section('content')
<div class="p-6">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Provider Performance</h1>
                <p class="text-gray-600">{{ $provider->user->name ?? 'Provider' }}'s detailed performance metrics</p>
            </div>
            <a href="{{ route('dashboard.admin.reports.pdf') }}" 
               class="px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition shadow-lg">
                <i class="ph ph-download-simple mr-2"></i>Download PDF
            </a>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Total Bookings --}}
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="ph ph-calendar-check text-2xl"></i>
                </div>
            </div>
            <h3 class="text-3xl font-bold mb-1">{{ number_format($totalBookings) }}</h3>
            <p class="text-blue-100 text-sm">Total Bookings</p>
        </div>

        {{-- Completed Bookings --}}
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="ph ph-check-circle text-2xl"></i>
                </div>
            </div>
            <h3 class="text-3xl font-bold mb-1">{{ number_format($completedBookings) }}</h3>
            <p class="text-green-100 text-sm">Completed</p>
        </div>

        {{-- Completion Rate --}}
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="ph ph-percent text-2xl"></i>
                </div>
            </div>
            <h3 class="text-3xl font-bold mb-1">{{ $completionRate }}%</h3>
            <p class="text-purple-100 text-sm">Completion Rate</p>
            <div class="mt-3">
                <div class="w-full bg-white/20 rounded-full h-2">
                    <div class="bg-white rounded-full h-2 transition-all" style="width: {{ $completionRate }}%"></div>
                </div>
            </div>
        </div>

        {{-- Total Revenue --}}
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="ph ph-currency-circle-dollar text-2xl"></i>
                </div>
            </div>
            <h3 class="text-3xl font-bold mb-1">RWF {{ number_format($totalRevenue) }}</h3>
            <p class="text-orange-100 text-sm">Total Revenue</p>
        </div>
    </div>

    {{-- Revenue Chart --}}
    <div class="bg-white rounded-2xl p-6 shadow-md border border-gray-200">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ph ph-chart-line-up text-blue-600"></i>
                Revenue Over Last 7 Days
            </h2>
            <p class="text-sm text-gray-600 mt-1">Daily revenue breakdown</p>
        </div>

        <div style="height: 350px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartData = @json($chartData);
    const labels = chartData.map(item => item.date);
    const totals = chartData.map(item => item.total);

    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue (RWF)',
                data: totals,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 5,
                pointBackgroundColor: '#2563eb',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 7,
                pointHoverBackgroundColor: '#1d4ed8',
                pointHoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    display: false 
                },
                tooltip: {
                    backgroundColor: '#1f2937',
                    padding: 12,
                    borderColor: '#374151',
                    borderWidth: 1,
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: RWF ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f3f4f6',
                        borderDash: [5, 5]
                    },
                    ticks: {
                        callback: value => 'RWF ' + value.toLocaleString(),
                        font: {
                            size: 11
                        },
                        color: '#6b7280'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        },
                        color: '#6b7280'
                    }
                }
            }
        }
    });
</script>
@endpush
