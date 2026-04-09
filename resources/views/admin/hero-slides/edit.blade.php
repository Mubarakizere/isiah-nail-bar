@extends('layouts.dashboard')

@section('title', 'Edit Hero Slide')
@section('page-title', 'Edit Hero Slide')
@section('page-subtitle', 'Update this carousel slide')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Form Header --}}
        <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.hero-slides.index') }}" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-white transition-colors">
                    <i class="ph ph-arrow-left text-xl"></i>
                </a>
                <div>
                    <h3 class="font-serif text-lg font-bold text-gray-900">Edit Slide</h3>
                    <p class="text-sm text-gray-500">Update the carousel slide details</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.hero-slides.update', $heroSlide) }}" enctype="multipart/form-data" class="p-8 space-y-6">
            @csrf
            @method('PUT')

            {{-- Image Upload --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Slide Image</label>
                <div class="relative" x-data="{ preview: '{{ asset('storage/' . $heroSlide->image) }}' }">
                    <div class="border-2 border-dashed border-gray-200 rounded-2xl hover:border-rose-300 transition-colors cursor-pointer overflow-hidden"
                         :class="{ 'border-rose-400': preview }">
                        <template x-if="preview">
                            <div class="relative aspect-[16/9]">
                                <img :src="preview" class="w-full h-full object-cover">
                                <button type="button" @click="preview = null; $refs.imageInput.value = ''"
                                        class="absolute top-3 right-3 p-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors shadow-lg">
                                    <i class="ph ph-x text-lg"></i>
                                </button>
                            </div>
                        </template>
                        <template x-if="!preview">
                            <label class="flex flex-col items-center justify-center p-12 cursor-pointer">
                                <div class="w-16 h-16 bg-rose-50 rounded-2xl flex items-center justify-center mb-4">
                                    <i class="ph ph-cloud-arrow-up text-3xl text-rose-400"></i>
                                </div>
                                <p class="text-gray-900 font-medium mb-1">Click to upload new image</p>
                                <p class="text-gray-400 text-sm">JPG, PNG or WebP • Max 5MB</p>
                            </label>
                        </template>
                    </div>
                    <input type="file" name="image" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" x-ref="imageInput"
                           @change="const file = $event.target.files[0]; if(file) { const reader = new FileReader(); reader.onload = e => preview = e.target.result; reader.readAsDataURL(file); }">
                </div>
                @error('image') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Subtitle --}}
            <div>
                <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-2">Tagline / Subtitle</label>
                <input type="text" name="subtitle" id="subtitle" value="{{ old('subtitle', $heroSlide->subtitle) }}"
                       placeholder="e.g. Kigali's Premier Nail Salon"
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 text-sm">
                @error('subtitle') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Title --}}
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" value="{{ old('title', $heroSlide->title) }}"
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 text-sm">
                @error('title') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" id="description" rows="3"
                          class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 text-sm resize-none">{{ old('description', $heroSlide->description) }}</textarea>
                @error('description') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Buttons --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="button_text" class="block text-sm font-medium text-gray-700 mb-2">Primary Button Text</label>
                    <input type="text" name="button_text" id="button_text" value="{{ old('button_text', $heroSlide->button_text) }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 text-sm">
                </div>
                <div>
                    <label for="button_url" class="block text-sm font-medium text-gray-700 mb-2">Primary Button URL</label>
                    <input type="text" name="button_url" id="button_url" value="{{ old('button_url', $heroSlide->button_url) }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="secondary_button_text" class="block text-sm font-medium text-gray-700 mb-2">Secondary Button Text</label>
                    <input type="text" name="secondary_button_text" id="secondary_button_text" value="{{ old('secondary_button_text', $heroSlide->secondary_button_text) }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 text-sm">
                </div>
                <div>
                    <label for="secondary_button_url" class="block text-sm font-medium text-gray-700 mb-2">Secondary Button URL</label>
                    <input type="text" name="secondary_button_url" id="secondary_button_url" value="{{ old('secondary_button_url', $heroSlide->secondary_button_url) }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 text-sm">
                </div>
            </div>

            {{-- Sort & Status --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $heroSlide->sort_order) }}" min="0"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 text-sm">
                </div>
                <div class="flex items-end pb-1">
                    <label class="relative inline-flex items-center cursor-pointer gap-3">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $heroSlide->is_active) ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-rose-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
                        <span class="text-sm font-medium text-gray-700">Active</span>
                    </label>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.hero-slides.index') }}" class="px-6 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-8 py-2.5 bg-gray-900 text-white rounded-xl text-sm font-medium hover:bg-rose-600 transition-all shadow-sm">
                    <i class="ph ph-floppy-disk mr-1"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
