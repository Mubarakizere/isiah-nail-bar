<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Support\EmailTemplates;
use App\Support\PdfTemplates;

class BookingConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        // ✅ Prepare logo for PDF
        $logoPath = public_path('storage/logo.png');
        $logo = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        // ✅ Generate PDF
        $pdf = Pdf::loadHTML(PdfTemplates::receipt($this->booking, $logo))->setPaper('a4');

        // ✅ Return mailable with attachment
        return $this->subject('Your Appointment is Confirmed – Isaiah Nail Bar')
                    ->html(EmailTemplates::booking($this->booking, 'confirmed'))
                    ->attachData($pdf->output(), 'receipt_booking_' . $this->booking->id . '.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}
