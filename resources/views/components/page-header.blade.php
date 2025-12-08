{{-- Optimized Page Header Component --}}
<div class="page-header text-center py-5 position-relative d-flex align-items-center justify-content-center" 
     style="background: linear-gradient(135deg, rgba(212, 113, 122, 0.8) 0%, rgba(45, 55, 72, 0.9) 100%), 
                      url('{{ asset('storage/images/header-bg.jpg') }}') center center / cover no-repeat;
            min-height: 300px;">
    
    {{-- Simplified overlay for better performance --}}
    <div class="position-absolute w-100 h-100 top-0 start-0" 
         style="background: rgba(0, 0, 0, 0.2); z-index: 1;"></div>
    
    <div class="container position-relative" style="z-index: 2;">
        <div data-aos="fade-up" data-aos-duration="600">
            <h1 class="fw-bold text-white mb-3" 
                style="font-family: 'Playfair Display', serif; 
                       font-size: clamp(2rem, 5vw, 3.5rem);
                       text-shadow: 2px 2px 8px rgba(0,0,0,0.6);
                       line-height: 1.2;">
                {{ $title }}
            </h1>
            
            @if ($subtitle)
            <p class="text-white opacity-90 mb-0" 
               style="font-size: clamp(1rem, 2.5vw, 1.25rem);
                      max-width: 600px; 
                      margin: 0 auto;
                      font-weight: 400;
                      text-shadow: 1px 1px 4px rgba(0,0,0,0.5);">
                {{ $subtitle }}
            </p>
            @endif
            
            {{-- Decorative element --}}
            <div class="mx-auto mt-4" 
                 style="width: 80px; 
                        height: 3px; 
                        background: var(--glass-white); 
                        border-radius: 2px;
                        box-shadow: 0 2px 10px rgba(255,255,255,0.3);"></div>
        </div>
    </div>
    
    {{-- Optional floating elements for visual interest --}}
    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10" style="z-index: 1;">
        <div class="floating-element position-absolute" 
             style="top: 20%; left: 10%; 
                    width: 60px; height: 60px; 
                    background: rgba(255, 255, 255, 0.2); 
                    border-radius: 50%;
                    animation: float-gentle 6s ease-in-out infinite;"></div>
        <div class="floating-element position-absolute" 
             style="top: 70%; right: 15%; 
                    width: 40px; height: 40px; 
                    background: rgba(212, 113, 122, 0.3); 
                    border-radius: 50%;
                    animation: float-gentle 8s ease-in-out infinite reverse;"></div>
    </div>
</div>

@push('styles')
<style>
/* Performance-optimized animations */
@keyframes float-gentle {
    0%, 100% { 
        transform: translateY(0px) scale(1); 
        opacity: 0.6;
    }
    50% { 
        transform: translateY(-15px) scale(1.1); 
        opacity: 0.8;
    }
}

/* Page header optimizations */
.page-header {
    transition: var(--transition-smooth);
    will-change: transform;
}

.floating-element {
    will-change: transform;
    pointer-events: none;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .page-header {
        min-height: 250px;
        padding: 3rem 0;
    }
    
    .floating-element {
        display: none; /* Hide on mobile for better performance */
    }
}

@media (max-width: 576px) {
    .page-header {
        min-height: 200px;
        padding: 2rem 0;
    }
}

/* Accessibility improvements */
@media (prefers-reduced-motion: reduce) {
    .floating-element {
        animation: none;
        display: none;
    }
}

/* Improved focus states */
.page-header:focus-within {
    outline: 2px solid var(--primary-color);
    outline-offset: 4px;
}
</style>
@endpush