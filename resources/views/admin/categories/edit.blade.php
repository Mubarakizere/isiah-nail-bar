@extends('layouts.dashboard')

@section('title', 'Edit Category')

@section('content')
<div class="container my-5">
    <h2 class="fw-bold text-center mb-4"><i class="ph ph-pencil-simple me-1 text-primary"></i> Edit Category</h2>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-semibold">Category Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">← Cancel</a>
                    <button type="submit" class="btn btn-dark">
                        <i class="ph ph-check-circle me-1"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
