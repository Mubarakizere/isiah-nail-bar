@extends('layouts.dashboard')

@section('title', 'Provider Dashboard')

@section('content')
<div class="container py-4">

    <!-- Header -->
    <div class="mb-4">
        <h4 class="fw-bold"><i class="ph ph-briefcase me-1"></i> Provider Overview</h4>
        <p class="text-muted">Track your performance, earnings, and manage bookings.</p>
    </div>

    <!-- Metrics -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card text-center shadow-sm border-0 h-100">
                <div class="card-body">
                    <i class="ph ph-calendar-check fs-2 text-primary"></i>
                    <p class="mt-2 text-muted small">Total Bookings</p>
                    <h4 class="fw-bold">{{ $total }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center shadow-sm border-0 h-100">
                <div class="card-body">
                    <i class="ph ph-percent fs-2 text-success"></i>
                    <p class="mt-2 text-muted small">Completion Rate</p>
                    <h4 class="fw-bold">{{ $rate }}%</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center shadow-sm border-0 h-100">
                <div class="card-body">
                    <i class="ph ph-currency-circle-dollar fs-2 text-warning"></i>
                    <p class="mt-2 text-muted small">Total Revenue</p>
                    <h4 class="fw-bold">RWF {{ number_format($revenue) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Earnings Chart -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="ph ph-chart-line-up text-primary me-1"></i> Earnings (Last 7 Days)</h5>
                <a href="{{ route('provider.earnings.pdf') }}" class="btn btn-sm btn-outline-dark">
                    <i class="ph ph-download me-1"></i> Download Report
                </a>
            </div>
            <canvas id="earningsChart" height="100"></canvas>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h5 class="fw-bold mb-3"><i class="ph ph-calendar me-1"></i> My Bookings</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Services</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($myBookings as $booking)
                            <tr>
                                <td>{{ $booking->id }}</td>
                                <td>{{ $booking->customer->user->name ?? '—' }}</td>
                                <td>
                                    <ul class="list-unstyled small mb-0">
                                        @foreach($booking->services as $service)
                                            <li><i class="ph ph-scissors me-1 text-muted"></i> {{ $service->name }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($booking->date)->format('M d, Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking->time)->format('H:i') }}</td>
                                <td>
                                    <span class="badge bg-{{ match($booking->status) {
                                        'pending' => 'warning',
                                        'accepted' => 'info',
                                        'completed' => 'success',
                                        'cancelled', 'declined' => 'secondary',
                                        default => 'dark'
                                    } }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                                        @if($booking->status === 'pending')
                                            <form method="POST" action="{{ route('provider.booking.action', ['id' => $booking->id, 'action' => 'accept']) }}">
                                                @csrf
                                                <button class="btn btn-sm btn-success">Accept</button>
                                            </form>
                                            <form method="POST" action="{{ route('provider.booking.action', ['id' => $booking->id, 'action' => 'decline']) }}">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger">Decline</button>
                                            </form>
                                        @elseif($booking->status === 'accepted')
                                            <form method="POST" action="{{ route('provider.booking.complete', $booking->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-primary">Mark Completed</button>
                                            </form>
                                        @endif
                                        <a href="{{ route('booking.receipt', $booking->id) }}" class="btn btn-sm btn-outline-dark">
                                            Receipt
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No bookings found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Chart Script --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('earningsChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartData->pluck('label')) !!},
            datasets: [{
                label: 'Revenue (RWF)',
                data: {!! json_encode($chartData->pluck('total')) !!},
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13,110,253,0.1)',
                tension: 0.3,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
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
@endsection
