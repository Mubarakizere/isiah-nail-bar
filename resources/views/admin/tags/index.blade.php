@extends('layouts.dashboard')

@section('title', 'Manage Tags')

@section('content')
<div class="container my-5">
    <h2 class="fw-bold mb-4 text-center">
        <i class="ph ph-tag text-primary me-1"></i> Manage Tags
    </h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-4 text-end">
        <a href="{{ route('admin.tags.create') }}" class="btn btn-primary">
            <i class="ph ph-plus-circle me-1"></i> Add New Tag
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tag Name</th>
                        <th>Created At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tags as $index => $tag)
                        <tr>
                            <td>{{ $tags->firstItem() + $index }}</td>
                            <td>{{ $tag->name }}</td>
                            <td>{{ $tag->created_at->diffForHumans() }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.tags.edit', $tag->id) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="ph ph-pencil-simple me-1"></i> Edit
                                </a>
                                <form action="{{ route('admin.tags.destroy', $tag->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this tag?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="ph ph-trash-simple me-1"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No tags found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $tags->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
