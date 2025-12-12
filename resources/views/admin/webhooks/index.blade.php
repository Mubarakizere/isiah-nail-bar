@extends('layouts.dashboard')

@section('title', 'Webhook Logs')

@section('content')
<div class="p-6">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            <i class="ph ph-webhooks-logo mr-2 text-blue-600"></i>Webhook Logs
        </h1>
        <p class="text-gray-600">Monitor payment gateway webhook activity and debugging information</p>
    </div>

    @if($logs->count())
        {{-- Table Card --}}
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Payload</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Received At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($logs as $log)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    #{{ $log->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-700 capitalize">
                                        {{ str_replace('_', ' ', $log->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($log->status === 'success' || $log->status === 'SUCCESS')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                            <i class="ph ph-check-circle mr-1"></i>Success
                                        </span>
                                    @elseif ($log->status === 'failed' || $log->status === 'FAILED')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                            <i class="ph ph-x-circle mr-1"></i>Failed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-800">
                                            {{ ucfirst($log->status ?? 'N/A') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <button type="button"
                                            class="px-3 py-1.5 bg-blue-100 text-blue-700 text-sm font-semibold rounded-lg hover:bg-blue-200 transition btn-view-payload"
                                            data-payload="{{ json_encode($log->payload, JSON_PRETTY_PRINT) }}"
                                            data-id="{{ $log->id }}">
                                        <i class="ph ph-code mr-1"></i>View Payload
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $log->created_at->format('Y-m-d H:i:s') }}
                                    <br>
                                    <span class="text-xs text-gray-500">{{ $log->created_at->diffForHumans() }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-12 text-center">
            <i class="ph ph-webhooks-logo text-6xl text-blue-300 mb-4"></i>
            <p class="text-blue-900 font-semibold text-lg">No webhook logs yet</p>
            <p class="text-blue-700 text-sm mt-2">Webhook activity will be recorded here for debugging</p>
        </div>
    @endif
</div>

{{-- Payload Modal --}}
<div id="payloadModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] flex flex-col">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-gray-900">
                    <i class="ph ph-code text-blue-600 mr-2"></i>Webhook Payload
                </h3>
                <button type="button" 
                        onclick="closePayloadModal()"
                        class="text-gray-400 hover:text-gray-600 transition">
                    <i class="ph ph-x text-2xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6 overflow-auto flex-1">
            <pre id="modal-payload-content" class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm overflow-x-auto whitespace-pre-wrap font-mono"></pre>
        </div>
        <div class="p-6 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
            <button type="button" 
                    onclick="copyPayload()"
                    class="px-6 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                <i class="ph ph-copy mr-2"></i>Copy
            </button>
            <button type="button" 
                    onclick="closePayloadModal()"
                    class="px-6 py-2.5 bg-gray-900 text-white font-semibold rounded-lg hover:bg-gray-800 transition">
                Close
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const payloadModal = document.getElementById('payloadModal');
const payloadContent = document.getElementById('modal-payload-content');

document.querySelectorAll('.btn-view-payload').forEach(button => {
    button.addEventListener('click', function() {
        payloadContent.textContent = this.dataset.payload;
        payloadModal.classList.remove('hidden');
    });
});

function closePayloadModal() {
    payloadModal.classList.add('hidden');
}

function copyPayload() {
    navigator.clipboard.writeText(payloadContent.textContent);
    alert('Payload copied to clipboard!');
}

// Close on outside click
payloadModal.addEventListener('click', function(e) {
    if (e.target === payloadModal) {
        closePayloadModal();
    }
});

// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !payloadModal.classList.contains('hidden')) {
        closePayloadModal();
    }
});
</script>
@endpush
