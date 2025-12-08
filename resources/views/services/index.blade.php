@extends('layouts.public')

@section('title', 'Our Nail Services')

@section('content')
<x-page-header 
    title="Our Services" 
    subtitle="Professional nail care in Kigali" 
/>

<!-- Services Content -->
<section class="py-5" style="background: #fefefe;">
    <div class="container">
        
        <!-- Share Section -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8">
                <div class="share-section text-center p-4 rounded-3" style="background: #f8f9fa; border: 1px solid #e9ecef;">
                    <h5 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif; color: #2c2c2c;">
                        Share Our Services
                    </h5>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                           target="_blank" class="btn btn-sm px-3 py-2 rounded-pill"
                           style="background: #1877f2; color: white;">
                            <i class="fab fa-facebook-f me-1"></i>Facebook
                        </a>
                        <a href="https://wa.me/?text={{ urlencode('Check out Isaiah Nail Bar services: ' . url()->current()) }}"
                           target="_blank" class="btn btn-sm px-3 py-2 rounded-pill"
                           style="background: #25d366; color: white;">
                            <i class="fab fa-whatsapp me-1"></i>WhatsApp
                        </a>
                        <button onclick="copyLink()" class="btn btn-sm px-3 py-2 rounded-pill" style="background: #6c757d; color: white;">
                            <i class="fas fa-link me-1"></i>Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services by Category -->
        @forelse($categories as $category)
            @php $services = $category->services->sortBy('price'); @endphp
            
            <div class="category-section mb-5">
                <div class="category-header mb-4 p-4 rounded-3" style="background: var(--primary-gradient); color: white;">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif;">{{ $category->name }}</h3>
                            <p class="mb-0 opacity-75">{{ $services->count() }} {{ Str::plural('service', $services->count()) }} available</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-spa fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>

                @if($services->count())
                    <div class="services-grid">
                        @foreach($services as $service)
                            <div class="service-item mb-4 p-4 rounded-3 border" style="background: white; transition: all 0.2s ease;">
                                <div class="row align-items-center">
                                    <!-- Service Info -->
                                    <div class="col-lg-8 col-md-7">
                                        <div class="service-details">
                                            <div class="d-flex align-items-start justify-content-between mb-2">
                                                <h5 class="service-name fw-bold mb-0" style="font-family: 'Playfair Display', serif; color: #2c2c2c;">
                                                    {{ $service->name }}
                                                </h5>
                                                <span class="price-tag fw-bold" style="color: var(--primary-color); font-size: 1.1rem;">
                                                    RWF {{ number_format($service->price) }}
                                                </span>
                                            </div>
                                            
                                            @if($service->description)
                                                <div class="service-description mb-3">
                                                    <p class="text-muted mb-0 lh-base">{{ $service->description }}</p>
                                                </div>
                                            @endif

                                            @if($service->duration)
                                                <div class="service-meta mb-3">
                                                    <span class="badge bg-light text-dark">
                                                        <i class="fas fa-clock me-1"></i>{{ $service->duration }} min
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Service Actions -->
                                    <div class="col-lg-4 col-md-5">
                                        <div class="service-actions text-end">
                                            <div class="d-flex gap-2 justify-content-end">
                                                <a href="{{ route('booking.step1', ['service_id' => $service->id]) }}" 
                                                   class="btn btn-primary px-4 py-2 rounded-pill">
                                                    Book Now
                                                </a>
                                                @if($service->description)
                                                    <button class="btn btn-outline-secondary px-3 py-2 rounded-pill" 
                                                            data-bs-toggle="modal" data-bs-target="#serviceModal{{ $service->id }}">
                                                        <i class="fas fa-info"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Service Modal -->
                            @if($service->description)
                                <div class="modal fade" id="serviceModal{{ $service->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 rounded-4">
                                            <div class="modal-header border-0 pb-0" style="background: var(--primary-gradient); color: white; border-radius: 1rem 1rem 0 0;">
                                                <h5 class="modal-title fw-bold" style="font-family: 'Playfair Display', serif;">{{ $service->name }}</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="service-details">
                                                    @if($service->image)
                                                        <div class="service-image mb-3">
                                                            <img src="{{ asset('storage/' . $service->image) }}" 
                                                                 class="img-fluid rounded-3" alt="{{ $service->name }}">
                                                        </div>
                                                    @endif
                                                    
                                                    <div class="price-info mb-3 p-3 rounded-3" style="background: var(--glass-pink);">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="fw-semibold">Price:</span>
                                                            <span class="fw-bold" style="color: var(--primary-color);">RWF {{ number_format($service->price) }}</span>
                                                        </div>
                                                        @if($service->duration)
                                                            <div class="d-flex justify-content-between align-items-center mt-2">
                                                                <span class="fw-semibold">Duration:</span>
                                                                <span>{{ $service->duration }} minutes</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    
                                                    <div class="description">
                                                        <h6 class="fw-bold mb-2">Description</h6>
                                                        <p class="text-muted lh-base">{{ $service->description }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 pt-0">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                                <a href="{{ route('booking.step1', ['service_id' => $service->id]) }}" 
                                                   class="btn btn-primary">Book This Service</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <div class="text-muted">
                            <i class="fas fa-spa fa-3x mb-3 opacity-25"></i>
                            <p>No services available in this category yet.</p>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-5">
                <div class="empty-state">
                    <i class="fas fa-spa fa-4x text-muted mb-3 opacity-25"></i>
                    <h4 class="text-muted mb-3">No Services Available</h4>
                    <p class="text-muted">We're updating our services. Please check back soon!</p>
                    <a href="{{ url('/contact') }}" class="btn btn-outline-primary">Contact Us</a>
                </div>
            </div>
        @endforelse

        <!-- Book Now Section -->
        <div class="cta-section mt-5 p-5 rounded-4 text-center" style="background: var(--dark-primary); color: white;">
            <h3 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif;">Ready to Book?</h3>
            <p class="mb-4 opacity-75">Schedule your appointment and experience luxury nail care</p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="{{ route('booking.step1') }}" class="btn btn-primary btn-lg px-5 py-3 rounded-pill">
                    Book Appointment
                </a>
                <a href="{{ url('/contact') }}" class="btn btn-outline-light btn-lg px-5 py-3 rounded-pill">
                    Contact Us
                </a>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
/* Service Cards */
.service-item {
    transition: all 0.15s ease;
}

.service-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    border-color: var(--primary-color) !important;
}

