@extends('layouts.dashboard')

@section('title', 'Hero Slides')
@section('page-title', 'Hero Slides')
@section('page-subtitle', 'Manage homepage carousel images and content')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <p class="text-gray-500 text-sm">Add, edit, and reorder hero carousel slides on your homepage.</p>
        </div>
        <a href="{{ route('admin.hero-slides.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-900 text-white rounded-xl text-sm font-medium hover:bg-rose-600 transition-all shadow-sm hover:shadow-md">
            <i class="ph ph-plus-circle text-lg"></i>
            Add New Slide
        </a>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <i class="ph ph-check-circle text-lg"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Slides Grid --}}
    @if($slides->count())
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($slides as $slide)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden group hover:shadow-lg transition-all duration-300">
                    {{-- Image Preview --}}
                    <div class="relative aspect-[16/9] overflow-hidden bg-gray-100">
                        <img src="{{ asset('storage/' . $slide->image) }}"
                             alt="{{ $slide->title }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">

                        {{-- Overlay Badges --}}
                        <div class="absolute top-3 left-3 flex gap-2">
                            <span class="px-3 py-1 rounded-full text-xs font-bold backdrop-blur-md shadow-sm
                                {{ $slide->is_active ? 'bg-green-500/90 text-white' : 'bg-gray-800/70 text-gray-300' }}">
                                {{ $slide->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-black/50 text-white backdrop-blur-md">
                                #{{ $slide->sort_order }}
                            </span>
                        </div>

                        {{-- Dark Gradient for Text Preview --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>

                        {{-- Text Preview --}}
                        <div class="absolute bottom-0 left-0 right-0 p-5">
                            @if($slide->subtitle)
                                <p class="text-rose-300 text-xs font-medium tracking-widest uppercase mb-1">{{ $slide->subtitle }}</p>
                            @endif
                            <h3 class="text-white font-serif text-xl font-bold leading-tight">{{ $slide->title }}</h3>
                            @if($slide->description)
                                <p class="text-gray-300 text-sm mt-1 line-clamp-2">{{ $slide->description }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="p-4 flex items-center justify-between border-t border-gray-50">
                        <div class="flex items-center gap-2">
                            {{-- Toggle Active --}}
                            <form method="POST" action="{{ route('admin.hero-slides.toggle', $slide) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="p-2 rounded-lg text-sm transition-colors {{ $slide->is_active ? 'text-green-600 hover:bg-green-50' : 'text-gray-400 hover:bg-gray-50' }}"
                                        title="{{ $slide->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="ph {{ $slide->is_active ? 'ph-eye' : 'ph-eye-slash' }} text-lg"></i>
                                </button>
                            </form>

                            {{-- Edit --}}
                            <a href="{{ route('admin.hero-slides.edit', $slide) }}"
                               class="p-2 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors"
                               title="Edit">
                                <i class="ph ph-pencil-simple text-lg"></i>
                            </a>
                        </div>

                        {{-- Delete --}}
                        <form method="POST" action="{{ route('admin.hero-slides.destroy', $slide) }}"
                              onsubmit="return confirm('Are you sure you want to delete this slide?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="p-2 rounded-lg text-red-500 hover:bg-red-50 transition-colors"
                                    title="Delete">
                                <i class="ph ph-trash text-lg"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- Empty State --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
            <div class="w-20 h-20 bg-rose-50 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <i class="ph ph-image text-3xl text-rose-400"></i>
            </div>
            <h3 class="text-xl font-serif font-bold text-gray-900 mb-2">No Hero Slides Yet</h3>
            <p class="text-gray-500 mb-6 max-w-sm mx-auto">Add your first hero slide to create a stunning carousel on your homepage.</p>
            <a href="{{ route('admin.hero-slides.create') }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-gray-900 text-white rounded-xl text-sm font-medium hover:bg-rose-600 transition-all">
                <i class="ph ph-plus-circle text-lg"></i>
                Add First Slide
            </a>
        </div>
    @endif
</div>
@endsection
