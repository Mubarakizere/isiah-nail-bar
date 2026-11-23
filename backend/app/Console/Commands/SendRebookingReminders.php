<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\CustomerRecall;
use App\Models\EmailLog;
use App\Mail\RebookingReminderMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendRebookingReminders extends Command
{
    protected $signature = 'bookings:send-rebooking-reminders';
    protected $description = 'Send 3-week rebooking email reminders to clients whose last appointment was 21 days ago';

    public function handle(): int
    {
        $now = now(config('app.timezone'));
        $targetDate = $now->copy()->subDays(21)->toDateString();
        $sentCount = 0;

        $this->info("Scanning completed bookings on {$targetDate} (21 days ago)...");

        // Find bookings on targetDate with status in accepted/completed
        $eligibleBookings = Booking::query()
            ->with(['customer.user', 'services', 'provider'])
            ->whereDate('date', $targetDate)
            ->whereIn('status', ['accepted', 'completed'])
            ->get();

        foreach ($eligibleBookings as $booking) {
            $customer = $booking->customer;
            if (!$customer) {
                continue;
            }

            // Check if client has future/upcoming booking (today onwards)
            $hasUpcomingBooking = Booking::query()
                ->where('customer_id', $customer->id)
                ->whereDate('date', '>=', $now->toDateString())
                ->whereIn('status', ['pending', 'accepted', 'completed'])
                ->exists();

            if ($hasUpcomingBooking) {
                $this->line("Skipping customer #{$customer->id}: has upcoming appointment scheduled.");
                continue;
            }

            // Check if recall record exists and whether client is marked do_not_contact or email already sent for this booking
            $recall = CustomerRecall::firstOrNew([
                'customer_id' => $customer->id,
                'last_booking_id' => $booking->id,
            ]);

            if ($recall->status === 'do_not_contact') {
                $this->line("Skipping customer #{$customer->id}: marked as do_not_contact.");
                continue;
            }

            if ($recall->email_reminder_sent_at) {
                $this->line("Skipping customer #{$customer->id}: rebooking email already sent on {$recall->email_reminder_sent_at}.");
                continue;
            }

            // Determine recipient email
            $email = $customer->email ?? $customer->user?->email;
            if (!$email) {
                $this->warn("Customer #{$customer->id} ({$customer->name}) has no email address. Recall logged for staff phone call.");
                $recall->save();
                continue;
            }

            try {
                Mail::to($email)->send(new RebookingReminderMail($booking));

                $recall->email_reminder_sent_at = now();
                $recall->save();

                EmailLog::create([
                    'recipient_email' => $email,
                    'recipient_name'  => $customer->name,
                    'recipient_type'  => 'customer',
                    'subject'         => "It's Been 3 Weeks! Time for a Fresh Set or Refill? - Isaiah Nail Bar",
                    'email_type'      => 'rebooking_reminder_3w',
                    'booking_id'      => $booking->id,
                    'status'          => 'sent',
                    'sent_at'         => now(),
                ]);

                $sentCount++;
                $this->info("Sent rebooking reminder email to {$customer->name} ({$email})");
            } catch (Throwable $e) {
                Log::error("Failed to send 3-week rebooking reminder to {$email}: " . $e->getMessage());

                EmailLog::create([
                    'recipient_email' => $email,
                    'recipient_name'  => $customer->name,
                    'recipient_type'  => 'customer',
                    'subject'         => "It's Been 3 Weeks! Time for a Fresh Set or Refill? - Isaiah Nail Bar",
                    'email_type'      => 'rebooking_reminder_3w',
                    'booking_id'      => $booking->id,
                    'status'          => 'failed',
                    'error_message'   => $e->getMessage(),
                ]);
            }
        }

        $this->info("Completed 3-week rebooking run. Sent: {$sentCount} email(s).");
        return self::SUCCESS;
    }
}
