@extends('layouts.dashboard')

@section('title', 'Edit Provider')

@section('content')
<div class="container my-5">
    <h2 class="fw-bold text-center mb-4">Edit Provider Profile</h2>

    @if ($errors->any())
        <div class="alert alert-danger small">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>🔴 {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.providers.update', $provider->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card shadow-sm p-4 mb-4">
            <div class="mb-3">
                <label class="form-label fw-semibold">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $provider->name) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $provider->phone) }}">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $provider->email) }}">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Bio</label>
                <textarea name="bio" class="form-control" rows="3">{{ old('bio', $provider->bio) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold d-block">Current Photo:</label>
                @if($provider->photo)
                    <img src="{{ asset('storage/'.$provider->photo) }}" alt="Provider Photo" class="img-thumbnail mb-2" style="max-width: 150px;">
                @else
                    <p class="text-muted">No photo uploaded.</p>
                @endif
                <input type="file" name="photo" class="form-control mt-2">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Assign Services</label>
                <div class="row row-cols-1 row-cols-md-2 g-2">
                    @foreach ($services as $service)
                        <div class="col">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="services[]" value="{{ $service->id }}"
                                       id="service-{{ $service->id }}"
                                       {{ in_array($service->id, old('services', $provider->services->pluck('id')->toArray())) ? 'checked' : '' }}>
                                <label class="form-check-label" for="service-{{ $service->id }}">
                                    {{ $service->name }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('admin.providers.index') }}" class="btn btn-outline-secondary">← Back</a>
            <button type="submit" class="btn btn-primary">💾 Update Provider</button>
        </div>
    </form>
</div>
@endsection
