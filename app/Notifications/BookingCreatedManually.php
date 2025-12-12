<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BookingCreatedManually extends Notification
{
    use Queueable;

    public $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $booking = $this->booking->load(['services', 'provider']);
        
        // Log email before sending
        $this->logEmail($notifiable);

        return (new MailMessage)
            ->subject('Booking Confirmation - Isaiah Nail Bar')
            ->markdown('emails.booking-manual', [
                'booking' => $booking,
                'customer' => $notifiable,
            ]);
    }

    private function logEmail($notifiable)
    {
        EmailLog::create([
            'recipient_email' => $notifiable->email,
            'recipient_name' => $notifiable->name,
            'recipient_type' => 'customer',
            'subject' => 'Booking Confirmation - Isaiah Nail Bar',
            'email_type' => 'booking_confirmation',
            'booking_id' => $this->booking->id,
            'status' => 'pending',
            'metadata' => [
                'booking_date' => $this->booking->date,
                'booking_time' => $this->booking->time,
            ],
        ]);
    }
}
