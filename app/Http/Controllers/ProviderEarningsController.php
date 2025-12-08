<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ProviderEarningsController extends Controller
{
    public function downloadReport()
    {
        $provider = Auth::user()->provider;
        $bookings = DB::table('bookings')
            ->join('services', 'bookings.service_id', '=', 'services.id')
            ->where('bookings.provider_id', $provider->id)
            ->where('bookings.status', 'completed')
            ->select('bookings.date', 'bookings.time', 'services.name', 'services.price')
            ->orderBy('bookings.date', 'desc')
            ->get();

        $total = $bookings->sum('price');
        $today = Carbon::now()->format('M d, Y');

        $pdf = Pdf::loadView('dashboard.provider.earnings-pdf', [
            'bookings' => $bookings,
            'total' => $total,
            'provider' => $provider,
            'today' => $today
        ]);

        return $pdf->download('provider-earnings-report.pdf');
    }
}
