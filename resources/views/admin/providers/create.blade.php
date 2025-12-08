@extends('layouts.dashboard')

@section('title', 'Add Provider')

@section('content')
<div class="container my-5">
    <h2 class="fw-bold text-center mb-4">Add New Provider</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 small">
                @foreach ($errors->all() as $error)
                    <li>🔴 {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.providers.store') }}" enctype="multipart/form-data" class="card shadow-sm p-4 border-0">
        @csrf

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Full Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Photo</label>
                <input type="file" name="photo" class="form-control">
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold">Bio</label>
                <textarea name="bio" rows="3" class="form-control">{{ old('bio') }}</textarea>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold">Assign Services</label>
<div class="row row-cols-1 row-cols-md-2 g-2">
    @foreach ($services as $service)
        <div class="col">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="services[]" value="{{ $service->id }}"
                       id="service-{{ $service->id }}" {{ in_array($service->id, old('services', [])) ? 'checked' : '' }}>
                <label class="form-check-label" for="service-{{ $service->id }}">
                    {{ $service->name }}
                </label>
            </div>
        </div>
    @endforeach
</div>

            </div>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('admin.providers.index') }}" class="btn btn-outline-secondary">← Cancel</a>
            <button type="submit" class="btn btn-primary px-4">💾 Save Provider</button>
        </div>
    </form>
</div>
@endsection
