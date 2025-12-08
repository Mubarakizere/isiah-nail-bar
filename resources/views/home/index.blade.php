@extends('layouts.public')

@section('title', 'Welcome to Isaiah Nail Bar')

@section('hero')
    @include('partials.hero')
@endsection

@section('content')

<!-- Featured Services -->
<section class="py-5" style="background: #fefefe;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="h1 fw-bold mb-3" style="font-family: 'Playfair Display', serif; color: #2c2c2c;">
                Featured Services
            </h2>
            <p class="lead text-muted">Our most popular treatments</p>
        </div>

        <div class="row g-4">
            @foreach($featuredServices as $service)
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="card border-0 h-100 shadow" style="border-radius: 15px;">
                            <div class="position-relative overflow-hidden" style="border-radius: 15px 15px 0 0;">
                                <div class="ratio ratio-4x3">
                                    <img src="{{ $service->image ? asset('storage/' . $service->image) : asset('images/default-service.jpg') }}"
                                         class="card-img-top object-fit-cover" alt="{{ $service->name }}">
                                </div>
                                <div class="position-absolute top-0 end-0 m-3">
                                    <span class="badge bg-primary px-3 py-2" style="border-radius: 20px;">
                                        Featured
                                    </span>
                                </div>
                            </div>
                            
                            <div class="card-body text-center p-4">
                                <h5 class="fw-bold mb-2" style="font-family: 'Playfair Display', serif;">{{ $service->name }}</h5>
                                @if($service->category)
                                    <span class="badge text-bg-light mb-3" style="border-radius: 12px;">
                                        {{ $service->category->name }}
                                    </span>
                                @endif
                                <div class="mb-3">
                                    <span class="h5 fw-bold text-primary">RWF {{ number_format($service->price) }}</span>
                                </div>
                                <button class="btn btn-outline-primary rounded-pill px-4">
                                    Book Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-5">
            <a href="{{ url('/services') }}" class="btn btn-primary btn-lg px-5 py-3 rounded-pill">
                View All Services
            </a>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="py-5" style="background: var(--primary-gradient);">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="h1 fw-bold text-white mb-3" style="font-family: 'Playfair Display', serif;">
                Client Reviews
            </h2>
            <p class="lead text-white opacity-75">What our customers say about us</p>
        </div>
        
        <div class="testimonials-container p-4 rounded-4" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(15px);">
            <script src="https://static.elfsight.com/platform/platform.js" async></script>
            <div class="elfsight-app-9ff69c4b-c01b-4803-9a0b-9fb0d89e41eb" data-elfsight-app-lazy></div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-5" style="background: #f8f9fa;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="h1 fw-bold mb-3" style="font-family: 'Playfair Display', serif; color: #2c2c2c;">
                Why Choose Us
            </h2>
            <p class="lead text-muted">What makes Isaiah Nail Bar special</p>
        </div>

        <div class="row g-4">
            @foreach([
                ['icon' => 'fa-user-check', 'title' => 'Expert Team', 'desc' => 'Certified nail technicians with years of experience'],
                ['icon' => 'fa-calendar-check', 'title' => 'Easy Booking', 'desc' => 'Simple online booking system for your convenience'],
                ['icon' => 'fa-gem', 'title' => 'Quality Products', 'desc' => 'Premium nail care products for lasting results'],
                ['icon' => 'fa-shield-alt', 'title' => 'Clean & Safe', 'desc' => 'Highest hygiene standards and safety protocols']
            ] as $feature)
                <div class="col-lg-3 col-md-6">
                    <div class="text-center h-100">
                        <div class="feature-icon mb-4 mx-auto d-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px; background: var(--glass-pink); border-radius: 50%;">
                            <i class="fas {{ $feature['icon'] }} fa-2x" style="color: var(--primary-color);"></i>
                        </div>
                        <h5 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif;">{{ $feature['title'] }}</h5>
                        <p class="text-muted">{{ $feature['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- About & Contact -->
<section class="py-5" style="background: #2c2c2c;">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h2 class="h1 fw-bold text-white mb-4" style="font-family: 'Playfair Display', serif;">
                    About Isaiah Nail Bar
                </h2>
                <p class="text-white-50 mb-4 fs-5">
                    Isaiah Nail Bar is Kigali's premier nail salon. We provide luxury nail services 
                    with attention to detail and customer satisfaction as our top priority.
                </p>
                <div class="row text-center mb-4">
                    <div class="col-4">
                        <div class="h3 fw-bold text-white">500+</div>
                        <div class="small text-white-50">Happy Clients</div>
                    </div>
                    <div class="col-4">
                        <div class="h3 fw-bold text-white">3+</div>
                        <div class="small text-white-50">Years</div>
                    </div>
                    <div class="col-4">
                        <div class="h3 fw-bold text-white">50+</div>
                        <div class="small text-white-50">Services</div>
                    </div>
                </div>
                <a href="{{ url('/about') }}" class="btn btn-outline-light btn-lg rounded-pill px-4">
                    Learn More
                </a>
            </div>

            <div class="col-lg-6">
                <div class="contact-card p-4 rounded-4" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px);">
                    <h3 class="fw-bold text-white mb-4" style="font-family: 'Playfair Display', serif;">
                        Contact Information
                    </h3>
                    
                    <div class="contact-item d-flex align-items-center mb-3 p-3 rounded-3" style="background: rgba(255, 255, 255, 0.05);">
                        <div class="icon-circle me-3 d-flex align-items-center justify-content-center" 
                             style="width: 45px; height: 45px; background: var(--primary-gradient); border-radius: 50%;">
                            <i class="fas fa-map-marker-alt text-white"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-white">Location</div>
                            <div class="text-white-50 small">KG 4 Roundabout, Gisementi, Kigali</div>
                        </div>
                    </div>

                    <div class="contact-item d-flex align-items-center mb-3 p-3 rounded-3" style="background: rgba(255, 255, 255, 0.05);">
                        <div class="icon-circle me-3 d-flex align-items-center justify-content-center" 
                             style="width: 45px; height: 45px; background: var(--primary-gradient); border-radius: 50%;">
                            <i class="fas fa-phone text-white"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-white">Phone</div>
                            <a href="tel:0788421063" class="text-white-50 text-decoration-none small">0788 421 063</a>
                        </div>
                    </div>

                    <div class="contact-item d-flex align-items-center p-3 rounded-3" style="background: rgba(255, 255, 255, 0.05);">
                        <div class="icon-circle me-3 d-flex align-items-center justify-content-center" 
                             style="width: 45px; height: 45px; background: linear-gradient(135deg, #25d366, #128c7e); border-radius: 50%;">
                            <i class="fab fa-whatsapp text-white"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-white">WhatsApp</div>
                            <a href="https://wa.me/250788421063" target="_blank" class="text-white-50 text-decoration-none small">
                                Message us
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
/* Clean hover effects */
.service-card .card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.service-card:hover .card {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.service-card img {
    transition: transform 0.3s ease;
}

.service-card:hover img {
    transform: scale(1.03);
}

.btn:hover {
    transform: translateY(-1px);
}

.contact-item {
    transition: all 0.2s ease;
}

.contact-item:hover {
    background: rgba(255, 255, 255, 0.1) !important;
    transform: translateX(5px);
}

.feature-icon {
    transition: transform 0.2s ease;
}

.feature-icon:hover {
    transform: scale(1.05);
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .py-5 {
        padding: 3rem 0 !important;
    }
    
    .contact-card {
        padding: 2rem !important;
    }
    
    .feature-icon {
        width: 60px !important;
        height: 60px !important;
    }
    
    .feature-icon i {
        font-size: 1.5rem !important;
    }
}

@media (max-width: 576px) {
    .h1 {
        font-size: 1.8rem !important;
    }
    
    .lead {
        font-size: 1rem !important;
    }
    
    .btn-lg {
        padding: 0.75rem 1.5rem !important;
    }
}
</style>
@endpush

@endsection