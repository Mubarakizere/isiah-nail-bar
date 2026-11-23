<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BlockedSlot;
use App\Models\Booking;
use App\Models\Provider;
use App\Models\ProviderWorkingHour;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminCalendarApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'start' => ['required', 'date_format:Y-m-d'],
            'end' => ['required', 'date_format:Y-m-d', 'after_or_equal:start'],
            'provider_id' => ['nullable', 'integer', 'exists:providers,id'],
        ]);

        $start = Carbon::createFromFormat('Y-m-d', $filters['start'], config('app.timezone'))->startOfDay();
        $end = Carbon::createFromFormat('Y-m-d', $filters['end'], config('app.timezone'))->endOfDay();
        if ($start->diffInDays($end) > 31) {
            throw ValidationException::withMessages(['end' => 'Calendar range cannot exceed 32 days.']);
        }

        $providerId = isset($filters['provider_id']) ? (int) $filters['provider_id'] : null;
        $providers = Provider::query()
            ->with('services:id')
            ->when($providerId, fn ($query) => $query->whereKey($providerId))
            ->orderBy('name')
            ->get(['id', 'name', 'active']);

        $bookings = Booking::query()
            ->with(['services', 'provider', 'customer.user', 'payments', 'pickupLocation'])
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->when($providerId, fn ($query) => $query->where('provider_id', $providerId))
            ->orderBy('date')
            ->orderBy('time')
            ->orderBy('id')
            ->get();

        $events = $bookings->map(function (Booking $booking): array {
            $services = $booking->services->map(fn ($service) => [
                'id' => (int) $service->id,
                'name' => $service->name,
                'price' => (float) ($service->pivot?->price_at_booking ?? $service->price),
                'duration_minutes' => (int) ($service->pivot?->duration_minutes_at_booking ?? $service->duration_minutes ?? 60),
            ])->values();

            $serviceTotal = (float) $services->sum('price');
            $duration = max(15, (int) ($services->sum('duration_minutes') ?: 60));
            $total = ($booking->is_home_service ? $serviceTotal * 2 : $serviceTotal)
                + (float) ($booking->pickup_fee ?? 0);
            $paid = (float) $booking->payments->where('status', 'paid')->sum('amount');
            $latestPayment = $booking->payments->sortByDesc('created_at')->first();
            $paymentStatus = $total > 0 && $paid >= $total
                ? 'paid'
                : ($paid > 0
                    ? 'deposit'
                    : ($latestPayment?->status === 'failed'
                        ? 'failed'
                        : ($latestPayment?->status === 'pending' ? 'pending' : 'unpaid')));

            $date = Carbon::parse($booking->date)->toDateString();
            $time = substr((string) $booking->time, 0, 5);
            $startAt = Carbon::parse($date.' '.$time, config('app.timezone'));

            return [
                'id' => (int) $booking->id,
                'reference' => $booking->reference ?: 'INB-'.str_pad((string) $booking->id, 5, '0', STR_PAD_LEFT),
                'date' => $date,
                'time' => $time,
                'end_time' => $startAt->copy()->addMinutes($duration)->format('H:i'),
                'duration_minutes' => $duration,
                'status' => $booking->status,
                'source' => $booking->source ?: 'legacy',
                'provider_id' => $booking->provider_id ? (int) $booking->provider_id : null,
                'provider_name' => $booking->provider?->name ?? 'Unassigned',
                'customer_name' => $booking->customer?->name ?? $booking->customer?->user?->name ?? 'Guest Customer',
                'customer_email' => $booking->customer?->email ?? $booking->customer?->user?->email,
                'customer_phone' => $booking->customer?->phone ?? $booking->customer?->user?->phone,
                'services' => $services,
                'total_price' => $total,
                'total_paid' => $paid,
                'balance' => max(0, $total - $paid),
                'payment_status' => $paymentStatus,
                'payment_option' => $booking->payment_option,
                'deposit_amount' => (float) ($booking->deposit_amount ?? 0),
                'notes' => $booking->notes,
                'is_home_service' => (bool) $booking->is_home_service,
                'address' => $booking->address,
                'pickup_location_name' => $booking->pickupLocation?->name,
                'pickup_fee' => (float) ($booking->pickup_fee ?? 0),
            ];
        })->values();

        $providerIds = $providers->pluck('id')->all();
        $blockedSlots = BlockedSlot::query()
            ->with('provider:id,name')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->when($providerId, fn ($query) => $query->where('provider_id', $providerId))
            ->orderBy('date')
            ->orderBy('time')
            ->get()
            ->map(fn (BlockedSlot $slot) => [
                'id' => (int) $slot->id,
                'provider_id' => (int) $slot->provider_id,
                'provider_name' => $slot->provider?->name ?? 'Provider',
                'date' => Carbon::parse($slot->date)->toDateString(),
                'time' => substr((string) $slot->time, 0, 5),
            ])
            ->values();

        $workingHours = ProviderWorkingHour::query()
            ->whereIn('provider_id', $providerIds)
            ->orderBy('provider_id')
            ->orderBy('day_of_week')
            ->orderByDesc('id')
            ->get()
            ->unique(fn (ProviderWorkingHour $hour) => $hour->provider_id.'-'.$hour->day_of_week)
            ->map(fn (ProviderWorkingHour $hour) => [
                'provider_id' => (int) $hour->provider_id,
                'day_of_week' => (int) $hour->day_of_week,
                'start_time' => $hour->start_time ? substr((string) $hour->start_time, 0, 5) : null,
                'end_time' => $hour->end_time ? substr((string) $hour->end_time, 0, 5) : null,
                'break_start' => $hour->break_start ? substr((string) $hour->break_start, 0, 5) : null,
                'break_end' => $hour->break_end ? substr((string) $hour->break_end, 0, 5) : null,
                'is_day_off' => (bool) $hour->is_day_off,
                'is_holiday' => (bool) $hour->is_holiday,
            ])
            ->values();

        return response()->json([
            'success' => true,
            'range' => ['start' => $start->toDateString(), 'end' => $end->toDateString()],
            'providers' => $providers->map(fn (Provider $provider) => [
                'id' => (int) $provider->id,
                'name' => $provider->name,
                'active' => (bool) $provider->active,
                'service_ids' => $provider->services->pluck('id')->map(fn ($id) => (int) $id)->values(),
            ])->values(),
            'events' => $events,
            'blocked_slots' => $blockedSlots,
            'working_hours' => $workingHours,
            'summary' => [
                'bookings' => $events->count(),
                'walk_ins' => $events->where('source', 'walk_in')->count(),
                'pending' => $events->where('status', 'pending')->count(),
                'in_progress' => $events->where('status', 'in_progress')->count(),
                'blocked_slots' => $blockedSlots->count(),
            ],
        ]);
    }
}
