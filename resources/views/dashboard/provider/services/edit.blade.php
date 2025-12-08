@extends('layouts.dashboard')

@section('title', 'Edit Service')

@section('content')
<div class="container py-4">
    <div class="mb-4 text-center">
        <h4 class="fw-bold">
            <i class="ph ph-pencil-simple-line text-primary me-1"></i> Edit Service
        </h4>
        <p class="text-muted">Update your service details below.</p>
    </div>

    <form action="{{ route('provider.services.update', $service->id) }}" method="POST" enctype="multipart/form-data" class="card shadow-sm border-0 p-4">
        @csrf
        @method('PUT')

        {{-- Service Name --}}
        <div class="mb-3">
            <label class="form-label fw-semibold"><i class="ph ph-scissors me-1 text-muted"></i> Service Name</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $service->name) }}">
            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- Description --}}
        <div class="mb-3">
            <label class="form-label fw-semibold"><i class="ph ph-file-text me-1 text-muted"></i> Description</label>
            <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $service->description) }}</textarea>
            @error('description') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="row g-3">
            {{-- Price --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold"><i class="ph ph-currency-circle-dollar me-1 text-muted"></i> Price (RWF)</label>
                <input type="number" name="price" class="form-control @error('price') is-invalid @enderror"
                       value="{{ old('price', $service->price) }}">
                @error('price') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Duration --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold"><i class="ph ph-clock me-1 text-muted"></i> Duration (minutes)</label>
                <input type="number" name="duration_minutes" class="form-control @error('duration_minutes') is-invalid @enderror"
                       value="{{ old('duration_minutes', $service->duration_minutes) }}">
                @error('duration_minutes') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
        </div>

        {{-- Category --}}
        <div class="mt-3 mb-3">
            <label class="form-label fw-semibold"><i class="ph ph-tag me-1 text-muted"></i> Category</label>
            <input type="text" name="category" class="form-control @error('category') is-invalid @enderror"
                   value="{{ old('category', $service->category) }}">
            @error('category') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- Image Upload with Preview --}}
        <div class="mb-4">
            <label class="form-label fw-semibold"><i class="ph ph-image me-1 text-muted"></i> Image</label><br>

            <img id="previewImage" src="{{ $service->image ? asset('storage/' . $service->image) : 'https://via.placeholder.com/100x100?text=Preview' }}"
                 class="img-thumbnail mb-2" width="100" alt="Preview">

            <input type="file" name="image" accept="image/*" class="form-control mt-2 @error('image') is-invalid @enderror" onchange="previewSelectedImage(event)">
            @error('image') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- Submit Buttons --}}
        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('provider.services.index') }}" class="btn btn-outline-secondary">
                <i class="ph ph-arrow-left me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="ph ph-check-circle me-1"></i> Update Service
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function previewSelectedImage(event) {
        const reader = new FileReader();
        reader.onload = function () {
            document.getElementById('previewImage').src = reader.result;
        }
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
@endpush
