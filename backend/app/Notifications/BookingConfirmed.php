<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BookingConfirmed extends Notification
{
    use Queueable;

    public $booking;

    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // You can add 'sms' later
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('🎉 Booking Confirmed - Isiah Nail Bar')
            ->greeting("Hello {$notifiable->name},")
            ->line('We’re excited to confirm your booking at Isiah Nail Bar!')
            ->line("📅 Date: {$this->booking->date}")
            ->line("⏰ Time: {$this->booking->time}")
            ->line("💅 Service: " . optional($this->booking->service)->name)
            ->line("💳 Payment: " . ucfirst($this->booking->payment_option))
            ->action('View Your Booking', url('/dashboard/customer'))
            ->line('Thank you for choosing us! 💖');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Your booking is confirmed.',
            'booking_id' => $this->booking->id,
        ];
    }
}
