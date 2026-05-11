@extends('layouts.dashboard')

@section('page-title', 'Add Google Review')
@section('page-subtitle', 'Manually add a review from your Google Business profile')

@section('content')
<div class="max-w-3xl mx-auto">
    {{-- Back Link --}}
    <div class="mb-6">
        <a href="{{ route('admin.reviews.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 transition-colors">
            <i class="ph ph-arrow-left"></i>
            Back to Reviews
        </a>
    </div>

    {{-- Info Banner --}}
    <div class="mb-8 p-5 bg-blue-50 border border-blue-200 rounded-2xl flex items-start gap-4">
        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
            <i class="ph ph-google-logo text-xl text-blue-600"></i>
        </div>
        <div>
            <h3 class="font-semibold text-blue-900 mb-1">Copy from Google Business</h3>
            <p class="text-sm text-blue-700 leading-relaxed">
                Go to your <a href="https://business.google.com" target="_blank" class="underline font-medium hover:text-blue-900">Google Business Profile</a>, 
                find a review, and copy the details below. The review will appear on your homepage with a Google badge.
            </p>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-400 to-rose-600 flex items-center justify-center">
                    <i class="ph ph-star text-white text-xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Review Details</h2>
                    <p class="text-sm text-gray-500">Fill in the details exactly as they appear on Google</p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.reviews.store') }}" method="POST" class="p-8 space-y-6">
            @csrf

            {{-- Source (hidden) --}}
            <input type="hidden" name="source" value="google">

            {{-- Reviewer Name --}}
            <div>
                <label for="reviewer_name" class="block text-sm font-semibold text-gray-700 mb-2">
                    Reviewer Name <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="reviewer_name" id="reviewer_name" required
                       value="{{ old('reviewer_name') }}"
                       placeholder="e.g. Jane Uwimana"
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all">
                @error('reviewer_name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Rating --}}
            <div x-data="{ rating: {{ old('rating', 5) }} }">
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
                          placeholder="Paste the review text from Google..."
                          class="w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all resize-none">{{ old('comment') }}</textarea>
                @error('comment')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Review Date --}}
            <div>
                <label for="review_date" class="block text-sm font-semibold text-gray-700 mb-2">
                    Date of Review <span class="text-gray-400 font-normal">(optional)</span>
                </label>
                <input type="date" name="review_date" id="review_date"
                       value="{{ old('review_date', now()->toDateString()) }}"
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all">
                <p class="mt-1 text-xs text-gray-400">When was this review originally posted on Google?</p>
                @error('review_date')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Avatar URL (optional) --}}
            <div>
                <label for="avatar_url" class="block text-sm font-semibold text-gray-700 mb-2">
                    Profile Photo URL <span class="text-gray-400 font-normal">(optional)</span>
                </label>
                <input type="url" name="avatar_url" id="avatar_url"
                       value="{{ old('avatar_url') }}"
                       placeholder="https://..."
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all">
                <p class="mt-1 text-xs text-gray-400">Right-click the reviewer's photo on Google and copy the image URL</p>
                @error('avatar_url')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Preview --}}
            <div x-data x-show="$refs.reviewer_name_input ? true : true" class="border-t border-gray-100 pt-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <i class="ph ph-eye text-gray-400"></i>
                    Preview (how it will look on homepage)
                </h3>
                <div class="bg-gray-900 rounded-xl p-6">
                    <div class="bg-white rounded-xl p-6 max-w-sm">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-500 font-bold text-sm">
                                    G
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 text-sm">Google Reviewer</h4>
                                    <p class="text-xs text-gray-500">Just now</p>
                                </div>
                            </div>
                            <i class="ph-fill ph-google-logo text-gray-400 text-lg"></i>
                        </div>
                        <div class="flex text-yellow-400 mb-3 text-sm">
                            @for($i = 0; $i < 5; $i++)
                                <i class="ph-fill ph-star"></i>
                            @endfor
                        </div>
                        <p class="text-gray-600 text-sm leading-relaxed">"Amazing nail salon! Best in Kigali..."</p>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.reviews.index') }}" 
                   class="px-6 py-3 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-8 py-3 bg-gray-900 text-white font-medium rounded-xl hover:bg-rose-600 transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5 inline-flex items-center gap-2">
                    <i class="ph ph-google-logo"></i>
                    Add Google Review
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
