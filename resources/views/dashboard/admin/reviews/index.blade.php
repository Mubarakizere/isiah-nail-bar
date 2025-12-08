@extends('layouts.dashboard')

@section('title', 'Customer Reviews')

@section('content')
<div class="container my-5">
    <h2 class="fw-bold text-center mb-4">🌟 Customer Reviews</h2>



    @if($reviews->count())
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reviews as $review)
                        <tr>
                            <td>{{ $review->customer->user->name ?? 'N/A' }}</td>
                            <td>{{ $review->service->name }}</td>
                            <td>
                                @for ($i = 0; $i < $review->rating; $i++)
                                    ⭐
                                @endfor
                            </td>
                            <td>{{ $review->comment ?? '—' }}</td>
                            <td>{{ $review->created_at->diffForHumans() }}</td>
                            <td>
                                <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this review?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">🗑️ Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $reviews->links() }}
        </div>
    @else
        <p class="text-muted text-center">No reviews found.</p>
    @endif
</div>
@endsection
