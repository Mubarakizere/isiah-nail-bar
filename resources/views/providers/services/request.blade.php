@extends('layouts.dashboard')

@section('title', 'Request a New Service')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-2 flex items-center justify-center gap-3">
            <i class="ph ph-plus-circle text-blue-600"></i>
            <span>Request New Service</span>
        </h2>
        <p class="text-gray-600">Submit a request to add a new service to your portfolio</p>
    </div>

    <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-200">
        <form method="POST" action="{{ route('provider.services.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- Service Name --}}
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="ph ph-sparkle text-blue-600 mr-2"></i>Service Name
                </label>
                <input type="text" 
                       name="name" 
                       required
                       class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all outline-none"
                       placeholder="e.g., Luxury Gel Manicure">
            </div>

            {{-- Description --}}
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="ph ph-text-align-left text-blue-600 mr-2"></i>Description
                </label>
                <textarea name="description" 
                          required 
                          rows="4"
                          class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all outline-none resize-vertical"
                          placeholder="Describe your service..."></textarea>
            </div>

            {{-- Duration --}}
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="ph ph-clock text-blue-600 mr-2"></i>Duration (minutes)
                </label>
                <input type="number" 
                       name="duration_minutes" 
                       required
                       class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all outline-none"
                       placeholder="e.g., 60">
            </div>

            {{-- Price --}}
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="ph ph-currency-circle-dollar text-blue-600 mr-2"></i>Price (RWF)
                </label>
                <input type="number" 
                       name="price" 
                       required
                       class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all outline-none"
                       placeholder="e.g., 15000">
            </div>

            {{-- Category --}}
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="ph ph-folders text-blue-600 mr-2"></i>Category
                </label>
                <select name="category" 
                        required
                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all outline-none">
                    <option value="">Select category</option>
                    <option value="Manicure">Manicure</option>
                    <option value="Pedicure">Pedicure</option>
                    <option value="Acrylic Nails">Acrylic Nails</option>
                    <option value="Gel Nails">Gel Nails</option>
                    <option value="Nail Art">Nail Art</option>
                    <option value="French Tips">French Tips</option>
                </select>
            </div>

            {{-- Image --}}
            <div class="mb-8">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="ph ph-image text-blue-600 mr-2"></i>Service Image
                </label>
                <input type="file" 
                       name="image"
                       class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all outline-none">
                <p class="mt-2 text-sm text-gray-500">Upload a high-quality image of your service</p>
            </div>

            {{-- Submit Button --}}
            <div class="flex gap-4">
                <a href="{{ route('provider.services.index') }}" 
                   class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition text-center">
                    Cancel
                </a>
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-indigo-700 shadow-lg hover:shadow-xl transition-all">
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
