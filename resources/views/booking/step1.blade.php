@extends('layouts.public')

@section('title', 'Select Services')

@section('content')

{{-- Hero Header --}}
<div class="bg-gray-900 py-12 relative overflow-hidden">
    <div class="absolute inset-0 opacity-20">
        <img src="{{ asset('storage/banner.jpg') }}" alt="" class="w-full h-full object-cover">
    </div>
    <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-transparent"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <span class="text-rose-400 font-medium tracking-widest text-xs uppercase mb-2 block">Step 1 of 4</span>
        <h1 class="text-3xl md:text-4xl font-serif text-white mb-2">Curate Your Experience</h1>
        <p class="text-gray-400 font-light text-lg">Select the treatments you'd like to indulge in today.</p>
    </div>
</div>

<div class="bg-white min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- Main Content --}}
            <div class="lg:col-span-8 space-y-8">
                
                {{-- Toolbar --}}
                <div class="flex flex-col md:flex-row gap-4 sticky top-24 z-20 bg-white/95 backdrop-blur-sm p-4 rounded-xl border border-gray-100 shadow-sm transition-all" id="toolbar">
                    <div class="flex-1 relative">
                        <input type="text" id="serviceSearch" 
                               placeholder="Search treatments..." 
                               class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-gray-900 focus:name-1 transition-all">
                        <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <div class="w-full md:w-64">
                         <select id="categoryFilter" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-gray-900 cursor-pointer">
                            <option value="all">All Categories</option>
                            @foreach($categories as $category)
                                <option value="cat-{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <form method="POST" action="{{ route('booking.step1.submit') }}" id="servicesForm">
                    @csrf
                    
                    <div class="space-y-8">
                        @foreach($categories as $category)
                            @if($category->services->isNotEmpty())
                                <div id="cat-{{ $category->id }}" class="category-section scroll-mt-40">
                                    <div class="flex items-center gap-4 mb-4">
                                        <h3 class="text-xl font-serif text-gray-900">{{ $category->name }}</h3>
                                        <div class="h-px bg-gray-100 flex-1"></div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach($category->services as $service)
                                            <div class="service-card group relative cursor-pointer bg-white rounded-xl overflow-hidden border border-gray-200 hover:border-gray-900 hover:shadow-lg transition-all duration-300"
                                                 data-name="{{ strtolower($service->name) }}">
                                                
                                                <input type="checkbox" 
                                                        name="service_ids[]" 
                                                        value="{{ $service->id }}" 
                                                        class="hidden service-checkbox"
                                                        data-name="{{ $service->name }}"
                                                        data-price="{{ $service->price }}"
                                                        data-duration="{{ $service->duration }}"
                                                        {{ in_array($service->id, $selectedServices) ? 'checked' : '' }}>

                                                <div class="flex h-full">
                                                    {{-- Image --}}
                                                    <div class="w-1/3 relative bg-gray-100">
                                                        <img src="{{ $service->image ? asset('storage/' . $service->image) : asset('images/default-service.jpg') }}" 
                                                                alt="{{ $service->name }}"
                                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                                        <div class="absolute inset-0 bg-gray-900/0 group-hover:bg-gray-900/5 transition-colors"></div>
                                                    </div>

                                                    {{-- Content --}}
                                                    <div class="w-2/3 p-4 flex flex-col justify-between">
                                                        <div>
                                                            <div class="flex justify-between items-start gap-2 mb-1">
                                                                <h4 class="font-medium text-gray-900 text-sm md:text-base group-hover:text-rose-600 transition-colors">{{ $service->name }}</h4>
                                                                <div class="shrink-0 selection-indicator w-5 h-5 rounded-full border-2 border-gray-200 flex items-center justify-center transition-all">
                                                                    <div class="w-2.5 h-2.5 rounded-full bg-white opacity-0 transform scale-50 transition-all"></div>
                                                                </div>
                                                            </div>
                                                            <p class="text-xs text-gray-500 line-clamp-2 mb-3">{{ $service->description }}</p>
                                                        </div>
                                                        
                                                        <div class="flex items-center justify-between text-sm">
                                                            <span class="font-bold text-gray-900">{{ number_format($service->price) }} <span class="text-xs font-normal text-gray-500">RWF</span></span>
                                                            @if($service->duration)
                                                                <span class="text-xs text-gray-400 flex items-center gap-1">
                                                                    <i class="ph ph-clock"></i> {{ $service->duration }}m
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </form>
            </div>

            {{-- Sidebar (Desktop) --}}
            <div class="hidden lg:block lg:col-span-4 pl-4">
                <div class="sticky top-24">
                    <div id="summaryCard" class="bg-gray-900 text-white rounded-2xl p-6 shadow-xl overflow-hidden relative">
                        <div class="absolute top-0 right-0 p-4 opacity-10">
                            <i class="ph ph-receipt text-9xl text-white"></i>
                        </div>
                        
                        <h3 class="text-lg font-serif mb-6 relative z-10">Your Selection</h3>
                        
                        <div id="emptySummary" class="text-center py-8 text-gray-500 relative z-10">
                            <i class="ph ph-basket text-3xl mb-2"></i>
                            <p class="text-sm">No services selected yet.</p>
                        </div>

                        <div id="selectionContent" class="hidden relative z-10">
                            <div class="space-y-3 mb-6 max-h-[40vh] overflow-y-auto pr-2 custom-scrollbar">
                                <ul id="summaryList" class="space-y-3">
                                    {{-- JS injected --}}
                                </ul>
                            </div>
                            
                            <div class="border-t border-gray-700 pt-4 mb-6">
                                <div class="flex justify-between items-end">
                                    <span class="text-gray-400 text-sm">Estimated Total</span>
                                    <span id="summaryTotal" class="text-2xl font-serif text-white">RWF 0</span>
                                </div>
                                <div class="flex justify-between items-center mt-1">
                                    <span class="text-gray-500 text-xs">Duration</span>
                                    <span id="summaryDuration" class="text-gray-400 text-xs">0 mins</span>
                                </div>
                            </div>

                            <button type="submit" form="servicesForm" class="w-full py-4 bg-white text-gray-900 font-bold rounded-xl hover:bg-rose-50 transition-all flex items-center justify-center gap-2 group">
                                <span>Continue to Stylist</span>
                                <i class="ph ph-arrow-right group-hover:translate-x-1 transition-transform"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Help Card --}}
                    <div class="mt-6 bg-rose-50 rounded-xl p-4 border border-rose-100 flex items-start gap-3">
                        <div class="bg-white p-2 rounded-full text-rose-500 shadow-sm">
                            <i class="ph ph-chat-circle-dots"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-900 font-medium mb-1">Need help choosing?</p>
                            <p class="text-xs text-gray-600">Our support team is available to assist you.</p>
                            <a href="https://wa.me/250790395169" target="_blank" class="text-xs font-bold text-rose-600 mt-2 inline-block hover:underline">Chat on WhatsApp</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Mobile Summary Bar --}}
