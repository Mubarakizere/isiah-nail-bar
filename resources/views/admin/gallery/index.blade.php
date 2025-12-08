@extends('layouts.dashboard')

@section('title', 'Instagram Gallery')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">
            <i class="bi bi-instagram me-2 text-danger"></i> Instagram Gallery
        </h2>
    </div>

    {{-- Toastr Success --}}
    @if(session('success'))
        <div id="toast-success" data-message="{{ session('success') }}"></div>
    @endif

    {{-- Add Instagram Post Form --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body p-4">
            <h5 class="fw-semibold mb-3">Add New Instagram Post</h5>
            <form method="POST" action="{{ route('admin.gallery-instagram.store') }}">
                @csrf
                <div class="row g-3 align-items-center">
                    <div class="col-md-10">
                        <input type="url" name="url" class="form-control" placeholder="https://www.instagram.com/p/POST_ID/" required>
                    </div>
                    <div class="col-md-2 text-end">
                        <button class="btn btn-dark w-100">
                            <i class="bi bi-plus-circle me-1"></i> Add
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Gallery Grid --}}
    @if($posts->count())
        <div class="row g-4">
            @foreach($posts as $post)
                <div class="col-md-6 col-lg-4">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-body p-3">
                            <blockquote class="instagram-media"
                                        data-instgrm-permalink="{{ $post->url }}"
                                        data-instgrm-version="14"
                                        style="background:#FFF; border:0; margin:0 auto;">
                            </blockquote>
                        </div>
                        <div class="card-footer bg-white border-0 text-end">
                            <form method="POST" action="{{ route('admin.gallery-instagram.destroy', $post->id) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-trash3 me-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info text-center mt-5">
            <i class="bi bi-info-circle me-2"></i> No Instagram posts added yet.
        </div>
    @endif
</div>
@endsection

@push('scripts')
    <script async src="//www.instagram.com/embed.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toast = document.getElementById('toast-success');
            if (toast) {
                toastr.success(toast.dataset.message, 'Success', {
                    closeButton: true,
                    progressBar: true,
                    timeOut: 4000,
                });
            }
        });
    </script>
@endpush

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush
