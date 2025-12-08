@extends('layouts.dashboard')

@section('title', 'Manage Providers')

@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            <i class="ph ph-users-three me-2 text-primary"></i> Manage Providers
        </h2>
        <a href="{{ route('admin.providers.create') }}" class="btn btn-primary shadow-sm">
            <i class="ph ph-plus-circle me-1"></i> Add Provider
        </a>
    </div>

    <div class="table-responsive shadow-sm border rounded bg-white">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($providers as $provider)
                    <tr>
                        <td class="fw-semibold">{{ $provider->name }}</td>
                        <td>{{ $provider->phone ?? '—' }}</td>
                        <td>
                            <span class="badge rounded-pill bg-{{ $provider->active ? 'success' : 'secondary' }}">
                                {{ $provider->active ? 'Approved' : 'Pending' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group" role="group" aria-label="Actions">
                                {{-- Edit --}}
                                <a href="{{ route('admin.providers.edit', $provider->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Provider">
                                    <i class="ph ph-pencil-simple"></i>
                                </a>

                                {{-- Working Hours --}}
                                <a href="{{ route('admin.providers.hours.edit', $provider->id) }}" class="btn btn-sm btn-outline-info" title="Working Hours">
                                    <i class="ph ph-clock"></i>
                                </a>

                                {{-- Performance --}}
                                <a href="{{ route('admin.providers.performance.single', $provider->id) }}" class="btn btn-sm btn-outline-dark" title="Performance">
                                    <i class="ph ph-chart-line"></i>
                                </a>

                                {{-- Approve / Decline --}}
                                @if (!$provider->active)
                                    <form action="{{ route('admin.providers.approve', $provider->id) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                            <i class="ph ph-check-circle"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.providers.decline', $provider->id) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Decline">
                                            <i class="ph ph-x-circle"></i>
                                        </button>
                                    </form>
                                @endif

                                {{-- Delete Button triggers Modal --}}
                                <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteModal"
                                    data-id="{{ $provider->id }}"
                                    data-name="{{ $provider->name }}"
                                    title="Delete">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            <i class="ph ph-user-x fs-4 mb-2 d-block"></i>
                            No providers found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="ph ph-trash me-2"></i> Confirm Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete <strong id="providerName"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const providerId = button.getAttribute('data-id');
        const providerName = button.getAttribute('data-name');

        document.getElementById('providerName').textContent = providerName;

        // Set correct action with double admin prefix
        document.getElementById('deleteForm').action = "{{ url('dashboard/admin/admin/providers') }}/" + providerId;
    });
</script>
@endpush
