@extends('layouts.dashboard')

@section('title', 'Leave a Review')

@section('content')
<div class="container my-5" style="max-width: 700px;">
    <h2 class="fw-bold text-center mb-4">
        <i class="ph ph-pencil-line text-primary me-1"></i> Leave a Review
    </h2>

    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif

    @if($booking)
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h5 class="fw-bold mb-2">
                    <i class="ph ph-scissors me-1 text-muted"></i> 
                    Services You Booked:
                </h5>
                <ul class="list-unstyled">
                    @foreach($booking->services as $service)
                        <li><i class="ph ph-check-circle text-success me-1"></i> {{ $service->name }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <form action="{{ route('customer.reviews.store') }}" method="POST">
            @csrf
            <input type="hidden" name="booking_id" value="{{ $booking->id }}">
            
            <div class="mb-3">
                <label for="service_id" class="form-label">Select Service</label>
                <select name="service_id" id="service_id" class="form-select" required>
                    <option value="">Choose one</option>
                    @foreach($booking->services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="rating" class="form-label">Rating (1 to 5)</label>
                <select name="rating" id="rating" class="form-select" required>
                    @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}">{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                    @endfor
                </select>
            </div>

            <div class="mb-3">
                <label for="comment" class="form-label">Your Feedback</label>
                <textarea name="comment" id="comment" rows="4" class="form-control" placeholder="Write your review..." required></textarea>
            </div>

            <button type="submit" class="btn btn-dark w-100 rounded-pill">
                <i class="ph ph-paper-plane me-1"></i> Submit Review
            </button>
        </form>
    @else
        <div class="alert alert-warning text-center">
            Booking not found or unauthorized access.
        </div>
    @endif
</div>
@endsection
