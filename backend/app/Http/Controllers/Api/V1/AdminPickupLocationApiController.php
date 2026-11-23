<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PickupLocation;
use Illuminate\Http\Request;

class AdminPickupLocationApiController extends Controller
{
    public function index()
    {
        $locations = PickupLocation::query()
            ->withCount('bookings')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $locations->map(fn (PickupLocation $location) => $this->serializeLocation($location))->values(),
            'stats' => [
                'total' => $locations->count(),
                'active' => $locations->where('is_active', true)->count(),
                'inactive' => $locations->where('is_active', false)->count(),
                'bookings' => (int) $locations->sum('bookings_count'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $location = PickupLocation::create([
            'name' => trim($validated['name']),
            'fee' => $validated['fee'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pickup location added successfully.',
            'data' => $this->serializeLocation($location->loadCount('bookings')),
        ], 201);
    }

    public function update(Request $request, PickupLocation $pickupLocation)
    {
        $validated = $request->validate($this->rules());
        $pickupLocation->update([
            'name' => trim($validated['name']),
            'fee' => $validated['fee'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pickup location updated successfully.',
            'data' => $this->serializeLocation($pickupLocation->fresh()->loadCount('bookings')),
        ]);
    }

    public function toggle(PickupLocation $pickupLocation)
    {
        $pickupLocation->update(['is_active' => !$pickupLocation->is_active]);

        return response()->json([
            'success' => true,
            'message' => $pickupLocation->is_active ? 'Pickup location activated.' : 'Pickup location deactivated.',
            'data' => $this->serializeLocation($pickupLocation->fresh()->loadCount('bookings')),
        ]);
    }

    public function destroy(PickupLocation $pickupLocation)
    {
        $pickupLocation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pickup location deleted successfully.',
        ]);
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'fee' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    private function serializeLocation(PickupLocation $location): array
    {
        return [
            'id' => $location->id,
            'name' => $location->name,
            'fee' => (float) $location->fee,
            'is_active' => (bool) $location->is_active,
            'bookings_count' => (int) ($location->bookings_count ?? 0),
            'created_at' => $location->created_at?->toISOString(),
            'updated_at' => $location->updated_at?->toISOString(),
        ];
    }
}
