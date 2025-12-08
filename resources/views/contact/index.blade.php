@extends('layouts.public')

@section('title', 'Contact Us')

@section('content')
<x-page-header title="Contact Isaiah Nail Bar" subtitle="We're here to help. Reach out anytime." />

{{-- 🎀 Highlight Banner --}}
<section class="py-4" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
    <div class="container">
        <div class="highlight-banner text-center" data-aos="zoom-in">
            <div class="d-flex align-items-center justify-content-center gap-3 flex-wrap">
                <div class="highlight-icon d-flex align-items-center justify-content-center" 
                     style="width: 60px; height: 60px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; backdrop-filter: blur(10px);">
                    <i class="fas fa-comments fa-2x text-white"></i>
                </div>
                <div class="highlight-content text-white">
                    <h5 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif;">We Love Hearing From You!</h5>
                    <p class="mb-0 opacity-90">Whether it's booking help, feedback, or just saying hi drop us a message ✨</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 💌 Main Contact Section --}}
<section class="py-5 position-relative overflow-hidden" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
    <!-- Decorative Background Elements -->
    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-5">
        <div class="floating-shape position-absolute" style="top: 10%; left: 5%; width: 100px; height: 100px; background: linear-gradient(45deg, #667eea, #764ba2); border-radius: 50%; animation: float 8s ease-in-out infinite;"></div>
        <div class="floating-shape position-absolute" style="top: 60%; right: 10%; width: 80px; height: 80px; background: linear-gradient(45deg, #f093fb, #f5576c); border-radius: 50%; animation: float 6s ease-in-out infinite reverse;"></div>
        <div class="floating-shape position-absolute" style="bottom: 20%; left: 15%; width: 60px; height: 60px; background: linear-gradient(45deg, #667eea, #f5576c); border-radius: 50%; animation: float 10s ease-in-out infinite;"></div>
    </div>

    <div class="container position-relative">
        <div class="row g-5 align-items-stretch">
            <!-- ✍️ Contact Form -->
            <div class="col-lg-6" data-aos="fade-right">
                <div class="contact-form-card h-100 p-5 rounded-4 position-relative overflow-hidden" 
                     style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.3); box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);">
                    
                    <!-- Form Header -->
                    <div class="form-header mb-5">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-wrapper me-3 d-flex align-items-center justify-content-center" 
                                 style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%;">
                                <i class="fas fa-paper-plane fa-lg text-white"></i>
                            </div>
                            <h4 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif; color: #2d3748;">Send Us a Message</h4>
                        </div>
                        <p class="text-muted mb-0">We'll get back to you within 24 hours</p>
                    </div>

                    <!-- Contact Form -->
                    <form method="POST" action="{{ route('contact.submit') }}" class="contact-form">
                        @csrf

                        <div class="form-group mb-4">
                            <label class="form-label fw-semibold text-dark mb-2">
                                <i class="fas fa-user me-2 text-primary"></i>Full Name
                            </label>
                            <input type="text" name="name" 
                                   class="form-control form-control-lg border-0 rounded-3 shadow-sm" 
                                   required value="{{ old('name') }}"
                                   style="background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); padding: 1rem 1.25rem; transition: all 0.3s ease;"
                                   placeholder="Enter your full name">
                            @error('name') 
                                <div class="text-danger small mt-2">
                                    <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                </div> 
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label fw-semibold text-dark mb-2">
                                <i class="fas fa-phone me-2 text-primary"></i>Phone Number
                            </label>
                            <input type="text" name="phone" 
                                   class="form-control form-control-lg border-0 rounded-3 shadow-sm" 
                                   required value="{{ old('phone') }}"
                                   style="background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); padding: 1rem 1.25rem; transition: all 0.3s ease;"
                                   placeholder="+250 7XX XXX XXX">
                            @error('phone') 
                                <div class="text-danger small mt-2">
                                    <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                </div> 
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label fw-semibold text-dark mb-2">
                                <i class="fas fa-envelope me-2 text-primary"></i>Email Address 
                                <span class="text-muted small">(optional)</span>
                            </label>
                            <input type="email" name="email" 
                                   class="form-control form-control-lg border-0 rounded-3 shadow-sm" 
                                   value="{{ old('email') }}"
                                   style="background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); padding: 1rem 1.25rem; transition: all 0.3s ease;"
                                   placeholder="your.email@example.com">
                            @error('email') 
                                <div class="text-danger small mt-2">
                                    <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                </div> 
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label fw-semibold text-dark mb-2">
                                <i class="fas fa-comment-dots me-2 text-primary"></i>Your Message
                            </label>
                            <textarea name="message" rows="5" 
                                      class="form-control form-control-lg border-0 rounded-3 shadow-sm" 
                                      required
                                      style="background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); padding: 1rem 1.25rem; resize: vertical; transition: all 0.3s ease;"
                                      placeholder="Tell us how we can help you...">{{ old('message') }}</textarea>
                            @error('message') 
                                <div class="text-danger small mt-2">
                                    <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                </div> 
                            @enderror
                        </div>

                        <div class="form-submit">
                            <button type="submit" class="btn btn-lg w-100 py-3 rounded-3 position-relative overflow-hidden" 
                                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; font-weight: 600; box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3); transition: all 0.3s ease;">
                                <span class="position-relative z-index-2">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 🗺️ Location & Info -->
            <div class="col-lg-6" data-aos="fade-left">
                <div class="location-card h-100 d-flex flex-column">
                    
                    <!-- Map Section -->
                    <div class="map-section mb-4">
                        <div class="map-card p-4 rounded-4" 
                             style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.3); box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);">
                            
                            <div class="map-header d-flex align-items-center mb-4">
                                <div class="icon-wrapper me-3 d-flex align-items-center justify-content-center" 
                                     style="width: 50px; height: 50px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 50%;">
                                    <i class="fas fa-map-marker-alt text-white"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif; color: #2d3748;">Find Us</h5>
                                    <p class="text-muted small mb-0">Located in the heart of Kigali</p>
                                </div>
                            </div>
                            
                            <div class="map-container rounded-3 overflow-hidden shadow-sm mb-3" style="height: 280px;">
                                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d63799.99498399705!2d30.042312719531232!3d-1.9534315141480736!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x19dca7c3ab9dc249%3A0x64c2fcb3dba4102e!2sIsaiah%20Nail%20Bar!5e0!3m2!1sen!2srw!4v1750174033147!5m2!1sen!2srw" 
                                        width="100%" 
                                        height="100%" 
                                        style="border:0; filter: grayscale(20%);" 
                                        allowfullscreen="" 
                                        loading="lazy" 
                                        referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            </div>
                            
                            <a href="https://maps.google.com/maps?q=Isaiah+Nail+Bar,+Kigali" target="_blank" 
                               class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="fas fa-external-link-alt me-2"></i>Open in Maps
                            </a>
                        </div>
                    </div>

                    <!-- Contact Details -->
                    <div class="contact-details">
                        <div class="details-card p-4 rounded-4" 
                             style="background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%); color: white;">
                            
                            <div class="details-header mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-wrapper me-3 d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; backdrop-filter: blur(10px);">
                                        <i class="fas fa-info-circle text-white"></i>
                                    </div>
                                    <h5 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;">Contact Information</h5>
                                </div>
                            </div>

                            <div class="contact-info">
                                <!-- Phone -->
                                <div class="contact-item d-flex align-items-center mb-4 p-3 rounded-3" 
                                     style="background: rgba(255, 255, 255, 0.1); transition: all 0.3s ease;">
                                    <div class="contact-icon me-3 d-flex align-items-center justify-content-center" 
                                         style="width: 45px; height: 45px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%;">
                                        <i class="fas fa-phone text-white"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold mb-1">Phone</div>
                                        <a href="tel:+250790395169" class="text-white opacity-75 text-decoration-none">
                                            +250 790 395 169
                                        </a>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="contact-item d-flex align-items-center mb-4 p-3 rounded-3" 
                                     style="background: rgba(255, 255, 255, 0.1); transition: all 0.3s ease;">
                                    <div class="contact-icon me-3 d-flex align-items-center justify-content-center" 
                                         style="width: 45px; height: 45px; background: linear-gradient(135deg, #f093fb, #f5576c); border-radius: 50%;">
                                        <i class="fas fa-envelope text-white"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold mb-1">Email</div>
                                        <a href="mailto:info@isaiahnailbar.com" class="text-white opacity-75 text-decoration-none">
                                            info@isaiahnailbar.com
                                        </a>
                                    </div>
                                </div>

                                <!-- Hours -->
                                <div class="contact-item d-flex align-items-center mb-4 p-3 rounded-3" 
                                     style="background: rgba(255, 255, 255, 0.1); transition: all 0.3s ease;">
                                    <div class="contact-icon me-3 d-flex align-items-center justify-content-center" 
                                         style="width: 45px; height: 45px; background: linear-gradient(135deg, #25d366, #128c7e); border-radius: 50%;">
                                        <i class="fas fa-clock text-white"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold mb-1">Business Hours</div>
                                        <div class="text-white opacity-75">Mon–Sat, 9:00 AM – 9:00 PM</div>
                                    </div>
                                </div>

                                <!-- Address -->
                                <div class="contact-item d-flex align-items-start p-3 rounded-3" 
                                     style="background: rgba(255, 255, 255, 0.1); transition: all 0.3s ease;">
                                    <div class="contact-icon me-3 d-flex align-items-center justify-content-center" 
                                         style="width: 45px; height: 45px; background: linear-gradient(135deg, #764ba2, #667eea); border-radius: 50%;">
                                        <i class="fas fa-map-marker-alt text-white"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold mb-1">Address</div>
                                        <div class="text-white opacity-75">KG 4 Roundabout, Gisementi, Kigali</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Social Media & Quick Actions -->
