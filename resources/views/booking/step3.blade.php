@extends('layouts.public')

@section('title', 'Select Date & Time')

@section('content')


{{-- Hero Header --}}
<div class="bg-gray-900 py-12 relative overflow-hidden">
    <div class="absolute inset-0 opacity-20">
        <img src="{{ asset('storage/banner.jpg') }}" alt="" class="w-full h-full object-cover">
    </div>
    <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-transparent"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <span class="text-rose-400 font-medium tracking-widest text-xs uppercase mb-2 block">Step 3 of 4</span>
        <h1 class="text-3xl md:text-4xl font-serif text-white mb-2">Schedule Your Visit</h1>
        <p class="text-gray-400 font-light text-lg">Find the perfect time for your luxury experience.</p>
    </div>
</div>

<div class="bg-white min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('booking.step3.submit') }}" id="timeForm">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                
                {{-- Main Content --}}
                <div class="lg:col-span-8">
                    
                    {{-- Date Scroller --}}
                    <div class="mb-10">
                        <h3 class="text-lg font-serif text-gray-900 mb-4 flex items-center gap-2">
                             <span class="w-8 h-8 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-bold">1</span>
                             Select Date
                        </h3>
                        <div class="flex gap-3 overflow-x-auto pb-4 scrollbar-hide snap-x">
                            @for ($i = 0; $i < 14; $i++)
                                @php
                                    $day = \Carbon\Carbon::today()->addDays($i);
                                    $isSelected = $selectedDate === $day->toDateString();
                                @endphp
                                <a href="{{ route('booking.step3', ['booking_date' => $day->toDateString()]) }}"
                                   class="snap-start flex-shrink-0 w-24 p-3 rounded-2xl border text-center transition-all duration-300 group
                                   {{ $isSelected 
                                      ? 'bg-gray-900 border-gray-900 text-white shadow-lg scale-105' 
                                      : 'bg-white border-gray-200 text-gray-500 hover:border-gray-900 hover:text-gray-900' }}">
                                    <span class="block text-xs font-medium uppercase tracking-wider opacity-60 mb-1">{{ $day->format('M') }}</span>
                                    <span class="block text-2xl font-serif font-bold mb-1 {{ $isSelected ? 'text-white' : 'text-gray-900' }}">{{ $day->format('j') }}</span>
                                    <span class="block text-xs {{ $isSelected ? 'text-rose-400' : '' }}">{{ $day->format('D') }}</span>
                                </a>
                            @endfor
                        </div>
                        <input type="hidden" name="booking_date" value="{{ $selectedDate }}">
                    </div>

                    {{-- Time Slots --}}
                    <div>
                        <h3 class="text-lg font-serif text-gray-900 mb-4 flex items-center gap-2">
                             <span class="w-8 h-8 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-bold">2</span>
                             Select Time
                        </h3>
                        
                        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                             <div class="flex items-center justify-between mb-6">
                                <span class="text-sm text-gray-500">Available slots for <span class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($selectedDate)->format('l, F jS') }}</span></span>
                                <div class="flex gap-4 text-xs">
                                    <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-white border border-gray-300"></span> Available</div>
                                    <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-gray-900"></span> Selected</div>
                                    <div class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-gray-200"></span> Taken</div>
                                </div>
                            </div>

                            @if (empty($slotsWithStatus))
                                <div class="text-center py-12">
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-200 mb-4">
                                        <i class="ph ph-calendar-x text-2xl text-gray-400"></i>
                                    </div>
                                    <h4 class="text-gray-900 font-medium mb-1">No availability</h4>
                                    <p class="text-gray-500 text-sm">Please select another date.</p>
                                </div>
                            @else
                                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                                    @foreach ($slotsWithStatus as $slot)
                                        <div class="relative">
                                            <input type="radio"
                                                   name="booking_time"
                                                   id="time_{{ $slot['time'] }}"
                                                   value="{{ $slot['time'] }}"
                                                   class="peer hidden"
                                                   {{ old('booking_time') == $slot['time'] ? 'checked' : '' }}
                                                   {{ $slot['status'] !== 'available' ? 'disabled' : '' }}>
                                            
                                            <label for="time_{{ $slot['time'] }}"
                                                   class="flex flex-col items-center justify-center py-3 rounded-xl border text-sm font-medium transition-all duration-200
                                                   {{ $slot['status'] === 'available' 
                                                      ? 'bg-white border-gray-200 text-gray-700 hover:border-gray-900 hover:shadow-md cursor-pointer peer-checked:bg-gray-900 peer-checked:border-gray-900 peer-checked:text-white peer-checked:shadow-lg' 
                                                      : 'bg-gray-100 border-transparent text-gray-400 cursor-not-allowed decoration-slice line-through' }}">
                                                {{ \Carbon\Carbon::createFromFormat('H:i', $slot['time'])->format('H:i') }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('booking_time')
                                    <p class="text-red-500 text-sm mt-3 flex items-center gap-1">
                                        <i class="ph ph-warning-circle"></i> Please select a time slot.
                                    </p>
                                @enderror
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-8 mt-8 border-t border-gray-100">
                        <a href="{{ route('booking.step2') }}" class="text-gray-500 hover:text-gray-900 font-medium flex items-center gap-2 transition-colors">
                            <i class="ph ph-arrow-left"></i> Back to Artist
                        </a>
                    </div>
                </div>

                {{-- Sidebar (Desktop) --}}
                <div class="hidden lg:block lg:col-span-4 pl-4">
                    <div class="sticky top-24">
                        <div id="summaryCard" class="bg-gray-900 text-white rounded-2xl p-6 shadow-xl overflow-hidden relative">
                            <div class="absolute top-0 right-0 p-4 opacity-10">
                                <i class="ph ph-receipt text-9xl text-white"></i>
                            </div>
                            
                            <h3 class="text-lg font-serif mb-6 relative z-10">Booking Summary</h3>
                            
                            {{-- Info --}}
                            <div class="space-y-4 mb-6 pb-6 border-b border-gray-700 relative z-10">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-400">Customer</span>
                                    <span class="text-white">{{ auth()->user()->name ?? 'Guest' }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-400">Artist</span>
                                    <span class="text-rose-400">{{ $provider->name }}</span>
                                </div>
                                 <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-400">Date</span>
                                    <span class="text-white">{{ \Carbon\Carbon::parse($selectedDate)->format('M j, Y') }}</span>
                                </div>
                            </div>

                            {{-- Services --}}
                            <div class="space-y-2 mb-6 relative z-10 max-h-[25vh] overflow-y-auto custom-scrollbar pr-2">
                                @foreach($services as $service)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-300">{{ $service->name }}</span>
                                        <span class="text-gray-500">{{ number_format($service->price) }}</span>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="border-t border-gray-700 pt-4 mb-6 relative z-10">
                                <div class="flex justify-between items-end">
                                    <span class="text-gray-400 text-sm">Total</span>
                                    <span class="text-2xl font-serif text-white">RWF {{ number_format($services->sum('price')) }}</span>
                                </div>
                            </div>

                            <button type="submit" form="timeForm" class="w-full py-4 bg-white text-gray-900 font-bold rounded-xl hover:bg-rose-50 transition-all flex items-center justify-center gap-2 group relative z-10">
                                <span>Complete Booking</span>
                                <i class="ph ph-check-circle group-hover:text-green-600 transition-colors"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Mobile FAB --}}
<div class="lg:hidden fixed bottom-6 right-6 z-40">
    <button type="submit" form="timeForm" class="w-16 h-16 bg-gray-900 text-white rounded-full shadow-2xl flex items-center justify-center hover:bg-rose-600 transition-colors">
        <i class="ph ph-arrow-right text-2xl"></i>
    </button>
</div>

@endsection

@push('styles')
<style>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
@endpush
