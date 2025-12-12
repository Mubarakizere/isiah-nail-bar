@extends('layouts.dashboard')

@section('title', 'Add New Tag')

@section('content')
<div class="p-6">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Add New Tag</h1>
        <p class="text-gray-600">Create a new service tag</p>
    </div>

    {{-- Form Card --}}
    <div class="max-w-2xl">
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6">
            <form action="{{ route('admin.tags.store') }}" method="POST">
                @csrf

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Tag Name
                    </label>
                    <input type="text" 
                           name="name" 
                           value="{{ old('name') }}" 
                           required
                           placeholder="Enter tag name"
                           class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none @error('name') border-red-500 @enderror">
                    @error('name') 
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('admin.tags.index') }}" 
                       class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition text-center">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-gray-900 text-white font-semibold rounded-lg hover:bg-gray-800 transition">
                        <i class="ph ph-check-circle mr-2"></i>Save Tag
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
