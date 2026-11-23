<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Mail\Mailable;
use App\Support\EmailTemplates;

class RebookingReminderMail extends Mailable
{
    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        return $this->subject("It's Been 3 Weeks! Time for a Fresh Set or Refill? - Isaiah Nail Bar")
                    ->html(EmailTemplates::rebookingReminder($this->booking));
    }
}
