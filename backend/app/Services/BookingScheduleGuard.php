<?php

namespace App\Services;

use App\Models\BlockedSlot;
use App\Models\Booking;
use App\Models\Provider;
use App\Models\ProviderWorkingHour;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingScheduleGuard
{
    public function assertProviderOffersServices(int $providerId, array $serviceIds): void
    {
        $serviceIds = array_values(array_unique(array_map('intval', $serviceIds)));
        if ($serviceIds === []) {
            return;
        }

        $offeredCount = DB::table('provider_service')
            ->where('provider_id', $providerId)
            ->whereIn('service_id', $serviceIds)
            ->distinct()
            ->count('service_id');

        if ($offeredCount !== count($serviceIds)) {
            throw ValidationException::withMessages([
                'provider_id' => 'The selected provider is not assigned to every service in this booking.',
            ]);
        }
    }

    public function assertAvailable(
        Provider $provider,
        string $date,
        string $time,
        int $durationMinutes,
        ?int $ignoreBookingId = null
    ): void {
        $start = Carbon::parse($date.' '.$time, config('app.timezone'));
        $end = $start->copy()->addMinutes(max(15, $durationMinutes));

        if ($date === now(config('app.timezone'))->toDateString() && $start->lte(now(config('app.timezone'))) ) {
            throw ValidationException::withMessages(['time' => 'Choose a future time for this appointment.']);
        }

        $hours = ProviderWorkingHour::query()
            ->where('provider_id', $provider->id)
            ->where('day_of_week', Carbon::parse($date)->dayOfWeek)
            ->orderByDesc('id')
            ->first();

        if (!$hours || $hours->is_day_off || $hours->is_holiday || !$hours->start_time || !$hours->end_time) {
            throw ValidationException::withMessages(['date' => 'The selected provider is unavailable on this date.']);
        }

        $opens = Carbon::parse($date.' '.$hours->start_time, config('app.timezone'));
        $closes = Carbon::parse($date.' '.$hours->end_time, config('app.timezone'));
        if ($start->lt($opens) || $end->gt($closes)) {
            throw ValidationException::withMessages(['time' => 'The appointment must fit inside the provider’s working hours.']);
        }

        if ($hours->break_start && $hours->break_end) {
            $breakStart = Carbon::parse($date.' '.$hours->break_start, config('app.timezone'));
            $breakEnd = Carbon::parse($date.' '.$hours->break_end, config('app.timezone'));
            if ($start->lt($breakEnd) && $end->gt($breakStart)) {
                throw ValidationException::withMessages(['time' => 'The appointment overlaps the provider’s break.']);
            }
        }

        $blockedSlots = BlockedSlot::query()
            ->where('provider_id', $provider->id)
            ->whereDate('date', $date)
            ->get(['time']);

        foreach ($blockedSlots as $blockedSlot) {
            $blockedStart = Carbon::parse($date.' '.$blockedSlot->time, config('app.timezone'));
            $blockedEnd = $blockedStart->copy()->addMinutes(30);
            if ($start->lt($blockedEnd) && $end->gt($blockedStart)) {
                throw ValidationException::withMessages(['time' => 'The appointment overlaps a blocked time slot.']);
            }
        }

        $this->assertNoOverlap($provider, $date, $time, $durationMinutes, $ignoreBookingId);
    }

    /**
     * Reject appointments already occupying any part of a provider's service window.
     * Walk-in recording uses this without the future-time and working-hours checks because
     * staff may be documenting a visit that has already started or finished.
     *
     * @param array<int, string> $blockingStatuses
     */
    public function assertNoOverlap(
        Provider $provider,
        string $date,
        string $time,
        int $durationMinutes,
        ?int $ignoreBookingId = null,
        array $blockingStatuses = ['pending', 'accepted', 'confirmed', 'in_progress']
    ): void {
        $start = Carbon::parse($date.' '.$time, config('app.timezone'));
        $end = $start->copy()->addMinutes(max(15, $durationMinutes));

        $existingBookings = Booking::query()
            ->with(['services:id,duration_minutes'])
            ->where('provider_id', $provider->id)
            ->whereDate('date', $date)
            ->whereIn('status', $blockingStatuses)
            ->when($ignoreBookingId, fn ($query) => $query->where('id', '!=', $ignoreBookingId))
            ->get();

        foreach ($existingBookings as $booking) {
            $bookingStart = Carbon::parse($date.' '.$booking->time, config('app.timezone'));
            $bookingDuration = max(15, (int) $booking->services->sum(
                fn ($service) => $service->pivot?->duration_minutes_at_booking ?? $service->duration_minutes ?? 60
            ));
            $bookingEnd = $bookingStart->copy()->addMinutes($bookingDuration);

            if ($start->lt($bookingEnd) && $end->gt($bookingStart)) {
                throw ValidationException::withMessages(['time' => 'This provider already has an appointment during that time.']);
            }
        }
    }
}
