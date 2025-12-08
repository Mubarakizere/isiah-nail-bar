@extends('layouts.dashboard')

@section('title', 'Edit Service')

@section('content')
<div class="container my-5">
    <h4 class="fw-bold text-center mb-4"><i class="ph ph-pencil-simple me-1"></i> Edit Service</h4>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.services.update', $service->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Service Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $service->name) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Duration (minutes)</label>
                        <input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes', $service->duration_minutes) }}" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" required>{{ old('description', $service->description) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Price (RWF)</label>
                        <input type="number" name="price" class="form-control" value="{{ old('price', $service->price) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select a category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id', $service->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tags (comma separated)</label>
                        <input type="text" name="tags" class="form-control" value="{{ old('tags', $service->tags->pluck('tag')->implode(', ')) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Change Image (optional)</label>
                        <input type="file" name="image" class="form-control">
                        @if ($service->image)
                            <img src="{{ asset('storage/' . $service->image) }}" class="img-thumbnail mt-2" style="max-height: 120px;">
                        @endif
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary">← Back</a>
                    <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk me-1"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
