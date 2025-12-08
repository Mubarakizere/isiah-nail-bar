@extends('layouts.dashboard')

@section('title', 'My Services')

@section('content')
<div class="container py-4">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="fw-bold mb-0">
            <i class="ph ph-scissors me-1 text-primary"></i> My Services
        </h4>
        <a href="{{ route('provider.services.request') }}" class="btn btn-sm btn-outline-primary">
            <i class="ph ph-plus me-1"></i> Request New Service
        </a>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success text-center shadow-sm">{{ session('success') }}</div>
    @endif

    @if($services->count())
        <!-- Services Table -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body table-responsive">
                <table class="table align-middle table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($services as $service)
                            <tr>
                                <td>{{ $service->id }}</td>
                                <td>
                                    <img src="{{ asset('storage/' . ($service->image ?? 'placeholder.png')) }}" width="60" class="rounded shadow-sm" alt="Service Image">
                                </td>
                                <td class="fw-semibold">{{ $service->name }}</td>
                                <td>{{ $service->category->name }}</td>
                                <td>RWF {{ number_format($service->price) }}</td>
                                <td>{{ $service->duration_minutes }} min</td>
                                <td>
                                    <span class="badge bg-{{ $service->approved ? 'success' : 'warning' }}">
                                        {{ $service->approved ? 'Approved' : 'Pending' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                                        <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#serviceModal{{ $service->id }}" title="View Details">
                                            <i class="ph ph-eye"></i>
                                        </button>

                                        @if($service->provider_id === auth()->user()->provider->id)
                                            <a href="{{ route('provider.services.edit', $service->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="ph ph-pencil-simple"></i>
                                            </a>
                                            <form method="POST" action="{{ route('provider.services.destroy', $service->id) }}" onsubmit="return confirm('Are you sure you want to delete this service?')" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="ph ph-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">Assigned</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $services->withQueryString()->links() }}
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-warning text-center shadow-sm">
            <i class="ph ph-warning-circle me-1"></i> No services assigned yet.
        </div>
    @endif
</div>

<!-- View Modals -->
@foreach($services as $service)
    <div class="modal fade" id="serviceModal{{ $service->id }}" tabindex="-1" aria-labelledby="modalLabel{{ $service->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel{{ $service->id }}">{{ $service->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <img src="{{ asset('storage/' . ($service->image ?? 'placeholder.png')) }}" class="img-fluid rounded mb-3 shadow" alt="{{ $service->name }}">
                    <p><strong>Category:</strong> {{ $service->category->name }}</p>
                    <p><strong>Price:</strong> RWF {{ number_format($service->price) }}</p>
                    <p><strong>Duration:</strong> {{ $service->duration_minutes }} minutes</p>
                    <p><strong>Description:</strong><br>{{ $service->description ?: '—' }}</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
