@extends('layouts.public')

@section('title', 'Meet Our Team')

@section('content')
<x-page-header title="Meet Our Team" subtitle="The skilled hands behind your beautiful nails" />

<!-- Team Section -->
<section class="py-5" style="background: #fafafa;">
    <div class="container">
        <!-- Simple intro -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <p class="lead text-muted mb-4">
                    Our nail technicians bring years of experience and genuine care to every appointment. 
                    Each specialist has their own style and expertise to help you achieve the perfect look.
                </p>
            </div>
        </div>

       

        <!-- Team members -->
        <div class="row g-4">
            @forelse($providers as $provider)
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm team-card">
                        <!-- Photo -->
                        <div class="card-img-top position-relative overflow-hidden">
                            <img src="{{ $provider->photo ? asset('storage/' . $provider->photo) : 'https://via.placeholder.com/400x300/e9ecef/6c757d?text=' . urlencode($provider->name) }}"
                                 alt="{{ $provider->name }}"
                                 class="w-100"
                                 style="height: 280px; object-fit: cover;">
                            
                            @if($provider->status === 'active')
                            <span class="position-absolute top-0 end-0 m-3">
                                <span class="badge bg-success">Available</span>
                            </span>
                            @endif
                        </div>

                        <div class="card-body text-center">
                            <h5 class="card-title mb-2">{{ $provider->name }}</h5>
                            <p class="text-primary mb-3">Nail Specialist</p>
                            
                            @if($provider->bio)
                            <p class="card-text text-muted small mb-3">
                                {{ Str::limit($provider->bio, 100) }}
                            </p>
                            @endif

                            @if($provider->phone)
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-phone me-1"></i>{{ $provider->phone }}
                                </small>
                            </div>
                            @endif

                            <!-- Specialties -->
                            <div class="mb-3">
                                <span class="badge bg-light text-dark me-1">Manicures</span>
                                <span class="badge bg-light text-dark me-1">Gel Polish</span>
                                <span class="badge bg-light text-dark">Nail Art</span>
                            </div>
                        </div>

                        <div class="card-footer bg-transparent border-0 pt-0">
                            <a href="{{ route('booking.step2') }}" class="btn btn-primary w-100">
                                Book with {{ explode(' ', $provider->name)[0] }}
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center">
                    <div class="py-5">
                        <i class="fas fa-users text-muted mb-3" style="font-size: 4rem; opacity: 0.3;"></i>
                        <h4 class="text-muted">We're Building Our Team</h4>
                        <p class="text-muted">We're currently hiring talented nail technicians. Check back soon!</p>
                        <a href="{{ url('/contact') }}" class="btn btn-outline-primary">
                            Contact Us
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>


<!-- Call to action -->
<section class="py-5" style="background: var(--primary-color);">
    <div class="container text-center text-white">
        <h3 class="mb-3">Ready for Your Next Appointment?</h3>
        <p class="lead mb-4">Book online or call us directly - we're here to help you look and feel your best</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="{{ route('booking.step1') }}" class="btn btn-light btn-lg">
                Book Online
            </a>
            <a href="tel:+250788421063" class="btn btn-outline-light btn-lg">
                Call Us
            </a>
        </div>
    </div>
</section>

@push('styles')
<style>
.team-card {
    transition: transform 0.2s ease;
}

.team-card:hover {
    transform: translateY(-5px);
}

.card-img-top img {
    transition: transform 0.3s ease;
}

.team-card:hover .card-img-top img {
    transform: scale(1.05);
}

/* Simple, clean hover effects */
.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

/* Clean badge styling */
.badge {
    font-weight: normal;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .d-flex.gap-3 {
        flex-direction: column;
    }
    
    .d-flex.gap-3 .btn {
        margin-bottom: 0.5rem;
    }
}
</style>
@endpush

@endsection