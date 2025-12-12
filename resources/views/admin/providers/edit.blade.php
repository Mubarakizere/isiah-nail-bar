@extends('layouts.dashboard')

@section('title', 'Edit Provider')

@section('content')
<div class="p-6">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Edit Provider</h1>
        <p class="text-gray-600">Update provider profile information</p>
    </div>

    {{-- Error Messages --}}
    @if ($errors->any())
        <div class="mb-6 bg-red-50 border-2 border-red-200 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <i class="ph ph-warning-circle text-2xl text-red-600 flex-shrink-0"></i>
                <div class="flex-1">
                    <h3 class="font-bold text-red-900 mb-2">Please fix the following errors:</h3>
                    <ul class="text-sm text-red-800 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Form Card --}}
    <div class="max-w-4xl">
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6">
            <form action="{{ route('admin.providers.update', $provider->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="space-y-6 mb-6">
                    {{-- Name --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Provider Name</label>
                        <input type="text" 
                               name="name" 
                               value="{{ old('name', $provider->name) }}" 
                               required
                               class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                    </div>

                    {{-- Phone & Email --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Phone</label>
                            <input type="text" 
                                   name="phone" 
                                   value="{{ old('phone', $provider->phone) }}"
                                   placeholder="+250 XXX XXX XXX"
                                   class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                            <input type="email" 
                                   name="email" 
                                   value="{{ old('email', $provider->email) }}"
                                   placeholder="provider@example.com"
                                   class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                        </div>
                    </div>

                    {{-- Bio --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Bio</label>
                        <textarea name="bio" 
                                  rows="4"
                                  placeholder="Tell us about this provider..."
                                  class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none resize-vertical">{{ old('bio', $provider->bio) }}</textarea>
                    </div>

                    {{-- Photo --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Provider Photo</label>
                        @if($provider->photo)
                            <img src="{{ asset('storage/'.$provider->photo) }}" 
                                 alt="Provider Photo" 
                                 class="mb-3 rounded-lg border-2 border-gray-200 max-h-40 object-cover">
                        @else
                            <p class="text-gray-500 text-sm mb-3">No photo uploaded</p>
                        @endif
                        <input type="file" 
                               name="photo"
                               accept="image/*"
                               class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                        <p class="text-xs text-gray-500 mt-1">Maximum file size: 30MB</p>
                    </div>

                    {{-- Assign Services --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Assign Services</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach ($services as $service)
                                <label class="flex items-center gap-3 p-3 rounded-lg border-2 border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition cursor-pointer">
                                    <input class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500" 
                                           type="checkbox" 
                                           name="services[]" 
                                           value="{{ $service->id }}"
                                           id="service-{{ $service->id }}"
                                           {{ in_array($service->id, old('services', $provider->services->pluck('id')->toArray())) ? 'checked' : '' }}>
                                    <span class="text-sm font-medium text-gray-700">{{ $service->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Submit Buttons --}}
                <div class="flex gap-3">
                    <a href="{{ route('admin.providers.index') }}" 
                       class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition text-center">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-gray-900 text-white font-semibold rounded-lg hover:bg-gray-800 transition">
                        <i class="ph ph-check-circle mr-2"></i>Update Provider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
