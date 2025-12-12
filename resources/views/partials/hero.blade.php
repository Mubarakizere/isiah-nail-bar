{{-- Modern Luxury Hero Section --}}
<section class="relative h-[90vh] min-h-[600px] flex items-center justify-center overflow-hidden bg-gray-900">
    
    {{-- Background Image --}}
    <div class="absolute inset-0 z-0">
        <img src="{{ asset('storage/banner.jpg') }}" alt="Hero Background" class="w-full h-full object-cover">
        
        {{-- Luxury Gradient Overlay --}}
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-black/30"></div>
        <div class="absolute inset-0 bg-gray-900/20 backdrop-brightness-75"></div>
    </div>

    {{-- Content --}}
    <div class="container mx-auto px-4 relative z-10 text-center">
        <div class="max-w-5xl mx-auto space-y-8 animate-fade-in-up">
            
            {{-- Decorative Element --}}
            <div class="inline-block mb-4">
                <span class="px-4 py-1.5 rounded-full border border-white/30 text-white/90 text-sm font-medium tracking-widest uppercase backdrop-blur-sm">
                    Kigali's Premier Nail Salon
                </span>
            </div>

            <h1 class="text-5xl md:text-7xl lg:text-8xl font-serif text-white leading-tight tracking-tight drop-shadow-lg">
                Artistry at Your <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-200 to-white">Fingertips</span>
            </h1>
            
            <p class="text-xl md:text-2xl text-gray-200 font-light max-w-2xl mx-auto leading-relaxed opacity-90">
                Experience the perfect blend of luxury, hygiene, and style.
            </p>
            
            <div class="pt-8 flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="{{ route('booking.step1') }}" 
                   class="min-w-[200px] px-8 py-4 bg-white text-gray-900 font-medium rounded-full hover:bg-rose-50 transition-all duration-300 transform hover:-translate-y-1 shadow-[0_0_20px_rgba(255,255,255,0.3)]">
                    Book Appointment
                </a>
                <a href="{{ url('/services') }}" 
                   class="min-w-[200px] px-8 py-4 bg-transparent border border-white/30 text-white font-medium rounded-full hover:bg-white/10 hover:border-white transition-all duration-300 backdrop-blur-sm">
                    View Services
                </a>
            </div>
        </div>
    </div>

    {{-- Scroll Indicator --}}
    <div class="absolute bottom-10 left-1/2 -translate-x-1/2 z-10 animate-bounce">
        <span class="text-white/50 text-sm uppercase tracking-widest mb-2 block text-center text-[10px]">Scroll</span>
        <i class="ph ph-caret-down text-2xl text-white/70"></i>
    </div>
</section>

<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translate3d(0, 40px, 0);
        }
        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }
    .animate-fade-in-up {
        animation: fadeInUp 1s cubic-bezier(0.2, 0.8, 0.2, 1);
    }
</style>