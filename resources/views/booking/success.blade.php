@extends('layouts.public')

@section('title', 'Booking Successful')

@section('content')
<div class="container my-5 text-center">
    @php
        $summary = session('last_booking');
        $bookingId = session('last_booking_id');
    @endphp

    @if($summary)
    <!-- ✅ Success Icon -->
    <div class="mb-4">
        <div class="d-inline-flex justify-content-center align-items-center bg-success-subtle rounded-circle p-4 shadow-sm animate__animated animate__zoomIn" style="width: 90px; height: 90px;">
            <i class="fas fa-check-circle text-success fa-3x"></i>
        </div>
        <h2 class="fw-bold text-success mt-3">Appointment Confirmed</h2>
        <p class="text-muted">Thank you! Your booking is complete. We're excited to serve you.</p>
    </div>

    <!-- ✅ Appointment Summary Card -->
    <div class="card mx-auto mb-4 shadow-sm border-0" style="max-width: 520px;">
        <div class="card-body text-start">
            <h5 class="fw-semibold text-center mb-3">
                <i class="fas fa-calendar-alt text-primary me-1"></i> Booking Summary
            </h5>
            <ul class="list-group list-group-flush small text-start">
                <li class="list-group-item">
                    <strong>Services:</strong>
                    <ul class="mb-0 ps-3">
                        @foreach ($summary['service_names'] ?? [] as $service)
                            <li>{{ $service }}</li>
                        @endforeach
                    </ul>
                </li>
                <li class="list-group-item"><strong>Provider:</strong> {{ $summary['provider_name'] }}</li>
                <li class="list-group-item"><strong>Date:</strong> {{ \Carbon\Carbon::parse($summary['date'])->format('D, M j, Y') }}</li>
                <li class="list-group-item"><strong>Time:</strong> {{ $summary['time'] }}</li>
                <li class="list-group-item"><strong>Payment:</strong> {{ $summary['payment'] }}</li>
            </ul>
        </div>
    </div>

    <!-- ✅ Receipt Actions -->
    <div class="mb-4 d-flex flex-wrap justify-content-center gap-2">
        <a href="{{ route('booking.receipt', $bookingId) }}" target="_blank" class="btn btn-outline-dark shadow-sm">
            <i class="fas fa-eye me-1"></i> View Receipt
        </a>
        <a href="{{ route('download.receipt', $bookingId) }}" class="btn btn-success shadow-sm">
            <i class="fas fa-file-pdf me-1"></i> Download PDF
        </a>
    </div>
    @endif

    <!-- ✅ Call to Action -->
    <div class="mt-4 d-flex justify-content-center flex-wrap gap-3">
        <a href="{{ url('/') }}" class="btn btn-link text-decoration-none">
            <i class="fas fa-home me-1"></i> Back to Home
        </a>
        <a href="{{ route('booking.step1') }}" class="btn btn-link text-decoration-none">
            Book Another <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>
</div>
@endsection
