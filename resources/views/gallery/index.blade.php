@extends('layouts.public')

@section('title', 'Gallery - Isaiah Nail Bar')

@section('content')
<x-page-header 
    title="Instagram Highlights"
    subtitle="Our latest nail designs, beauty moments, and salon vibes  straight from our official feed."
/>

<div class="container my-5">
    @if($posts->count())
        <div class="row g-4 justify-content-center" data-aos="fade-up" data-aos-delay="100">
            @foreach($posts as $post)
                <div class="col-md-6 col-lg-4">
                    <div class="gallery-card card border-0 shadow-lg h-100 overflow-hidden p-3 bg-white">
                        <blockquote class="instagram-media"
                                    data-instgrm-permalink="{{ $post->url }}"
                                    data-instgrm-version="14"
                                    style="background: #fff; border: 0; margin: 0 auto; min-height: 400px;">
                        </blockquote>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- CTA Footer --}}
        <div class="text-center mt-5" data-aos="fade-up" data-aos-delay="300">
            <p class="lead fw-semibold mb-3">Want more glam?</p>
            <a href="https://www.instagram.com/isaiah_nails_art_kigali_rwanda" target="_blank" class="btn btn-lg btn-outline-dark rounded-pill px-4">
                <i class="bi bi-instagram me-2"></i> Follow Us @isaiah_nails_art_kigali_rwanda
            </a>
        </div>
    @else
        <div class="alert alert-info text-center mt-5">
            <i class="bi bi-info-circle me-2"></i> Our Instagram gallery is loading soon. Stay tuned!
        </div>
    @endif
</div>
@endsection

@push('scripts')
    <script async src="//www.instagram.com/embed.js"></script>
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({ once: true });
    </script>
@endpush

@push('styles')
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet" />
    <style>
        .gallery-card {
            transition: transform 0.4s ease, box-shadow 0.4s ease;
            border-radius: 16px;
        }

        .gallery-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        @media (max-width: 576px) {
            .gallery-card blockquote {
                min-height: 360px;
            }
        }
    </style>
@endpush
