@extends('layouts.dashboard')

@section('title', 'Contact Messages')

@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            <i class="ph ph-envelope-simple text-primary me-2"></i> Contact Messages
        </h2>
        <a href="{{ route('dashboard.admin') }}" class="btn btn-outline-secondary btn-sm">
            <i class="ph ph-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    @if($messages->count())
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th><i class="ph ph-user text-muted"></i> Name</th>
                                <th><i class="ph ph-at text-muted"></i> Email</th>
                                <th><i class="ph ph-chat-text text-muted"></i> Subject</th>
                                <th><i class="ph ph-envelope-open text-muted"></i> Message</th>
                                <th><i class="ph ph-clock text-muted"></i> Sent</th>
                                <th class="text-end"><i class="ph ph-gear text-muted"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($messages as $message)
                                <tr>
                                    <td class="fw-semibold">{{ $message->id }}</td>
                                    <td>{{ $message->name }}</td>
                                    <td><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></td>
                                    <td>{{ $message->subject }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewMessage{{ $message->id }}">
                                            <i class="ph ph-eye"></i> View
                                        </button>
                                    </td>
                                    <td><span class="badge bg-light text-muted">{{ $message->created_at->diffForHumans() }}</span></td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('admin.messages.destroy', $message->id) }}" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this message?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="ph ph-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Message Modal -->
                                <div class="modal fade" id="viewMessage{{ $message->id }}" tabindex="-1" aria-labelledby="modalLabel{{ $message->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title" id="modalLabel{{ $message->id }}">
                                                    Message from {{ $message->name }}
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Subject:</strong> {{ $message->subject }}</p>
                                                <hr>
                                                <p>{{ $message->message }}</p>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    Close
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-3 py-3">
                    {{ $messages->links() }}
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info text-center shadow-sm">
            <i class="ph ph-info me-1"></i> No messages received yet.
        </div>
    @endif
</div>
@endsection
