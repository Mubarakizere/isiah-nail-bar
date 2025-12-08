@extends('layouts.dashboard')

@section('title', 'Add New Tag')

@section('content')
<div class="container my-5">
    <h2 class="fw-bold text-center mb-4">
        <i class="ph ph-tag-simple me-1 text-primary"></i> Add New Tag
    </h2>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.tags.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Tag Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter tag name" required>
                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.tags.index') }}" class="btn btn-outline-secondary">← Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ph ph-floppy-disk me-1"></i> Save Tag
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
