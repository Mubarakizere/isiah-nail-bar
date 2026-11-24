<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Jobs\SendBookingReminder;
use Carbon\Carbon;

class SendBookingReminders extends Command
{
    protected $signature = 'bookings:send-reminders';
    protected $description = 'Queue reminders for accepted appointments starting within 15 minutes';

    public function handle(): int
    {
        $now = now(config('app.timezone'));
        $windowEnd = $now->copy()->addMinutes(15);
        $queued = 0;

        Booking::query()
            ->where('status', 'accepted')
            ->where('reminder_sent', false)
            ->whereNull('reminder_failed_at')
            ->whereDate('date', '>=', $now->toDateString())
            ->whereDate('date', '<=', $windowEnd->toDateString())
            ->chunkById(100, function ($bookings) use ($now, $windowEnd, &$queued): void {
                foreach ($bookings as $booking) {
                    try {
                        $startsAt = Carbon::parse(
                            $booking->date.' '.$booking->time,
                            config('app.timezone')
                        );
                    } catch (\Throwable $exception) {
                        $this->warn("Skipping booking {$booking->id}: invalid appointment date or time.");
                        continue;
                    }

                    if (!$startsAt->gt($now) || !$startsAt->lte($windowEnd)) {
                        continue;
                    }

                    try {
                        SendBookingReminder::dispatchSync((int) $booking->id);
                    } catch (\Throwable $dispatchException) {
                        SendBookingReminder::dispatch((int) $booking->id)->onQueue('default');
                    }
                    $queued++;
                }
            });

        $this->info("Queued {$queued} eligible appointment reminder(s).");

        return self::SUCCESS;
    }
}
