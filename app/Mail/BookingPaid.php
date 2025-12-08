<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class BookingPaid extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        $logoPath = public_path('storage/logo.png');
        $logo = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $pdf = Pdf::loadView('booking.receipt-pdf', [
            'booking' => $this->booking,
            'logo' => $logo,
            'qr' => null, // Skipped for now
        ])->setPaper('a4');

        return $this->subject('Your Isaiah Nail Bar Receipt')
            ->markdown('emails.booking.paid')
            ->attachData($pdf->output(), "receipt_booking_{$this->booking->id}.pdf", [
                'mime' => 'application/pdf',
            ]);
    }
}
