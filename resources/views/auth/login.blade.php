@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-sm p-4 border-0 rounded-4" style="width: 100%; max-width: 420px;">
        <div class="text-center mb-4">
            <img src="{{ asset('storage/logo.png') }}" alt="Isiah Logo" style="width: 80px;">
            <h4 class="fw-bold mt-3">Welcome Back</h4>
            <p class="text-muted small">Sign in to continue</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success small">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
                </div>
                @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-1">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" required>
                </div>
                @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            {{-- Forgot Password Link --}}
            <div class="mb-3 text-end">
                <a href="{{ route('password.request') }}" class="small text-decoration-none">Forgot your password?</a>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Remember Me</label>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-dark rounded-pill">
                    <i class="fas fa-sign-in-alt me-1"></i> Login
                </button>
            </div>

            <div class="text-center">
                <a href="{{ route('register') }}" class="text-decoration-none small">Don’t have an account? Register →</a>
            </div>
        </form>
    </div>
</div>
@endsection
