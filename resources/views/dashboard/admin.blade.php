@extends('layouts.dashboard')

@section('title', 'Executive Overview')
@section('page-subtitle', 'Real-time insight into your salon\'s performance.')

@section('content')
<div class="space-y-8">
    
    {{-- High-Level Metrics --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        {{-- Total Revenue --}}
        <div class="bg-white rounded-2xl p-6 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.1)] border border-gray-100 group hover:border-gray-200 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-rose-50 flex items-center justify-center text-rose-500 group-hover:scale-110 transition-transform">
                    <i class="ph ph-currency-circle-dollar text-2xl"></i>
                </div>
                <div class="flex flex-col items-end">
                     <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">
                        <i class="ph ph-trend-up"></i>
                        +12%
                    </span>
                    <span class="text-xs text-gray-400 mt-1">vs last month</span>
                </div>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 font-serif">Total Revenue</p>
                <h3 class="text-3xl font-bold text-gray-900 mt-1">RWF {{ number_format($totalRevenue) }}</h3>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-50 flex justify-between items-center text-xs">
                 <span class="text-gray-500">MOMO: <span class="font-semibold text-gray-900">{{ number_format($momoRevenue) }}</span></span>
                 <span class="text-gray-500">Card: <span class="font-semibold text-gray-900">{{ number_format($cardRevenue) }}</span></span>
            </div>
        </div>

        {{-- Bookings --}}
        <div class="bg-white rounded-2xl p-6 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.1)] border border-gray-100 group hover:border-gray-200 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-500 group-hover:scale-110 transition-transform">
                    <i class="ph ph-calendar-check text-2xl"></i>
                </div>
                <span class="text-xs font-semibold text-gray-500 bg-gray-50 px-2 py-0.5 rounded-full">
                   All Time
                </span>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 font-serif">Total Bookings</p>
                <h3 class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($totalBookings) }}</h3>
            </div>
             <div class="mt-4 pt-4 border-t border-gray-50 flex items-center gap-2">
                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: 75%"></div>
                </div>
                <span class="text-xs text-gray-500">75% capacity</span>
            </div>
        </div>

        {{-- Active Customers --}}
        <div class="bg-white rounded-2xl p-6 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.1)] border border-gray-100 group hover:border-gray-200 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center text-purple-500 group-hover:scale-110 transition-transform">
                    <i class="ph ph-users-three text-2xl"></i>
                </div>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 font-serif">Active Clients</p>
                <h3 class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($totalCustomers) }}</h3>
            </div>
            <p class="text-xs text-gray-400 mt-4">
                <i class="ph ph-heart-straight text-rose-400 mr-1"></i>
                Most loyal: Sarah K.
            </p>
        </div>

        {{-- Net Income (Tax Calculated) --}}
        <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl p-6 shadow-xl text-white relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-5">
                <i class="ph ph-crown text-9xl"></i>
            </div>
            
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center backdrop-blur-sm">
                        <i class="ph ph-wallet text-2xl text-white"></i>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-400 font-serif">Net Income (After Tax)</p>
                    <h3 class="text-3xl font-bold text-white mt-1">RWF {{ number_format($revenueAfterTax) }}</h3>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-700 flex justify-between items-center text-xs text-gray-400">
                    <span>Tax (5%): {{ number_format($taxAmount) }}</span>
                    <a href="{{ route('dashboard.admin.reports') }}" class="hover:text-white transition-colors">View Report &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart Section --}}
    <div class="bg-white rounded-3xl p-8 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] border border-gray-100">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900 font-serif">Revenue Performance</h2>
                <p class="text-sm text-gray-500 mt-1">Daily revenue breakdown for the past 7 days</p>
            </div>
            <div class="flex items-center gap-2">
                <select class="bg-gray-50 border-0 text-gray-600 text-sm rounded-lg focus:ring-rose-500 focus:border-rose-500 block p-2.5">
                    <option>Last 7 Days</option>
                    <option>This Month</option>
                    <option>Last Month</option>
                </select>
                <button class="p-2.5 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors">
                    <i class="ph ph-download-simple"></i>
                </button>
            </div>
        </div>

        @if(collect($last7DaysRevenue)->pluck('total')->sum() == 0)
            <div class="h-64 flex flex-col items-center justify-center bg-gray-50/50 rounded-2xl border-2 border-dashed border-gray-200">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="ph ph-chart-line text-3xl text-gray-400"></i>
                </div>
                <p class="text-gray-900 font-medium">No revenue data available</p>
                <p class="text-gray-500 text-sm">Processing bookings will populate this chart</p>
            </div>
        @else
            <div class="h-[400px]">
                <canvas id="revenueChart"></canvas>
            </div>
        @endif
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
    
    // Gradient styling
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(225, 29, 72, 0.15)'); // Rose-600
    gradient.addColorStop(1, 'rgba(225, 29, 72, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue',
                data: data,
                borderColor: '#e11d48', // Rose-600
                backgroundColor: gradient,
                borderWidth: 3,
                tension: 0.4,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#e11d48',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointHoverBackgroundColor: '#e11d48',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 3,
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
                        color: 'rgba(243, 244, 246, 0.6)',
                        borderDash: [5, 5]
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
