@extends('layouts.dashboard')

@section('title', 'My Reviews')

@section('content')
<div class="container py-5">
    <h2 class="fw-bold text-center mb-4 text-dark">
        <i class="ph ph-star text-warning me-1"></i> My Reviews
    </h2>

    @if(session('success'))
        <div class="alert alert-success text-center shadow-sm rounded">{{ session('success') }}</div>
    @endif

    @forelse ($reviews as $review)
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">
                            <i class="ph ph-scissors me-1 text-secondary"></i> {{ $review->service->name }}
                        </h5>

                        <div class="mb-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="ph {{ $i <= $review->rating ? 'ph-star-fill text-warning' : 'ph-star text-muted' }}"></i>
                            @endfor
                            <small class="text-muted ms-2">({{ $review->rating }} / 5)</small>
                        </div>

                        @if($review->booking && $review->booking->provider)
                            <p class="text-muted mb-0">
                                <i class="ph ph-user me-1"></i> With {{ $review->booking->provider->name }}
                            </p>
                        @endif

                        <p class="text-muted small mb-0">
                            <i class="ph ph-calendar me-1"></i> {{ $review->created_at->format('F j, Y') }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('customer.reviews.destroy', $review->id) }}" onsubmit="return confirm('Are you sure you want to delete this review?')" class="ms-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Review">
                            <i class="ph ph-trash"></i>
                        </button>
                    </form>
                </div>

                <hr class="my-3">
                <p class="mb-0">{{ $review->comment }}</p>
            </div>
        </div>
    @empty
        <div class="text-center text-muted mt-5">
            <i class="ph ph-star text-secondary fs-1 mb-3 d-block"></i>
            <p class="mb-0">You haven’t left any reviews yet.</p>
        </div>
    @endforelse

    <div class="d-flex justify-content-center mt-4">
        {{ $reviews->links() }}
    </div>
</div>
@endsection
