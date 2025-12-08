@extends('layouts.dashboard')

@section('title', 'Booking Details')

@section('content')
<div class="container my-4">
    <h2 class="fw-bold text-center mb-4">🧾 Booking Details</h2>

    <div class="card shadow-sm p-4 mb-4">
        <div class="row mb-3">
            <div class="col-md-4 fw-semibold text-muted">Service</div>
            <div class="col-md-8">{{ $booking->service->name }}</div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4 fw-semibold text-muted">Provider</div>
            <div class="col-md-8">{{ $booking->provider->name }}</div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4 fw-semibold text-muted">Date</div>
            <div class="col-md-8">{{ \Carbon\Carbon::parse($booking->date)->format('l, M d Y') }}</div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4 fw-semibold text-muted">Time</div>
            <div class="col-md-8">{{ \Carbon\Carbon::parse($booking->time)->format('H:i') }}</div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4 fw-semibold text-muted">Status</div>
            <div class="col-md-8">
                <span class="badge bg-{{ match($booking->status) {
                    'pending' => 'warning',
                    'accepted' => 'info',
                    'completed' => 'success',
                    'cancelled' => 'secondary',
                    default => 'dark'
                } }}">
                    {{ ucfirst($booking->status) }}
                </span>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4 fw-semibold text-muted">Payment Option</div>
            <div class="col-md-8">
                {{ $booking->payment_option === 'deposit' ? 'Deposit (RWF 10,000)' : 'Full Payment' }}
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4 fw-semibold text-muted">Paid Full?</div>
            <div class="col-md-8">
                {{ $booking->is_fully_paid || !$booking->deposit_amount ? '✅ Yes' : '❌ No (deposit only)' }}
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            @if ($booking->status === 'pending')
                <form method="POST" action="{{ route('customer.booking.cancel', $booking->id) }}">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-outline-danger">Cancel Booking</button>
                </form>
            @endif

            @if ($booking->deposit_amount && !$booking->is_fully_paid)
                <form method="POST" action="{{ route('customer.booking.payRemaining', $booking->id) }}">
                    @csrf
                    <button class="btn btn-warning">Pay Remaining</button>
                </form>
            @endif

            <a href="{{ route('booking.receipt', $booking->id) }}" class="btn btn-success">
                Download Receipt
            </a>

            <a href="{{ route('dashboard.customer') }}" class="btn btn-secondary">← Back to My Bookings</a>
        </div>
    </div>
</div>
@endsection
