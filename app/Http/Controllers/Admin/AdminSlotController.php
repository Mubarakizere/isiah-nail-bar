<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Provider, ProviderWorkingHour, Booking, BlockedSlot};
use Carbon\Carbon;

class AdminSlotController extends Controller
{
    public function index(Request $request)
    {
        $providers = Provider::all();
        $selectedDate = $request->query('date', now()->toDateString());
        $providerId = $request->query('provider_id');

        $slots = [];
        $provider = null;

        if ($providerId) {
            $provider = Provider::findOrFail($providerId);
            $dayOfWeek = Carbon::parse($selectedDate)->dayOfWeek;

            $workingHour = ProviderWorkingHour::where('provider_id', $providerId)
                ->where('day_of_week', $dayOfWeek)
                ->first();

            if ($workingHour && !$workingHour->is_day_off && !$workingHour->is_holiday) {
                $start = Carbon::createFromFormat('H:i:s', $workingHour->start_time);
                $end = Carbon::createFromFormat('H:i:s', $workingHour->end_time);
                $slotDuration = 60; // 1 hour
                $maxBookingsPerSlot = 3;

                // Fetch bookings
                $bookings = Booking::where('provider_id', $providerId)
                    ->where('date', $selectedDate)
                    ->where('status', 'accepted')
                    ->get();

                $bookingCounts = [];
                foreach ($bookings as $booking) {
                    $time = Carbon::parse($booking->time)->format('H:i');
                    $bookingCounts[$time] = ($bookingCounts[$time] ?? 0) + 1;
                }

                // Fetch blocked slots
                $blocked = BlockedSlot::where('provider_id', $providerId)
                    ->where('date', $selectedDate)
                    ->pluck('time')
                    ->map(fn($t) => Carbon::parse($t)->format('H:i'))
                    ->toArray();

                // Build slots
                while ($start->lt($end)) {
                    $time = $start->format('H:i');
                    $status = 'available';

                    if (in_array($time, $blocked)) {
                        $status = 'blocked';
                    } elseif (($bookingCounts[$time] ?? 0) >= $maxBookingsPerSlot) {
                        $status = 'fully_booked';
                    }

                    $slots[] = [
                        'time' => $time,
                        'formatted' => $start->format('h:i A'),
                        'status' => $status,
                        'count' => $bookingCounts[$time] ?? 0,
                    ];

                    $start->addMinutes($slotDuration);
                }
            }
        }

        return view('admin.slots.index', compact(
            'providers',
            'selectedDate',
            'providerId',
            'slots',
            'provider'
        ));
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
            return back()->with('warning', 'This slot is already blocked.');
        }

        BlockedSlot::create($request->only('provider_id', 'date', 'time'));

        return back()->with('success', 'Slot blocked successfully.');
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

        return back()->with('success', 'Slot unblocked successfully.');
    }
}
