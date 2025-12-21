@extends('layouts.dashboard')

@section('page-title', 'Customer Reviews')
@section('page-subtitle', 'Manage all reviews from customers and external sources')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl flex items-center gap-3">
            <i class="ph ph-check-circle text-2xl"></i>
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-center gap-3">
            <i class="ph ph-warning-circle text-2xl"></i>
            <p class="font-medium">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Reviews Table Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($reviews->count())
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Service</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Rating</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Comment</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Source</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($reviews as $review)
                            <tr class="hover:bg-gray-50 transition-colors">
                                {{-- Customer Name --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($review->customer && $review->customer->user)
                                            {{-- Internal Review --}}
                                            @if($review->customer->user->photo)
                                                <img src="{{ asset('storage/' . $review->customer->user->photo) }}" 
                                                     class="w-10 h-10 rounded-full object-cover">
                                            @else
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-rose-400 to-rose-600 flex items-center justify-center text-white font-bold">
                                                    {{ strtoupper(substr($review->customer->user->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $review->customer->user->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $review->customer->user->email }}</p>
                                            </div>
                                        @else
                                            {{-- External Review --}}
                                            @if($review->avatar_url)
                                                <img src="{{ $review->avatar_url }}" 
                                                     class="w-10 h-10 rounded-full object-cover">
                                            @else
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold">
                                                    {{ strtoupper(substr($review->reviewer_name ?? 'G', 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $review->reviewer_name ?? 'Anonymous' }}</p>
                                                <p class="text-xs text-gray-500">External Review</p>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- Service --}}
                                <td class="px-6 py-4">
                                    @if($review->service)
                                        <span class="inline-flex items-center gap-2 px-3 py-1 bg-rose-50 text-rose-700 rounded-full text-sm font-medium">
                                            <i class="ph ph-scissors"></i>
                                            {{ $review->service->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-sm">General Review</span>
                                    @endif
                                </td>

                                {{-- Rating --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1">
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <i class="ph-fill ph-star text-yellow-400 text-lg"></i>
                                            @else
                                                <i class="ph ph-star text-gray-300 text-lg"></i>
                                            @endif
                                        @endfor
                                        <span class="ml-2 text-sm font-medium text-gray-600">{{ $review->rating }}/5</span>
                                    </div>
                                </td>

                                {{-- Comment --}}
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-700 max-w-md truncate">
                                        {{ $review->comment ?? '—' }}
                                    </p>
                                </td>

                                {{-- Source --}}
                                <td class="px-6 py-4">
                                    @if($review->source === 'google')
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 rounded-md text-xs font-medium">
                                            <i class="ph ph-google-logo"></i>
                                            Google
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-50 text-green-700 rounded-md text-xs font-medium">
                                            <i class="ph ph-check-circle"></i>
                                            Internal
                                        </span>
                                    @endif
                                </td>

                                {{-- Date --}}
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-600">{{ $review->created_at->format('M d, Y') }}</p>
                                    <p class="text-xs text-gray-400">{{ $review->created_at->diffForHumans() }}</p>
                                </td>

                                {{-- Actions --}}
                                <td class="px-6 py-4">
                                    <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" 
                                          onsubmit="return confirm('Are you sure you want to delete this review?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg text-sm font-medium transition-colors">
                                            <i class="ph ph-trash"></i>
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $reviews->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="py-16 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                    <i class="ph ph-star text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Reviews Yet</h3>
                <p class="text-gray-500 max-w-sm mx-auto">
                    Customer reviews will appear here once they start submitting feedback.
                </p>
            </div>
        @endif
    </div>

    {{-- Stats Summary --}}
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-rose-100 rounded-lg flex items-center justify-center">
                    <i class="ph ph-star text-2xl text-rose-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Reviews</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $reviews->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="ph ph-trend-up text-2xl text-yellow-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Average Rating</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ $reviews->count() > 0 ? number_format($reviews->avg('rating'), 1) : '0.0' }}/5
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="ph ph-google-logo text-2xl text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">External Reviews</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ $reviews->where('source', 'google')->count() }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
