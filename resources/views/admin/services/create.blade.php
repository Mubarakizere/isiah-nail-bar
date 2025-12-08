@extends('layouts.dashboard')

@section('title', 'Add New Service')

@section('content')
<div class="container my-5">
    <h4 class="fw-bold text-center mb-4"><i class="ph ph-plus-circle me-1"></i> Add New Service</h4>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.services.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Service Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Duration (minutes)</label>
                        <input type="number" name="duration_minutes" class="form-control" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Price (RWF)</label>
                        <input type="number" name="price" class="form-control" required>
                    </div>

                    <div class="col-md-6">
    <label class="form-label">Category</label>
    <select name="category_id" class="form-select" required>
        <option value="">Select a category</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}">{{ $category->name }}</option>
        @endforeach
    </select>
</div>


                    <div class="col-md-6">
                        <label class="form-label">Tags (comma separated)</label>
                        <input type="text" name="tags" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Service Image</label>
                        <input type="file" name="image" class="form-control">
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary">← Cancel</a>
                    <button type="submit" class="btn btn-success"><i class="ph ph-check-circle me-1"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
