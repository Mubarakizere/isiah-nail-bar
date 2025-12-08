@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Booking Details</h2>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $booking->service->name }}</h5>
            <p><strong>Provider:</strong> {{ $booking->provider->name }}</p>
            <p><strong>Date:</strong> {{ $booking->date }} at {{ $booking->time }}</p>
            <p><strong>Status:</strong> {{ ucfirst($booking->status) }}</p>
            <p><strong>Payment:</strong> {{ ucfirst($booking->payment_status) }}</p>

            <a href="{{ route('dashboard.customer') }}" class="btn btn-secondary">Back to Bookings</a>
        </div>
    </div>
</div>
@endsection
