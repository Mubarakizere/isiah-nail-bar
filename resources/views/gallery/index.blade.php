@extends('layouts.public')

@section('title', 'Gallery - Our Works')

@section('content')

{{-- Gallery Hero --}}
<div class="relative bg-gray-900 py-24 overflow-hidden">
    <div class="absolute inset-0 opacity-40">
        <img src="{{ asset('storage/banner.jpg') }}" alt="Gallery Banner" class="w-full h-full object-cover">
    </div>
    <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/60 to-transparent"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center pt-12">
        <span class="text-rose-400 font-medium tracking-widest text-sm uppercase mb-3 block animate-fade-in-up">Instagram Feed</span>
        <h1 class="text-4xl md:text-6xl font-serif text-white mb-6 animate-fade-in-up delay-100">Visual Stories</h1>
        <p class="text-xl text-gray-300 font-light max-w-2xl mx-auto animate-fade-in-up delay-200">
            A curation of our finest work, straight from our studio to your screen.
        </p>
        
        <div class="mt-8 animate-fade-in-up delay-300">
            <a href="https://www.instagram.com/isaiah_nails_art_kigali_rwanda" target="_blank" class="inline-flex items-center gap-2 text-white border border-white/30 px-6 py-3 rounded-full hover:bg-white hover:text-gray-900 transition-all duration-300 backdrop-blur-sm">
                <i class="ph ph-instagram-logo text-xl"></i>
                <span>@isaiah_nails_art_kigali_rwanda</span>
            </a>
        </div>
    </div>
</div>

{{-- Gallery Grid --}}
<section class="py-16 bg-white min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($posts->count())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($posts as $post)
                    <div class="group relative bg-gray-50 rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-1">
                        {{-- Instagram Embed --}}
                        <div class="instagram-embed-wrapper">
                            <blockquote class="instagram-media"
                                        data-instgrm-permalink="{{ $post->url }}"
                                        data-instgrm-version="14"
                                        style="background: #FFF; border: 0; border-radius: 3px; box-shadow: none; margin: 1px; max-width: 540px; min-width: 326px; padding: 0; width: 99.375%; width: -webkit-calc(100% - 2px); width: calc(100% - 2px);">
                            </blockquote>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-20 text-center">
                <p class="text-gray-500 mb-6">See more of our daily updates</p>
                <a href="https://www.instagram.com/isaiah_nails_art_kigali_rwanda" target="_blank" class="inline-flex items-center gap-3 px-8 py-4 bg-gray-900 text-white font-medium rounded-full hover:bg-rose-600 transition-all shadow-lg hover:shadow-xl hover:-translate-y-1">
                    <i class="ph ph-arrow-right"></i>
                    <span>View More on Instagram</span>
                </a>
            </div>
        @else
            <div class="text-center py-32">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-50 mb-6">
                    <i class="ph ph-instagram-logo text-4xl text-gray-300"></i>
                </div>
                <h3 class="text-2xl font-serif text-gray-900 mb-2">Feed Loading...</h3>
                <p class="text-gray-500 max-w-md mx-auto mb-8">
                    We're connecting to Instagram to show you our latest work. Please check back in a moment.
                </p>
                <a href="https://www.instagram.com/isaiah_nails_art_kigali_rwanda" target="_blank" class="text-rose-600 hover:text-rose-700 font-medium underline underline-offset-4">
                    Visit our Instagram directly
                </a>
            </div>
        @endif
    </div>
</section>

@push('scripts')
    <script async src="//www.instagram.com/embed.js"></script>
@endpush

@endsection
