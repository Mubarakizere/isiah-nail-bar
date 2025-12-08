@extends('layouts.dashboard')


@section('title', 'Notifications')

@section('content')
<div class="container my-5">
    <h2 class="fw-bold text-center mb-4">
        <i class="ph ph-bell-ringing me-1 text-primary"></i> Admin Notifications
    </h2>

    @if ($notifications->count())
        <div class="d-flex justify-content-end mb-3">
            <form method="POST" action="{{ route('admin.notifications.markAll') }}">
                @csrf
                <button class="btn btn-sm btn-outline-success">
                    <i class="ph ph-checks me-1"></i> Mark All as Read
                </button>
            </form>
        </div>

        <div class="list-group shadow-sm">
            @foreach ($notifications as $notification)
                <div class="list-group-item d-flex justify-content-between align-items-start {{ is_null($notification->read_at) ? 'bg-light' : '' }}">
                    <div>
                        <p class="mb-1">
                            <i class="ph ph-info me-1 text-secondary"></i>
                            {{ $notification->data['message'] ?? 'New notification' }}
                        </p>
                        <small class="text-muted">
                            <i class="ph ph-clock me-1"></i>
                            {{ $notification->created_at->diffForHumans() }}
                        </small>
                    </div>
                    @if (is_null($notification->read_at))
                        <form method="POST" action="{{ route('admin.notifications.mark', $notification->id) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="ph ph-check-circle"></i> Mark as Read
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $notifications->links() }}
        </div>
    @else
        <div class="text-center text-muted mt-5">
            <i class="ph ph-bell-slash fs-1 mb-3 d-block"></i>
            No notifications found.
        </div>
    @endif
</div>
@endsection
