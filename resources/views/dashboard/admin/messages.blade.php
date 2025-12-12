@extends('layouts.dashboard')

@section('title', 'Contact Messages')

@section('content')
<div class="p-6">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Contact Messages</h1>
                <p class="text-gray-600">View and manage customer inquiries</p>
            </div>
            <a href="{{ route('dashboard.admin') }}" 
               class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">
                <i class="ph ph-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>

    @if($messages->count())
        {{-- Table Card --}}
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">#</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Sent</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($messages as $message)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    #{{ $message->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-semibold text-gray-900">{{ $message->name }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="mailto:{{ $message->email }}" 
                                       class="text-blue-600 hover:text-blue-800 transition text-sm">
                                        {{ $message->email }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-700">{{ $message->subject }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-500">{{ $message->created_at->diffForHumans() }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" 
                                                class="px-3 py-1.5 bg-blue-100 text-blue-700 text-sm font-semibold rounded-lg hover:bg-blue-200 transition btn-view-message"
                                                data-id="{{ $message->id }}"
                                                data-name="{{ $message->name }}"
                                                data-subject="{{ $message->subject }}"
                                                data-message="{{ $message->message }}">
                                            <i class="ph ph-eye"></i>
                                        </button>
                                        <button type="button"
                                                class="px-3 py-1.5 bg-red-100 text-red-700 text-sm font-semibold rounded-lg hover:bg-red-200 transition btn-delete-message"
                                                data-url="{{ route('admin.messages.destroy', $message->id) }}"
                                                data-name="{{ $message->name }}">
                                            <i class="ph ph-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($messages->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $messages->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-12 text-center">
            <i class="ph ph-envelope text-6xl text-blue-300 mb-4"></i>
            <p class="text-blue-900 font-semibold text-lg">No messages received yet</p>
            <p class="text-blue-700 text-sm mt-2">Customer inquiries will appear here</p>
        </div>
    @endif
</div>

{{-- View Message Modal --}}
<div id="viewMessageModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Message from <span id="modal-sender-name"></span></h3>
                    <p class="text-sm text-gray-600 mt-1"><strong>Subject:</strong> <span id="modal-subject"></span></p>
                </div>
                <button type="button" 
                        onclick="closeViewModal()"
                        class="text-gray-400 hover:text-gray-600 transition">
                    <i class="ph ph-x text-2xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <p id="modal-message-content" class="text-gray-700 leading-relaxed whitespace-pre-wrap"></p>
        </div>
        <div class="p-6 bg-gray-50 border-t border-gray-200 flex justify-end">
            <button type="button" 
                    onclick="closeViewModal()"
                    class="px-6 py-2.5 bg-gray-900 text-white font-semibold rounded-lg hover:bg-gray-800 transition">
                Close
            </button>
        </div>
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
                Are you sure you want to delete the message from <strong id="delete-message-name" class="text-gray-900"></strong>?
            </p>
            <div class="flex gap-3">
                <button type="button" 
                        onclick="closeDeleteModal()"
                        class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">
                    Cancel
                </button>
                <form method="POST" id="deleteMessageForm" class="flex-1">
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
const viewModal = document.getElementById('viewMessageModal');
const deleteModal = document.getElementById('confirmDeleteModal');
const deleteForm = document.getElementById('deleteMessageForm');

// View Message
document.querySelectorAll('.btn-view-message').forEach(button => {
    button.addEventListener('click', function() {
        document.getElementById('modal-sender-name').textContent = this.dataset.name;
        document.getElementById('modal-subject').textContent = this.dataset.subject;
        document.getElementById('modal-message-content').textContent = this.dataset.message;
        viewModal.classList.remove('hidden');
    });
});

function closeViewModal() {
    viewModal.classList.add('hidden');
}

// Delete Message
document.querySelectorAll('.btn-delete-message').forEach(button => {
    button.addEventListener('click', function() {
        const url = this.dataset.url;
        const name = this.dataset.name;
        
        document.getElementById('delete-message-name').textContent = name;
        deleteForm.setAttribute('action', url);
        deleteModal.classList.remove('hidden');
    });
});

function closeDeleteModal() {
    deleteModal.classList.add('hidden');
}

// Close on outside click
viewModal.addEventListener('click', function(e) {
    if (e.target === viewModal) closeViewModal();
});

deleteModal.addEventListener('click', function(e) {
    if (e.target === deleteModal) closeDeleteModal();
});

// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeViewModal();
        closeDeleteModal();
    }
});
</script>
@endpush
