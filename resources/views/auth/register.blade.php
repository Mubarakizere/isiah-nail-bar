@extends('layouts.auth')

@section('title', 'Register')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-sm p-4 border-0 rounded-4" style="width: 100%; max-width: 450px;">
        <div class="text-center mb-4">
            <a href="{{ url('/') }}"><img src="{{ asset('storage/logo.png') }}" alt="Isiah Logo" style="width: 80px;"></a>
            <h4 class="fw-bold mt-3">Create Your Account</h4>
            <p class="text-muted small">Join the Isiah experience</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                </div>
                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                </div>
                @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <!-- ✅ Add Phone Field -->
<div class="mb-3">
    <label class="form-label">Phone</label>
    <div class="input-group">
        <span class="input-group-text"><i class="fas fa-phone"></i></span>
        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" required>
    </div>
    @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
</div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" required>
                </div>
                @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-dark rounded-pill">
                    <i class="fas fa-user-plus me-1"></i> Register
                </button>
            </div>

            <div class="text-center">
                <a href="{{ route('login') }}" class="text-decoration-none small">Already have an account? Login →</a>
            </div>
        </form>
    </div>
</div>
@endsection
