<?php

namespace App\Mail;

use App\Models\Booking;
use App\Support\EmailTemplates;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingManualConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function build(): static
    {
        return $this->subject('Booking Confirmation - Isaiah Nail Bar')
            ->html(EmailTemplates::booking($this->booking, 'manual'));
    }
}
