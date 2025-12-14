<?php

namespace App\Listeners;

use App\Models\EmailLog;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;

class LogSentEmail
{
    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        try {
            $message = $event->message;
            
            // Extract recipient info
            $to = $message->getTo();
            $recipient_email = null;
            $recipient_name = null;

            if (is_array($to) && count($to) > 0) {
                foreach ($to as $address) {
                    // Symfony Mailer Address object
                    if ($address instanceof \Symfony\Component\Mime\Address) {
                        $recipient_email = $address->getAddress();
                        $recipient_name = $address->getName();
                        break; // Just take the first one
                    }
                }
            }

            // Extract email type from subject or headers
            $subject = $message->getSubject();
            $email_type = $this->detectEmailType($subject);

            // Try to extract booking_id from message headers or body
            $booking_id = $this->extractBookingId($message);

            // Create email log
            EmailLog::create([
                'recipient_email' => $recipient_email,
                'recipient_name' => $recipient_name,
                'recipient_type' => 'customer', // Default, can be customized
                'subject' => $subject,
                'email_type' => $email_type,
                'booking_id' => $booking_id,
                'status' => 'sent',
                'sent_at' => now(),
                'metadata' => [
                    'message_id' => $message->getId(),
                ],
            ]);
        } catch (\Throwable $e) {
            // Log error but don't fail email sending
            Log::error('Failed to log email: ' . $e->getMessage());
        }
    }

    /**
     * Detect email type from subject
     */
    private function detectEmailType(string $subject): string
    {
        $subject_lower = strtolower($subject);

        if (str_contains($subject_lower, 'booking confirmation') || str_contains($subject_lower, 'confirmed')) {
            return 'booking_confirmation';
        } elseif (str_contains($subject_lower, 'reminder')) {
            return 'booking_reminder';
        } elseif (str_contains($subject_lower, 'cancelled')) {
            return 'booking_cancelled';
        } elseif (str_contains($subject_lower, 'review')) {
            return 'review_request';
        } elseif (str_contains($subject_lower, 'password')) {
            return 'password_reset';
        }

        return 'general';
    }

    /**
     * Try to extract booking ID from message
     */
    private function extractBookingId($message): ?int
    {
        // This is a simple implementation
        // You might need to customize based on how you pass booking_id
        return null; // Will be set manually in notifications
    }
}
