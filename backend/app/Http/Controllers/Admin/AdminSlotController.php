<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Provider, ProviderWorkingHour, Booking, BlockedSlot};
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AdminSlotController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'provider_id' => ['nullable', 'integer', 'exists:providers,id'],
            'date' => ['nullable', 'date'],
        ]);

        $providers = Provider::orderBy('name')->get(['id', 'name', 'active']);
        $selectedDate = $request->query('date', now()->toDateString());
        $providerId = $request->query('provider_id');

        $slots = [];
        $provider = null;
        $workingHour = null;
        $unavailableReason = null;

        if ($providerId) {
            $provider = Provider::findOrFail($providerId);
            $dayOfWeek = Carbon::parse($selectedDate)->dayOfWeek;

            $workingHour = ProviderWorkingHour::where('provider_id', $providerId)
                ->where('day_of_week', $dayOfWeek)
                ->first();

            if (!$workingHour) {
                $unavailableReason = 'missing_working_hours';
            } elseif ($workingHour->is_day_off) {
                $unavailableReason = 'day_off';
            } elseif ($workingHour->is_holiday) {
                $unavailableReason = 'holiday';
            } else {
                $start = Carbon::parse($selectedDate.' '.$workingHour->start_time);
                $end = Carbon::parse($selectedDate.' '.$workingHour->end_time);
                $slotDuration = 30;
                $maxBookingsPerSlot = 1;

                $bookings = Booking::with(['services:id,duration_minutes'])
                    ->where('provider_id', $providerId)
                    ->where('date', $selectedDate)
                    ->whereIn('status', ['pending', 'accepted', 'confirmed', 'in_progress', 'completed'])
                    ->get();

                // Fetch blocked slots
                $blocked = BlockedSlot::where('provider_id', $providerId)
                    ->where('date', $selectedDate)
                    ->pluck('time')
                    ->map(fn($t) => Carbon::parse($t)->format('H:i'))
                    ->toArray();

                // Build slots
                while ($start->lt($end)) {
                    $time = $start->format('H:i');
                    $slotStart = $start->copy();
                    $slotEnd = $slotStart->copy()->addMinutes($slotDuration);
                    if ($slotEnd->gt($end)) {
                        break;
                    }

                    $bookingCount = $bookings->filter(function (Booking $booking) use ($selectedDate, $slotStart, $slotEnd) {
                        $bookingStart = Carbon::parse($selectedDate.' '.$booking->time);
                        $duration = max(15, (int) $booking->services->sum(
                            fn ($service) => $service->pivot?->duration_minutes_at_booking ?? $service->duration_minutes ?? 60
                        ));
                        $bookingEnd = $bookingStart->copy()->addMinutes($duration);
                        return $slotStart->lt($bookingEnd) && $slotEnd->gt($bookingStart);
                    })->count();

                    $overlapsBreak = $workingHour->break_start && $workingHour->break_end
                        && $slotStart->lt(Carbon::parse($selectedDate.' '.$workingHour->break_end))
                        && $slotEnd->gt(Carbon::parse($selectedDate.' '.$workingHour->break_start));
                    $status = 'available';
                    $reason = null;

                    if (in_array($time, $blocked)) {
                        $status = 'blocked';
                    } elseif ($overlapsBreak) {
                        $status = 'unavailable';
                        $reason = 'provider_break';
                    } elseif ($bookingCount >= $maxBookingsPerSlot) {
                        $status = 'fully_booked';
                    }

                    $slots[] = [
                        'time' => $time,
                        'formatted' => $start->format('h:i A'),
                        'status' => $status,
                        'count' => $bookingCount,
                        'capacity' => $maxBookingsPerSlot,
                        'reason' => $reason,
                    ];

                    $start->addMinutes($slotDuration);
                }
            }
        }

        return response()->json([
            'success' => true,
            'providers' => $providers->map(fn (Provider $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'active' => (bool) $item->active,
            ])->values(),
            'selected_date' => $selectedDate,
            'selected_provider_id' => $provider?->id,
            'provider' => $provider ? ['id' => $provider->id, 'name' => $provider->name] : null,
            'working_hours' => $workingHour ? [
                'start_time' => substr((string) $workingHour->start_time, 0, 5),
                'end_time' => substr((string) $workingHour->end_time, 0, 5),
                'break_start' => $workingHour->break_start ? substr((string) $workingHour->break_start, 0, 5) : null,
                'break_end' => $workingHour->break_end ? substr((string) $workingHour->break_end, 0, 5) : null,
                'is_day_off' => (bool) $workingHour->is_day_off,
                'is_holiday' => (bool) $workingHour->is_holiday,
            ] : null,
            'unavailable_reason' => $unavailableReason,
            'slots' => $slots,
        ]);
    }

    public function block(Request $request)
    {
        $request->validate([
            'provider_id' => 'required|exists:providers,id',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
        ]);

        $exists = BlockedSlot::where('provider_id', $request->provider_id)
            ->where('date', $request->date)
            ->where('time', $request->time)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This slot is already blocked.',
            ], 409);
        }

        $this->assertSlotCanBeBlocked((int) $request->provider_id, $request->date, $request->time);

        BlockedSlot::create($request->only('provider_id', 'date', 'time'));

        return response()->json([
            'success' => true,
            'message' => 'Slot blocked successfully.',
        ]);
    }

    public function unblock(Request $request)
    {
        $request->validate([
            'provider_id' => 'required|exists:providers,id',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
        ]);

        BlockedSlot::where('provider_id', $request->provider_id)
            ->where('date', $request->date)
            ->where('time', $request->time)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Slot unblocked successfully.',
        ]);
    }

    private function assertSlotCanBeBlocked(int $providerId, string $date, string $time): void
    {
        $hours = ProviderWorkingHour::query()
            ->where('provider_id', $providerId)
            ->where('day_of_week', Carbon::parse($date)->dayOfWeek)
            ->orderByDesc('id')
            ->first();

        if (!$hours || $hours->is_day_off || $hours->is_holiday || !$hours->start_time || !$hours->end_time) {
            throw ValidationException::withMessages(['time' => 'This provider has no working hours on that date.']);
        }

        $start = Carbon::parse($date.' '.$time);
        $end = $start->copy()->addMinutes(30);
        $opens = Carbon::parse($date.' '.$hours->start_time);
        $closes = Carbon::parse($date.' '.$hours->end_time);
        if ($start->lt($opens) || $end->gt($closes)) {
            throw ValidationException::withMessages(['time' => 'Only working-time slots can be blocked.']);
        }

        if ($hours->break_start && $hours->break_end) {
            $breakStart = Carbon::parse($date.' '.$hours->break_start);
            $breakEnd = Carbon::parse($date.' '.$hours->break_end);
            if ($start->lt($breakEnd) && $end->gt($breakStart)) {
                throw ValidationException::withMessages(['time' => 'That time is already unavailable during the provider’s break.']);
            }
        }

        $bookings = Booking::query()
            ->with(['services:id,duration_minutes'])
            ->where('provider_id', $providerId)
            ->whereDate('date', $date)
            ->whereIn('status', ['pending', 'accepted', 'confirmed', 'in_progress', 'completed'])
            ->get();

        foreach ($bookings as $booking) {
            $bookingStart = Carbon::parse($date.' '.$booking->time);
            $duration = max(15, (int) $booking->services->sum(
                fn ($service) => $service->pivot?->duration_minutes_at_booking ?? $service->duration_minutes ?? 60
            ));
            $bookingEnd = $bookingStart->copy()->addMinutes($duration);
            if ($start->lt($bookingEnd) && $end->gt($bookingStart)) {
                throw ValidationException::withMessages(['time' => 'That time overlaps an existing appointment.']);
            }
        }
    }
}