/* Category Headers */
.category-header {
    transition: all 0.2s ease;
}

.category-section:hover .category-header {
    transform: translateY(-1px);
    box-shadow: 0 8px 25px rgba(212, 113, 122, 0.2);
}

/* Buttons */
.btn:hover {
    transform: translateY(-1px);
}

.btn-primary:hover {
    box-shadow: 0 5px 15px rgba(212, 113, 122, 0.3);
}

/* Share buttons */
.share-section .btn:hover {
    transform: translateY(-2px) scale(1.02);
}

/* Modal styling */
.modal-content {
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .service-item .row {
        text-align: center;
    }
    
    .service-actions {
        text-align: center !important;
        margin-top: 1rem;
    }
    
    .service-actions .d-flex {
        justify-content: center !important;
    }
    
    .service-details .d-flex {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .price-tag {
        align-self: center;
        margin-top: 0.5rem;
    }
    
    .cta-section .d-flex {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .share-section .d-flex {
        gap: 0.5rem;
    }
    
    .category-header {
        padding: 1.5rem !important;
    }
    
    .service-item {
        padding: 1.5rem !important;
    }
}

@media (max-width: 576px) {
    .btn-lg {
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .share-section .btn {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem !important;
    }
}

/* Remove excessive animations on mobile for better performance */
@media (max-width: 768px) {
    .service-item:hover {
        transform: none;
    }
    
    .category-section:hover .category-header {
        transform: none;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Simple copy function
function copyLink() {
    const url = window.location.href;
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            showMessage('Link copied!');
        });
    } else {
        // Fallback
        const input = document.createElement('input');
        input.value = url;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        showMessage('Link copied!');
    }
}

// Simple notification
function showMessage(text) {
    if (typeof toastr !== 'undefined') {
        toastr.success(text);
    } else {
        alert(text);
    }
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(el => new bootstrap.Tooltip(el));
    }
});
</script>
@endpush

@endsection