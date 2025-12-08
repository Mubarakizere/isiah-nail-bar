@extends('layouts.payment')

@section('content')
<div class="container py-4">
    <h4 class="mb-4">Retry Payment for Booking #{{ $booking->id }}</h4>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('booking.retryPayment.post', $booking->id) }}">
        @csrf

        <div class="mb-3">
            <label for="phone" class="form-label">Phone Number</label>
            <input type="text" name="phone" class="form-control" placeholder="e.g. 0788123456" required>
        </div>

        <div class="mb-3">
            <label for="payment_method" class="form-label">Payment Method</label>
            <select name="payment_method" class="form-select" required>
                <option value="momo">MoMo</option>
                <option value="card">Card</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Proceed to Payment</button>
    </form>
</div>
@endsection
