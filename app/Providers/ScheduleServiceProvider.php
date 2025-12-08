<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use App\Models\Booking;
use App\Jobs\SendBookingReminder;
use Illuminate\Support\Facades\Log;

class ScheduleServiceProvider extends ServiceProvider
{
    public function boot(Schedule $schedule)
    {
        // Existing auto-cancel job
        $schedule->command('bookings:auto-cancel')->everyMinute();

        // 🕒 Send reminders 15 minutes before
        $schedule->call(function () {
            $now = now();
            $targetTime = $now->copy()->addMinutes(15)->format('H:i');

            $bookings = Booking::where('date', $now->toDateString())
                ->where('time', $targetTime)
                ->where('status', 'accepted')
                ->get();

            if ($bookings->isEmpty()) {
                Log::info("⏳ No bookings found for {$targetTime}");
            }

            foreach ($bookings as $booking) {
                dispatch(new SendBookingReminder($booking));
                Log::info("⏰ Dispatched reminder for Booking ID {$booking->id}");
            }
        })->everyMinute();
    }
}
