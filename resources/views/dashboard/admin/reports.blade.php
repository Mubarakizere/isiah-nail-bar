@extends('layouts.dashboard')

@section('title', 'Revenue Reports')

@section('content')
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            <i class="ph ph-chart-line-up me-2 text-success"></i> Revenue Reports
        </h2>
        <a href="{{ route('dashboard.admin') }}" class="btn btn-outline-secondary btn-sm">← Back to Dashboard</a>
    </div>

    <!-- Filters -->
    <form method="GET" class="row g-3 mb-4 align-items-end">
        <div class="col-md-4">
            <label for="from" class="form-label fw-semibold">From</label>
            <input type="date" id="from" name="from" class="form-control" value="{{ $from }}">
        </div>
        <div class="col-md-4">
            <label for="to" class="form-label fw-semibold">To</label>
            <input type="date" id="to" name="to" class="form-control" value="{{ $to }}">
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-dark w-50">
                <i class="ph ph-magnifying-glass me-1"></i> Filter
            </button>
            <a href="{{ route('dashboard.admin.reports.pdf', ['from' => $from, 'to' => $to]) }}" class="btn btn-outline-primary w-50">
                <i class="ph ph-file-pdf me-1"></i> PDF
            </a>
        </div>
    </form>

    <!-- Summary Cards -->
    <div class="row mb-4 text-center">
        <div class="col-md-4 mb-2">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Total Revenue</h6>
                    <h4 class="fw-bold text-success">RWF {{ number_format($total) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Bookings</h6>
                    <h4 class="fw-bold">{{ count($bookings) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Avg. per Booking</h6>
                    <h4 class="fw-bold">
                        RWF {{ count($bookings) > 0 ? number_format($total / count($bookings)) : '0' }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Services</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="text-end">Amount Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            <tr>
                                <td>{{ $booking->id }}</td>
                                <td>{{ $booking->customer->user->name ?? '-' }}</td>
                                <td>
                                    @if ($booking->services && $booking->services->count())
                                        <ul class="list-unstyled mb-0">
                                            @foreach ($booking->services as $service)
                                                <li class="small">{{ $service->name }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted">No Services</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($booking->date)->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ match($booking->status) {
                                        'pending' => 'warning',
                                        'accepted' => 'primary',
                                        'completed' => 'success',
                                        'declined', 'cancelled' => 'secondary',
                                        default => 'dark'
                                    } }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @php
                                        $paid = $booking->payments->where('status', 'paid')->sum('amount');
                                    @endphp

                                    @if ($paid > 0)
                                        RWF {{ number_format($paid) }}
                                    @else
                                        <span class="text-muted">Unpaid</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No bookings found in this range.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
