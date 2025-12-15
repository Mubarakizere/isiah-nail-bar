@extends('layouts.public')

@section('title', 'Best Nail Salon in Rwanda | Luxury Manicure & Pedicure')

@push('meta')
    <meta name="description" content="Isaiah Nail Bar is the best nail salon in Rwanda, offering premium manicure, pedicure, gel polish, and acrylic services in Kigali. Book your appointment today!">
    <meta name="keywords" content="Best Nail Salon Rwanda, Nails Kigali, Manicure Kigali, Pedicure Kigali, Gisementi Salon, Luxury Nails Rwanda">
@endpush

@section('hero')
    @include('partials.hero')
@endsection

@section('content')

{{-- Services Preview --}}
<section class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="text-rose-600 font-medium tracking-widest text-sm uppercase mb-3 block">Our Expertise</span>
            <h2 class="text-4xl md:text-5xl font-serif font-medium text-gray-900 mb-6">
                Curated Treatments
            </h2>
            <p class="text-lg text-gray-500 font-light leading-relaxed">
                Discover our most popular services, designed to rejuvenate your hands and feet with premium products and expert care.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            @foreach($featuredServices as $service)
                <div class="group cursor-pointer">
                    <div class="relative aspect-[4/5] overflow-hidden rounded-2xl mb-6 bg-gray-100">
                        <img src="{{ $service->image ? asset('storage/' . $service->image) : asset('images/default-service.jpg') }}" 
                             alt="{{ $service->name }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                        
                        {{-- Booking Overlay --}}
                        <div class="absolute inset-x-0 bottom-0 p-6 bg-gradient-to-t from-black/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center">
                            <a href="{{ route('booking.step1') }}" class="px-6 py-3 bg-white text-gray-900 rounded-full font-medium text-sm hover:bg-rose-50 transition-colors transform translate-y-4 group-hover:translate-y-0 duration-300">
                                Book This Service
                            </a>
                        </div>
                    </div>
                    
                    <div class="text-center group-hover:-translate-y-1 transition-transform duration-300">
                        <h5 class="text-xl font-serif text-gray-900 mb-2 group-hover:text-rose-600 transition-colors">{{ $service->name }}</h5>
                        <div class="flex items-center justify-center gap-3 text-sm">
                            @if($service->category)
                                <span class="text-gray-500 uppercase tracking-wider text-xs">{{ $service->category->name }}</span>
                                <span class="text-gray-300">•</span>
                            @endif
                            <span class="font-medium text-gray-900">RWF {{ number_format($service->price) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-16">
            <a href="{{ url('/services') }}" class="inline-flex items-center gap-2 text-rose-600 font-medium hover:text-rose-700 hover:gap-3 transition-all">
                <span>View Full Menu</span>
                <i class="ph ph-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

{{-- The Experience (Why Choose Us) --}}
<section class="py-24 bg-rose-50/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div class="order-2 lg:order-1">
                <div class="grid grid-cols-2 gap-6">
                    @foreach([
                        ['icon' => 'ph-star-four', 'title' => 'Expert Care', 'desc' => 'Master technicians dedicated to perfection.'],
                        ['icon' => 'ph-sparkle', 'title' => 'Premium Products', 'desc' => 'Only the finest polishes and treatments.'],
                        ['icon' => 'ph-shield-check', 'title' => 'Hygiene First', 'desc' => 'Hospital-grade sanitation standards.'],
                        ['icon' => 'ph-heart', 'title' => 'Relaxing Atmosphere', 'desc' => 'A sanctuary of calm in the city.']
                    ] as $feature)
                        <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300">
                            <div class="w-12 h-12 bg-rose-50 rounded-xl flex items-center justify-center mb-4 text-rose-600">
                                <i class="ph {{ $feature['icon'] }} text-2xl"></i>
                            </div>
                            <h4 class="text-lg font-bold text-gray-900 mb-2">{{ $feature['title'] }}</h4>
                            <p class="text-sm text-gray-500 leading-relaxed">{{ $feature['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="order-1 lg:order-2">
                <span class="text-rose-600 font-medium tracking-widest text-sm uppercase mb-3 block">Why Choose Us</span>
                <h2 class="text-4xl md:text-5xl font-serif font-medium text-gray-900 mb-6 leading-tight">
                    Beyond Just a <br>Nail Service
                </h2>
                <p class="text-lg text-gray-500 font-light leading-relaxed mb-8">
                    At Isaiah Nail Bar, we believe self-care is an art form. Every detail of our salon is designed to provide you with a moment of tranquility and luxury. From our ergonomic chairs to our selected playlists, we ensure your visit is nothing short of perfect.
                </p>
                
                <div class="flex items-center gap-8">
                    <div>
                        <span class="text-3xl font-serif text-gray-900 block">500+</span>
                        <span class="text-sm text-gray-500 uppercase tracking-wider">Happy Clients</span>
                    </div>
                    <div class="h-10 w-px bg-gray-200"></div>
                    <div>
                        <span class="text-3xl font-serif text-gray-900 block">4.9</span>
                        <span class="text-sm text-gray-500 uppercase tracking-wider">Rating</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Reviews (Dark Section) --}}
<section class="py-24 bg-gray-900 overflow-hidden relative">
    {{-- Background Pattern --}}
    <div class="absolute top-0 right-0 p-12 opacity-5 translate-x-1/3 -translate-y-1/3">
        <i class="ph ph-quotes text-9xl text-white"></i>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-serif text-white mb-4">
                Client Stories
            </h2>
            <p class="text-gray-400 font-light">Join our community of satisfied clients</p>
        </div>
        
        <div x-data="{
                activeSlide: 0,
                slides: {{ $reviews->count() }},
                next() {
                    this.activeSlide = (this.activeSlide === this.slides - 1) ? 0 : this.activeSlide + 1;
                },
                prev() {
                    this.activeSlide = (this.activeSlide === 0) ? this.slides - 1 : this.activeSlide - 1;
                }
             }" 
             class="relative">
            
            {{-- Carousel Nav --}}
            <button @click="prev()" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 z-20 w-12 h-12 rounded-full bg-white text-gray-900 flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all shadow-lg hidden md:flex">
                <i class="ph ph-caret-left text-xl"></i>
            </button>
            <button @click="next()" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 z-20 w-12 h-12 rounded-full bg-white text-gray-900 flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all shadow-lg hidden md:flex">
                <i class="ph ph-caret-right text-xl"></i>
            </button>

            {{-- Reviews Grid --}}
            <div class="overflow-x-auto pb-8 hide-scrollbar snap-x snap-mandatory flex gap-6 md:grid md:grid-cols-2 lg:grid-cols-3 md:gap-8 md:overflow-visible">
                @foreach($reviews as $review)
                    <div class="min-w-[85vw] md:min-w-0 snap-center bg-white rounded-2xl p-8 hover:-translate-y-1 transition-transform duration-300 relative group">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                @if($review->avatar_url)
                                    <img src="{{ $review->avatar_url }}" class="w-12 h-12 rounded-full object-cover border border-gray-100">
                                @else
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-500 font-bold text-lg">
                                        {{ strtoupper(substr($review->reviewer_name ?? $review->booking->customer->user->name ?? 'A', 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <h4 class="font-bold text-gray-900 text-sm">{{ $review->reviewer_name ?? $review->booking->customer->user->name ?? 'Anonymous' }}</h4>
                                    <p class="text-xs text-gray-500">{{ $review->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            @if($review->source === 'google')
                                <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" class="w-6 h-6" alt="Google">
                            @endif
                        </div>

                        <div class="flex text-yellow-400 mb-4 text-sm">
                            @for($i = 0; $i < 5; $i++)
                                <i class="ph-fill ph-star{{ $i < $review->rating ? '' : '-half' }}"></i>
                            @endfor
                        </div>

                        <p class="text-gray-600 text-sm leading-relaxed mb-4 line-clamp-4">
                            {{ $review->comment }}
                        </p>
                        
                        <a href="https://share.google/OtbpRmUNfC1eA9kmZ" target="_blank" class="text-rose-600 text-sm font-medium hover:underline inline-flex items-center gap-1">
                            Read more
                            <i class="ph ph-arrow-right"></i>
                        </a>
                    </div>
                @endforeach
            </div>
            
            {{-- Mobile Dots --}}
            <div class="flex justify-center gap-2 mt-4 md:hidden">
                @foreach($reviews as $key => $review)
                    <div class="w-2 h-2 rounded-full transition-colors duration-300" 
                         :class="{ 'bg-rose-500': activeSlide === {{ $key }}, 'bg-gray-600': activeSlide !== {{ $key }} }"></div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- CTA / Map --}}
<section class="relative bg-white">
    <div class="grid grid-cols-1 lg:grid-cols-2">
        <div class="bg-gray-50 px-8 py-24 lg:px-16 flex flex-col justify-center">
            <span class="text-rose-600 font-medium tracking-widest text-sm uppercase mb-3 block">Visit Us</span>
            <h2 class="text-4xl font-serif font-medium text-gray-900 mb-8">
                Ready for your glow up?
            </h2>
            
            <div class="space-y-6 mb-10">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-white border border-gray-100 flex items-center justify-center shadow-sm flex-shrink-0">
                        <i class="ph ph-map-pin text-rose-500 text-lg"></i>
                    </div>
                    <div>
                        <h5 class="font-bold text-gray-900">Location</h5>
                        <p class="text-gray-500">KG 4 Roundabout, Gisementi, Kigali</p>
                    </div>
                </div>
                
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-white border border-gray-100 flex items-center justify-center shadow-sm flex-shrink-0">
                        <i class="ph ph-clock text-rose-500 text-lg"></i>
                    </div>
                    <div>
                        <h5 class="font-bold text-gray-900">Opening Hours</h5>
                        <p class="text-gray-500">Mon - Sat: 8:00 AM - 8:00 PM</p>
                        <p class="text-gray-500">Sunday: 10:00 AM - 6:00 PM</p>
                    </div>
                </div>
            </div>

            <div class="flex gap-4">
                <a href="{{ route('booking.step1') }}" class="px-8 py-4 bg-gray-900 text-white font-medium rounded-full hover:bg-rose-600 transition-all shadow-lg hover:shadow-xl hover:-translate-y-1">
                    Book Now
                </a>
                <a href="https://wa.me/250788421063" target="_blank" class="px-8 py-4 bg-white border border-gray-200 text-gray-700 font-medium rounded-full hover:border-gray-900 hover:text-gray-900 transition-all">
                    WhatsApp Us
                </a>
            </div>
        </div>
        
        <div class="h-96 lg:h-auto bg-gray-200 relative">
             <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15950.019185121285!2d30.108678!3d-1.954736!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x19dca6ee46244f67%3A0x6291244078657662!2sKisimenti!5e0!3m2!1sen!2srw!4v1683838383838!5m2!1sen!2srw" 
                width="100%" 
                height="100%" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade"
                class="absolute inset-0 transition-all duration-700">
            </iframe>
        </div>
    </div>
</section>

@endsection