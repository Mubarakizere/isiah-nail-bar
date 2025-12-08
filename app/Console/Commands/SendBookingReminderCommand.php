<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Jobs\SendBookingReminder;

class SendBookingReminderCommand extends Command
{
    protected $signature = 'booking:send-reminder {bookingId}';
    protected $description = 'Send SMS and email reminder for a specific booking ID';

    public function handle()
    {
        $id = $this->argument('bookingId');

        $booking = Booking::with(['customer.user', 'services', 'provider'])->find($id);

        if (!$booking) {
            $this->error("❌ Booking ID {$id} not found.");
            return 1;
        }

        dispatch(new SendBookingReminder($booking));

        // Optional: mark reminder_sent = 1
        $booking->reminder_sent = 1;
        $booking->save();

        $this->info("✅ Reminder dispatched for Booking ID: {$id}");

        return 0;
    }
}
