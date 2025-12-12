@extends('layouts.public')

@section('title', 'Our Luxury Nail Services')

@section('content')

{{-- Hero Section --}}
<div class="relative bg-gray-900 py-24 overflow-hidden">
    <div class="absolute inset-0 opacity-40">
        <img src="{{ asset('storage/banner.jpg') }}" alt="Services Banner" class="w-full h-full object-cover">
    </div>
    <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/60 to-transparent"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center pt-12">
        <span class="text-rose-400 font-medium tracking-widest text-sm uppercase mb-3 block animate-fade-in-up">Our Expertise</span>
        <h1 class="text-4xl md:text-6xl font-serif text-white mb-6 animate-fade-in-up delay-100">Service Menu</h1>
        <p class="text-xl text-gray-300 font-light max-w-2xl mx-auto animate-fade-in-up delay-200">
            Discover our curated selection of nail treatments, designed to provide the ultimate in care and relaxation.
        </p>
    </div>
</div>

{{-- Main Content --}}
<section class="py-16 bg-white min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Search & Filter Bar --}}
        <div class="flex flex-col md:flex-row justify-between items-center gap-6 mb-12 sticky top-24 z-30 bg-white/95 backdrop-blur-md py-4 transition-all duration-300 border-b border-gray-100" id="filterBar">
            {{-- Categories --}}
            <div class="overflow-x-auto scrollbar-hide w-full md:w-auto -mx-4 px-4 md:mx-0 md:px-0">
                <div class="flex gap-2 min-w-max">
                    <button onclick="filterCategory('all', this)" 
                            class="category-tab active px-5 py-2 rounded-full text-sm font-medium transition-all duration-300 border border-transparent bg-gray-900 text-white">
                        All Services
                    </button>
                    @foreach($categories as $category)
                        <button onclick="filterCategory('{{ $category->id }}', this)" 
                                class="category-tab px-5 py-2 rounded-full text-sm font-medium transition-all duration-300 border border-gray-200 text-gray-600 hover:border-gray-900 hover:text-gray-900 bg-white">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Search --}}
            <div class="relative w-full md:w-72">
                <input type="text" 
                       id="searchInput" 
                       placeholder="Search treatments..." 
                       class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-full text-sm focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-all">
                <i class="ph ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
        </div>

        @if($categories->count() > 0)
            <div id="servicesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-12">
                @foreach($categories as $category)
                    @foreach($category->services->sortBy('price') as $service)
                        <div class="service-card group cursor-pointer" 
                             onclick="openServiceModal({{ json_encode([
                                'id' => $service->id,
                                'name' => $service->name,
                                'price' => number_format($service->price),
                                'duration' => $service->duration,
                                'category' => $category->name,
                                'description' => $service->description,
                                'image' => $service->image ? asset('storage/' . $service->image) : null
                            ]) }})"
                             data-category="{{ $category->id }}"
                             data-name="{{ strtolower($service->name) }}"
                             data-description="{{ strtolower($service->description ?? '') }}">
                            
                            {{-- Image/Placement --}}
                            <div class="relative aspect-[4/3] mb-4 overflow-hidden rounded-lg bg-gray-100">
                                @if($service->image)
                                    <img src="{{ asset('storage/' . $service->image) }}" alt="{{ $service->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gray-50 text-gray-300">
                                        <i class="ph ph-sparkle text-3xl"></i>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-300"></div>
                            </div>

                            {{-- Content --}}
                            <div class="flex justify-between items-start gap-4">
                                <div>
                                    <h3 class="text-lg font-serif text-gray-900 group-hover:text-rose-600 transition-colors">{{ $service->name }}</h3>
                                    <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $service->description }}</p>
                                    @if($service->duration)
                                        <div class="flex items-center gap-1 mt-2 text-xs text-gray-400">
                                            <i class="ph ph-clock"></i>
                                            <span>{{ $service->duration }} mins</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span class="block text-lg font-medium text-gray-900 whitespace-nowrap">
                                        {{ number_format($service->price) }} <span class="text-xs text-gray-500 font-normal">RWF</span>
                                    </span>
                                    <button class="mt-2 text-sm font-medium text-rose-600 opacity-0 group-hover:opacity-100 transition-opacity -translate-x-2 group-hover:translate-x-0 duration-300">
                                        Book <i class="ph ph-arrow-right text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>

            {{-- No Results --}}
            <div id="noResults" class="hidden text-center py-24">
                <i class="ph ph-magnifying-glass text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">No services found matching your criteria.</p>
            </div>
        @else
            <div class="text-center py-24">
                <p class="text-gray-500">Our service menu is currently being updated.</p>
            </div>
        @endif
    </div>
