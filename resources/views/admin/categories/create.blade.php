@extends('layouts.dashboard')

@section('title', 'Add Category')

@section('content')
<div class="container my-5">
    <h2 class="fw-bold text-center mb-4"><i class="ph ph-plus-circle me-1 text-primary"></i> Add New Category</h2>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Category Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">← Cancel</a>
                    <button type="submit" class="btn btn-dark">
                        <i class="ph ph-check-circle me-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
