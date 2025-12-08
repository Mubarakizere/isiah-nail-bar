<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Booking, Provider, Service, User};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\RequestReviewMail;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    public function adminDashboard()
    {
        $totalRevenue = DB::table('payments')
    ->where('status', 'paid')
    ->sum('amount');

// Tax calculation (5%)
$taxAmount = $totalRevenue * 0.05;
$revenueAfterTax = $totalRevenue - $taxAmount;

// Revenue breakdown by method
$momoRevenue = DB::table('payments')
    ->where('status', 'paid')
    ->where('method', 'momo')
    ->sum('amount');

$cardRevenue = DB::table('payments')
    ->where('status', 'paid')
    ->where('method', 'card')
    ->sum('amount');

// Other counts
$totalBookings = Booking::count();
$totalCustomers = User::role('customer')->count();
$totalProviders = Provider::count();

        $last7DaysRevenue = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();

            $total = DB::table('payments')
                ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
                ->whereDate('bookings.date', $date)
                ->where('payments.status', 'paid')
                ->sum('payments.amount');

            $last7DaysRevenue[] = [
                'date' => Carbon::parse($date)->format('M d'),
                'total' => (float) $total,
            ];
        }

        return view('dashboard.admin', compact(
    'totalBookings',
    'totalRevenue',
    'taxAmount',
    'revenueAfterTax',
    'momoRevenue',
    'cardRevenue',
    'totalCustomers',
    'totalProviders',
    'last7DaysRevenue'
));

    }

    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');
        $paymentStatus = $request->query('payment_status');

        $bookings = Booking::with(['services', 'provider', 'customer.user', 'payments'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($search, fn($q) => $q->whereHas('customer.user', fn($q2) =>
                $q2->where('name', 'like', "%$search%")))
            ->when($paymentStatus === 'unpaid', fn($q) =>
                $q->whereDoesntHave('payments', fn($q2) =>
                    $q2->where('status', 'paid')))
            ->when(in_array($paymentStatus, ['paid', 'pending', 'failed']), fn($q) =>
                $q->whereHas('payments', fn($q2) =>
                    $q2->where('status', $paymentStatus)))
            ->latest()
            ->paginate(10);

        return view('dashboard.admin.bookings', compact('bookings', 'status', 'search', 'paymentStatus'));
    }
    public function update(Request $request, $id)
{
    $booking = Booking::with(['customer.user'])->findOrFail($id);
    $oldStatus = $booking->status;

    $booking->status = $request->input('status');
    $booking->save();

    // Send review email if status just became completed
    if ($oldStatus !== 'completed' && $booking->status === 'completed') {
        if ($booking->customer && $booking->customer->user && $booking->customer->user->email) {
            Mail::to($booking->customer->user->email)
                ->send(new RequestReviewMail($booking));
        }
    }

    return redirect()->back()->with('success', 'Booking status updated.');
}
    public function revenueReport(Request $request)
    {
        $from = $request->query('from') ?? now()->startOfMonth()->toDateString();
        $to = $request->query('to') ?? now()->endOfMonth()->toDateString();

        $bookings = Booking::with(['services', 'payments', 'customer.user'])
            ->whereBetween('date', [$from, $to])
            ->whereIn('status', ['accepted', 'completed'])
            ->get();

        $total = $bookings->reduce(function ($carry, $booking) {
            return $carry + $booking->payments->where('status', 'paid')->sum('amount');
        }, 0);

        return view('dashboard.admin.reports', compact('bookings', 'from', 'to', 'total'));
    }

    public function revenueReportPdf(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');

        $bookings = Booking::with(['services', 'payments', 'customer.user'])
            ->whereBetween('date', [$from, $to])
            ->whereIn('status', ['accepted', 'completed'])
            ->get();

        $total = $bookings->reduce(function ($carry, $booking) {
            return $carry + $booking->payments->where('status', 'paid')->sum('amount');
        }, 0);

        $pdf = Pdf::loadView('dashboard.admin.reports-pdf', [
            'bookings' => $bookings,
            'from' => $from,
            'to' => $to,
            'total' => $total
        ]);

        return $pdf->download("revenue_report_{$from}_to_{$to}.pdf");
    }

    public function receipt($id)
    {
        $booking = Booking::with(['customer.user', 'provider.user', 'services', 'payments'])->findOrFail($id);
        return view('admin.bookings.receipt', compact('booking'));
    }
}
