@extends('layouts.dashboard')

@section('title', 'Edit Service')

@section('content')
<div class="p-6">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Edit Service</h1>
        <p class="text-gray-600">Update service information</p>
    </div>

    {{-- Form Card --}}
    <div class="max-w-4xl">
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6">
            <form action="{{ route('admin.services.update', $service->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Service Name</label>
                        <input type="text" name="name" required
                               value="{{ old('name', $service->name) }}"
                               class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                        <textarea name="description" required rows="4"
                                  class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none resize-vertical">{{ old('description', $service->description) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Price (RWF)</label>
                        <input type="number" name="price" required
                               value="{{ old('price', $service->price) }}"
                               class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Duration (minutes)</label>
                        <input type="number" name="duration_minutes" required
                               value="{{ old('duration_minutes', $service->duration_minutes) }}"
                               class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                        <select name="category_id" required
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                            <option value="">Select a category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id', $service->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Service Image</label>
                        <input type="file" name="image"
                               class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                        @if ($service->image)
                            <img src="{{ asset('storage/' . $service->image) }}" 
                                 class="mt-3 rounded-lg border-2 border-gray-200 max-h-32 object-cover">
                        @endif
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tags (comma separated)</label>
                        <input type="text" name="tags"
                               value="{{ old('tags', $service->tags->pluck('tag')->implode(', ')) }}"
                               placeholder="e.g., manicure, pedicure, spa"
                               class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                    </div>
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('admin.services.index') }}" 
                       class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition text-center">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-gray-900 text-white font-semibold rounded-lg hover:bg-gray-800 transition">
                        <i class="ph ph-check-circle mr-2"></i>Update Service
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
