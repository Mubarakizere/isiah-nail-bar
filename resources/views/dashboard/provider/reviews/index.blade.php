@extends('dashboard.provider.layout')

@section('title', 'Customer Reviews')

@section('content')
<div class="container my-5">
    <h2 class="fw-bold text-center mb-4">
        <i class="ph ph-chat-circle-dots me-1 text-primary"></i> Customer Reviews
    </h2>

    @if ($reviews->count())
        @foreach ($reviews as $review)
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h5 class="fw-bold mb-1">
                                <i class="ph ph-user me-1 text-muted"></i>
                                {{ $review->customer->user->name ?? 'Unknown Customer' }}
                            </h5>
                            <p class="text-muted mb-1">
                                <i class="ph ph-scissors me-1"></i>
                                <strong>Service:</strong> {{ $review->service->name ?? '—' }}
                            </p>
                            <p class="mb-2">{{ $review->message }}</p>

                            <div class="small text-muted">
                                <i class="ph ph-star-fill text-warning me-1"></i>
                                {{ $review->rating }} / 5
                                &middot;
                                <i class="ph ph-clock me-1"></i>
                                {{ $review->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="d-flex justify-content-center mt-4">
            {{ $reviews->links() }}
        </div>
    @else
        <div class="text-center text-muted">
            <i class="ph ph-chat-centered-dots fs-1 mb-3 d-block"></i>
            You haven’t received any reviews yet.
        </div>
    @endif
</div>
@endsection
