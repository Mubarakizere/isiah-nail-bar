@extends('layouts.public')

@section('title', $service->name)

@section('content')
<div class="container my-5">
    <div class="row g-5 align-items-start">
        {{-- 🖼️ Image Section --}}
        <div class="col-12 col-md-6" data-aos="fade-right">
            @if($service->image)
                <img src="{{ asset('storage/' . $service->image) }}" 
                     class="img-fluid rounded shadow-sm w-100 service-image" 
                     alt="{{ $service->name }}">
            @else
                <img src="https://via.placeholder.com/600x400?text=No+Image" 
                     class="img-fluid rounded shadow-sm" 
                     alt="No Image">
            @endif
        </div>

        {{-- 📝 Details Section --}}
        <div class="col-12 col-md-6" data-aos="fade-left">
            <h2 class="fw-bold mb-3 text-dark">{{ $service->name }}</h2>

            <p class="text-muted mb-4">{{ $service->description }}</p>

            @if($service->category)
                <span class="badge bg-white border text-muted mb-3"><i class="ph ph-tag me-1"></i>{{ $service->category->name }}</span>
            @endif

            <ul class="list-unstyled text-muted fs-6 mb-4">
                <li class="mb-2"><i class="ph ph-clock me-2 text-primary"></i> <strong>Duration:</strong> {{ $service->duration_minutes }} minutes</li>
                <li class="mb-2"><i class="ph ph-currency-circle-dollar me-2 text-primary"></i> <strong>Price:</strong> RWF {{ number_format($service->price) }}</li>
                @if($service->provider)
                    <li><i class="ph ph-user me-2 text-primary"></i> <strong>Provider:</strong> {{ $service->provider->name }}</li>
                @endif
            </ul>

            <a href="{{ route('booking.step1', ['service_id' => $service->id]) }}" 
               class="btn btn-success btn-lg w-100 animate__animated animate__pulse animate__infinite">
                <i class="ph ph-calendar-check me-1"></i> Book This Service
            </a>
        </div>
    </div>

    {{-- 💬 Reviews --}}
    @if($service->reviews->count())
    <div class="mt-5" data-aos="fade-up">
        <h4 class="fw-bold mb-4">
            <i class="ph ph-chat-circle-text me-2 text-secondary"></i> Customer Reviews
        </h4>

        @foreach($service->reviews as $review)
            <div class="border rounded p-3 mb-3 shadow-sm review-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>{{ $review->user->name ?? 'Anonymous' }}</strong>
                    <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                </div>
                <p class="mb-1">{{ $review->comment }}</p>
            </div>
        @endforeach
    </div>
    @endif

    {{-- ✍️ Leave Review --}}
    @auth
        @if(auth()->user()->hasBookedService($service->id))
        <div class="mt-5" data-aos="fade-up">
            <h5 class="fw-bold mb-3">Leave a Review</h5>

            <form method="POST" action="{{ route('reviews.store') }}" class="p-4 bg-light rounded-3 shadow-sm">
                @csrf
                <input type="hidden" name="service_id" value="{{ $service->id }}">

                <div class="mb-3">
                    <label class="form-label">Rating (1 to 5)</label>
                    <select name="rating" class="form-select shadow-sm" required>
                        <option value="">Select rating</option>
                        @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}">{{ $i }} ★</option>
                        @endfor
                    </select>
                    @error('rating') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Comment</label>
                    <textarea name="comment" rows="3" class="form-control shadow-sm" required></textarea>
                    @error('comment') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-paper-plane-tilt me-1"></i> Submit Review
                </button>
            </form>
        </div>
        @endif
    @endauth
</div>

{{-- ✨ Optional CSS --}}
<style>
    .service-image {
        transition: transform 0.3s ease;
    }

    .service-image:hover {
        transform: scale(1.03);
    }

    .review-card {
        transition: box-shadow 0.3s ease;
    }

    .review-card:hover {
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    }

    select.form-select:focus, textarea:focus, input:focus {
        box-shadow: 0 0 0 0.2rem rgba(25,135,84,0.25);
    }
</style>
@endsection
