@extends('layouts.dashboard')

@section('title', 'My Bookings')

@section('content')
<div class="container py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="fw-bold mb-0">
            <i class="ph ph-calendar-check text-primary me-1"></i> My Bookings
        </h4>
        <form method="GET" class="d-flex align-items-center gap-2">
            <select name="filter" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="upcoming" {{ request('filter') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                <option value="past" {{ request('filter') === 'past' ? 'selected' : '' }}>Past</option>
            </select>
        </form>
    </div>

    @if ($bookings->count())
        <div class="row g-4">
            @foreach ($bookings as $booking)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body pb-3">

                            <!-- Status & Date -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-{{ match($booking->status) {
                                    'pending' => 'warning',
                                    'accepted' => 'info',
                                    'completed' => 'success',
                                    'cancelled', 'declined' => 'secondary',
                                    default => 'dark'
                                } }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                                <small class="text-muted text-end">
                                    <i class="ph ph-calendar me-1"></i> {{ \Carbon\Carbon::parse($booking->date)->format('M d, Y') }}<br>
                                    <i class="ph ph-clock me-1"></i> {{ \Carbon\Carbon::parse($booking->time)->format('H:i') }}
                                </small>
                            </div>

                            <!-- Services -->
                            <div class="mb-2">
                                <h6 class="fw-bold mb-1">Services:</h6>
                                <ul class="list-unstyled small mb-2">
                                    @foreach ($booking->services as $service)
                                        <li><i class="ph ph-scissors me-1 text-muted"></i> {{ $service->name }}</li>
                                    @endforeach
                                </ul>
                            </div>

                            <!-- Provider -->
                            <p class="mb-0 text-muted small">
                                <i class="ph ph-user me-1"></i> {{ $booking->provider->name ?? '—' }}
                            </p>

                            <!-- Payment Summary -->
                            @php
                                $totalPrice = $booking->services->sum(fn($s) => $s->price);
                                $totalPaid = $booking->payments->where('status', 'paid')->sum('amount');
                                $latestPayment = $booking->payments->where('status', 'paid')->sortByDesc('created_at')->first();
                            @endphp

                            <p class="text-muted small mt-2">
                                <i class="ph ph-currency-circle-dollar me-1"></i>
                                {{ strtoupper($latestPayment->method ?? '—') }} • {{ number_format($totalPaid) }} RWF
                                <span class="badge ms-2 bg-{{
                                    $totalPaid >= $totalPrice ? 'success' :
                                    ($totalPaid > 0 ? 'warning text-dark' : 'danger')
                                }}">
                                    {{ $totalPaid >= $totalPrice ? 'Paid' : ($totalPaid > 0 ? 'Partial' : 'Unpaid') }}
                                </span>
                            </p>

                            @if ($totalPaid < $totalPrice)
                                <p class="text-danger small mb-0">
                                    Remaining Balance: <strong>RWF {{ number_format($totalPrice - $totalPaid) }}</strong>
                                </p>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3">
                            <a href="{{ route('booking.receipt', $booking->id) }}" class="btn btn-sm btn-outline-dark">
                                <i class="ph ph-receipt me-1"></i> Receipt
                            </a>

                            @if ($booking->status === 'pending')
                                <form method="POST" action="{{ route('customer.booking.cancel', $booking->id) }}" onsubmit="return confirm('Cancel this booking?')">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="ph ph-x-circle me-1"></i> Cancel
                                    </button>
                                </form>

                                @if ($totalPaid == 0)
                                    <a href="{{ route('booking.retryPayment', $booking->id) }}" class="btn btn-sm btn-warning">
                                        <i class="ph ph-wallet me-1"></i> Retry Payment
                                    </a>
                                @elseif($booking->deposit_amount && $totalPaid < $totalPrice)
                                    <a href="{{ route('customer.booking.payRemaining', $booking->id) }}" class="btn btn-sm btn-warning">
                                        <i class="ph ph-wallet me-1"></i> Pay Remaining
                                    </a>
                                @endif
                            @elseif($booking->status === 'accepted' && $booking->deposit_amount && $totalPaid < $totalPrice)
                                <a href="{{ route('customer.booking.payRemaining', $booking->id) }}" class="btn btn-sm btn-warning">
                                    <i class="ph ph-wallet me-1"></i> Pay Remaining
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $bookings->withQueryString()->links() }}
        </div>
    @else
        <div class="alert alert-info text-center mt-5">
            <i class="ph ph-calendar-x me-1"></i>
            No {{ request('filter') === 'past' ? 'past' : 'upcoming' }} bookings found.
        </div>
    @endif
</div>
@endsection
