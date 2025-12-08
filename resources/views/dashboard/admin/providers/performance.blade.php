@extends('layouts.dashboard')

@section('title', 'Provider Performance Report')

@section('content')
<div class="container py-5">

    <!-- Title -->
    <div class="text-center mb-5">
        <h2 class="fw-bold mb-1">
            <i class="ph ph-bar-chart text-primary me-2"></i>
            Performance Report
        </h2>
        <p class="text-muted fs-6">Provider: <strong>{{ $provider->user->name ?? 'N/A' }}</strong></p>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-5 text-center">
        <div class="col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body">
                    <small class="text-muted">Total Bookings</small>
                    <h4 class="fw-bold mt-1">{{ $totalBookings }}</h4>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body">
                    <small class="text-muted">Completed</small>
                    <h4 class="fw-bold mt-1">{{ $completedBookings }}</h4>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body">
                    <small class="text-muted">Completion Rate</small>
                    <h4 class="fw-bold mt-1">
                        <span class="badge bg-{{ $completionRate >= 75 ? 'success' : ($completionRate >= 50 ? 'warning text-dark' : 'danger') }}">
                            {{ $completionRate }}%
                        </span>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body">
                    <small class="text-muted">Total Revenue</small>
                    <h4 class="fw-bold mt-1">RWF {{ number_format($totalRevenue) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-5">
        <div class="card-body">
            <h5 class="text-center fw-semibold mb-4 text-primary">
                Revenue Over the Last 7 Days
            </h5>
            <canvas id="revenueChart" height="100"></canvas>
        </div>
    </div>

    <!-- Footer Actions -->
    <div class="text-center">
        <a href="{{ route('dashboard.admin.reports.pdf') }}" class="btn btn-outline-primary">
            <i class="ph ph-file-pdf me-1"></i> Download Full PDF Report
        </a>
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
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13,110,253,0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointRadius: 3,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => 'RWF ' + ctx.raw.toLocaleString()
                    }
                }
            },
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
</script>
@endpush
