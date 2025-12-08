@extends('layouts.dashboard')

@section('title', 'Edit Tag')

@section('content')
<div class="container my-5">
    <h2 class="fw-bold text-center mb-4">
        <i class="ph ph-pencil-simple text-primary me-1"></i> Edit Tag
    </h2>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.tags.update', $tag->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Tag Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $tag->name) }}" required>
                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.tags.index') }}" class="btn btn-outline-secondary">← Cancel</a>
                    <button type="submit" class="btn btn-success">
                        <i class="ph ph-check-circle me-1"></i> Update Tag
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