<section class="py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="social-actions text-center" data-aos="fade-up">
            <h4 class="fw-bold text-white mb-3" style="font-family: 'Playfair Display', serif;">Connect With Us</h4>
            <p class="text-white opacity-75 mb-4">Follow us for daily inspiration and beauty tips</p>
            
            <div class="d-flex justify-content-center gap-3 mb-4 flex-wrap">
                <!-- Instagram -->
                <a href="https://www.instagram.com/isaiah_nails_art_kigali_rwanda/" target="_blank" 
                   class="social-btn d-flex align-items-center justify-content-center"
                   style="width: 60px; height: 60px; background: linear-gradient(135deg, #833ab4, #fd1d1d, #fcb045); border-radius: 50%; transition: all 0.3s ease; color: white; text-decoration: none;">
                    <i class="fab fa-instagram fa-lg"></i>
                </a>

                <!-- Facebook -->
                <a href="https://www.facebook.com/profile.php?id=100076518765944" target="_blank" 
                   class="social-btn d-flex align-items-center justify-content-center"
                   style="width: 60px; height: 60px; background: linear-gradient(135deg, #1877f2, #42a5f5); border-radius: 50%; transition: all 0.3s ease; color: white; text-decoration: none;">
                    <i class="fab fa-facebook-f fa-lg"></i>
                </a>

                <!-- TikTok -->
                <a href="https://www.tiktok.com/@isaiahnailsartkigali?_t=ZM-8xRQQEmtep3&_r=1" target="_blank" 
                   class="social-btn d-flex align-items-center justify-content-center"
                   style="width: 60px; height: 60px; background: linear-gradient(135deg, #000000, #fe2c55); border-radius: 50%; transition: all 0.3s ease; color: white; text-decoration: none;">
                    <i class="fab fa-tiktok fa-lg"></i>
                </a>

                <!-- WhatsApp -->
                <a href="https://wa.me/+250790395169" target="_blank" 
                   class="social-btn d-flex align-items-center justify-content-center"
                   style="width: 60px; height: 60px; background: linear-gradient(135deg, #25d366, #128c7e); border-radius: 50%; transition: all 0.3s ease; color: white; text-decoration: none;">
                    <i class="fab fa-whatsapp fa-lg"></i>
                </a>
            </div>

            <div class="quick-actions d-flex gap-3 justify-content-center flex-wrap">
                <a href="{{ route('booking.step1') }}" class="btn btn-lg px-5 py-3 rounded-pill" 
                   style="background: rgba(255, 255, 255, 0.2); color: white; border: 2px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(10px); font-weight: 500; transition: all 0.3s ease;">
                    <i class="fas fa-calendar-plus me-2"></i>Book Appointment
                </a>
                <a href="{{ url('/services') }}" class="btn btn-outline-light btn-lg px-5 py-3 rounded-pill" 
                   style="border: 2px solid rgba(255, 255, 255, 0.5); font-weight: 500; transition: all 0.3s ease;">
                    <i class="fas fa-spa me-2"></i>View Services
                </a>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
