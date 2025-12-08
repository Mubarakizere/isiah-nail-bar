<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use Carbon\Carbon;

class AutoCancelUnpaidBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Run using: php artisan bookings:auto-cancel
     */
    protected $signature = 'bookings:auto-cancel';

    /**
     * The console command description.
     */
    protected $description = 'Automatically cancels unpaid bookings with deposit if customer does not show up in time.';

    public function handle()
    {
        $now = Carbon::now(config('app.timezone'));

        $bookings = Booking::where('status', 'pending')
            ->where('payment_option', 'deposit')
            ->where('is_fully_paid', false)
            ->get();

        $cancelled = 0;

        foreach ($bookings as $booking) {
            // Combine date + time correctly
            $dateTimeString = $booking->date . ' ' . $booking->time; // Example: "2025-05-01 10:10:00"

            try {
                $bookingTime = Carbon::createFromFormat('Y-m-d H:i:s', $dateTimeString, config('app.timezone'));

                // If booking is over 15 minutes late
                if ($bookingTime->addMinutes(15)->lt($now)) {
                    $booking->status = 'cancelled';
                    $booking->notes = 'Auto-cancelled due to no-show.';
                    $booking->save();

                    $cancelled++;
                }
            } catch (\Exception $e) {
                \Log::error("Failed to parse booking time for ID {$booking->id}: " . $e->getMessage());
            }
        }

        $this->info("✅ Auto-cancelled {$cancelled} unpaid bookings.");
    }
}
