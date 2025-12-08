@extends('layouts.dashboard')

@section('title', 'Edit Profile')

@section('content')
<div class="container">
    <h2 class="fw-bold text-center mb-4">
        <i class="ph ph-user-circle me-2"></i> Edit Your Profile
    </h2>



    @if($errors->any())
        <div class="alert alert-danger text-center small">
            {{ implode('', $errors->all(':message')) }}
        </div>
    @endif

    {{-- Profile Info --}}
    <form method="POST" action="{{ route('profile.update') }}" class="mb-5">
        @csrf
        @method('PATCH')

        <div class="card shadow-sm p-4">
            <h5 class="fw-bold mb-3">
                <i class="ph ph-identification-card me-1"></i> Account Information
            </h5>

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                    <i class="ph ph-arrow-left me-1"></i> Back
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-floppy-disk me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </form>

    {{-- Password Update --}}
    <form method="POST" action="{{ route('profile.password.update') }}">
        @csrf
        @method('PATCH')

        <div class="card shadow-sm p-4">
            <h5 class="fw-bold mb-3">
                <i class="ph ph-lock-key-open me-1"></i> Change Password
            </h5>

            <div class="mb-3">
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-warning">
                    <i class="ph ph-shield-check me-1"></i> Update Password
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