/* Floating Animations */
@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
}

/* Form Styling */
.contact-form .form-control:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
    background: rgba(255, 255, 255, 0.95) !important;
    transform: translateY(-2px);
}

.contact-form .form-control::placeholder {
    color: rgba(45, 55, 72, 0.6);
    font-style: italic;
}

/* Submit Button Animation */
.form-submit .btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.6s ease;
    z-index: 1;
}

.form-submit .btn:hover::before {
    left: 100%;
}

.form-submit .btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 40px rgba(102, 126, 234, 0.4);
}

/* Contact Items Hover */
.contact-item:hover {
    background: rgba(255, 255, 255, 0.2) !important;
    transform: translateX(10px);
}

/* Map Container Hover */
.map-container:hover iframe {
    filter: grayscale(0%);
    transition: filter 0.3s ease;
}

/* Social Buttons */
.social-btn:hover {
    transform: translateY(-5px) scale(1.1);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

/* Highlight Banner Animation */
.highlight-banner:hover .highlight-icon {
    transform: scale(1.1) rotate(10deg);
    transition: all 0.3s ease;
}

/* Card Hover Effects */
.contact-form-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.15);
}

.map-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

/* Quick Actions Buttons */
.quick-actions .btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(255, 255, 255, 0.2);
}

