@extends('layouts.public')

@section('title', 'Contact Us')

@section('content')

{{-- Contact Hero --}}
<div class="relative bg-gray-900 py-24 overflow-hidden">
    <div class="absolute inset-0 opacity-40">
        <img src="{{ asset('storage/banner.jpg') }}" alt="Contact Banner" class="w-full h-full object-cover">
    </div>
    <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/60 to-transparent"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center pt-12">
        <span class="text-rose-400 font-medium tracking-widest text-sm uppercase mb-3 block animate-fade-in-up">Get in Touch</span>
        <h1 class="text-4xl md:text-6xl font-serif text-white mb-6 animate-fade-in-up delay-100">Let's Connect</h1>
        <p class="text-xl text-gray-300 font-light max-w-2xl mx-auto animate-fade-in-up delay-200">
            Have a question or need to book a special session? We're here to help.
        </p>
    </div>
</div>

{{-- Contact Content --}}
<section class="py-20 bg-white min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
            
            {{-- Form Side --}}
            <div>
                <div class="bg-gray-50 rounded-2xl p-10 shadow-sm border border-gray-100">
                    <h2 class="text-2xl font-serif text-gray-900 mb-6">Send us a message</h2>
                    <form method="POST" action="{{ route('contact.submit') }}" class="space-y-6">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                                <input type="text" name="name" value="{{ old('name') }}" required 
                                       class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-all placeholder-gray-400"
                                       placeholder="Your name">
                                @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" required 
                                       class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-all placeholder-gray-400"
                                       placeholder="+250 ...">
                                @error('phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-gray-400 font-light">(Optional)</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" 
                                   class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-all placeholder-gray-400"
                                   placeholder="you@example.com">
                            @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                            <textarea name="message" rows="5" required 
                                      class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-all placeholder-gray-400 resize-none"
                                      placeholder="How can we help you today?">{{ old('message') }}</textarea>
                            @error('message') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="w-full px-8 py-4 bg-gray-900 text-white font-medium rounded-full hover:bg-rose-600 transition-all transform hover:-translate-y-0.5 shadow-lg">
                            Send Message
                        </button>
                    </form>
                </div>
            </div>

            {{-- Info Side --}}
            <div class="space-y-10">
                {{-- Quick Contact Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <a href="tel:0790395169" class="p-6 bg-white border border-gray-100 rounded-2xl hover:border-gray-900 transition-colors group">
                        <i class="ph ph-phone text-3xl text-gray-400 group-hover:text-rose-500 transition-colors mb-4"></i>
                        <h3 class="font-serif text-lg text-gray-900 mb-1">Call Us</h3>
                        <p class="text-gray-500 font-light">+250 788 421 063</p>
                    </a>
                    
                    <a href="https://wa.me/250790395169" target="_blank" class="p-6 bg-white border border-gray-100 rounded-2xl hover:border-gray-900 transition-colors group">
                        <i class="ph ph-whatsapp-logo text-3xl text-gray-400 group-hover:text-rose-500 transition-colors mb-4"></i>
                        <h3 class="font-serif text-lg text-gray-900 mb-1">WhatsApp</h3>
                        <p class="text-gray-500 font-light">Chat with us</p>
                    </a>

                    <div class="p-6 bg-white border border-gray-100 rounded-2xl group">
                        <i class="ph ph-clock text-3xl text-gray-400 group-hover:text-rose-500 transition-colors mb-4"></i>
                        <h3 class="font-serif text-lg text-gray-900 mb-1">Opening Hours</h3>
                        <p class="text-gray-500 font-light text-sm">Mon-Sat: 8am - 8pm</p>
                        <p class="text-gray-500 font-light text-sm">Sun: 10am - 6pm</p>
                    </div>

                    <a href="mailto:info@isaiahnailbar.com" class="p-6 bg-white border border-gray-100 rounded-2xl hover:border-gray-900 transition-colors group">
                        <i class="ph ph-envelope text-3xl text-gray-400 group-hover:text-rose-500 transition-colors mb-4"></i>
                        <h3 class="font-serif text-lg text-gray-900 mb-1">Email</h3>
                        <p class="text-gray-500 font-light text-sm break-all">info@isaiahnailbar.com</p>
                    </a>
                </div>

                {{-- Map --}}
                <div class="h-80 w-full bg-gray-100 rounded-2xl overflow-hidden relative transition-all duration-700">
                     <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15950.019185121285!2d30.108678!3d-1.954736!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x19dca6ee46244f67%3A0x6291244078657662!2sKisimenti!5e0!3m2!1sen!2srw!4v1683838383838!5m2!1sen!2srw" 
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection