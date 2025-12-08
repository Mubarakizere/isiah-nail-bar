@extends('layouts.public')

@section('title', 'About Isaiah Nail Bar')

@section('content')
{{-- Hero Section --}}
<x-page-header 
    title="About Isaiah Nail Bar" 
    subtitle="Luxury Nail Care in Kigali — Where Elegance Begins at Your Fingertips." 
/>

{{-- Our Story Section --}}
<section class="py-5" style="background: var(--light-bg);">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="story-content">
                    <div class="section-badge mb-3">
                        <span class="badge rounded-pill px-3 py-2" style="background: var(--glass-pink); color: var(--primary-color); font-weight: 500;">
                            Our Story
                        </span>
                    </div>
                    
                    <h2 class="mb-4" style="font-family: 'Playfair Display', serif; color: var(--dark-primary);">
                        Luxury Meets Precision
                    </h2>
                    
                    <p class="lead text-muted mb-4">
                        Isaiah Nail Bar is Kigali's premier destination for flawless nails. From timeless classics to creative artistry, we offer unmatched craftsmanship and hygiene with every set, every time.
                    </p>
                    
                    <div class="stats-row row g-3 mb-4">
                        <div class="col-4">
                            <div class="stat-card text-center p-3 rounded-3" style="background: var(--glass-pink);">
                                <div class="h4 mb-1 fw-bold" style="color: var(--primary-color);">500+</div>
                                <div class="small text-muted">Happy Clients</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-card text-center p-3 rounded-3" style="background: var(--glass-pink);">
                                <div class="h4 mb-1 fw-bold" style="color: var(--primary-color);">3+</div>
                                <div class="small text-muted">Years Experience</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-card text-center p-3 rounded-3" style="background: var(--glass-pink);">
                                <div class="h4 mb-1 fw-bold" style="color: var(--primary-color);">50+</div>
                                <div class="small text-muted">Services</div>
                            </div>
                        </div>
                    </div>
                    
                    <a href="{{ route('booking.step1') }}" class="btn btn-primary">
                        <i class="fas fa-calendar-plus me-2"></i>Book Your Session
                    </a>
                </div>
            </div>

            <div class="col-lg-6" data-aos="fade-left">
                <div class="services-overview">
                    <h5 class="mb-4" style="font-family: 'Playfair Display', serif; color: var(--dark-primary);">What We Offer</h5>
                    
                    <div class="services-grid row g-3 mb-4">
                        @foreach(['Gel Polish', 'Acrylic Sets', 'French Tips', 'Custom Art', 'Nail Extensions', 'Nail Repair'] as $service)
                        <div class="col-6">
                            <div class="service-item p-3 rounded-3 d-flex align-items-center" 
                                 style="background: rgba(255, 255, 255, 0.8); border: 1px solid var(--glass-pink); transition: var(--transition-fast);">
                                <i class="fas fa-check-circle me-2" style="color: var(--primary-color);"></i>
                                <span class="fw-medium">{{ $service }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="mission-box p-4 rounded-3" style="background: var(--primary-gradient); color: white;">
                        <h6 class="fw-bold mb-2" style="font-family: 'Playfair Display', serif;">Our Mission</h6>
                        <p class="mb-0 opacity-90">
                            To redefine beauty experiences in Rwanda through professionalism, comfort, and high quality finishes so your nails speak confidence before you say a word.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Why Choose Us --}}
<section class="py-5" style="background: var(--primary-gradient);">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h3 class="fw-bold text-white mb-3" style="font-family: 'Playfair Display', serif;">Why Clients Choose Isaiah</h3>
            <p class="text-white opacity-75">Experience the difference that sets us apart</p>
        </div>

        <div class="row g-4">
            @php
            $features = [
                ['fas fa-user-check', 'Certified Artists', 'Every technician is professionally trained to deliver stunning results.'],
                ['fas fa-shield-alt', 'Spotless Hygiene', 'We prioritize sterilized tools and a clean workspace every session.'],
                ['fas fa-magic', 'Tailored Nail Art', 'From simple elegance to bold expressions — your nails, your style.'],
                ['fas fa-calendar-check', 'Easy Booking', 'Select your service, provider, and time online in under 2 minutes.']
            ];
            @endphp

            @foreach($features as $index => $feature)
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                <div class="feature-card h-100 p-4 rounded-4 text-center" 
                     style="background: var(--glass-white); border: 1px solid rgba(255, 255, 255, 0.3); transition: var(--transition-smooth);">
                    
                    <div class="icon-wrapper mx-auto mb-3 d-flex align-items-center justify-content-center" 
                         style="width: 60px; height: 60px; background: var(--glass-pink); border-radius: 50%;">
                        <i class="{{ $feature[0] }} fa-lg" style="color: var(--primary-color);"></i>
                    </div>
                    
                    <h6 class="fw-bold mb-2" style="font-family: 'Playfair Display', serif; color: var(--dark-primary);">{{ $feature[1] }}</h6>
                    <p class="text-muted small mb-0">{{ $feature[2] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Core Values --}}
