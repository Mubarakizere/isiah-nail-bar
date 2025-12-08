{{-- Professional Footer --}}
<footer class="position-relative" style="background: var(--dark-primary); color: white;">
    <div class="container py-5">
        <div class="row gy-4 text-center text-md-start">
            
            {{-- Company Info --}}
            <div class="col-lg-4 col-md-6">
                <div class="footer-section">
                    <div class="d-flex align-items-center justify-content-center justify-content-md-start mb-3">
                        <img src="{{ asset('storage/logo.png') }}" alt="Isaiah Nail Bar" 
                             style="height: 40px; filter: brightness(0) invert(1);" class="me-2">
                        <h5 class="mb-0 fw-bold" style="font-family: 'Playfair Display', serif;">Isaiah Nail Bar</h5>
                    </div>
                    
                    <p class="opacity-75 mb-4 lh-base">
                        Kigali's premier luxury nail destination. Professional nail care with attention to detail and hygiene standards you can trust.
                    </p>
                    
                    <div class="contact-info">
                        <div class="d-flex align-items-center justify-content-center justify-content-md-start mb-2">
                            <i class="fas fa-clock me-2" style="color: var(--primary-color); width: 16px;"></i>
                            <span class="small opacity-75">Mon-Sun: 9:00 AM - 9:00 PM</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center justify-content-md-start mb-2">
                            <i class="fas fa-map-marker-alt me-2" style="color: var(--primary-color); width: 16px;"></i>
                            <span class="small opacity-75">KG 4 Roundabout, Kigali, Rwanda</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                            <i class="fas fa-phone me-2" style="color: var(--primary-color); width: 16px;"></i>
                            <a href="tel:+250790395169" class="text-white opacity-75 text-decoration-none small">+250790395169</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="col-lg-2 col-md-6">
                <div class="footer-section">
                    <h6 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif;">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="{{ url('/') }}" class="text-white opacity-75 text-decoration-none footer-link">
                                Home
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="{{ url('/services') }}" class="text-white opacity-75 text-decoration-none footer-link">
                                Services
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="{{ url('/gallery') }}" class="text-white opacity-75 text-decoration-none footer-link">
                                Gallery
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="{{ url('/about') }}" class="text-white opacity-75 text-decoration-none footer-link">
                                About
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="{{ url('/contact') }}" class="text-white opacity-75 text-decoration-none footer-link">
                                Contact
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/providers') }}" class="text-white opacity-75 text-decoration-none footer-link">
                                Our Team
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Services --}}
            <div class="col-lg-3 col-md-6">
                <div class="footer-section">
                    <h6 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif;">Popular Services</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <span class="text-white opacity-75 small">Gel Manicures</span>
                        </li>
                        <li class="mb-2">
                            <span class="text-white opacity-75 small">Acrylic Extensions</span>
                        </li>
                        <li class="mb-2">
                            <span class="text-white opacity-75 small">French Tips</span>
                        </li>
                        <li class="mb-2">
                            <span class="text-white opacity-75 small">Nail Art Design</span>
                        </li>
                        <li class="mb-2">
                            <span class="text-white opacity-75 small">Pedicures</span>
                        </li>
                        <li>
                            <a href="{{ url('/services') }}" class="text-decoration-none" style="color: var(--primary-color);">
                                View All Services
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Connect & Payment --}}
            <div class="col-lg-3 col-md-6">
                <div class="footer-section">
                    <h6 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif;">Connect With Us</h6>
                    
                    {{-- Social Media --}}
                    <div class="social-links mb-4">
                        <div class="d-flex gap-3 justify-content-center justify-content-md-start">
                            <a href="https://www.instagram.com/isaiah_nails_art_kigali_rwanda/" target="_blank" 
                               class="social-link d-flex align-items-center justify-content-center text-white"
                               style="width: 40px; height: 40px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; transition: var(--transition-fast);">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="https://www.facebook.com/profile.php?id=100076518765944" target="_blank" 
                               class="social-link d-flex align-items-center justify-content-center text-white"
                               style="width: 40px; height: 40px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; transition: var(--transition-fast);">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://www.tiktok.com/@isaiahnailsartkigali?_t=ZM-8xRQQEmtep3&_r=1" target="_blank" 
                               class="social-link d-flex align-items-center justify-content-center text-white"
                               style="width: 40px; height: 40px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; transition: var(--transition-fast);">
                                <i class="fab fa-tiktok"></i>
                            </a>
                            <a href="https://wa.me/250788421063" target="_blank" 
                               class="social-link d-flex align-items-center justify-content-center text-white"
                               style="width: 40px; height: 40px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; transition: var(--transition-fast);">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>

                    {{-- Payment Methods --}}
                    <div class="payment-section">
                        <h6 class="small fw-semibold mb-2 opacity-75">Payment Options</h6>
                        <div class="d-flex gap-2 justify-content-center justify-content-md-start flex-wrap">
                            <div class="payment-method p-2 rounded" 
                                 style="background: rgba(255, 255, 255, 0.1); transition: var(--transition-fast);">
                                <span class="small opacity-75">MTN MoMo</span>
                            </div>
                            <div class="payment-method p-2 rounded" 
                                 style="background: rgba(255, 255, 255, 0.1); transition: var(--transition-fast);">
                                <span class="small opacity-75">Cash</span>
                            </div>
                            <div class="payment-method p-2 rounded" 
                                 style="background: rgba(255, 255, 255, 0.1); transition: var(--transition-fast);">
                                <span class="small opacity-75">Bank Transfer</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Newsletter Section --}}
        <div class="newsletter-section mt-5 pt-4" style="border-top: 1px solid rgba(255, 255, 255, 0.1);">
            <div class="row align-items-center">
                <div class="col-lg-6 text-center text-lg-start mb-3 mb-lg-0">
                    <h5 class="fw-bold mb-2" style="font-family: 'Playfair Display', serif;">Stay Updated</h5>
                    <p class="opacity-75 mb-0">Get exclusive offers and nail care tips delivered to your inbox</p>
                </div>
                <div class="col-lg-6">
                    <form class="newsletter-form">
                        <div class="input-group">
                            <input type="email" class="form-control border-0 py-3 px-4" 
                                   placeholder="Enter your email address"
                                   style="background: rgba(255, 255, 255, 0.1); color: white; border-radius: var(--border-radius) 0 0 var(--border-radius);">
                            <button class="btn px-4 py-3" type="submit"
                                    style="background: var(--primary-color); border: none; color: white; font-weight: 500; border-radius: 0 var(--border-radius) var(--border-radius) 0;">
                                <i class="fas fa-paper-plane me-2"></i>Subscribe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Footer Bottom --}}
        <div class="footer-bottom mt-5 pt-4" style="border-top: 1px solid rgba(255, 255, 255, 0.1);">
            <div class="row align-items-center">
                <div class="col-lg-6 text-center text-lg-start mb-3 mb-lg-0">
                    <p class="opacity-75 mb-0 small">
                        © {{ now()->year }} Isaiah Nail Bar. All rights reserved.
                    </p>
                </div>
                <div class="col-lg-6 text-center text-lg-end">
                    <div class="d-flex gap-3 justify-content-center justify-content-lg-end align-items-center flex-wrap">
                        <a href="#" class="text-white opacity-75 text-decoration-none small">Privacy Policy</a>
                        <span class="opacity-50">|</span>
                        <a href="#" class="text-white opacity-75 text-decoration-none small">Terms of Service</a>
                        <span class="opacity-50">|</span>
                        <span class="small opacity-75">
                            Built by 
                            <a href="https://mubarakizere.github.io/izere-mubaraka/" 
                               target="_blank" 
                               class="text-white fw-medium text-decoration-none"
                               style="color: var(--primary-color) !important;">
                                Izere Moubarak
                            </a>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

