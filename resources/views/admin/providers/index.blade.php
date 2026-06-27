@extends('layouts.dashboard')

@section('title', 'Manage Providers')

@section('content')
<div class="p-6 max-w-7xl mx-auto">
    {{-- Header Section --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight mb-2">Providers</h1>
            <p class="text-gray-500 text-lg">Manage your service professionals and staff</p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-center gap-4 w-full md:w-auto">
            {{-- Search Form --}}
            <form action="{{ route('admin.providers.index') }}" method="GET" class="w-full sm:w-80 relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="ph ph-magnifying-glass text-gray-400 group-focus-within:text-indigo-500 transition-colors"></i>
                </div>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search providers..." 
                       class="w-full pl-10 pr-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent shadow-sm transition-all duration-300">
                @if(!empty($search))
                    <a href="{{ route('admin.providers.index') }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <i class="ph ph-x-circle"></i>
                    </a>
                @endif
            </form>

            <a href="{{ route('admin.providers.create') }}" 
               class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-gray-900 to-gray-800 text-white font-semibold rounded-xl hover:from-black hover:to-gray-900 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center">
                <i class="ph ph-plus-circle mr-2 text-xl"></i> Add Provider
            </a>
        </div>
    </div>

    {{-- Providers List --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden relative">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-6 py-5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Provider</th>
                        <th class="px-6 py-5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($providers as $provider)
                        <tr class="hover:bg-gray-50/80 transition-colors duration-200 group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-4">
                                    <div class="relative">
                                        @if($provider->photo)
                                            <img src="{{ asset('storage/' . $provider->photo) }}" alt="{{ $provider->name }}" class="w-12 h-12 rounded-2xl object-cover shadow-sm border border-gray-100">
                                        @else
                                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center text-lg font-bold shadow-sm">
                                                {{ strtoupper(substr($provider->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        @if($provider->active)
                                            <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full"></span>
                                        @else
                                            <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-amber-500 border-2 border-white rounded-full"></span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 text-base">{{ $provider->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $provider->email ?? 'No email provided' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center text-gray-600">
                                    <i class="ph ph-phone mr-2 text-gray-400"></i>
                                    <span class="text-sm font-medium">{{ $provider->phone ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($provider->active)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-200/50">
                                        <i class="ph ph-check-circle mr-1.5"></i> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200/50">
                                        <i class="ph ph-clock mr-1.5"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    <a href="{{ route('admin.providers.edit', $provider->id) }}" 
                                       class="p-2 bg-gray-50 hover:bg-indigo-50 text-gray-600 hover:text-indigo-600 rounded-xl transition-colors duration-200"
                                       data-tippy-content="Edit Details">
                                        <i class="ph ph-pencil-simple text-lg"></i>
                                    </a>

                                    <a href="{{ route('admin.providers.hours.edit', $provider->id) }}" 
                                       class="p-2 bg-gray-50 hover:bg-blue-50 text-gray-600 hover:text-blue-600 rounded-xl transition-colors duration-200"
                                       data-tippy-content="Working Hours">
                                        <i class="ph ph-clock text-lg"></i>
                                    </a>

                                    <a href="{{ route('admin.providers.performance.single', $provider->id) }}" 
                                       class="p-2 bg-gray-50 hover:bg-purple-50 text-gray-600 hover:text-purple-600 rounded-xl transition-colors duration-200"
                                       data-tippy-content="Performance">
                                        <i class="ph ph-chart-line text-lg"></i>
                                    </a>

                                    @if (!$provider->active)
                                        <form action="{{ route('admin.providers.approve', $provider->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button class="p-2 bg-gray-50 hover:bg-green-50 text-gray-600 hover:text-green-600 rounded-xl transition-colors duration-200"
                                                    data-tippy-content="Approve">
                                                <i class="ph ph-check-circle text-lg"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.providers.decline', $provider->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button class="p-2 bg-gray-50 hover:bg-amber-50 text-gray-600 hover:text-amber-600 rounded-xl transition-colors duration-200"
                                                    data-tippy-content="Decline">
                                                <i class="ph ph-x-circle text-lg"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <button type="button"
                                            class="p-2 bg-gray-50 hover:bg-red-50 text-gray-600 hover:text-red-600 rounded-xl transition-colors duration-200 btn-delete"
                                            data-url="{{ route('admin.providers.destroy', $provider->id) }}"
                                            data-name="{{ $provider->name }}"
                                            data-tippy-content="Delete Provider">
                                        <i class="ph ph-trash text-lg"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center bg-gray-50/30">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                                    <i class="ph ph-users-three text-3xl text-gray-400"></i>
                                </div>
                                @if(!empty($search))
                                    <h3 class="text-lg font-bold text-gray-900 mb-1">No providers found</h3>
                                    <p class="text-gray-500">We couldn't find any providers matching "{{ $search }}"</p>
                                    <a href="{{ route('admin.providers.index') }}" class="inline-block mt-4 text-indigo-600 font-semibold hover:text-indigo-700">
                                        Clear Search
                                    </a>
                                @else
                                    <h3 class="text-lg font-bold text-gray-900 mb-1">No providers yet</h3>
                                    <p class="text-gray-500">Add your first provider to start managing your team.</p>
                                    <a href="{{ route('admin.providers.create') }}" class="inline-block mt-4 px-6 py-2.5 bg-gray-900 text-white font-medium rounded-lg hover:bg-gray-800 transition">
                                        Add First Provider
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($providers->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $providers->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="confirmDeleteModal" class="hidden fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-opacity duration-300">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full transform scale-95 opacity-0 transition-all duration-300" id="modalContent">
        <div class="p-8">
            <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-50 rounded-full mb-6">
                <i class="ph ph-warning-circle text-3xl text-red-500"></i>
            </div>
            
            <h3 class="text-2xl font-bold text-center text-gray-900 mb-2">Delete Provider?</h3>
            <p class="text-center text-gray-500 mb-8">
                Are you sure you want to delete <strong id="delete-item-name" class="text-gray-900"></strong>? This action cannot be undone and will remove their access.
            </p>
            
            <div class="flex gap-4">
                <button type="button" 
                        onclick="closeDeleteModal()"
                        class="flex-1 px-5 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors duration-200">
                    Cancel
                </button>
                <form method="POST" id="deleteForm" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="w-full px-5 py-3 bg-red-500 text-white font-bold rounded-xl hover:bg-red-600 shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center">
                        <i class="ph ph-trash mr-2"></i>Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Include Tippy.js for Tooltips if available, or a simple fallback -->
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<script>
    // Initialize tooltips if tippy is loaded
    if(typeof tippy !== 'undefined') {
        tippy('[data-tippy-content]', {
            theme: 'light',
            animation: 'scale',
            placement: 'top',
        });
    }

    const modal = document.getElementById('confirmDeleteModal');
    const modalContent = document.getElementById('modalContent');
    const deleteForm = document.getElementById('deleteForm');
    const itemNameSpan = document.getElementById('delete-item-name');

    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function () {
            const action = this.getAttribute('data-url');
            const name = this.getAttribute('data-name');
            
            itemNameSpan.textContent = name;
            deleteForm.setAttribute('action', action);
            
            // Show modal with animation
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.add('opacity-100');
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        });
    });

    function closeDeleteModal() {
        // Hide modal with animation
        modal.classList.remove('opacity-100');
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeDeleteModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeDeleteModal();
        }
    });
</script>
@endpush
