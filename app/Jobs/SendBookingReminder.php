<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\TwilioService;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBookingReminder implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function handle(TwilioService $twilio)
    {
        Log::info("🔔 Reminder Job Triggered for Booking ID: {$this->booking->id}");

        $customer = $this->booking->customer;
        $user = $customer?->user;
        $provider = $this->booking->provider;

        $customerName = $user?->name ?? $customer?->name ?? 'Valued Customer';
        $email = $user?->email ?? null;
        $rawPhone = $customer?->phone ?? $user?->phone ?? null;

        $phone = $rawPhone
            ? (str_starts_with($rawPhone, '+') ? $rawPhone : '+250' . ltrim($rawPhone, '0'))
            : null;

        // Services summary
        $serviceNames = $this->booking->services->pluck('name')->implode(', ');
        $time = $this->booking->time;

        // 📤 SMS to Customer
        if ($phone) {
            try {
                $message = "Hi {$customerName}, your appointment for {$serviceNames} at {$time} today is in 15 minutes. – Isaiah Nail Bar";
                $twilio->sendSms($phone, $message);
                Log::info("✅ SMS sent to customer {$phone} for Booking ID {$this->booking->id}");
            } catch (\Exception $e) {
                Log::error("❌ SMS to customer failed for Booking ID {$this->booking->id}: " . $e->getMessage());
            }
        } else {
            Log::warning("⚠️ No customer phone for Booking ID {$this->booking->id}");
        }

        // 📤 Email to Customer
        if ($email) {
            try {
                Mail::to($email)->send(new \App\Mail\BookingReminderMail($this->booking));
                Log::info("✅ Email sent to customer {$email} for Booking ID {$this->booking->id}");
            } catch (\Exception $e) {
                Log::error("❌ Email to customer failed for Booking ID {$this->booking->id}: " . $e->getMessage());
            }
        } else {
            Log::warning("⚠️ No customer email for Booking ID {$this->booking->id}");
        }

        // ========================
        // 📢 Notify the Provider
        // ========================
        if ($provider) {
            $providerName = $provider->name ?? 'Provider';
            $providerPhone = $provider->phone ?? null;
            $providerEmail = $provider->email ?? null;

            $providerPhoneFormatted = $providerPhone
                ? (str_starts_with($providerPhone, '+') ? $providerPhone : '+250' . ltrim($providerPhone, '0'))
                : null;

            $providerMessage = "Heads-up {$providerName}, you have an appointment for {$serviceNames} with {$customerName} at {$time} today. – Isaiah Nail Bar";

            // 📞 SMS to Provider
            if ($providerPhoneFormatted) {
                try {
                    $twilio->sendSms($providerPhoneFormatted, $providerMessage);
                    Log::info("✅ SMS sent to provider {$providerPhoneFormatted} for Booking ID {$this->booking->id}");
                } catch (\Exception $e) {
                    Log::error("❌ SMS to provider failed for Booking ID {$this->booking->id}: " . $e->getMessage());
                }
            } else {
                Log::warning("⚠️ No provider phone for Booking ID {$this->booking->id}");
            }

            // 📧 Email to Provider (uses general emails if provider has no personal email)
            foreach ($provider->getNotificationEmails() as $notifyEmail) {
                try {
                    Mail::to($notifyEmail)->send(new \App\Mail\ProviderBookingReminderMail($this->booking));
                    Log::info("✅ Email sent to provider {$notifyEmail} for Booking ID {$this->booking->id}");
                } catch (\Exception $e) {
                    Log::error("❌ Email to provider {$notifyEmail} failed for Booking ID {$this->booking->id}: " . $e->getMessage());
                }
            }
        }
    }
}
