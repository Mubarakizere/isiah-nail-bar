@extends('layouts.public')

@section('title', 'Choose Payment Option')

@section('content')
@include('partials.booking-progress', ['currentStep' => 4])

@php
    $totalPrice = $services->sum('price');
    $depositAmount = round($totalPrice * 0.4);
    $isLocal = app()->environment('local');
@endphp

<div class="container my-5">
    @if ($isLocal)
        <div class="alert alert-warning text-center shadow-sm">
            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
            Payments are disabled in local mode. You can simulate payment or enable it after deployment.
        </div>
    @endif

    <form method="POST" action="{{ route('booking.step4.submit') }}">
        @csrf
        <div class="row g-5">
            <!-- Help Sidebar -->
            <div class="col-md-3 d-none d-md-block border-end pe-4" data-aos="fade-right">
                <div class="text-center text-md-start">
                    <img src="{{ asset('storage/images/payment-icon.svg') }}" alt="Payment Icon" class="mb-3" style="width: 50px;">
                    <h5 class="fw-bold">Payment Option</h5>
                    <p class="text-muted small">Choose to pay in full or reserve with a 40% deposit.</p>
                    <hr>
                    <p class="text-muted small mb-1">Need help?</p>
                    <a href="tel:+250788421063" class="text-decoration-none small">
                        <i class="fas fa-phone-alt me-1 text-primary"></i> 0788 421 063
                    </a>
                </div>
            </div>

            <!-- Payment Options -->
            <div class="col-12 col-md-6" data-aos="fade-up">
                <h4 class="fw-semibold mb-4">Select Your Preferred Payment</h4>

                {{-- Payment Option Type --}}
                <div class="vstack gap-3 mb-4">
                    <label class="payment-option d-flex justify-content-between align-items-center p-3 shadow-sm rounded">
                        <input type="radio" name="payment_option" value="full" required>
                        <div>
                            <strong class="d-block">Full Payment</strong>
                            <small class="text-muted">Pay the full amount now — RWF {{ number_format($totalPrice) }}</small>
                        </div>
                        <i class="fas fa-credit-card fs-4 text-secondary"></i>
                    </label>

                    <label class="payment-option d-flex justify-content-between align-items-center p-3 shadow-sm rounded">
                        <input type="radio" name="payment_option" value="deposit">
                        <div>
                            <strong class="d-block">Deposit</strong>
                            <small class="text-muted">Pay 40% now — RWF {{ number_format($depositAmount) }}, pay the rest on appointment</small>
                        </div>
                        <i class="fas fa-wallet fs-4 text-secondary"></i>
                    </label>
                </div>

                {{-- Payment Phone --}}
                <div class="mb-3">
                    <label for="payment_phone" class="form-label fw-semibold">Phone Number to Pay With</label>
                    <input type="text" name="payment_phone" id="payment_phone" class="form-control" placeholder="e.g. 0788xxxxxx" value="{{ auth()->user()->phone ?? '' }}" required>
                </div>

                {{-- Payment Method (MoMo or Card) --}}
                <div class="mb-3">
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

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('booking.step3') }}" class="btn btn-outline-secondary">← Back</a>
                    <button type="submit" class="btn btn-primary px-4" id="payNowBtn" {{ $isLocal ? 'disabled' : '' }}>
                        Pay Now
                    </button>
                </div>
            </div>

            <!-- Summary -->
            <div class="col-md-3 d-none d-md-block" data-aos="fade-left">
                <div class="bg-white border rounded p-3 shadow-sm sticky-top" style="top: 100px;">
                    <h6 class="fw-bold mb-3">Booking Summary</h6>

                    @foreach ($services as $srv)
                        <p class="mb-1"><i class="fas fa-check-circle me-1 text-success"></i> {{ $srv->name }}</p>
                    @endforeach

                    <hr class="my-2">
                    <p class="mb-1"><strong>Agent:</strong> {{ $provider->name }}</p>
                    <p class="mb-1"><strong>Customer:</strong> {{ auth()->user()->name ?? 'Guest' }}</p>
                    <p class="mb-1"><strong>Date:</strong> {{ session('booking.date') }}</p>
                    <p class="mb-1"><strong>Time:</strong> {{ \Carbon\Carbon::createFromFormat('H:i', session('booking.time'))->format('h:i A') }}</p>

                    <hr class="my-2">
                    <p class="mb-1"><strong>Total:</strong> RWF {{ number_format($totalPrice) }}</p>
                    <p class="mb-0"><strong>Deposit (40%):</strong> RWF {{ number_format($depositAmount) }}</p>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .payment-option {
        border: 2px solid transparent;
        background-color: #fff;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
    }
    .payment-option:hover {
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }
    .payment-option input[type="radio"] {
        display: none;
    }
    .payment-option.selected {
        border-color: #0d6efd;
        background-color: #eaf4ff;
    }
    .payment-option.selected i {
        color: #0d6efd;
    }
</style>
@endpush

@push('scripts')
<script>
    document.querySelectorAll('.payment-option').forEach(option => {
        option.addEventListener('click', function () {
            document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            this.querySelector('input[type="radio"]').checked = true;

            const btn = document.getElementById('payNowBtn');
            const value = this.querySelector('input').value;

            btn.textContent = value === 'deposit' ? 'Pay Deposit' : 'Pay Full Amount';
        });
    });
</script>
@endpush
