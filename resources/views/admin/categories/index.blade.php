@extends('layouts.dashboard')

@section('title', 'Manage Categories')

@section('content')
<div class="p-6">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Categories</h1>
                <p class="text-gray-600">Manage service categories</p>
            </div>
            <a href="{{ route('admin.categories.create') }}" 
               class="px-6 py-3 bg-gray-900 text-white font-semibold rounded-lg hover:bg-gray-800 transition">
                <i class="ph ph-plus-circle mr-2"></i>Add Category
            </a>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Slug</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($categories as $category)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-semibold text-gray-900">{{ $category->name }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-500">{{ $category->slug }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $category->created_at->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.categories.edit', $category->id) }}" 
                                       class="px-3 py-1.5 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>
                                    <button type="button"
                                            class="px-3 py-1.5 bg-red-100 text-red-700 text-sm font-semibold rounded-lg hover:bg-red-200 transition btn-delete"
                                            data-url="{{ route('admin.categories.destroy', $category->id) }}"
                                            data-name="{{ $category->name }}">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <i class="ph ph-folder-notch-open text-5xl text-gray-300 mb-3"></i>
                                <p class="text-gray-500 font-semibold">No categories found</p>
                                <p class="text-gray-400 text-sm mt-1">Create your first category to get started</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($categories->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="confirmDeleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="ph ph-warning text-2xl text-red-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Confirm Deletion</h3>
                    <p class="text-sm text-gray-600">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-gray-700 mb-6">
                Are you sure you want to delete <strong id="delete-item-name" class="text-gray-900"></strong>?
            </p>
            <div class="flex gap-3">
                <button type="button" 
                        onclick="closeDeleteModal()"
                        class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">
                    Cancel
                </button>
                <form method="POST" id="deleteForm" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="w-full px-4 py-2.5 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition">
                        <i class="ph ph-trash mr-1"></i>Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const modal = document.getElementById('confirmDeleteModal');
const deleteForm = document.getElementById('deleteForm');
const itemNameSpan = document.getElementById('delete-item-name');

document.querySelectorAll('.btn-delete').forEach(button => {
    button.addEventListener('click', function () {
        const action = this.getAttribute('data-url');
        const name = this.getAttribute('data-name');
        
        itemNameSpan.textContent = name;
        deleteForm.setAttribute('action', action);
        
        modal.classList.remove('hidden');
    });
});

function closeDeleteModal() {
    modal.classList.add('hidden');
}

// Close on outside click
modal.addEventListener('click', function(e) {
    if (e.target === modal) {
        closeDeleteModal();
    }
});

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
        closeDeleteModal();
    }
});
</script>
@endpush
