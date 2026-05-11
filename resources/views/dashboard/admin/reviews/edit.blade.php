@extends('layouts.dashboard')

@section('page-title', 'Edit Google Review')
@section('page-subtitle', 'Update the details of this Google review')

@section('content')
<div class="max-w-3xl mx-auto">
    {{-- Back Link --}}
    <div class="mb-6">
        <a href="{{ route('admin.reviews.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 transition-colors">
            <i class="ph ph-arrow-left"></i>
            Back to Reviews
        </a>
    </div>

    {{-- Form Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                    <i class="ph ph-pencil-simple text-white text-xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Edit Review</h2>
                    <p class="text-sm text-gray-500">
                        @if($review->source === 'google')
                            <span class="inline-flex items-center gap-1 text-blue-600">
                                <i class="ph ph-google-logo"></i> Google Review
                            </span>
                        @else
                            Internal Review
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.reviews.update', $review->id) }}" method="POST" class="p-8 space-y-6">
            @csrf
            @method('PUT')

            {{-- Source --}}
            <div>
                <label for="source" class="block text-sm font-semibold text-gray-700 mb-2">Source</label>
                <select name="source" id="source" 
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all">
                    <option value="google" {{ $review->source === 'google' ? 'selected' : '' }}>Google</option>
                    <option value="internal" {{ $review->source === 'internal' ? 'selected' : '' }}>Internal</option>
                </select>
            </div>

            {{-- Reviewer Name --}}
            <div>
                <label for="reviewer_name" class="block text-sm font-semibold text-gray-700 mb-2">
                    Reviewer Name <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="reviewer_name" id="reviewer_name" required
                       value="{{ old('reviewer_name', $review->reviewer_name ?? ($review->customer->user->name ?? '')) }}"
                       placeholder="e.g. Jane Uwimana"
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all">
                @error('reviewer_name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Rating --}}
            <div x-data="{ rating: {{ old('rating', $review->rating) }} }">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Rating <span class="text-rose-500">*</span>
                </label>
                <input type="hidden" name="rating" :value="rating">
                <div class="flex items-center gap-1">
                    <template x-for="star in 5" :key="star">
                        <button type="button" @click="rating = star" 
                                class="text-3xl transition-transform hover:scale-110 focus:outline-none"
                                :class="star <= rating ? 'text-yellow-400' : 'text-gray-300'">
                            <i class="ph-fill ph-star"></i>
                        </button>
                    </template>
                    <span class="ml-3 text-sm font-medium text-gray-500" x-text="rating + '/5'"></span>
                </div>
                @error('rating')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Comment --}}
            <div>
                <label for="comment" class="block text-sm font-semibold text-gray-700 mb-2">
                    Review Text <span class="text-rose-500">*</span>
                </label>
                <textarea name="comment" id="comment" rows="4" required
                          placeholder="Paste the review text..."
                          class="w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all resize-none">{{ old('comment', $review->comment) }}</textarea>
                @error('comment')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Avatar URL --}}
            <div>
                <label for="avatar_url" class="block text-sm font-semibold text-gray-700 mb-2">
                    Profile Photo URL <span class="text-gray-400 font-normal">(optional)</span>
                </label>
                <input type="url" name="avatar_url" id="avatar_url"
                       value="{{ old('avatar_url', $review->avatar_url) }}"
                       placeholder="https://..."
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all">
                @error('avatar_url')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.reviews.index') }}" 
                   class="px-6 py-3 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-8 py-3 bg-gray-900 text-white font-medium rounded-xl hover:bg-rose-600 transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5 inline-flex items-center gap-2">
                    <i class="ph ph-floppy-disk"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
