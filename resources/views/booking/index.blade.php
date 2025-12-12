@extends('layouts.public')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-b from-blue-50 to-white py-12 px-4">
    <div class="text-center">
        <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="ph ph-calendar-check text-4xl text-blue-600"></i>
        </div>
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Booking Page</h1>
        <p class="text-lg text-gray-600 mb-8">Welcome to Isaiah Nail Bar booking system</p>
        <a href="{{ route('booking.step1') }}" class="inline-block px-8 py-4 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition shadow-lg">
            Start Booking
        </a>
    </div>
</div>
@endsection
