@extends('layouts.dashboard')

@section('title', 'Provider Performance Report')

@section('content')
<div class="container my-5">
    <h2 class="text-center fw-bold mb-4">
        <i class="ph ph-bar-chart text-primary me-1"></i> Performance Report for {{ $provider->user->name ?? 'Provider' }}
    </h2>

    <div class="row text-center mb-5">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Total Bookings</h6>
                    <h4 class="fw-bold">{{ $totalBookings }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Completed</h6>
                    <h4 class="fw-bold">{{ $completedBookings }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Completion Rate</h6>
                    <h4 class="fw-bold">
                        <span class="badge bg-{{ $completionRate >= 75 ? 'success' : ($completionRate >= 50 ? 'warning' : 'danger') }}">
                            {{ $completionRate }}%
                        </span>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Total Revenue</h6>
                    <h4 class="fw-bold">RWF {{ number_format($totalRevenue) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body">
            <h5 class="text-center mb-3">Revenue Over Last 7 Days</h5>
            <canvas id="revenueChart" height="100"></canvas>
        </div>
    </div>

    <div class="text-center">
        <a href="{{ route('dashboard.admin.reports.pdf') }}" class="btn btn-outline-primary">
            <i class="ph ph-download-simple me-1"></i> Download PDF
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
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
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
