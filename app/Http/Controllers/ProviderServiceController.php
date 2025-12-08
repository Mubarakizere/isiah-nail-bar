<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class ProviderServiceController extends Controller
{
    use AuthorizesRequests;
    /**
     * Show the request form.
     */
    public function create()
    {
        return view('dashboard.provider.services.request');
    }

    /**
     * Handle form submission and store requested service.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:5',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048',
        ]);

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('services', 'public')
            : null;

        $provider = Auth::user()->provider;

        $service = new Service();
        $service->name = $request->name;
        $service->description = $request->description;
        $service->price = $request->price;
        $service->duration_minutes = $request->duration_minutes;
        $service->category = $request->category;
        $service->image = $imagePath;
        $service->provider_id = $provider->id;
        $service->approved = false; // Admin needs to approve
        $service->save();

        return redirect()
            ->route('provider.services.index')
            ->with('success', 'Service request submitted and waiting for approval!');
    }
    public function edit(Service $service)
{
    $this->authorize('update', $service); // Optional: Add policy if needed
    return view('dashboard.provider.services.edit', compact('service'));
}

public function update(Request $request, Service $service)
{
    $this->authorize('update', $service); // Optional

    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'required|string',
        'price' => 'required|numeric|min:0',
        'duration_minutes' => 'required|integer|min:0',
        'category' => 'required|string|max:255',
        'image' => 'nullable|image|max:2048',
    ]);

    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('services', 'public');
        $service->image = $imagePath;
    }

    $service->update($request->only([
        'name', 'description', 'price', 'duration_minutes', 'category'
    ]));

    $service->approved = false; // Mark for re-approval
    $service->save();

    return redirect()->route('provider.services.index')->with('success', 'Service updated and sent for re-approval.');
}

public function destroy(Service $service)
{
    $this->authorize('delete', $service); // Optional
    $service->delete();

    return redirect()->route('provider.services.index')->with('success', 'Service deleted.');
}

}
