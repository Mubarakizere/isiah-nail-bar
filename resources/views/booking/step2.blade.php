@extends('layouts.public')

@section('title', 'Step 2: Choose Your Provider')

@section('content')
@include('partials.booking-progress', ['currentStep' => 2])

<div class="container my-5">
    <div class="row">
        <!-- 📞 Help Sidebar -->
        <div class="col-md-3 d-none d-md-block text-center text-md-start mb-4" data-aos="fade-right">
            <div class="mb-3">
                <img src="{{ asset('storage/images/agent-icon.svg') }}" alt="Agent Icon" style="height: 60px;">
            </div>
            <h5 class="fw-bold">Choose Your Agent</h5>
            <p class="text-muted small">Select the provider you'd like for this booking. Tap to confirm.</p>
            <p class="mt-4 small">
                <strong>Need help?</strong><br>
                <a href="tel:0788421063" class="text-decoration-none">📞 0788 421 063</a>
            </p>
        </div>

        <!-- 👤 Provider Cards -->
        <div class="col-md-6 col-12 mb-4" data-aos="fade-up">
            <h5 class="fw-bold mb-3">Available Providers</h5>

            <form method="POST" action="{{ route('booking.step2.submit') }}" id="providerForm">
                @csrf
                <div class="row row-cols-1 row-cols-sm-2 g-3">
                    @foreach ($providers as $provider)
                        <div class="col">
                            <label class="card provider-card h-100 text-center shadow-sm p-3 position-relative {{ $providerId == $provider->id ? 'selected' : '' }}">
                                <input type="radio" name="provider_id" value="{{ $provider->id }}" class="d-none"
                                       {{ $providerId == $provider->id ? 'checked' : '' }}>

                                <div class="d-flex justify-content-center mb-2">
                                    <img src="{{ $provider->photo ? asset('storage/' . $provider->photo) : asset('images/default-user.png') }}"
                                         class="rounded-circle object-fit-cover shadow-sm border"
                                         style="width: 60px; height: 60px;" alt="{{ $provider->name }}">
                                </div>

                                <h6 class="mb-1 fw-semibold">{{ ucfirst($provider->name) }}</h6>
                                <p class="text-muted small">{{ Str::limit($provider->bio, 60) }}</p>

                                <span class="badge bg-success position-absolute top-0 end-0 m-2 selected-badge {{ $providerId == $provider->id ? '' : 'd-none' }}">
                                    Selected
                                </span>
                            </label>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 text-end">
                    <a href="{{ route('booking.step1') }}" class="btn btn-outline-secondary">← Back</a>
                    <button type="submit" class="btn btn-primary">
                        Continue <i class="ph ph-arrow-right ms-1"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- 📋 Booking Summary -->
        <div class="col-md-3 d-none d-md-block" data-aos="fade-left">
            <div class="border p-3 shadow-sm rounded sticky-top bg-light" style="top: 100px;">
                <h6 class="fw-bold mb-3">Booking Summary</h6>

                @php
                    $totalPrice = $selectedServices->sum('price');
                    $totalDuration = $selectedServices->sum('duration_minutes');
                @endphp

                @foreach ($selectedServices as $service)
                    <div class="mb-2">
                        <p class="mb-0 fw-semibold">
                            <i class="ph ph-scissors me-1"></i> {{ $service->name }}
                        </p>
                        <p class="mb-0 text-muted small">
                            <i class="ph ph-folder-notch me-1"></i> {{ $service->category->name ?? 'Uncategorized' }}
                        </p>
                        <p class="mb-0">
                            <i class="ph ph-currency-circle me-1"></i> RWF {{ number_format($service->price) }}
                        </p>
                        <p class="mb-2">
                            <i class="ph ph-clock me-1"></i> {{ $service->duration_minutes }} mins
                        </p>
                        <hr>
                    </div>
                @endforeach

                <p class="fw-bold mb-1">
                    <i class="ph ph-user me-1"></i> Customer: {{ auth()->user()->name ?? '—' }}
                </p>
                <p class="fw-bold mt-3 mb-1">
                    <i class="ph ph-currency-circle me-1"></i> Total Price: RWF {{ number_format($totalPrice) }}
                </p>
                <p class="fw-bold mb-0">
                    <i class="ph ph-clock me-1"></i> Total Time: {{ $totalDuration }} mins
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .provider-card {
        border: 2px solid transparent;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        border-radius: 8px;
        background-color: #fff;
        position: relative;
    }

    .provider-card:hover {
        border-color: #cce5ff;
        transform: translateY(-3px);
    }

    .provider-card.selected {
        border-color: #0d6efd;
        background-color: #f0f8ff;
        box-shadow: 0 0 0 2px #cfe2ff;
    }

    .selected-badge {
        font-size: 0.75rem;
        padding: 4px 8px;
        border-radius: 12px;
    }

    .sticky-top {
        z-index: 100;
    }

    @media (max-width: 768px) {
        .sticky-top {
            position: static !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.provider-card').forEach(card => {
            card.addEventListener('click', () => {
                document.querySelectorAll('.provider-card').forEach(c => {
                    c.classList.remove('selected');
                    c.querySelector('.selected-badge')?.classList.add('d-none');
                });

                card.classList.add('selected');
                card.querySelector('input[type="radio"]').checked = true;
                card.querySelector('.selected-badge')?.classList.remove('d-none');
            });
        });
    });
</script>
@endpush
