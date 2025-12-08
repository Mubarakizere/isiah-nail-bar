@extends('layouts.dashboard')

@section('title', 'All Bookings')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <i class="ph ph-calendar-check me-1 text-primary"></i> All Bookings
        </h4>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('dashboard.admin.bookings') }}" class="row g-3 align-items-end mb-4">
        <div class="col-md-4">
            <label for="search" class="form-label">
                <i class="ph ph-magnifying-glass me-1 text-muted"></i> Search
            </label>
            <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="Customer or Service">
        </div>

        <div class="col-md-3">
            <label for="status" class="form-label">
                <i class="ph ph-funnel me-1 text-muted"></i> Status
            </label>
            <select name="status" id="status" class="form-select">
                <option value="">All</option>
                @foreach(['pending', 'accepted', 'declined', 'completed', 'cancelled'] as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label for="payment_status" class="form-label">
                <i class="ph ph-wallet me-1 text-muted"></i> Payment Status
            </label>
            <select name="payment_status" id="payment_status" class="form-select">
                <option value="">All</option>
                @foreach(['paid', 'pending', 'failed', 'unpaid'] as $paymentStatus)
                    <option value="{{ $paymentStatus }}" {{ request('payment_status') === $paymentStatus ? 'selected' : '' }}>
                        {{ ucfirst($paymentStatus) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="ph ph-finder me-1"></i> Filter
            </button>
        </div>

        <div class="col-md-2">
            <a href="{{ route('dashboard.admin.bookings') }}" class="btn btn-outline-secondary w-100">
                <i class="ph ph-arrow-counter-clockwise me-1"></i> Reset
            </a>
        </div>
    </form>

    {{-- Fetch Time --}}
    @if(isset($fetchTime))
        <div class="text-end text-muted small mb-2">
            <i class="ph ph-clock me-1"></i> Loaded in {{ $fetchTime }}s
        </div>
    @endif

    {{-- Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th><i class="ph ph-user me-1 text-muted"></i> Customer</th>
                            <th><i class="ph ph-scissors me-1 text-muted"></i> Services</th>
                            <th><i class="ph ph-calendar-blank me-1 text-muted"></i> Date</th>
                            <th><i class="ph ph-clock me-1 text-muted"></i> Time</th>
                            <th><i class="ph ph-flag me-1 text-muted"></i> Status</th>
                            <th><i class="ph ph-currency-circle-dollar me-1 text-muted"></i> Payment</th>
                            <th class="text-end"><i class="ph ph-gear me-1 text-muted"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $booking)
                            <tr>
                                <td>{{ $booking->id }}</td>
                                <td>{{ $booking->customer->user->name ?? '—' }}</td>
                                <td>
                                    @if($booking->services && $booking->services->count())
                                        <ul class="list-unstyled mb-0">
                                            @foreach($booking->services as $s)
                                                <li class="d-flex align-items-center">
                                                    <i class="ph ph-scissors me-1 text-muted small"></i>
                                                    <span class="small">{{ $s->name }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($booking->date)->format('M d, Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking->time)->format('h:i A') }}</td>
                                <td>
                                    <span class="badge bg-{{ match($booking->status) {
                                        'pending' => 'warning',
                                        'accepted' => 'info',
                                        'completed' => 'success',
                                        'declined', 'cancelled' => 'secondary',
                                        default => 'dark'
                                    } }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $totalPaid = $booking->payments->where('status', 'paid')->sum('amount');
                                        $latestPayment = $booking->payments->sortByDesc('created_at')->first();
                                    @endphp

                                    @if ($totalPaid > 0)
                                        <span class="d-block fw-semibold text-uppercase">{{ strtoupper($latestPayment->method ?? '—') }}</span>
                                        <small class="text-muted">{{ number_format($totalPaid) }} RWF</small>
                                        <br>
                                        <span class="badge bg-success">Paid</span>
                                    @elseif ($latestPayment)
                                        <span class="d-block fw-semibold text-uppercase">{{ strtoupper($latestPayment->method) }}</span>
                                        <small class="text-muted">{{ number_format($latestPayment->amount) }} RWF</small>
                                        <br>
                                        <span class="badge bg-warning text-dark">{{ ucfirst($latestPayment->status) }}</span>
                                    @else
                                        <span class="badge bg-secondary">Unpaid</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end flex-wrap gap-2">
                                        @if($booking->status === 'pending')
                                            <form method="POST" action="{{ route('dashboard.admin.update', $booking->id) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="accepted">
                                                <button class="btn btn-sm btn-success">
                                                    <i class="ph ph-check"></i> Accept
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('dashboard.admin.update', $booking->id) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="declined">
                                                <button class="btn btn-sm btn-outline-danger">
                                                    <i class="ph ph-x"></i> Decline
                                                </button>
                                            </form>
                                        @elseif($booking->status === 'accepted')
                                            <form method="POST" action="{{ route('dashboard.admin.update', $booking->id) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="completed">
                                                <button class="btn btn-sm btn-primary">
                                                    <i class="ph ph-check-circle"></i> Mark Completed
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('booking.receipt', $booking->id) }}" class="btn btn-sm btn-outline-dark" title="View Receipt">
                                            <i class="ph ph-receipt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No bookings found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
<div class="mt-4 d-flex justify-content-center">
    <div class="rounded-pill shadow-sm px-4 py-2 bg-light">
        {{ $bookings->withQueryString()->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>

        </div>
    </div>
</div>
@endsection
