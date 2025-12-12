@extends('layouts.dashboard')

@section('title', 'Pending Services')

@section('content')
<div class="p-6">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Pending Services</h1>
        <p class="text-gray-600">Review and approve/reject service submissions from providers</p>
    </div>

    @if ($pendingServices->count())
        {{-- Services Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            @foreach ($pendingServices as $service)
                <div class="bg-white rounded-2xl shadow-md border-2 border-yellow-200 overflow-hidden hover:shadow-xl transition">
                    {{-- Card Header --}}
                    <div class="p-6 pb-4">
                        <div class="flex items-start justify-between mb-3">
                            <h3 class="text-lg font-bold text-gray-900 flex-1">{{ $service->name }}</h3>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 flex-shrink-0 ml-2">
                                <i class="ph ph-clock mr-1"></i>Pending
                            </span>
                        </div>

                        {{-- Service Details --}}
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <i class="ph ph-folder text-gray-400"></i>
                                <span>{{ $service->category->name ?? '—' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <i class="ph ph-timer text-gray-400"></i>
                                <span>{{ $service->duration_minutes }} minutes</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm font-semibold text-green-600">
                                <i class="ph ph-currency-circle-dollar text-green-500"></i>
                                <span>RWF {{ number_format($service->price) }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <i class="ph ph-user text-gray-400"></i>
                                <span>By <strong>{{ $service->provider->user->name ?? 'Unknown' }}</strong></span>
                            </div>
                        </div>

                        {{-- Description --}}
                        <p class="text-sm text-gray-600 leading-relaxed">
                            {{ \Illuminate\Support\Str::limit($service->description, 120) }}
                        </p>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="p-6 pt-0 flex gap-2">
                        <form action="{{ route('admin.services.approve', $service->id) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2.5 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                                <i class="ph ph-check-circle mr-1"></i>Approve
                            </button>
                        </form>
                        <form action="{{ route('admin.services.reject', $service->id) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2.5 bg-red-100 text-red-700 font-semibold rounded-lg hover:bg-red-200 transition">
                                <i class="ph ph-x-circle mr-1"></i>Reject
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($pendingServices->hasPages())
            <div class="flex justify-center">
                {{ $pendingServices->links() }}
            </div>
        @endif
    @else
        {{-- Empty State --}}
        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-12 text-center">
            <i class="ph ph-check-circle text-6xl text-blue-300 mb-4"></i>
            <p class="text-blue-900 font-semibold text-lg">All caught up!</p>
            <p class="text-blue-700 text-sm mt-2">No pending services to review at the moment</p>
        </div>
    @endif
</div>
@endsection
