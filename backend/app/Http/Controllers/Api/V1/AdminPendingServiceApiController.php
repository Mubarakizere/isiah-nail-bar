<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Notifications\ServiceStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminPendingServiceApiController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['page' => ['nullable', 'integer', 'min:1']]);

        $services = Service::query()
            ->where('approved', false)
            ->with(['category:id,name', 'provider.user'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $services->getCollection()
                ->map(fn (Service $service) => $this->serializeService($service))
                ->values(),
            'meta' => [
                'current_page' => $services->currentPage(),
                'last_page' => $services->lastPage(),
                'per_page' => $services->perPage(),
                'total' => $services->total(),
            ],
        ]);
    }

    public function approve(Service $service)
    {
        $pendingService = Service::query()
            ->where('approved', false)
            ->with('provider.user')
            ->findOrFail($service->id);

        $pendingService->update(['approved' => true]);

        $notificationSent = true;
        try {
            $pendingService->provider?->user?->notify(
                new ServiceStatusNotification($pendingService->name, 'approved')
            );
        } catch (\Throwable $exception) {
            report($exception);
            $notificationSent = false;
        }

        return response()->json([
            'success' => true,
            'message' => $notificationSent
                ? 'Service approved and provider notified.'
                : 'Service approved, but the provider notification could not be sent.',
        ]);
    }

    public function reject(Service $service)
    {
        $pendingService = Service::query()
            ->where('approved', false)
            ->with('provider.user')
            ->findOrFail($service->id);

        $serviceName = $pendingService->name;
        $providerUser = $pendingService->provider?->user;
        $image = $pendingService->image;

        $pendingService->tags()->delete();
        $pendingService->delete();
        if ($image && !str_starts_with($image, 'http://') && !str_starts_with($image, 'https://')) {
            Storage::disk('public')->delete($image);
        }

        $notificationSent = true;
        try {
            $providerUser?->notify(new ServiceStatusNotification($serviceName, 'rejected'));
        } catch (\Throwable $exception) {
            report($exception);
            $notificationSent = false;
        }

        return response()->json([
            'success' => true,
            'message' => $notificationSent
                ? 'Service rejected and removed. Provider notified.'
                : 'Service rejected and removed, but the provider notification could not be sent.',
        ]);
    }

    private function serializeService(Service $service): array
    {
        $image = $service->image;
        $imageUrl = $image
            ? (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')
                ? $image
                : Storage::disk('public')->url($image))
            : null;

        return [
            'id' => $service->id,
            'name' => $service->name,
            'description' => $service->description,
            'price' => (float) $service->price,
            'duration_minutes' => (int) $service->duration_minutes,
            'category' => $service->category
                ? ['id' => $service->category->id, 'name' => $service->category->name]
                : null,
            'provider_name' => $service->provider?->user?->name
                ?? $service->provider?->name
                ?? 'Unknown provider',
            'image_url' => $imageUrl,
            'created_at' => $service->created_at?->toISOString(),
            'created_ago' => $service->created_at?->diffForHumans(),
        ];
    }
}
