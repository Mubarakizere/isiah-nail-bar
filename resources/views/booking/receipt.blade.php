@extends('layouts.public')

@section('title', 'Booking Receipt')

@section('content')
<div class="container py-5">
    <div class="text-center mb-4">
        <img src="{{ asset('storage/logo.png') }}" alt="Logo" style="height: 50px;">
        <h3 class="fw-bold mt-3">Booking Receipt</h3>
        <p class="text-muted">Isaiah Nail Bar – Kigali</p>
    </div>

    <div class="card shadow-sm mx-auto" style="max-width: 650px;">
        <div class="card-body px-4 py-3">
            <ul class="list-group list-group-flush small">
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Booking ID:</span>
                    <span class="fw-semibold">#{{ $booking->id }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Customer:</span>
                    <span class="fw-semibold">{{ $booking->customer->user->name ?? '-' }} ({{ $booking->customer->user->email ?? '-' }})</span>
                </li>
                <li class="list-group-item">
                    <span class="text-muted d-block mb-2">Services:</span>
                    <ul class="mb-0 ps-3">
                        @foreach($booking->services as $service)
                            <li class="fw-semibold">
                                {{ $service->name }} – RWF {{ number_format($service->price) }}
                            </li>
                        @endforeach
                    </ul>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Provider:</span>
                    <span class="fw-semibold">{{ $booking->provider->name ?? '-' }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Date:</span>
                    <span class="fw-semibold">{{ \Carbon\Carbon::parse($booking->date)->format('D, M j, Y') }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Time:</span>
                    <span class="fw-semibold">{{ \Carbon\Carbon::parse($booking->time)->format('H:i') }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Payment Option:</span>
                    <span class="fw-semibold text-capitalize">{{ $booking->payment_option }}</span>
                </li>
                @if($booking->deposit_amount)
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Deposit Paid:</span>
                        <span class="fw-semibold">RWF {{ number_format($booking->deposit_amount) }}</span>
                    </li>
                @endif
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Total Amount:</span>
                    <span class="fw-semibold">
                        RWF {{ number_format($booking->services->sum('price')) }}
                    </span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="text-muted">Status:</span>
                    <span class="badge bg-{{ match($booking->status) {
                        'pending' => 'warning',
                        'accepted' => 'primary',
                        'completed' => 'success',
                        'cancelled', 'declined' => 'danger',
                        default => 'secondary'
                    } }}">
                        {{ ucfirst($booking->status) }}
                    </span>
                </li>
            </ul>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="{{ route('download.receipt', $booking->id) }}" class="btn btn-outline-dark">
            <i class="ph ph-file-arrow-down me-1"></i> Download PDF
        </a>
    </div>
</div>
@endsection
