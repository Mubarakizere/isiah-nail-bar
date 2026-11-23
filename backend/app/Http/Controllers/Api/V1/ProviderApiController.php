<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Http\Request;

class ProviderApiController extends Controller
{
    public function overview(Request $request)
    {
        $provider = $this->providerFor($request->user());
        $bookings = Booking::query()
            ->with(['services:id,name,price,duration_minutes', 'customer.user:id,name,email'])
            ->where('provider_id', $provider->id)
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->limit(100)
            ->get();

        $statusCounts = Booking::query()
            ->where('provider_id', $provider->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $completedBookings = Booking::query()
            ->with('services:id,price')
            ->where('provider_id', $provider->id)
            ->where('status', 'completed')
            ->get();

        return response()->json([
            'success' => true,
            'provider' => ['id' => $provider->id, 'name' => $provider->name],
            'stats' => [
                'total' => (int) $statusCounts->sum(),
                'pending' => (int) ($statusCounts['pending'] ?? 0),
                'upcoming' => (int) (($statusCounts['accepted'] ?? 0) + ($statusCounts['in_progress'] ?? 0)),
                'completed' => (int) ($statusCounts['completed'] ?? 0),
                'revenue' => (float) $completedBookings->sum(fn (Booking $booking) => $booking->services->sum('price')),
            ],
            'data' => $bookings->map(fn (Booking $booking) => $this->serializeBooking($booking))->values(),
        ]);
    }

    public function updateBookingStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:accept,decline,complete'],
        ]);
        $provider = $this->providerFor($request->user());
        $booking = Booking::query()
            ->with(['services:id,name,price,duration_minutes', 'customer.user:id,name,email'])
            ->where('provider_id', $provider->id)
            ->findOrFail($id);

        $transitions = [
            'accept' => ['pending' => 'accepted'],
            'decline' => ['pending' => 'declined'],
            'complete' => ['accepted' => 'completed', 'in_progress' => 'completed'],
        ];
        $nextStatus = $transitions[$validated['action']][$booking->status] ?? null;

        if (!$nextStatus) {
            return response()->json([
                'success' => false,
                'message' => 'That action is not available for this booking status.',
            ], 422);
        }

        $booking->update(['status' => $nextStatus]);

        if ($nextStatus === 'completed' && $booking->customer) {
            try {
                app(\App\Services\LoyaltyService::class)->awardStamp(
                    $booking->customer,
                    $booking,
                    "Completed appointment with {$provider->name}",
                    $request->user()
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Loyalty stamp award failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully.',
            'data' => $this->serializeBooking($booking->fresh(['services:id,name,price,duration_minutes', 'customer.user:id,name,email'])),
        ]);
    }

    private function providerFor(?User $user): Provider
    {
        abort_if(!$user, 401, 'Authentication is required.');
        abort_unless($user->provider, 403, 'Provider profile not found.');

        return $user->provider;
    }

    private function serializeBooking(Booking $booking): array
    {
        return [
            'id' => $booking->id,
            'reference' => $booking->reference ?: 'INB-'.str_pad((string) $booking->id, 5, '0', STR_PAD_LEFT),
            'date' => (string) $booking->date,
            'time' => $booking->time ? substr((string) $booking->time, 0, 5) : '',
            'status' => $booking->status,
            'customer' => $booking->customer?->user?->name ?? $booking->customer?->name ?? 'Guest',
            'customer_email' => $booking->customer?->user?->email ?? $booking->customer?->email ?? '',
            'customer_phone' => $booking->customer?->phone ?? '',
            'services' => $booking->services->map(fn ($service) => [
                'id' => $service->id,
                'name' => $service->name,
                'price' => (float) $service->price,
                'duration_minutes' => (int) $service->duration_minutes,
            ])->values(),
            'notes' => $booking->notes,
        ];
    }
}
