@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-sm border-0 p-4 rounded-4" style="max-width: 420px; width: 100%;">
        <div class="text-center mb-4">
            <img src="{{ asset('storage/logo.png') }}" alt="Logo" style="width: 80px;">
            <h5 class="fw-bold mt-3">Forgot Password?</h5>
            <p class="text-muted small">Enter your email and we’ll send you a reset link.</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success small">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
                </div>
                @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="d-grid">
                <button class="btn btn-dark rounded-pill" type="submit">
                    <i class="fas fa-paper-plane me-1"></i> Send Reset Link
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
