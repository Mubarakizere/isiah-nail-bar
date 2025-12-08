{{-- Optimized Hero Section --}}
<section class="hero-section text-white position-relative"
         style="background: url('{{ asset('storage/banner.jpg') }}') no-repeat center center / cover; 
                height: 100vh; display: flex; align-items: center;">
    
    <!-- Simplified overlay using layout variables -->
    <div class="overlay position-absolute w-100 h-100"
         style="background: linear-gradient(135deg, rgba(212, 113, 122, 0.6) 0%, rgba(45, 55, 72, 0.8) 100%);
                top: 0; left: 0; z-index: 0;"></div>
    
    <div class="container text-center position-relative" style="z-index: 1;">
        <div data-aos="fade-up" data-aos-duration="800">
            <h1 class="display-2 fw-bold mb-3"
                style="font-family: 'Playfair Display', serif; text-shadow: 2px 2px 8px rgba(0,0,0,0.5);">
                Luxury Nails. Effortlessly.
            </h1>
            
            <div class="hero-subtitle lead fs-4 mt-2 mb-4">
                <span id="typed-text" class="fw-light"></span><span class="cursor">|</span>
            </div>
            
            <a href="{{ route('booking.step1') }}" class="btn btn-lg px-5 py-3 hero-cta-btn">
                <i class="fas fa-calendar-check me-2"></i>Book Now
            </a>
        </div>
    </div>
    
    <!-- Simplified scroll indicator -->
    <div class="scroll-indicator position-absolute bottom-0 start-50 translate-middle-x mb-4" 
         style="z-index: 1;" data-aos="bounce" data-aos-delay="1200">
        <i class="fas fa-chevron-down fa-lg text-white opacity-75 scroll-bounce"></i>
    </div>
</section>

@push('styles')
<style>
/* Hero Button using layout variables */
.hero-cta-btn {
    background: var(--glass-white) !important;
    color: var(--primary-color) !important;
    border: 2px solid rgba(255, 255, 255, 0.3) !important;
    backdrop-filter: blur(15px);
    border-radius: 50px;
    font-weight: 600;
    transition: var(--transition-smooth);
    box-shadow: var(--shadow-soft);
}

.hero-cta-btn:hover {
    background: rgba(255, 255, 255, 0.95) !important;
    color: var(--accent-color) !important;
    transform: translateY(-3px) scale(1.02);
    box-shadow: var(--shadow-glow);
}

/* Optimized typing cursor */
.cursor {
    display: inline-block;
    animation: cursor-blink 1s infinite;
    color: rgba(255, 255, 255, 0.9);
    font-weight: 300;
}

@keyframes cursor-blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0; }
}

/* Simplified bounce animation */
.scroll-bounce {
    animation: scroll-bounce 2s ease-in-out infinite;
    transition: var(--transition-fast);
}

@keyframes scroll-bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

.scroll-indicator:hover .scroll-bounce {
    animation-duration: 0.8s;
    opacity: 1 !important;
}

/* Performance optimizations */
.hero-section {
    will-change: transform;
}

.hero-cta-btn,
.scroll-indicator {
    will-change: transform;
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .hero-section {
        height: 80vh;
        min-height: 600px;
    }
    
    .display-2 {
        font-size: 2.5rem !important;
    }
    
    .hero-subtitle {
        font-size: 1.1rem !important;
    }
    
    .hero-cta-btn {
        width: 100%;
        max-width: 280px;
        font-size: 1rem;
    }
}

@media (max-width: 576px) {
    .display-2 {
        font-size: 2rem !important;
        line-height: 1.2;
    }
    
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}

/* Reduce motion for accessibility */
@media (prefers-reduced-motion: reduce) {
    .cursor {
        animation: none;
        opacity: 1;
    }
    
    .scroll-bounce {
        animation: none;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Optimized typing effect
document.addEventListener('DOMContentLoaded', function() {
    const text = "Book your glow-up in Kigali — fast & easy.";
    const target = document.getElementById("typed-text");
    
    if (!target) return; // Safety check
    
    let i = 0;
    const typeSpeed = 50; // Slightly slower for better readability
    
    function typeWriter() {
        if (i < text.length) {
            target.textContent += text.charAt(i);
            i++;
            setTimeout(typeWriter, typeSpeed);
        } else {
            // Optional: Hide cursor after typing is complete
            setTimeout(() => {
                const cursor = document.querySelector('.cursor');
                if (cursor) cursor.style.opacity = '0';
            }, 3000);
        }
    }
    
    // Start typing after a short delay
    setTimeout(typeWriter, 1000);
});

// Smooth scroll for scroll indicator
document.addEventListener('DOMContentLoaded', function() {
    const scrollIndicator = document.querySelector('.scroll-indicator');
    if (scrollIndicator) {
        scrollIndicator.addEventListener('click', function(e) {
            e.preventDefault();
            const nextSection = document.querySelector('.hero-section').nextElementSibling;
            if (nextSection) {
                nextSection.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }
});
</script>
@endpush