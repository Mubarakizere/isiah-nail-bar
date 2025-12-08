@extends('layouts.public')

@section('title', 'Verify Order Details')

@section('content')
@include('partials.booking-progress', ['currentStep' => 5])

<div class="container my-5">
    <div class="row g-5">
        <!-- Help Sidebar -->
        <div class="col-md-4 d-none d-md-block border-end pe-4" data-aos="fade-right">
            <div class="text-center text-md-start">
                <img src="{{ asset('storage/images/verify.svg') }}" alt="Verify Icon" class="mb-3" width="60">
                <h5 class="fw-bold">Verify Order Details</h5>
                <p class="text-muted small">Please review all information carefully before confirming your booking.</p>
                <hr>
                <p class="text-muted small mb-1">Need Assistance?</p>
                <a href="tel:+250788421063" class="text-primary small fw-semibold text-decoration-none">
                    <i class="fas fa-phone me-1"></i> 0788 421 063
                </a>
            </div>
        </div>

        <!-- Main Panel -->
        <div class="col-md-5 mx-auto" data-aos="fade-up">
            <h4 class="fw-bold mb-4 text-center">Confirm Your Booking</h4>

            <!-- Selected Services -->
            <div class="mb-4 border rounded p-3 shadow-sm bg-light">
                <h6 class="fw-semibold mb-3"><i class="fas fa-list me-2 text-primary"></i> Selected Services</h6>
                <ul class="list-group list-group-flush">
                    @foreach ($services as $service)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $service->name }}</span>
                            <span class="text-end">RWF {{ number_format($service->price) }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-3 d-flex justify-content-between fw-bold border-top pt-2">
                    <span>Total</span>
                    <span>RWF {{ number_format($totalPrice) }}</span>
                </div>
            </div>

            <!-- Provider & Customer Info -->
            <div class="mb-4 border rounded p-3 shadow-sm bg-white">
                <h6 class="fw-semibold mb-3"><i class="fas fa-user-check me-2 text-primary"></i> Your Appointment</h6>
                <div class="row small">
                    <div class="col-6">
                        <p class="mb-1 text-muted fw-semibold">Provider</p>
                        <p class="mb-0">{{ $provider->name }}</p>
                    </div>
                    <div class="col-6 text-end">
                        <p class="mb-1 text-muted fw-semibold">Customer</p>
                        <p class="mb-0">{{ auth()->user()->name ?? 'Guest' }}</p>
                        <p class="mb-0 text-muted">{{ auth()->user()->email ?? 'Not logged in' }}</p>
                    </div>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="mb-4 border rounded p-3 shadow-sm bg-light">
                <h6 class="fw-semibold mb-3"><i class="fas fa-wallet me-2 text-primary"></i> Payment Summary</h6>
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>{{ $paymentOption }}</span>
                    <span>
                        @if($paymentOption === 'Deposit')
                            RWF {{ number_format($depositAmount) }}
                        @else
                            RWF {{ number_format($totalPrice) }}
                        @endif
                    </span>
                </div>
                <div class="d-flex justify-content-between fw-bold mt-2">
                    <span>Total to Pay Now</span>
                    <span>
                        @if($paymentOption === 'Deposit')
                            RWF {{ number_format($depositAmount) }}
                        @else
                            RWF {{ number_format($totalPrice) }}
                        @endif
                    </span>
                </div>
            </div>

            <!-- Confirmation -->
            <form method="POST" action="{{ route('booking.step5.submit') }}">
                @csrf
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('booking.step4') }}" class="btn btn-outline-secondary">
                        ← Back
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        Confirm Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