<section class="py-5" style="background: var(--light-bg);">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h3 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif; color: var(--dark-primary);">Our Core Values</h3>
            <p class="text-muted">The principles that guide everything we do</p>
        </div>

        <div class="row g-4 justify-content-center">
            @php
            $values = [
                ['fas fa-star', 'Excellence', 'We deliver work that is clean, long lasting, and camera ready every time.'],
                ['fas fa-heart', 'Client Care', 'We create safe, welcoming experiences that keep you coming back.'],
                ['fas fa-shield-alt', 'Hygiene', 'From tools to surfaces — cleanliness is our daily non-negotiable.']
            ];
            @endphp

            @foreach($values as $index => $value)
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                <div class="value-card h-100 p-4 rounded-4 text-center" 
                     style="background: rgba(255, 255, 255, 0.8); border: 1px solid var(--glass-pink); box-shadow: var(--shadow-soft); transition: var(--transition-smooth);">
                    
                    <div class="icon-wrapper mx-auto mb-3 d-flex align-items-center justify-content-center" 
                         style="width: 60px; height: 60px; background: var(--primary-gradient); border-radius: 50%;">
                        <i class="{{ $value[0] }} fa-lg text-white"></i>
                    </div>
                    
                    <h6 class="fw-bold mb-2" style="font-family: 'Playfair Display', serif; color: var(--dark-primary);">{{ $value[1] }}</h6>
                    <p class="text-muted small mb-0">{{ $value[2] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- FAQ Section --}}
<section class="py-5" style="background: var(--dark-primary);">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h3 class="fw-bold text-white mb-3" style="font-family: 'Playfair Display', serif;">Frequently Asked Questions</h3>
            <p class="text-white opacity-75">Everything you need to know about our services</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    @php
                    $faqs = [
                        ['Is a deposit required to book?', 'Yes, a <strong>non-refundable deposit of 40%</strong> is required to secure your slot.'],
                        ['What payment options do you accept?', 'We accept <strong>MTN Mobile Money</strong>, <strong>Cash</strong>, and <strong>Bank Transfer</strong>.'],
                        ['What if I arrive late?', 'If you\'re more than <strong>15 minutes late</strong>, your appointment may be forfeited.'],
                        ['Can I cancel or reschedule?', 'Yes, with at least <strong>48 hours\' notice</strong>. Otherwise, the deposit will be lost.'],
                        ['Do you offer home service?', 'Yes — <strong>Home service is available by special request</strong>. Please call us directly.'],
                        ['Can I bring someone with me?', '<strong>No extra guests</strong> are allowed for hygiene and space reasons.'],
                        ['What is a Silent Appointment?', 'You can request a <strong>Silent Appointment</strong> — no small talk, just peace and pampering.'],
                        ['How do I become a Nail Ambassador?', 'Follow us on IG, have 10K+ followers, and book regularly for discounts and perks.']
                    ];
                    @endphp

                    @foreach($faqs as $index => $faq)
                    <div class="accordion-item mb-3" data-aos="fade-up" data-aos-delay="{{ $index * 50 }}">
                        <h2 class="accordion-header">
                            <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }} fw-medium" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#faq{{ $index }}"
                                    style="background: var(--glass-white); border: none; border-radius: var(--border-radius); color: var(--dark-primary);">
                                {{ $faq[0] }}
                            </button>
                        </h2>
                        <div id="faq{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#faqAccordion">
                            <div class="accordion-body" style="background: var(--glass-white); color: var(--dark-primary);">
                                {!! $faq[1] !!}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Meet Our Team --}}
