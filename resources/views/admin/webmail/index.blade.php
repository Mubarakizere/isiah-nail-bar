@extends('layouts.dashboard')

@section('title', 'Webmail Inbox')

@section('content')
<div class="p-6">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Inbox</h1>
            <p class="text-gray-600">Manage your business emails</p>
        </div>
        <a href="{{ route('admin.webmail.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
            <i class="ph ph-arrows-clockwise mr-2"></i>Refresh
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-1/4">Sender</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-1/2">Subject</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider w-1/4">Date</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($messages as $message)
                        <tr class="hover:bg-gray-50 transition cursor-pointer {{ $message->getFlags()->has('seen') ? 'bg-white' : 'bg-blue-50/50' }}" 
                            onclick="window.location='{{ route('admin.webmail.show', $message->getUid()) }}'">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 flex-shrink-0">
                                        <span class="font-bold text-xs">{{ strtoupper(substr($message->getFrom()[0]->personal ?? $message->getFrom()[0]->mail, 0, 1)) }}</span>
                                    </div>
                                    <div class="truncate">
                                        <span class="block font-medium text-gray-900 truncate">{{ $message->getFrom()[0]->personal ?? $message->getFrom()[0]->mail }}</span>
                                        <span class="block text-xs text-gray-500 truncate">{{ $message->getFrom()[0]->mail }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-900 font-medium block truncate">{{ $message->getSubject() }}</span>
                                <span class="text-gray-500 text-sm truncate block">{{ Str::limit($message->getTextBody() ?? 'No text content', 80) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap text-sm text-gray-500">
                                {{ $message->getDate()->format('M d, H:i') }}
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap text-sm text-gray-500">
                                <i class="ph ph-caret-right"></i>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                <i class="ph ph-envelope-open text-4xl mb-3 text-gray-300"></i>
                                <p>Inbox is empty</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination if available --}}
        @if(method_exists($messages, 'links'))
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $messages->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
