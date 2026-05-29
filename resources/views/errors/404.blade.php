@extends('layouts.public')

@section('title', 'Page Not Found')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center py-24">
    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="mb-8 flex justify-center">
            <div class="w-24 h-24 bg-rose-50 rounded-full flex items-center justify-center">
                <i class="ph ph-magnifying-glass text-4xl text-rose-500"></i>
            </div>
        </div>
        <h1 class="text-7xl font-bold text-gray-900 font-serif mb-4 tracking-tight">404</h1>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Page Not Found</h2>
        <p class="text-gray-500 mb-10 text-lg">
            We couldn't find the page you were looking for. It might have been moved, deleted, or perhaps never existed.
        </p>
        <a href="{{ url('/') }}" class="inline-flex items-center px-8 py-3.5 bg-gray-900 text-white text-base font-medium rounded-full hover:bg-rose-600 transition-all duration-300 shadow-md hover:shadow-lg hover:-translate-y-0.5">
            <i class="ph ph-arrow-left mr-2"></i>
            Back to Home
        </a>
    </div>
</div>
@endsection
