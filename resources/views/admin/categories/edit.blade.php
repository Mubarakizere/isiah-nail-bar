@extends('layouts.dashboard')

@section('title', 'Edit Category')

@section('content')
<div class="p-6">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Edit Category</h1>
        <p class="text-gray-600">Update category information</p>
    </div>

    {{-- Form Card --}}
    <div class="max-w-2xl">
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6">
            <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Category Name
                    </label>
                    <input type="text" 
                           name="name" 
                           value="{{ $category->name }}" 
                           required
                           placeholder="Enter category name"
                           class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none @error('name') border-red-500 @enderror">
                    @error('name') 
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('admin.categories.index') }}" 
                       class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition text-center">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-gray-900 text-white font-semibold rounded-lg hover:bg-gray-800 transition">
                        <i class="ph ph-check-circle mr-2"></i>Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
