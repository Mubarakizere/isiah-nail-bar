@extends('layouts.public')

@section('title', $service->name)

@section('content')
<div class="bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-start">
            
            {{-- Image --}}
            <div>
                @if($service->image)
                    <img src="{{ asset('storage/' . $service->image) }}" 
                         alt="{{ $service->name }}"
                         class="w-full rounded-2xl shadow-xl hover:scale-105 transition-transform duration-300">
                @else
                    <div class="w-full h-96 bg-gray-200 rounded-2xl flex items-center justify-center">
                        <i class="ph ph-image text-6xl text-gray-400"></i>
                    </div>
                @endif
            </div>

            {{-- Details --}}
            <div>
                <h2 class="text-4xl font-bold text-gray-900 mb-4">{{ $service->name }}</h2>
                
                <p class="text-lg text-gray-600 mb-6">{{ $service->description }}</p>

                @if($service->category)
                    <span class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 rounded-full text-sm font-semibold mb-6">
                        <i class="ph ph-tag"></i>
                        {{ $service->category->name }}
                    </span>
                @endif

                <div class="bg-white rounded-xl p-6 shadow-md border border-gray-200 mb-6">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-clock text-2xl text-blue-600"></i>
                            <div>
                                <p class="text-sm text-gray-600">Duration</p>
                                <p class="font-bold text-gray-900">{{ $service->duration_minutes }} minutes</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            <i class="ph ph-currency-circle-dollar text-2xl text-blue-600"></i>
                            <div>
                                <p class="text-sm text-gray-600">Price</p>
                                <p class="text-2xl font-bold text-blue-600">RWF {{ number_format($service->price) }}</p>
                            </div>
                        </div>

                        @if($service->provider)
                            <div class="flex items-center gap-3">
                                <i class="ph ph-user text-2xl text-blue-600"></i>
                                <div>
                                    <p class="text-sm text-gray-600">Provider</p>
                                    <p class="font-bold text-gray-900">{{ $service->provider->name }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <a href="{{ route('booking.step1', ['service_id' => $service->id]) }}" 
                   class="block w-full px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-center font-bold rounded-xl hover:from-blue-700 hover:to-indigo-700 shadow-lg hover:shadow-xl transition-all">
                    <i class="ph ph-calendar-check mr-2"></i>Book This Service
                </a>
            </div>
        </div>

        {{-- Reviews --}}
        @if($service->reviews->count())
            <div class="mt-16">
                <h4 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <i class="ph ph-chat-circle-text text-blue-600"></i>
                    Customer Reviews
                </h4>

                <div class="space-y-4">
                    @foreach($service->reviews as $review)
                        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
                            <div class="flex justify-between items-center mb-3">
                                <strong class="text-gray-900">{{ $review->user->name ?? 'Anonymous' }}</strong>
                                <small class="text-gray-500">{{ $review->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="text-gray-700">{{ $review->comment }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Leave Review --}}
        @auth
            @if(auth()->user()->hasBookedService($service->id))
                <div class="mt-12 bg-white rounded-xl p-8 shadow-md border border-gray-200">
                    <h5 class="text-xl font-bold text-gray-900 mb-6">Leave a Review</h5>

                    <form method="POST" action="{{ route('reviews.store') }}">
                        @csrf
                        <input type="hidden" name="service_id" value="{{ $service->id }}">

                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Rating (1 to 5)</label>
                            <select name="rating" required class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                                <option value="">Select rating</option>
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}">{{ $i }} ★</option>
                                @endfor
                            </select>
                            @error('rating') <small class="text-red-600">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Comment</label>
                            <textarea name="comment" required rows="4" class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none resize-vertical"></textarea>
                            @error('comment') <small class="text-red-600">{{ $message }}</small> @enderror
                        </div>

                        <button type="submit" class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                            <i class="ph ph-paper-plane-tilt mr-2"></i>Submit Review
                        </button>
                    </form>
                </div>
            @endif
        @endauth
    </div>
</div>

@endsection