@push('styles')
<style>
/* Professional footer styles using layout variables */
.footer-link {
    transition: var(--transition-fast);
    padding: 0.25rem 0;
    border-radius: 4px;
    font-size: 0.9rem;
}

.footer-link:hover {
    color: var(--primary-color) !important;
    opacity: 1 !important;
    transform: translateX(3px);
}

.social-link {
    transition: var(--transition-smooth);
}

.social-link:hover {
    background: var(--primary-color) !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-soft);
}

.payment-method {
    transition: var(--transition-fast);
}

.payment-method:hover {
    background: rgba(255, 255, 255, 0.15) !important;
    transform: translateY(-1px);
}

.newsletter-form input {
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: var(--transition-fast);
}

.newsletter-form input::placeholder {
    color: rgba(255, 255, 255, 0.6);
}

.newsletter-form input:focus {
    outline: none;
    box-shadow: 0 0 0 2px var(--primary-color);
    background: rgba(255, 255, 255, 0.15);
}

.newsletter-form button {
    transition: var(--transition-smooth);
}

.newsletter-form button:hover {
    background: var(--accent-color) !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-soft);
}

/* Clean animations */
.footer-section img {
    transition: var(--transition-fast);
}

.footer-section:hover img {
    transform: scale(1.05);
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .footer-section {
        text-align: center !important;
    }
    
    .d-flex.justify-content-center.justify-content-md-start {
        justify-content: center !important;
    }
    
    .newsletter-form .input-group {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .newsletter-form input,
    .newsletter-form button {
        border-radius: var(--border-radius) !important;
        width: 100%;
    }
    
    .d-flex.gap-3.justify-content-center.justify-content-lg-end {
        flex-direction: column;
        gap: 1rem !important;
        text-align: center;
    }
    
    .d-flex.gap-3.justify-content-center.justify-content-lg-end span {
        display: none;
    }
}

/* Performance optimizations */
.social-link,
.payment-method,
.footer-link {
    will-change: transform;
}

/* Focus states for accessibility */
.footer-link:focus,
.social-link:focus {
    outline: 2px solid var(--primary-color);
    outline-offset: 2px;
}
</style>
@endpush