.quick-actions .btn[style*="background: rgba(255, 255, 255, 0.2)"]:hover {
    background: rgba(255, 255, 255, 0.3) !important;
}

/* Responsive Design */
@media (max-width: 768px) {
    .contact-form-card,
    .map-card,
    .details-card {
        padding: 2rem !important;
    }
    
    .form-header .d-flex,
    .map-header .d-flex,
    .details-header .d-flex {
        flex-direction: column !important;
        text-align: center !important;
    }
    
    .icon-wrapper {
        margin-right: 0 !important;
        margin-bottom: 1rem !important;
    }
    
    .contact-item {
        flex-direction: column !important;
        text-align: center !important;
    }
    
    .contact-icon {
        margin-right: 0 !important;
        margin-bottom: 1rem !important;
    }
    
    .highlight-banner .d-flex {
        flex-direction: column !important;
        gap: 1rem !important;
    }
    
    .social-actions .d-flex {
        justify-content: center !important;
    }
    
    .quick-actions {
        flex-direction: column !important;
        align-items: center !important;
    }
    
    .quick-actions .btn {
        width: 100% !important;
        max-width: 300px;
    }
}

/* Enhanced Focus States */
.btn:focus,
.form-control:focus {
    outline: 2px solid #667eea;
    outline-offset: 2px;
}

/* Loading States */
.contact-form-card,
.map-card,
.details-card {
    position: relative;
    overflow: hidden;
}

/* Form Validation Styling */
.form-control.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.2);
}

.form-control.is-valid {
    border-color: #28a745;
    box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation enhancement
    const form = document.querySelector('.contact-form');
    const inputs = form.querySelectorAll('.form-control');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim() !== '') {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            } else if (this.hasAttribute('required')) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            }
        });
        
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid', 'is-valid');
        });
    });
    
    // Form submission enhancement
    form.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
        submitBtn.disabled = true;
        
        // Re-enable button after 3 seconds (in case of slow response)
        setTimeout(() => {
            if (submitBtn.disabled) {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        }, 3000);
    });
    
    // Phone number formatting
    const phoneInput = document.querySelector('input[name="phone"]');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0 && !value.startsWith('250')) {
                if (value.startsWith('7') || value.startsWith('0')) {
                    value = '250' + value.replace(/^0/, '');
                }
            }
            
            if (value.length > 12) {
                value = value.slice(0, 12);
            }
            
            // Format: +250 7XX XXX XXX
            if (value.length >= 3) {
                value = '+' + value.slice(0, 3) + ' ' + value.slice(3);
            }
            if (value.length >= 8) {
                value = value.slice(0, 8) + ' ' + value.slice(8);
            }
            if (value.length >= 12) {
                value = value.slice(0, 12) + ' ' + value.slice(12);
            }
            
            e.target.value = value;
        });
    }
    
    // Success message animation
    @if(session('success'))
        const successAlert = document.createElement('div');
        successAlert.className = 'alert alert-success position-fixed';
        successAlert.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 9999;
            border-radius: 10px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            backdrop-filter: blur(10px);
            border: none;
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.3s ease;
        `;
        successAlert.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
            </div>
        `;
        
        document.body.appendChild(successAlert);
        
        setTimeout(() => {
            successAlert.style.opacity = '1';
            successAlert.style.transform = 'translateY(0)';
        }, 100);
        
        setTimeout(() => {
            successAlert.style.opacity = '0';
            successAlert.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                document.body.removeChild(successAlert);
            }, 300);
        }, 5000);
    @endif
});
</script>
@endpush

@endsection