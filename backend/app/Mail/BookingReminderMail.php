<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Mail\Mailable;
use App\Support\EmailTemplates;

class BookingReminderMail extends Mailable
{
    public Booking $booking;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Appointment Reminder - Isaiah Nail Bar')
                    ->html(EmailTemplates::booking($this->booking, 'reminder'));
    }
}
