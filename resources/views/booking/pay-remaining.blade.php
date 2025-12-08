@extends('layouts.public')

@section('title', 'Pay Remaining Balance')

@section('content')
<div class="container my-5" style="max-width: 650px;">
    <div class="card shadow-sm p-4 border-0 rounded-4">
        <h4 class="fw-bold mb-3">
            <i class="ph ph-wallet me-1 text-primary"></i> Pay Remaining Balance
        </h4>

        <p class="mb-4 text-muted">
            Please review your booking details and complete your remaining payment.
        </p>

        {{-- Booking Summary --}}
        <div class="mb-4">
            <ul class="list-group list-group-flush">
                {{-- Services List --}}
                @foreach ($booking->services as $service)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ $service->name }}</span>
                        <span>RWF {{ number_format($service->price) }}</span>
                    </li>
                @endforeach

                {{-- Totals --}}
                <li class="list-group-item d-flex justify-content-between">
                    <span class="fw-semibold">Total</span>
                    <span class="fw-semibold">RWF {{ number_format($booking->services->sum('price')) }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>Deposit Paid</span>
                    <span>RWF {{ number_format($booking->deposit_amount) }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between text-danger fw-bold">
                    <span>Remaining Balance</span>
                    <span>RWF {{ number_format($remainingAmount) }}</span>
                </li>
            </ul>
        </div>

        {{-- Payment Form --}}
        <form method="POST" action="{{ route('customer.booking.payRemainingPost', $booking->id) }}">
            @csrf

            {{-- Phone --}}
            <div class="mb-3">
                <label for="phone" class="form-label fw-semibold">Payment Phone Number</label>
                <input type="text" name="phone" id="phone" value="{{ $phone }}" required class="form-control" placeholder="e.g. 0788xxxxxx">
            </div>

            {{-- Payment Method --}}
            <div class="mb-4">
                <label class="form-label fw-semibold">Payment Method</label>
                <div class="vstack gap-2">
                    <label class="form-check border p-3 rounded">
                        <input class="form-check-input me-2" type="radio" name="payment_method" value="momo" required>
                        <span>Mobile Money (MoMo)</span>
                    </label>
                    <label class="form-check border p-3 rounded">
                        <input class="form-check-input me-2" type="radio" name="payment_method" value="card">
                        <span>Debit/Credit Card</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="ph ph-arrow-circle-right me-1"></i> Pay Now
            </button>
        </form>
    </div>
</div>
@endsection
