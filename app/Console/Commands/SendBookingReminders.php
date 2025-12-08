<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class SendBookingReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Send booking reminders via email and SMS';

    public function handle()
    {
        $now = Carbon::now();
        $reminderTime = $now->copy()->addMinutes(30)->format('H:i');
        $date = $now->toDateString();

        $bookings = Booking::with(['customer.user', 'provider.user'])
            ->where('date', $date)
            ->where('time', $reminderTime)
            ->where('status', 'confirmed')
            ->get();

        foreach ($bookings as $booking) {
            $customer = $booking->customer->user;

            // Email
            if ($customer->email) {
                Mail::raw("Hello {$customer->name}, your appointment is today at {$booking->time}.", function ($message) use ($customer) {
                    $message->to($customer->email)
                            ->subject('Booking Reminder');
                });
            }

            // SMS (example with Route Mobile API)
            if ($customer->phone) {
                Http::withHeaders([
                    'Authorization' => 'Bearer YOUR_API_KEY',
                ])->post('https://api.rmlconnect.net/bulksms/bulksms', [
                    'sender' => 'ISIAHNB',
                    'route' => '4', // Transactional route
                    'country' => 'RW',
                    'sms' => [[
                        'to' => $customer->phone,
                        'message' => "Hello {$customer->name}, this is a reminder for your appointment at {$booking->time} today. - Isiah Nail Bar",
                    ]],
                ]);
            }
        }

        $this->info("Sent reminders to {$bookings->count()} confirmed bookings.");
    }
}
