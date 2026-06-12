@extends('layouts.public')

@section('title', 'Best Nail Salon in Kigali, Rwanda | Luxury Manicure & Pedicure')

@section('meta_description', 'Isaiah Nail Bar is the #1 nail salon in Kigali, Rwanda. Premium manicure, pedicure, gel polish, acrylic nails & nail art by expert technicians. ⭐ 4.9 rated. Book your appointment online today!')
@section('meta_keywords', 'best nail salon Rwanda, nail salon Kigali, manicure Kigali, pedicure Kigali, gel nails Rwanda, acrylic nails Kigali, nail art Kigali, luxury nail salon Rwanda, nail salon near me Kigali, Isaiah Nail Bar, Gisementi nail salon, nail technician Kigali, best manicure Rwanda, nail spa Rwanda, beauty salon Kigali')

@push('meta')
    <meta name="description" content="Isaiah Nail Bar is the #1 nail salon in Kigali, Rwanda. Premium manicure, pedicure, gel polish, acrylic nails & nail art by expert technicians. ⭐ 4.9 rated. Book online today!">
    <meta name="keywords" content="best nail salon Rwanda, nail salon Kigali, manicure Kigali, pedicure Kigali, gel nails Rwanda, acrylic nails Kigali, nail art Kigali, luxury nail salon Rwanda, nail salon near me Kigali, Isaiah Nail Bar, Gisementi nail salon, nail technician Kigali, best manicure Rwanda, nail spa Rwanda, beauty salon Kigali">
@endpush

@push('schema')
{{-- AggregateRating Schema --}}
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "NailSalon",
  "@id": "{{ url('/') }}/#business",
  "name": "Isaiah Nail Bar",
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "{{ $averageRating }}",
    "reviewCount": "{{ $reviewCount }}",
    "bestRating": "5",
    "worstRating": "1"
  }
}
</script>

{{-- BreadcrumbList Schema --}}
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Home",
      "item": "{{ url('/') }}"
    }
  ]
}
</script>
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
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full mb-6">
                <svg class="w-5 h-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                <span class="text-white/90 text-sm font-medium">Verified Google Reviews</span>
                <div class="flex items-center gap-0.5 ml-1">
                    @for($i = 0; $i < 5; $i++)
                        <i class="ph-fill ph-star text-yellow-400 text-xs"></i>
                    @endfor
                </div>
                <span class="text-white/70 text-sm">{{ $averageRating }}</span>
            </div>
            <h2 class="text-3xl md:text-4xl font-serif text-white mb-4">
                What Our Clients Say
            </h2>
            <p class="text-gray-400 font-light">Real reviews from our Google Business profile</p>
        </div>

        @php
            /* Sort reviews: Google reviews first, then internal */
            $sortedReviews = $reviews->sortByDesc(function($review) {
                return $review->source === 'google' ? 1 : 0;
            })->values();
        @endphp

        <div x-data="{
                activeSlide: 0,
                slides: {{ $sortedReviews->count() }},
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
            <style>
                .hide-scrollbar::-webkit-scrollbar {
                    display: none;
                }
                .hide-scrollbar {
                    -ms-overflow-style: none;
                    scrollbar-width: none;
                }
            </style>
            <div class="overflow-x-auto pb-8 hide-scrollbar snap-x snap-mandatory flex gap-6 md:grid md:grid-cols-2 lg:grid-cols-3 md:gap-8 md:overflow-visible" x-ref="carousel">
                @foreach($sortedReviews as $review)
                    <div class="min-w-[85vw] md:min-w-0 snap-center bg-white rounded-2xl p-8 hover:-translate-y-1 transition-transform duration-300 relative group">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                @if($review->avatar_url)
                                    <img src="{{ $review->avatar_url }}" alt="{{ $review->reviewer_name }}" class="w-12 h-12 rounded-full object-cover">
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
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                    </svg>
                                </div>
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

                        @if($review->source === 'google')
                            <span class="text-xs text-gray-400 inline-flex items-center gap-1">
                                <i class="ph ph-seal-check text-blue-500"></i>
                                Posted on Google
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Mobile Dots --}}
            <div class="flex justify-center gap-2 mt-4 md:hidden">
                @foreach($sortedReviews as $key => $review)
                    <div class="w-2 h-2 rounded-full transition-colors duration-300"
                         :class="{ 'bg-rose-500': activeSlide === {{ $key }}, 'bg-gray-600': activeSlide !== {{ $key }} }"></div>
                @endforeach
            </div>
        </div>

        {{-- Google Review CTA --}}
        <div class="text-center mt-12">
            <a href="https://g.page/r/CS4QpNuz_MJkEAE/review" target="_blank" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-white/10 backdrop-blur-sm text-white rounded-full hover:bg-white/20 transition-all text-sm font-medium">
                <svg class="w-4 h-4" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Leave us a review on Google
                <i class="ph ph-arrow-up-right"></i>
            </a>
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
                        <p class="text-gray-500">Sunday: OFF</p>
                    </div>
                </div>
            </div>

            <div class="flex gap-4">
                <a href="{{ route('booking.step1') }}" class="px-8 py-4 bg-gray-900 text-white font-medium rounded-full hover:bg-rose-600 transition-all shadow-lg hover:shadow-xl hover:-translate-y-1">
                    Book Now
                </a>
                <a href="https://wa.me/250790395169" target="_blank" class="px-8 py-4 bg-white border border-gray-200 text-gray-700 font-medium rounded-full hover:border-gray-900 hover:text-gray-900 transition-all">
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
