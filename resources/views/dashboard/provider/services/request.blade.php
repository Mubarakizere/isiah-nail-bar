@extends('layouts.dashboard')

@section('title', 'Request New Service')

@section('content')
<div class="container py-4">
    <div class="mb-4 text-center">
        <h4 class="fw-bold">
            <i class="ph ph-plus-circle text-primary me-1"></i> Request New Service
        </h4>
        <p class="text-muted">Submit a request for a new service to be reviewed by admin.</p>
    </div>

    <form action="{{ route('provider.services.store') }}" method="POST" enctype="multipart/form-data" class="card shadow-sm border-0 p-4">
        @csrf

        {{-- Name --}}
        <div class="mb-3">
            <label class="form-label fw-semibold"><i class="ph ph-scissors me-1 text-muted"></i> Service Name</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- Description --}}
        <div class="mb-3">
            <label class="form-label fw-semibold"><i class="ph ph-file-text me-1 text-muted"></i> Description</label>
            <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
            @error('description') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="row g-3">
            {{-- Price --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold"><i class="ph ph-currency-circle-dollar me-1 text-muted"></i> Price (RWF)</label>
                <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}">
                @error('price') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            {{-- Duration --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold"><i class="ph ph-clock me-1 text-muted"></i> Duration (minutes)</label>
                <input type="number" name="duration_minutes" class="form-control @error('duration_minutes') is-invalid @enderror" value="{{ old('duration_minutes') }}">
                @error('duration_minutes') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
        </div>

        {{-- Category --}}
        <div class="mt-3 mb-3">
            <label class="form-label fw-semibold"><i class="ph ph-tag me-1 text-muted"></i> Category</label>
            <input type="text" name="category" class="form-control @error('category') is-invalid @enderror" value="{{ old('category') }}">
            @error('category') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- Image Upload --}}
        <div class="mb-4">
            <label class="form-label fw-semibold"><i class="ph ph-image me-1 text-muted"></i> Service Image</label>
            <input type="file" name="image" accept="image/*" class="form-control @error('image') is-invalid @enderror" onchange="previewImage(event)">
            @error('image') <small class="text-danger">{{ $message }}</small> @enderror

            <div class="mt-3">
                <img id="imagePreview" src="#" alt="Preview" style="display: none; max-height: 200px;" class="img-thumbnail">
            </div>
        </div>

        {{-- Submit Button --}}
        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('provider.services.index') }}" class="btn btn-outline-secondary">
                <i class="ph ph-arrow-left me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="ph ph-upload-simple me-1"></i> Submit Request
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function previewImage(event) {
        const input = event.target;
        const preview = document.getElementById('imagePreview');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
