@extends('layouts.dashboard')

@section('title', 'My Notifications')

@section('content')
<div class="container my-4">
    <h2 class="text-center fw-bold mb-4">
        <i class="ph ph-bell text-primary me-2"></i> Notifications
    </h2>

    @if ($notifications->count())
        <div class="d-flex justify-content-end mb-3">
            <form action="{{ route('notifications.markAll') }}" method="POST">
                @csrf
                <button class="btn btn-sm btn-outline-secondary">
                    <i class="ph ph-check-circle me-1"></i> Mark All as Read
                </button>
            </form>
        </div>

        <ul class="list-group shadow-sm">
            @foreach ($notifications as $notification)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">{{ $notification->data['message'] ?? 'You have a new notification.' }}</p>
                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>

                    @if (is_null($notification->read_at))
                        <form action="{{ route('notifications.mark', $notification->id) }}" method="POST">
                            @csrf
                            <button class="btn btn-sm btn-outline-primary">Mark as Read</button>
                        </form>
                    @else
                        <span class="badge bg-light text-secondary">Read</span>
                    @endif
                </li>
            @endforeach
        </ul>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @else
        <div class="alert alert-info text-center">
            <i class="ph ph-info me-1"></i> No notifications yet.
        </div>
    @endif
</div>
@endsection
