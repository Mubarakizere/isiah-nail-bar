@extends('layouts.dashboard')

@section('title', 'Email Logs')
@section('page-subtitle', 'View all emails sent to customers and providers')

@section('content')
<div>
    {{-- Filters --}}
    <form method="GET" class="bg-white border-2 border-gray-300 p-5 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Email, name, subject..."
                       class="w-full px-3 py-2 border border-gray-300 focus:border-blue-600 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Email Type</label>
                <select name="type" class="w-full px-3 py-2 border border-gray-300 focus:border-blue-600 focus:outline-none">
                    <option value="">All Types</option>
                    @foreach($emailTypes as $type)
                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                            {{ ucwords(str_replace('_', ' ', $type)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 focus:border-blue-600 focus:outline-none">
                    <option value="">All Status</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white font-medium hover:bg-blue-700 transition">
                    <i class="ph ph-funnel mr-2"></i>Filter
                </button>
            </div>
        </div>
    </form>

    @if($logs->count())
        <div class="bg-white border-2 border-gray-300">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100 border-b-2 border-gray-300">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Recipient</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Subject</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Sent At</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($logs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="font-medium text-gray-900 text-sm">{{ $log->recipient_name }}</p>
                                        <p class="text-xs text-gray-600">{{ $log->recipient_email }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $log->subject }}</td>
                                <td class="px-4 py-3">
                                    <span style="background: #DBEAFE; color: #1E40AF; padding: 3px 8px; font-size: 11px; font-weight: 600; text-transform: uppercase;">
                                        {{ ucwords(str_replace('_', ' ', $log->email_type)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($log->status === 'sent')
                                        <span style="background: #D1FAE5; color: #065F46; padding: 3px 8px; font-size: 11px; font-weight: 600; text-transform: uppercase;">
                                            <i class="ph ph-check-circle"></i> SENT
                                        </span>
                                    @elseif($log->status === 'failed')
                                        <span style="background: #FEE2E2; color: #991B1B; padding: 3px 8px; font-size: 11px; font-weight: 600; text-transform: uppercase;">
                                            <i class="ph ph-x-circle"></i> FAILED
                                        </span>
                                    @else
                                        <span style="background: #F3F4F6; color: #6B7280; padding: 3px 8px; font-size: 11px; font-weight: 600; text-transform: uppercase;">
                                            PENDING
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $log->sent_at ? $log->sent_at->format('Y-m-d H:i') : '—' }}<br>
                                    <span class="text-xs text-gray-500">{{ $log->sent_at ? $log->sent_at->diffForHumans() : '' }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.emails.show', $log) }}"
                                       class="inline-block px-3 py-1.5 bg-blue-100 text-blue-700 text-xs font-semibold hover:bg-blue-200 transition">
                                        <i class="ph ph-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="px-4 py-4 border-t-2 border-gray-300 bg-gray-50">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="bg-blue-50 border-2 border-blue-300 p-12 text-center">
            <i class="ph ph-envelope text-6xl text-blue-400 mb-4"></i>
            <p class="text-blue-900 font-semibold text-lg">No emails found</p>
            <p class="text-blue-700 text-sm mt-2">Email history will appear here</p>
        </div>
    @endif
</div>
@endsection
