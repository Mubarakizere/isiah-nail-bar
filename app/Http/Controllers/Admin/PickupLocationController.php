<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PickupLocation;
use Illuminate\Http\Request;

class PickupLocationController extends Controller
{
    public function index()
    {
        $locations = PickupLocation::latest()->get();
        return view('dashboard.admin.pickup-locations', compact('locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'fee' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        PickupLocation::create([
            'name' => $validated['name'],
            'fee' => $validated['fee'],
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Pickup location added successfully.');
    }

    public function update(Request $request, PickupLocation $pickupLocation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'fee' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $pickupLocation->update([
            'name' => $validated['name'],
            'fee' => $validated['fee'],
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Pickup location updated successfully.');
    }

    public function destroy(PickupLocation $pickupLocation)
    {
        $pickupLocation->delete();
        return back()->with('success', 'Pickup location deleted successfully.');
    }
}
