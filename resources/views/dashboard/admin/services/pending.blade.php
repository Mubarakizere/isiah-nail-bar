@extends('layouts.dashboard')

@section('title', 'Pending Services')

@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <i class="ph ph-hourglass-simple-high me-2 text-warning"></i>
            Pending Services for Approval
        </h4>
    </div>

    @if ($pendingServices->count())
        <div class="row g-4">
            @foreach ($pendingServices as $service)
                <div class="col-md-6 col-lg-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div class="mb-3">
                                <h5 class="fw-bold mb-1 d-flex align-items-center justify-content-between">
                                    {{ $service->name }}
                                    <span class="badge bg-warning text-dark">Pending</span>
                                </h5>

                                <p class="mb-1 text-muted">
    <i class="ph ph-scissors me-1"></i> {{ $service->category->name ?? '—' }}
</p>


                                <p class="mb-1 text-muted">
                                    <i class="ph ph-clock me-1"></i> {{ $service->duration_minutes }} min
                                </p>

                                <p class="mb-1 text-muted">
                                    <i class="ph ph-currency-circle-dollar me-1"></i>
                                    RWF {{ number_format($service->price) }}
                                </p>

                                <p class="text-muted small mb-2">
                                    <i class="ph ph-user me-1"></i>
                                    Submitted by: <strong>{{ $service->provider->user->name ?? 'Unknown' }}</strong>
                                </p>

                                <p class="text-muted small">
                                    {{ \Illuminate\Support\Str::limit($service->description, 100) }}
                                </p>
                            </div>

                            <div class="d-flex gap-2">
                                <form action="{{ route('admin.services.approve', $service->id) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-success w-100">
                                        <i class="ph ph-check-circle me-1"></i> Approve
                                    </button>
                                </form>
                                <form action="{{ route('admin.services.reject', $service->id) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger w-100">
                                        <i class="ph ph-x-circle me-1"></i> Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $pendingServices->links() }}
        </div>
    @else
        <div class="alert alert-info text-center">
            <i class="ph ph-info me-1"></i> No pending services at the moment.
        </div>
    @endif
</div>
@endsection
