@extends('layouts.dashboard')

@section('title', 'Email History')

@section('content')
<div class="p-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            <i class="ph ph-envelope-simple mr-2 text-blue-600"></i>Email History
        </h1>
        <p class="text-gray-600">View all emails sent to customers and providers</p>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-2xl p-6 shadow-md border border-gray-200 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Email, name, subject..."
                       class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email Type</label>
                <select name="type"
                        class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                    <option value="">All Types</option>
                    @foreach($emailTypes as $type)
                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                            {{ ucwords(str_replace('_', ' ', $type)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                <select name="status"
                        class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition outline-none">
                    <option value="">All Status</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full px-6 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    <i class="ph ph-funnel mr-2"></i>Filter
                </button>
            </div>
        </div>
    </form>

    @if($logs->count())
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase">Recipient</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase">Subject</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase">Type</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase">Sent At</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($logs as $log)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $log->recipient_name }}</p>
                                        <p class="text-sm text-gray-600">{{ $log->recipient_email }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $log->subject }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                        {{ ucwords(str_replace('_', ' ', $log->email_type)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($log->status === 'sent')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                            <i class="ph ph-check-circle mr-1"></i>Sent
                                        </span>
                                    @elseif($log->status === 'failed')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                            <i class="ph ph-x-circle mr-1"></i>Failed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-800">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $log->sent_at ? $log->sent_at->format('Y-m-d H:i') : '—' }}<br>
                                    <span class="text-xs text-gray-500">{{ $log->sent_at ? $log->sent_at->diffForHumans() : '' }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.emails.show', $log) }}"
                                       class="px-3 py-1.5 bg-blue-100 text-blue-700 text-sm font-semibold rounded-lg hover:bg-blue-200 transition">
                                        <i class="ph ph-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-12 text-center">
            <i class="ph ph-envelope text-6xl text-blue-300 mb-4"></i>
            <p class="text-blue-900 font-semibold text-lg">No emails found</p>
            <p class="text-blue-700 text-sm mt-2">Email history will appear here</p>
        </div>
    @endif
</div>
@endsection
