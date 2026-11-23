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

    public function handle(): int
    {
        $now = Carbon::now(config('app.timezone'));
        $cancelled = 0;

        Booking::query()
            ->where('status', 'pending')
            ->where('payment_option', 'deposit')
            ->where('is_fully_paid', false)
            ->chunkById(100, function ($bookings) use ($now, &$cancelled): void {
                foreach ($bookings as $booking) {
                    try {
                        $bookingTime = Carbon::parse(
                            $booking->date.' '.$booking->time,
                            config('app.timezone')
                        );
                    } catch (\Throwable $exception) {
                        \Log::warning('Unpaid booking could not be auto-cancelled due to invalid appointment time.', [
                            'booking_id' => $booking->id,
                            'exception' => $exception::class,
                        ]);
                        continue;
                    }

                    if ($bookingTime->copy()->addMinutes(15)->gte($now)) {
                        continue;
                    }

                    $updated = Booking::query()
                        ->whereKey($booking->id)
                        ->where('status', 'pending')
                        ->where('payment_option', 'deposit')
                        ->where('is_fully_paid', false)
                        ->update([
                            'status' => 'cancelled',
                            'notes' => 'Auto-cancelled due to no-show.',
                            'updated_at' => now(),
                        ]);

                    $cancelled += $updated;
                }
            });

        $this->info("Auto-cancelled {$cancelled} overdue unpaid booking(s).");

        return self::SUCCESS;
    }
}
