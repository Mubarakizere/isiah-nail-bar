<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Support\EmailTemplates;

class ProviderBookingReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function build()
    {
        return $this->subject('Upcoming Appointment – Isaiah Nail Bar')
                    ->html(EmailTemplates::booking($this->booking, 'provider-reminder'));
    }
}
