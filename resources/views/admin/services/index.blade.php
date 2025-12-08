@extends('layouts.dashboard')

@section('title', 'Manage Services')

@section('content')
<div class="container my-5">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
        <h4 class="fw-bold mb-0"><i class="ph ph-scissors me-1"></i> Manage Services</h4>
        <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
            <i class="ph ph-plus-circle me-1"></i> Add New Service
        </a>
    </div>
<br>
    {{-- Filter Form --}}
    <form method="GET" class="row g-3 mb-4 bg-white border rounded-4 shadow-sm p-3" data-aos="fade-up">
    <div class="col-md-3">
        <input type="text" name="search" class="form-control" placeholder="Search Service" value="{{ request('search') }}">
    </div>

    <div class="col-md-3">
        <select name="category_id" class="form-select">
            <option value="">All Categories</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2">
        <input type="number" name="min_price" class="form-control" placeholder="Min Price" value="{{ request('min_price') }}">
    </div>

    <div class="col-md-2">
        <input type="number" name="max_price" class="form-control" placeholder="Max Price" value="{{ request('max_price') }}">
    </div>

    <div class="col-md-2">
        <select name="status" class="form-select">
            <option value="">All Statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                    {{ ucfirst($status) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-12 col-md-12 d-grid">
        <button class="btn btn-outline-primary" type="submit">
            <i class="ph ph-funnel me-1"></i> Filter Services
        </button>
    </div>
</form>


    {{-- Service Table --}}
    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Service</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($services as $service)
                        <tr>
                            <td class="fw-semibold">{{ $service->name }}</td>
                            <td>{{ $service->category->name ?? '—' }}</td>
                            <td>RWF {{ number_format($service->price) }}</td>
                            <td>
                                @php
                                    $status = $service->status;
                                    $badge = match($status) {
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $badge }}">{{ ucfirst($status) }}</span>
                            </td>
                            <td>{{ $service->created_at->diffForHumans() }}</td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end flex-wrap gap-1">
                                    <a href="{{ route('admin.services.edit', $service->id) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>

                                    <form action="{{ route('admin.services.destroy', $service->id) }}" method="POST" onsubmit="return confirm('Delete this service?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="ph ph-trash"></i>
                                        </button>
                                    </form>

                                    @if ($status === 'pending')
                                        <form action="{{ route('admin.services.approve', $service->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-success">
                                                <i class="ph ph-check"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.services.decline', $service->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-danger">
                                                <i class="ph ph-x"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No services found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            <div class="mt-4 d-flex justify-content-center">
                {{ $services->withQueryString()->links('vendor.pagination.bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
