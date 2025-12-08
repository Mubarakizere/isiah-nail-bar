<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ProviderCalendarController extends Controller
{
    public function index()
    {
        $provider = auth()->user()->provider;

        if (!$provider) {
            return view('dashboard.provider.calendar', ['events' => [], 'services' => []]);
        }

        $services = $provider->services;

        $bookings = Booking::where('provider_id', $provider->id)
            ->with('services')
            ->get();

        $events = $bookings->flatMap(function ($booking) {
            return $booking->services->map(function ($service) use ($booking) {
                return [
                    'id'    => $booking->id,
                    'title' => $service->name ?? 'Booking',
                    'start' => $booking->date . 'T' . $booking->time,
                    'color' => match($booking->status) {
                        'pending' => '#ffc107',
                        'accepted' => '#0d6efd',
                        'completed' => '#198754',
                        'declined', 'cancelled' => '#6c757d',
                        default => '#343a40',
                    },
                    'url'   => route('booking.receipt', $booking->id),
                ];
            });
        });

        return view('dashboard.provider.calendar', [
            'events' => $events,
            'services' => $services
        ]);
    }

    public function fetch(Request $request)
    {
        $provider = auth()->user()->provider;

        if (!$provider) {
            return response()->json([]);
        }

        $query = Booking::where('provider_id', $provider->id)
            ->with('services');

        if ($request->filled('service')) {
            $query->whereHas('services', fn($q) =>
                $q->where('name', $request->service)
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $events = $query->get()->flatMap(function ($booking) {
            return $booking->services->map(function ($service) use ($booking) {
                return [
                    'id'    => $booking->id,
                    'title' => $service->name ?? 'Booking',
                    'start' => $booking->date . 'T' . $booking->time,
                    'color' => match($booking->status) {
                        'pending' => '#ffc107',
                        'accepted' => '#0d6efd',
                        'completed' => '#198754',
                        'declined', 'cancelled' => '#6c757d',
                        default => '#343a40',
                    },
                    'url'   => route('booking.receipt', $booking->id),
                ];
            });
        });

        return response()->json($events);
    }

    public function exportICal(): Response
    {
        $provider = auth()->user()->provider;

        if (!$provider) {
            abort(403);
        }

        $bookings = Booking::where('provider_id', $provider->id)
            ->with(['services', 'customer.user'])
            ->get();

        $ical = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Isaiah Nail Bar//Booking Calendar//EN\r\n";

        foreach ($bookings as $booking) {
            foreach ($booking->services as $service) {
                $start = \Carbon\Carbon::parse("{$booking->date} {$booking->time}");
                $end = (clone $start)->addMinutes($service->duration_minutes ?? 30);

                $ical .= "BEGIN:VEVENT\r\n";
                $ical .= "UID:booking-{$booking->id}@isaiahnailbar.com\r\n";
                $ical .= "DTSTAMP:" . now()->format('Ymd\THis\Z') . "\r\n";
                $ical .= "DTSTART:" . $start->format('Ymd\THis') . "\r\n";
                $ical .= "DTEND:" . $end->format('Ymd\THis') . "\r\n";
                $ical .= "SUMMARY:" . $service->name . "\r\n";
                $ical .= "DESCRIPTION:Booking with " . ($booking->customer->user->name ?? 'Client') . "\r\n";
                $ical .= "END:VEVENT\r\n";
            }
        }

        $ical .= "END:VCALENDAR\r\n";

        return response($ical, 200)
            ->header('Content-Type', 'text/calendar')
            ->header('Content-Disposition', 'attachment; filename=\"bookings.ics\"');
    }
}
