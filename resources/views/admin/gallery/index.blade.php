@extends('layouts.dashboard')

@section('title', 'Instagram Gallery')

@section('content')
<div class="p-6">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            <i class="ph ph-instagram-logo text-pink-600 mr-2"></i>Instagram Gallery
        </h1>
        <p class="text-gray-600">Manage Instagram posts displayed on your website</p>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-2 border-green-200 rounded-xl p-4 flex items-center gap-3">
            <i class="ph ph-check-circle text-2xl text-green-600"></i>
            <p class="text-green-900 font-semibold">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Add Instagram Post Form --}}
    <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Add New Instagram Post</h2>
        <form method="POST" action="{{ route('admin.gallery-instagram.store') }}">
            @csrf
            <div class="flex gap-3">
                <div class="flex-1">
                    <input type="url" 
                           name="url" 
                           required
                           placeholder="https://www.instagram.com/p/POST_ID/"
                           class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                    @error('url')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" 
                        class="px-6 py-3 bg-pink-600 text-white font-semibold rounded-lg hover:bg-pink-700 transition flex-shrink-0">
                    <i class="ph ph-plus-circle mr-2"></i>Add Post
                </button>
            </div>
        </form>
    </div>

    {{-- Gallery Grid --}}
    @if($posts->count())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($posts as $post)
                <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden hover:shadow-xl transition">
                    <div class="p-4">
                        <blockquote class="instagram-media"
                                    data-instgrm-permalink="{{ $post->url }}"
                                    data-instgrm-version="14"
                                    style="background:#FFF; border:0; margin:0 auto; max-width:100%;">
                        </blockquote>
                    </div>
                    <div class="p-4 pt-0 flex items-center justify-between border-t border-gray-100">
                        <a href="{{ $post->url }}" 
                           target="_blank"
                           class="text-sm text-blue-600 hover:text-blue-800 transition">
                            <i class="ph ph-arrow-square-out mr-1"></i>View on Instagram
                        </a>
                        <form method="POST" action="{{ route('admin.gallery-instagram.destroy', $post->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="px-3 py-1.5 bg-red-100 text-red-700 text-sm font-semibold rounded-lg hover:bg-red-200 transition"
                                    onclick="return confirm('Delete this Instagram post from gallery?')">
                                <i class="ph ph-trash mr-1"></i>Delete
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-pink-50 border-2 border-pink-200 rounded-xl p-12 text-center">
            <i class="ph ph-instagram-logo text-6xl text-pink-300 mb-4"></i>
            <p class="text-pink-900 font-semibold text-lg">No Instagram posts yet</p>
            <p class="text-pink-700 text-sm mt-2">Add your first Instagram post to showcase your work</p>
        </div>
    @endif
</div>
@endsection

@push('scripts')
    <script async src="//www.instagram.com/embed.js"></script>
@endpush