</section>

{{-- Minimalist Modal --}}
<div id="serviceModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm" onclick="closeServiceModal()">
    <div class="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl transform transition-all" onclick="event.stopPropagation()">
        {{-- Image --}}
        <div id="modalImageContainer" class="hidden relative h-48 sm:h-64">
            <img id="modalImage" src="" alt="" class="w-full h-full object-cover">
            <button onclick="closeServiceModal()" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center bg-black/20 hover:bg-black/40 text-white rounded-full backdrop-blur-md transition-colors">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <div id="modalNoImageHeader" class="hidden p-6 pb-0 flex justify-end">
            <button onclick="closeServiceModal()" class="text-gray-400 hover:text-gray-600">
                <i class="ph ph-x text-xl"></i>
            </button>
        </div>

        <div class="p-8">
            <span id="modalCategory" class="text-xs font-bold tracking-widest text-rose-600 uppercase mb-2 block"></span>
            <div class="flex justify-between items-start gap-4 mb-6">
                <h3 id="modalTitle" class="text-2xl font-serif text-gray-900"></h3>
                <div class="text-right">
                    <span id="modalPrice" class="text-xl font-medium text-gray-900 block"></span>
                    <span id="modalDuration" class="text-sm text-gray-500 block"></span>
                </div>
            </div>
            
            <p id="modalDescription" class="text-gray-600 leading-relaxed mb-8 font-light"></p>
            
            <a id="modalBookBtn" href="#" class="w-full block py-4 bg-gray-900 text-white text-center font-medium rounded-full hover:bg-rose-600 transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                Book Appointment
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Filter Logic
function filterCategory(catId, btn) {
    // Reset buttons
    document.querySelectorAll('.category-tab').forEach(b => {
        b.classList.remove('bg-gray-900', 'text-white', 'border-transparent');
        b.classList.add('bg-white', 'text-gray-600', 'border-gray-200');
    });
    
    // Active button style
    btn.classList.remove('bg-white', 'text-gray-600', 'border-gray-200');
    btn.classList.add('bg-gray-900', 'text-white', 'border-transparent');
    
    // Filter
    const cards = document.querySelectorAll('.service-card');
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    let hasVisible = false;

    cards.forEach(card => {
        const matchesCat = catId === 'all' || card.dataset.category === catId;
        const matchesSearch = card.dataset.name.includes(searchTerm) || card.dataset.description.includes(searchTerm);
        
        if (matchesCat && matchesSearch) {
            card.style.display = 'block';
            card.classList.add('animate-fade-in');
            hasVisible = true;
        } else {
            card.style.display = 'none';
        }
    });

    document.getElementById('noResults').classList.toggle('hidden', hasVisible);
}

// Search Logic
document.getElementById('searchInput').addEventListener('input', (e) => {
    // Trigger current active filter re-run
    const activeBtn = document.querySelector('.category-tab.bg-gray-900'); // roughly finds active
    const activeCat = activeBtn ? activeBtn.getAttribute('onclick').match(/'([^']+)'/)[1] : 'all';
    filterCategory(activeCat, activeBtn || document.querySelector('.category-tab'));
});

// Modal Logic
function openServiceModal(service) {
    document.getElementById('modalTitle').textContent = service.name;
    document.getElementById('modalCategory').textContent = service.category;
    document.getElementById('modalPrice').textContent = 'RWF ' + service.price;
    document.getElementById('modalDescription').textContent = service.description || 'No description available.';
    document.getElementById('modalBookBtn').href = '{{ route("booking.step1") }}?service_id=' + service.id;
    
    if (service.duration) {
        document.getElementById('modalDuration').textContent = service.duration + ' mins';
        document.getElementById('modalDuration').style.display = 'block';
    } else {
        document.getElementById('modalDuration').style.display = 'none';
    }

    const imgContainer = document.getElementById('modalImageContainer');
    const noImgHeader = document.getElementById('modalNoImageHeader');
    
    if (service.image) {
        document.getElementById('modalImage').src = service.image;
        imgContainer.classList.remove('hidden');
        noImgHeader.classList.add('hidden');
    } else {
        imgContainer.classList.add('hidden');
        noImgHeader.classList.remove('hidden');
    }

    const modal = document.getElementById('serviceModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex'); // Ensure flex is on
    document.body.style.overflow = 'hidden';
}

function closeServiceModal() {
    document.getElementById('serviceModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Fade in animation style
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fadeIn 0.4s ease-out forwards; }
`;
document.head.appendChild(style);
</script>
@endpush

@endsection