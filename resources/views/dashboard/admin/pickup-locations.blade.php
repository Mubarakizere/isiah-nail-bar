@extends('layouts.dashboard')

@section('title', 'Pickup Locations')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-gray-800 font-bold">Pickup Locations</h1>
            <p>Manage transport locations and fees for the pickup service.</p>
        </div>
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <button onclick="document.getElementById('add-modal').classList.remove('hidden')" class="btn bg-gray-900 text-white hover:bg-gray-800 rounded-lg px-4 py-2">
                <i class="ph ph-plus"></i> Add Location
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white shadow-lg rounded-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <thead class="text-xs font-semibold uppercase text-gray-500 bg-gray-50 border-t border-b border-gray-200">
                    <tr>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Location Name</div></th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Transport Fee (RWF)</div></th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Status</div></th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-right">Actions</div></th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-200">
                    @foreach($locations as $location)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium text-gray-800">{{ $location->name }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="text-gray-800">{{ number_format($location->fee) }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $location->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $location->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="space-x-1 flex justify-end">
                                <button onclick="openEditModal({{ $location->id }}, '{{ addslashes($location->name) }}', {{ $location->fee }}, {{ $location->is_active ? 'true' : 'false' }})" class="text-blue-500 hover:text-blue-600 rounded-full">
                                    <i class="ph ph-pencil-simple text-lg"></i>
                                </button>
                                <form action="{{ route('admin.pickup-locations.destroy', $location) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this location?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-600 rounded-full">
                                        <i class="ph ph-trash text-lg"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Modal --}}
<div id="add-modal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md mx-4 overflow-hidden">
        <form action="{{ route('admin.pickup-locations.store') }}" method="POST">
            @csrf
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900">Add Pickup Location</h3>
                <button type="button" onclick="document.getElementById('add-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500"><i class="ph ph-x text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location Name</label>
                    <input type="text" name="name" required class="w-full form-input rounded-lg border-gray-300" placeholder="e.g. Kigali City Center">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fee (RWF)</label>
                    <input type="number" name="fee" required min="0" value="50000" class="w-full form-input rounded-lg border-gray-300">
                </div>
                <div class="flex items-center mt-4">
                    <input type="checkbox" name="is_active" id="add_is_active" value="1" checked class="rounded border-gray-300 text-gray-900 shadow-sm focus:border-gray-300 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                    <label for="add_is_active" class="ml-2 text-sm text-gray-600">Active</label>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('add-modal').classList.add('hidden')" class="btn bg-white border-gray-200 text-gray-800 hover:bg-gray-50 rounded-lg px-4 py-2">Cancel</button>
                <button type="submit" class="btn bg-gray-900 text-white hover:bg-gray-800 rounded-lg px-4 py-2">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div id="edit-modal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md mx-4 overflow-hidden">
        <form id="edit-form" method="POST">
            @csrf
            @method('PUT')
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900">Edit Pickup Location</h3>
                <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500"><i class="ph ph-x text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location Name</label>
                    <input type="text" name="name" id="edit_name" required class="w-full form-input rounded-lg border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fee (RWF)</label>
                    <input type="number" name="fee" id="edit_fee" required min="0" class="w-full form-input rounded-lg border-gray-300">
                </div>
                <div class="flex items-center mt-4">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="rounded border-gray-300 text-gray-900 shadow-sm focus:border-gray-300 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                    <label for="edit_is_active" class="ml-2 text-sm text-gray-600">Active</label>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')" class="btn bg-white border-gray-200 text-gray-800 hover:bg-gray-50 rounded-lg px-4 py-2">Cancel</button>
                <button type="submit" class="btn bg-gray-900 text-white hover:bg-gray-800 rounded-lg px-4 py-2">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, name, fee, isActive) {
    document.getElementById('edit-form').action = '/dashboard/admin/pickup-locations/' + id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_fee').value = fee;
    document.getElementById('edit_is_active').checked = isActive;
    document.getElementById('edit-modal').classList.remove('hidden');
}
</script>
@endsection
