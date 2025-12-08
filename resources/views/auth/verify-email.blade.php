@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
<div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-sm border-0 p-4 rounded-4" style="max-width: 450px; width: 100%;">
        <div class="text-center mb-4">
            <img src="{{ asset('storage/logo.png') }}" alt="Logo" style="width: 80px;">
            <h5 class="fw-bold mt-3">Verify Your Email</h5>
            <p class="text-muted small">
                We’ve sent a verification link to your email. If you didn’t receive it, we’ll gladly send another.
                <br>
                <span class="text-danger fw-semibold">👉 Don’t forget to check your <u>Spam or Promotions</u> folder too!</span>
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success small text-center mb-4">
                A new verification link has been sent to your email.
            </div>
        @endif

        <div class="d-flex justify-content-between flex-wrap gap-2">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-dark rounded-pill">
                    <i class="fas fa-paper-plane me-1"></i> Resend Link
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary rounded-pill">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
