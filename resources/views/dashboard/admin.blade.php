@extends('layouts.dashboard')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container py-4">
    {{-- Header --}}
    <div class="mb-4">
        <h4 class="fw-bold mb-1">
            <i class="ph ph-gauge me-2 text-primary"></i> Admin Overview
        </h4>
        <p class="text-muted">Monitor system performance, users, bookings, and revenue at a glance.</p>
    </div>

    {{-- Quick Stats Cards --}}
    <div class="row g-3 mb-5">
       @php
    $cards = [
        ['label' => 'Total Bookings', 'value' => $totalBookings, 'icon' => 'calendar-check', 'color' => 'primary'],
        ['label' => 'Gross Revenue', 'value' => 'RWF ' . number_format($totalRevenue), 'icon' => 'currency-circle-dollar', 'color' => 'success'],
        ['label' => '5% Tax', 'value' => 'RWF ' . number_format($taxAmount), 'icon' => 'percent', 'color' => 'danger'],
        ['label' => 'Net Revenue', 'value' => 'RWF ' . number_format($revenueAfterTax), 'icon' => 'currency-dollar-simple', 'color' => 'success'],
        ['label' => 'Momo Revenue', 'value' => 'RWF ' . number_format($momoRevenue), 'icon' => 'cell-signal-full', 'color' => 'info'],
        ['label' => 'Card Revenue', 'value' => 'RWF ' . number_format($cardRevenue), 'icon' => 'credit-card', 'color' => 'secondary'],
        ['label' => 'Total Customers', 'value' => $totalCustomers, 'icon' => 'users-three', 'color' => 'info'],
        ['label' => 'Total Providers', 'value' => $totalProviders, 'icon' => 'scissors', 'color' => 'warning'],
    ];
@endphp


        @foreach($cards as $card)
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100 hover-lift">
                    <div class="card-body text-center py-4">
                        <i class="ph ph-{{ $card['icon'] }} fs-2 text-{{ $card['color'] }}"></i>
                        <p class="text-muted small mb-1 mt-2">{{ $card['label'] }}</p>
                        <h4 class="fw-bold">{{ $card['value'] }}</h4>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

 {{-- Revenue Chart --}}
<div class="card border-0 shadow-sm hover-lift">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">
                <i class="ph ph-chart-line-up text-primary me-2"></i>
                Revenue - Last 7 Days
            </h5>
            <span class="badge bg-light text-dark small">Updated: {{ now()->format('M d, H:i') }}</span>
        </div>

        @if(collect($last7DaysRevenue)->pluck('total')->sum() == 0)
            <div class="alert alert-warning text-center mb-0">
                No revenue recorded in the last 7 days.
            </div>
        @else
            <div style="height: 300px;">
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

    console.log("Chart Raw Data", chartData);

    const labels = chartData.map(item => item.date);
    const data = chartData.map(item => item.total);

    const canvas = document.getElementById('revenueChart');
    if (!canvas) {
        console.warn('Canvas not found');
        return;
    }

    const ctx = canvas.getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue (RWF)',
                data: data,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13,110,253,0.1)',
                fill: true,
                tension: 0.3,
                borderWidth: 2,
                pointRadius: 4,
                pointBackgroundColor: '#0d6efd',
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => 'RWF ' + value.toLocaleString()
                    }
                }
            }
        }
    });
});
</script>

<style>
    #revenueChart {
        width: 100% !important;
        min-height: 260px;
    }

    .hover-lift:hover {
        transform: translateY(-3px);
        transition: all 0.2s ease;
    }
</style>
@endpush
