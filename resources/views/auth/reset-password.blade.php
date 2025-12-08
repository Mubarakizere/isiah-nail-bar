@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-sm border-0 p-4 rounded-4" style="max-width: 420px; width: 100%;">
        <div class="text-center mb-4">
            <img src="{{ asset('storage/logo.png') }}" alt="Logo" style="width: 80px;">
            <h5 class="fw-bold mt-3">Reset Your Password</h5>
        </div>

        <form method="POST" action="{{ route('password.store') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $request->email) }}" required autofocus>
                @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" required>
                @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
                @error('password_confirmation') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-dark rounded-pill">
                    <i class="fas fa-check me-1"></i> Reset Password
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