<div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 shadow-[0_-4px_20px_rgba(0,0,0,0.1)] z-40 transition-transform duration-300 translate-y-full" id="mobileSummary">
    <div class="flex items-center justify-between gap-4">
        <div>
            <p class="text-xs text-gray-500 mb-0.5"><span id="mobileCount">0</span> selected</p>
            <p id="summaryTotalMobile" class="text-lg font-bold text-gray-900">RWF 0</p>
        </div>
        <button type="submit" form="servicesForm" class="px-8 py-3 bg-gray-900 text-white font-bold rounded-full hover:bg-gray-800 transition shadow-lg">
            Continue
        </button>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const servicesForm = document.getElementById('servicesForm');
    const searchInput = document.getElementById('serviceSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const cards = document.querySelectorAll('.service-card');
    
    // UI Elements
    const summaryCard = document.getElementById('summaryCard');
    const emptySummary = document.getElementById('emptySummary');
    const selectionContent = document.getElementById('selectionContent');
    const summaryList = document.getElementById('summaryList');
    const summaryTotal = document.getElementById('summaryTotal');
    const summaryDuration = document.getElementById('summaryDuration');
    const mobileSummary = document.getElementById('mobileSummary');
    const mobileTotal = document.getElementById('summaryTotalMobile');
    const mobileCount = document.getElementById('mobileCount');

    // Search & Filter
    function filterServices() {
        const term = searchInput.value.toLowerCase();
        const cat = categoryFilter.value; // 'all' or 'cat-ID'

        document.querySelectorAll('.category-section').forEach(section => {
            const sectionId = section.id;
            let hasVisible = false;

            // If a specific category is selected, hide others completely
            if (cat !== 'all' && sectionId !== cat) {
                section.classList.add('hidden');
                return; 
            }
            section.classList.remove('hidden');

            // Filter cards inside the visible section
            const sectionCards = section.querySelectorAll('.service-card');
            sectionCards.forEach(card => {
                const name = card.dataset.name;
                const match = name.includes(term);
                if (match) {
                    card.classList.remove('hidden');
                    hasVisible = true;
                } else {
                    card.classList.add('hidden');
                }
            });

            // Hide section text if no cards visible inside it
            if (!hasVisible) {
                section.querySelector('h3').classList.add('opacity-30'); // Visual feedback rather than hiding structure completely potentially
            } else {
                section.querySelector('h3').classList.remove('opacity-30');
            }
        });
    }

    searchInput.addEventListener('input', filterServices);
    categoryFilter.addEventListener('change', () => {
        // Scroll to category if selecting specific
        const val = categoryFilter.value;
        if (val !== 'all') {
            document.getElementById(val)?.scrollIntoView({ behavior: 'smooth' });
        }
        filterServices();
    });

    // Selection Logic
    function updateSummary() {
        let total = 0;
        let duration = 0;
        let count = 0;
        summaryList.innerHTML = '';

        document.querySelectorAll('.service-checkbox').forEach(cb => {
            const card = cb.closest('.service-card');
            const indicator = card.querySelector('.selection-indicator');
            const dot = indicator.querySelector('div');

            if (cb.checked) {
                total += parseFloat(cb.dataset.price);
                duration += parseInt(cb.dataset.duration || 0);
                count++;
                
                // Active Styling
                card.classList.add('border-gray-900', 'ring-1', 'ring-gray-900', 'bg-gray-50');
                indicator.classList.remove('border-gray-200');
                indicator.classList.add('bg-gray-900', 'border-gray-900');
                dot.classList.remove('opacity-0', 'scale-50');
                
                // Add to list
                const li = document.createElement('li');
                li.className = 'flex justify-between items-start text-sm group/item';
                li.innerHTML = `
                    <span class="text-gray-300 flex-1">${cb.dataset.name}</span>
                    <button type="button" class="ml-2 text-rose-400 hover:text-rose-300 opacity-0 group-hover/item:opacity-100 transition-opacity" onclick="deselect('${cb.value}')">
                        <i class="ph ph-x"></i>
                    </button>
                    <span class="text-white ml-2 font-medium">${parseInt(cb.dataset.price).toLocaleString()}</span>
                `;
                summaryList.appendChild(li);

            } else {
                // Inactive Styling
                card.classList.remove('border-gray-900', 'ring-1', 'ring-gray-900', 'bg-gray-50');
                indicator.classList.add('border-gray-200');
                indicator.classList.remove('bg-gray-900', 'border-gray-900');
                dot.classList.add('opacity-0', 'scale-50');
            }
        });

        // Update Text
        const formattedTotal = 'RWF ' + total.toLocaleString();
        summaryTotal.textContent = formattedTotal;
        mobileTotal.textContent = formattedTotal;
        summaryDuration.textContent = duration + ' mins';
        mobileCount.textContent = count;

        // Toggle Views
        if (count > 0) {
            emptySummary.classList.add('hidden');
            selectionContent.classList.remove('hidden');
            mobileSummary.classList.remove('translate-y-full');
        } else {
            emptySummary.classList.remove('hidden');
            selectionContent.classList.add('hidden');
            mobileSummary.classList.add('translate-y-full');
        }
    }

    // Card Click Event
    cards.forEach(card => {
        card.addEventListener('click', (e) => {
            if (e.target.closest('button')) return; // ignore remove buttons if any
            
            const cb = card.querySelector('.service-checkbox');
            cb.checked = !cb.checked;
            updateSummary();
        });
    });

    // Expose deselect function globally for the summary list buttons
    window.deselect = function(id) {
        const cb = document.querySelector(`.service-checkbox[value="${id}"]`);
        if (cb) {
            cb.checked = false;
            updateSummary();
        }
    };

    // Initialize
    updateSummary();
    
    // Scroll shadow effect for toolbar
    window.addEventListener('scroll', () => {
        const toolbar = document.getElementById('toolbar');
        if (window.scrollY > 100) {
            toolbar.classList.add('shadow-md', 'bg-white/95');
        } else {
            toolbar.classList.remove('shadow-md');
        }
    });
});
</script>
<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 4px;
}
</style>
@endpush

