<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $pdf = Pdf::loadView('booking.receipt-pdf', [
            'booking' => $this->booking,
            'logo'    => $logo,
        ])->setPaper('a4');

        // ✅ Return mailable with attachment
        return $this->subject('✅ Your Appointment is Confirmed – Isaiah Nail Bar')
                    ->markdown('emails.booking.confirmed')
                    ->with('booking', $this->booking)
                    ->attachData($pdf->output(), 'receipt_booking_' . $this->booking->id . '.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}