<section class="py-5" style="background: var(--light-bg);">
    <div class="container text-center">
        <div class="mb-5" data-aos="fade-up">
            <h3 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif; color: var(--dark-primary);">Meet Our Artists</h3>
            <p class="text-muted mx-auto" style="max-width: 600px;">
                Our nail technicians are certified professionals passionate about nail beauty. Experience the Isaiah touch with a team you'll trust and love.
            </p>
        </div>
        
        <div class="row g-4 justify-content-center mb-5">
            @foreach(['Master Artist', 'Creative Specialist', 'Luxury Technician'] as $index => $role)
            <div class="col-lg-3 col-md-4" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                <div class="artist-card p-4 rounded-4 text-center h-100" 
                     style="background: rgba(255, 255, 255, 0.8); border: 1px solid var(--glass-pink); box-shadow: var(--shadow-soft); transition: var(--transition-smooth);">
                    
                    <div class="artist-avatar mx-auto mb-3 d-flex align-items-center justify-content-center" 
                         style="width: 70px; height: 70px; background: var(--primary-gradient); border-radius: 50%; color: white;">
                        <i class="fas fa-user fa-lg"></i>
                    </div>
                    
                    <h6 class="fw-bold mb-1" style="color: var(--dark-primary);">{{ $role }}</h6>
                    <p class="text-muted small mb-0">Expert in luxury nail care</p>
                </div>
            </div>
            @endforeach
        </div>
        
        <a href="{{ url('/providers') }}" class="btn btn-outline-primary btn-lg">
            <i class="fas fa-users me-2"></i>View Our Full Team
        </a>
    </div>
</section>

{{-- Special Offer --}}
<section class="py-4" style="background: var(--primary-gradient);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <div class="d-flex align-items-center">
                    <div class="offer-icon me-3 d-flex align-items-center justify-content-center" 
                         style="width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2); border-radius: 50%;">
                        <i class="fas fa-gift text-white"></i>
                    </div>
                    <div class="offer-content text-white">
                        <h5 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif;">First-Time Clients Special!</h5>
                        <p class="mb-0 opacity-90">Enjoy <strong>10% OFF</strong> your first appointment. It's time to glow!</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0" data-aos="fade-left">
                <a href="{{ route('booking.step1') }}" class="btn btn-light btn-lg">
                    <i class="fas fa-calendar-plus me-2"></i>Book Now
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Final CTA --}}
<section class="py-5" style="background: var(--light-bg);">
    <div class="container text-center">
        <div class="cta-content" data-aos="zoom-in">
            <h3 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif; color: var(--dark-primary);">Ready for Your Isaiah Experience?</h3>
            <p class="text-muted mb-4 mx-auto" style="max-width: 500px;">
                Join hundreds of satisfied clients who trust Isaiah Nail Bar for their luxury nail care in Kigali.
            </p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="{{ route('booking.step1') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-calendar-plus me-2"></i>Book Appointment
                </a>
                <a href="{{ url('/services') }}" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-spa me-2"></i>View Services
                </a>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
/* Optimized animations using existing CSS variables */
.service-item:hover {
    background: var(--glass-white) !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-soft);
}

.artist-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-medium);
}

.stat-card {
    transition: var(--transition-smooth);
}

.stat-card:hover {
    transform: scale(1.05);
    box-shadow: var(--shadow-soft);
}

.feature-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-medium);
}

.accordion-button {
    box-shadow: none !important;
    border-radius: var(--border-radius) !important;
}

.accordion-button:not(.collapsed) {
    background: var(--glass-pink) !important;
    color: var(--primary-color) !important;
}

.accordion-item {
    border: none;
    background: transparent;
}

.accordion-item .accordion-button,
.accordion-item .accordion-body {
    border-radius: var(--border-radius) !important;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.offer-icon {
    transition: var(--transition-smooth);
}

.offer-content:hover + .offer-icon,
.offer-icon:hover {
    transform: scale(1.1) rotate(10deg);
}

/* Performance optimization */
.artist-card,
.feature-card,
.value-card,
.service-item,
.stat-card {
    will-change: transform;
}

@media (max-width: 768px) {
    .stats-row .col-4 {
        margin-bottom: 0.5rem;
    }
    
    .d-flex.gap-3.justify-content-center {
        flex-direction: column;
        align-items: center;
    }
    
    .btn-lg {
        width: 100%;
        max-width: 300px;
    }
}
</style>
@endpush
@endsection