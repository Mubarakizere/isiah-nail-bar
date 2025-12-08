<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Notifications\ServiceStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PendingServiceController extends Controller
{
    public function index()
    {
        $pendingServices = Service::where('approved', false)->latest()->paginate(10);
        return view('dashboard.admin.services.pending', compact('pendingServices'));
    }
    public function approve(Service $service)
    {
        Log::info("Approving service: " . $service->id);

        $service->update(['approved' => true]);

        if ($service->provider?->user) {
            $service->provider->user->notify(new ServiceStatusNotification($service->name, 'approved'));

        }

        return back()->with('success', 'Service approved and provider notified.');
    }


    public function reject(Service $service)
    {
        $service->delete(); // or update with `->update(['approved' => false])`

        if ($service->provider?->user) {
            $service->provider->user->notify(new ServiceStatusNotification($service->name, 'rejected'));
        }

        return back()->with('success', 'Service rejected and provider notified.');
    }

}
