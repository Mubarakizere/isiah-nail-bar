@extends('layouts.dashboard')

@section('title', 'Dashboard Overview')
@section('page-subtitle', 'Real-time insight into your salon\'s performance')

@section('content')
<div class="space-y-6">
    
    {{-- High-Level Metrics --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        
        {{-- Total Revenue --}}
        <div class="bg-white border-2 border-gray-300 p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 bg-rose-100 flex items-center justify-center text-rose-600">
                    <i class="ph ph-currency-circle-dollar text-xl"></i>
                </div>
                <span class="text-xs font-semibold text-green-700 bg-green-100 px-2 py-1">
                    +12%
                </span>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Revenue</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalRevenue) }} RWF</h3>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-200 flex justify-between text-xs">
                <span class="text-gray-600">MOMO: <span class="font-semibold text-gray-900">{{ number_format($momoRevenue) }}</span></span>
                <span class="text-gray-600">Card: <span class="font-semibold text-gray-900">{{ number_format($cardRevenue) }}</span></span>
            </div>
        </div>

        {{-- Bookings --}}
        <div class="bg-white border-2 border-gray-300 p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 bg-blue-100 flex items-center justify-center text-blue-600">
                    <i class="ph ph-calendar-check text-xl"></i>
                </div>
                <span class="text-xs font-semibold text-gray-600 bg-gray-100 px-2 py-1">
                   All Time
                </span>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Bookings</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalBookings) }}</h3>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="flex items-center gap-2">
                    <div class="flex-1 bg-gray-200 h-2">
                        <div class="bg-blue-600 h-2" style="width: 75%"></div>
                    </div>
                    <span class="text-xs text-gray-600 font-medium">75%</span>
                </div>
            </div>
        </div>

        {{-- Active Customers --}}
        <div class="bg-white border-2 border-gray-300 p-5">
            <div class="mb-4">
                <div class="w-10 h-10 bg-purple-100 flex items-center justify-center text-purple-600">
                    <i class="ph ph-users-three text-xl"></i>
                </div>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Active Clients</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalCustomers) }}</h3>
            </div>
            <p class="text-xs text-gray-500 mt-4 pt-4 border-t border-gray-200">
                <i class="ph ph-heart-straight text-rose-500 mr-1"></i>
                Most loyal: Sarah K.
            </p>
        </div>

        {{-- Net Income --}}
        <div class="bg-gray-900 border-2 border-gray-900 p-5 text-white">
            <div class="mb-4">
                <div class="w-10 h-10 bg-white bg-opacity-20 flex items-center justify-center">
                    <i class="ph ph-wallet text-xl text-white"></i>
                </div>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Net Income (After Tax)</p>
                <h3 class="text-2xl font-bold text-white mt-1">{{ number_format($revenueAfterTax) }} RWF</h3>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-700 flex justify-between text-xs">
                <span class="text-gray-400">Tax (5%): {{ number_format($taxAmount) }}</span>
                <a href="{{ route('dashboard.admin.reports') }}" class="text-white hover:underline">Report →</a>
            </div>
        </div>
    </div>

    {{-- Chart Section --}}
    <div class="bg-white border-2 border-gray-300 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4 pb-4 border-b-2 border-gray-200">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Revenue Performance</h2>
                <p class="text-sm text-gray-600 mt-0.5">Daily revenue breakdown for the past 7 days</p>
            </div>
            <div class="flex items-center gap-2">
                <select class="bg-gray-100 border border-gray-300 text-gray-700 text-sm px-3 py-2 focus:outline-none focus:border-rose-500">
                    <option>Last 7 Days</option>
                    <option>This Month</option>
                    <option>Last Month</option>
                </select>
                <button class="px-3 py-2 bg-gray-900 text-white hover:bg-gray-800 transition-colors">
                    <i class="ph ph-download-simple"></i>
                </button>
            </div>
        </div>

        @if(collect($last7DaysRevenue)->pluck('total')->sum() == 0)
            <div class="h-64 flex flex-col items-center justify-center bg-gray-50 border-2 border-dashed border-gray-300">
                <div class="w-16 h-16 bg-gray-200 flex items-center justify-center mb-4">
                    <i class="ph ph-chart-line text-3xl text-gray-500"></i>
                </div>
                <p class="text-gray-900 font-medium">No revenue data available</p>
                <p class="text-gray-600 text-sm">Processing bookings will populate this chart</p>
            </div>
        @else
            <div class="h-[400px]">
                <canvas id="revenueChart"></canvas>
            </div>
        @endif
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('dashboard.admin.bookings') }}" class="bg-white border-2 border-gray-300 p-5 hover:border-gray-400 transition-colors">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 flex items-center justify-center text-blue-600">
                    <i class="ph ph-calendar text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">Manage Bookings</p>
                    <p class="text-xs text-gray-600">View and manage all bookings</p>
                </div>
            </div>
        </a>

        <a href="{{ route('dashboard.admin.reports') }}" class="bg-white border-2 border-gray-300 p-5 hover:border-gray-400 transition-colors">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 flex items-center justify-center text-green-600">
                    <i class="ph ph-chart-line-up text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">View Reports</p>
                    <p class="text-xs text-gray-600">Detailed analytics and insights</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.services.index') }}" class="bg-white border-2 border-gray-300 p-5 hover:border-gray-400 transition-colors">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-100 flex items-center justify-center text-purple-600">
                    <i class="ph ph-scissors text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">Manage Services</p>
                    <p class="text-xs text-gray-600">Add or edit salon services</p>
                </div>
            </div>
        </a>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
window.addEventListener('load', function () {
    const chartData = @json($last7DaysRevenue);
    const labels = chartData.map(item => item.date);
    const data = chartData.map(item => item.total);

    const canvas = document.getElementById('revenueChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue',
                data: data,
                borderColor: '#e11d48',
                backgroundColor: 'rgba(225, 29, 72, 0.1)',
                borderWidth: 2,
                tension: 0,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#e11d48',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#e11d48',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#111827',
                    titleColor: '#f9fafb',
                    bodyColor: '#f3f4f6',
                    padding: 12,
                    borderColor: '#374151',
                    borderWidth: 1,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return 'RWF ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#e5e7eb',
                        borderDash: [3, 3]
                    },
                    ticks: {
                        callback: value => 'RWF ' + value.toLocaleString(),
                        font: { family: "'Inter', sans-serif", size: 11 },
                        color: '#6b7280'
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { family: "'Inter', sans-serif", size: 11 },
                        color: '#6b7280'
                    }
                }
            }
        }
    });
});
</script>
@endpush
