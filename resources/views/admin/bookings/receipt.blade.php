@extends('layouts.dashboard')

@section('title', 'Booking Receipt')

@section('content')
<div class="container my-5">
    <h2 class="fw-bold mb-4">📄 Admin View: Booking #{{ $booking->id }}</h2>

    <div class="card shadow-sm p-4">
        <ul class="list-group list-group-flush mb-4">
            <li class="list-group-item"><strong>Customer:</strong> {{ $booking->customer->user->name }}</li>
            <li class="list-group-item"><strong>Service:</strong> {{ $booking->service->name }}</li>
            <li class="list-group-item"><strong>Provider:</strong> {{ $booking->provider->user->name }}</li>
            <li class="list-group-item"><strong>Date:</strong> {{ \Carbon\Carbon::parse($booking->date)->format('D, M j Y') }}</li>
            <li class="list-group-item"><strong>Time:</strong> {{ \Carbon\Carbon::parse($booking->time)->format('H:i') }}</li>
            <li class="list-group-item"><strong>Status:</strong> {{ ucfirst($booking->status) }}</li>
            <li class="list-group-item"><strong>Payment:</strong>
                {{ $booking->payment_option === 'deposit' ? 'Deposit (RWF 10,000)' : 'Full Payment' }}
            </li>
        </ul>

        <div class="d-flex justify-content-center gap-3">
            <a href="{{ route('booking.receipt', $booking->id) }}" target="_blank" class="btn btn-outline-secondary">View as Customer</a>
            <a href="{{ route('download.receipt', $booking->id) }}" class="btn btn-success">Download PDF</a>
        </div>
    </div>
</div>
@endsection
