<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Booking, Provider, Service, Customer, Payment, Category, PickupLocation};
use App\Services\BookingScheduleGuard;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Support\PdfTemplates;
use App\Mail\RequestReviewMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AdminApiController extends Controller
{
    /**
     * Admin Dashboard Overview API (KPIs, Range Charts, Today Ops, Recent Bookings)
     */
    public function overview(Request $request)
    {
        $range = $request->query('range', '7days');

        // All-time or general revenue
        $totalRevenue = (float) DB::table('payments')
            ->where('status', 'paid')
            ->sum('amount');

        // Tax calculation (5%)
        $taxAmount = round($totalRevenue * 0.05, 2);
        $revenueAfterTax = $totalRevenue - $taxAmount;

        // Revenue breakdown by payment method
        $momoRevenue = (float) DB::table('payments')
            ->where('status', 'paid')
            ->where('method', 'momo')
            ->sum('amount');

        $cardRevenue = (float) DB::table('payments')
            ->where('status', 'paid')
            ->where('method', 'card')
            ->sum('amount');

        // Total Bookings & Customers & Stylists
        $totalBookings = Booking::count();
        // Customer rows include both registered customers and walk-in guests.
        $totalCustomers = Customer::count();
        $totalProviders = Provider::where('active', true)->count();
        if ($totalProviders === 0) {
            $totalProviders = Provider::count();
        }

        // Status Counts
        $pendingCount = Booking::where('status', 'pending')->count();
        $acceptedCount = Booking::where('status', 'accepted')->count();
        $completedCount = Booking::where('status', 'completed')->count();
        $inProgressCount = Booking::where('status', 'in_progress')->count();
        $cancelledCount = Booking::whereIn('status', ['cancelled', 'declined'])->count();

        $statusCounts = [
            'pending' => $pendingCount,
            'accepted' => $acceptedCount,
            'in_progress' => $inProgressCount,
            'completed' => $completedCount,
            'cancelled' => $cancelledCount,
        ];

        // Completion Rate
        $validBookings = max(1, $totalBookings - $cancelledCount);
        $completionRate = round(($completedCount / $validBookings) * 100);

        // Average Booking Value
        $activeBookingsCount = max(1, $completedCount + $acceptedCount + $inProgressCount);
        $avgBookingValue = round($totalRevenue / $activeBookingsCount);

        // Today's Operations
        $todayStr = Carbon::today()->toDateString();
        $todayBookingsCount = Booking::whereDate('date', $todayStr)->count();
        $todayRevenue = (float) DB::table('payments')
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->whereDate('bookings.date', $todayStr)
            ->where('payments.status', 'paid')
            ->sum('payments.amount');

        $todayOps = [
            'today_date' => Carbon::today()->format('l, F j, Y'),
            'today_bookings_count' => $todayBookingsCount,
            'today_revenue' => $todayRevenue,
            'pending_confirmations' => $pendingCount,
        ];

        // Real Top Customer from Database
        $topCustomerRecord = DB::table('bookings')
            ->join('customers', 'bookings.customer_id', '=', 'customers.id')
            ->where('bookings.status', '!=', 'cancelled')
            ->select('customers.name', DB::raw('count(bookings.id) as booking_count'))
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('booking_count')
            ->first();

        $topCustomer = [
            'name' => $topCustomerRecord ? $topCustomerRecord->name : 'Claire Mukamana',
            'bookings' => $topCustomerRecord ? (int) $topCustomerRecord->booking_count : 0,
        ];

        // Chart Revenue Data according to requested range
        $chartData = [];
        if ($range === '30days') {
            $daysCount = 30;
        } elseif ($range === 'this_month') {
            $daysCount = Carbon::now()->day;
        } else {
            $daysCount = 7;
        }

        for ($i = $daysCount - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();

            $total = (float) DB::table('payments')
                ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
                ->whereDate('bookings.date', $date)
                ->where('payments.status', 'paid')
                ->sum('payments.amount');

            $carbonDate = Carbon::parse($date);
            $chartData[] = [
                'date' => $carbonDate->format('M d'),
                'day_name' => $carbonDate->format('D'),
                'full_date' => $date,
                'total' => $total,
            ];
        }

        // Recent Bookings (All fields coming strictly from Database)
        $recentBookings = Booking::with(['services', 'provider', 'customer.user', 'payments', 'pickupLocation'])
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->take(15)
            ->get()
            ->map(fn($b) => $this->formatBookingItem($b));

        return response()->json([
            'success' => true,
            'kpis' => [
                'total_revenue' => $totalRevenue,
                'tax_amount' => $taxAmount,
                'revenue_after_tax' => $revenueAfterTax,
                'momo_revenue' => $momoRevenue,
                'card_revenue' => $cardRevenue,
                'total_bookings' => $totalBookings,
                'total_customers' => $totalCustomers,
                'total_providers' => $totalProviders,
                'completion_rate' => $completionRate,
                'avg_booking_value' => $avgBookingValue,
                'top_customer' => $topCustomer,
            ],
            'today_ops' => $todayOps,
            'status_counts' => $statusCounts,
            'chart_data' => $chartData,
            'recent_bookings' => $recentBookings,
        ]);
    }

    /**
     * Admin Bookings List API (Search, Filter, Pagination, Status counts)
     */
    public function bookings(Request $request)
    {
        $status = $request->query('status', 'all');
        $search = $request->query('search');
        $paymentStatus = $request->query('payment_status', 'all');
        $providerId = $request->query('provider_id', 'all');
        $dateFilter = $request->query('date', 'all');
        $serviceType = $request->query('service_type', 'all');
        $source = $request->query('source', 'all');
        $perPage = (int) $request->query('per_page', 15);
        $perPage = min(max($perPage, 5), 100);

        $query = Booking::with(['services', 'provider', 'customer.user', 'payments', 'pickupLocation']);

        // Search by customer name, email, phone, reference, or booking ID
        if ($search && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                $q->where('id', 'like', "%{$term}%")
                  ->orWhere('reference', 'like', "%{$term}%")
                  ->orWhere('notes', 'like', "%{$term}%")
                  ->orWhere('address', 'like', "%{$term}%")
                  ->orWhere('pickup_address', 'like', "%{$term}%")
                  ->orWhereHas('customer', function ($cq) use ($term) {
                      $cq->where('name', 'like', "%{$term}%")
                         ->orWhere('email', 'like', "%{$term}%")
                         ->orWhere('phone', 'like', "%{$term}%")
                         ->orWhereHas('user', function ($uq) use ($term) {
                             $uq->where('name', 'like', "%{$term}%")
                                ->orWhere('email', 'like', "%{$term}%")
                                ->orWhere('phone', 'like', "%{$term}%");
                         });
                  });
            });
        }

        // Status Filter
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if (in_array($source, ['website', 'admin_manual', 'walk_in'], true)) {
            $query->where('source', $source);
        }

        // Payment Status Filter
        if ($paymentStatus && $paymentStatus !== 'all') {
            if ($paymentStatus === 'unpaid') {
                $query->whereDoesntHave('payments', fn($q) => $q->where('status', 'paid'));
            } elseif ($paymentStatus === 'paid') {
                $query->whereHas('payments', fn($q) => $q->where('status', 'paid'));
            } elseif (in_array($paymentStatus, ['pending', 'failed'])) {
                $query->whereHas('payments', fn($q) => $q->where('status', $paymentStatus));
            }
        }

        // Provider Filter
        if ($providerId && $providerId !== 'all') {
            $query->where('provider_id', $providerId);
        }

        // Date Filter
        if ($dateFilter && $dateFilter !== 'all') {
            $today = Carbon::today()->toDateString();
            if ($dateFilter === 'today') {
                $query->whereDate('date', $today);
            } elseif ($dateFilter === 'tomorrow') {
                $query->whereDate('date', Carbon::tomorrow()->toDateString());
            } elseif ($dateFilter === 'upcoming') {
                $query->whereDate('date', '>=', $today);
            } elseif ($dateFilter === 'past') {
                $query->whereDate('date', '<', $today);
            } elseif ($dateFilter === 'this_week') {
                $query->whereBetween('date', [Carbon::now()->startOfWeek()->toDateString(), Carbon::now()->endOfWeek()->toDateString()]);
            } elseif ($dateFilter === 'this_month') {
                $query->whereBetween('date', [Carbon::now()->startOfMonth()->toDateString(), Carbon::now()->endOfMonth()->toDateString()]);
            } else {
                // Exact custom date
                $query->whereDate('date', $dateFilter);
            }
        }

        // Service Type Filter
        if ($serviceType && $serviceType !== 'all') {
            if ($serviceType === 'home') {
                $query->where('is_home_service', true);
            } elseif ($serviceType === 'pickup') {
                $query->whereNotNull('pickup_location_id');
            } elseif ($serviceType === 'salon') {
                $query->where('is_home_service', false)->whereNull('pickup_location_id');
            }
        }

        // Status Counts for Tabs (Unfiltered count by status for current other filters)
        $statusCounts = [
            'all' => Booking::count(),
            'pending' => Booking::where('status', 'pending')->count(),
            'accepted' => Booking::where('status', 'accepted')->count(),
            'in_progress' => Booking::where('status', 'in_progress')->count(),
            'completed' => Booking::where('status', 'completed')->count(),
            'declined' => Booking::where('status', 'declined')->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
        ];

        // Active Providers for dropdown
        $providers = Provider::select('id', 'name')
            ->orderBy('name')
            ->get();

        // Paginate results
        $paginator = $query->orderByDesc('date')
            ->orderByDesc('time')
            ->paginate($perPage);

        $formattedData = collect($paginator->items())->map(fn($b) => $this->formatBookingItem($b));

        return response()->json([
            'success' => true,
            'data' => $formattedData,
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'status_counts' => $statusCounts,
            'providers' => $providers,
        ]);
    }

    /**
     * Single Booking Details
     */
    public function bookingDetail($id)
    {
        $booking = Booking::with(['services', 'provider', 'customer.user', 'payments', 'pickupLocation'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->formatBookingItem($booking),
        ]);
    }

    /**
     * Update Booking Status (Accept, Decline, Complete, Cancel, Pending)
     */
    public function updateBookingStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,accepted,in_progress,completed,cancelled,declined',
        ]);

        $booking = Booking::with(['customer.user'])->findOrFail($id);
        $oldStatus = $booking->status;
        $booking->status = $validated['status'];
        $booking->save();

        // Send review invitation mail if status transitioned to completed
        if ($oldStatus !== 'completed' && $booking->status === 'completed') {
            if ($booking->customer && $booking->customer->user && $booking->customer->user->email) {
                try {
                    Mail::to($booking->customer->user->email)->send(new RequestReviewMail($booking));
                } catch (\Throwable $e) {
                    Log::warning('Review email send failed: ' . $e->getMessage());
                }
            }

            // Award loyalty stamp to customer's digital card
            if ($booking->customer) {
                try {
                    app(\App\Services\LoyaltyService::class)->awardStamp(
                        $booking->customer,
                        $booking,
                        "Completed appointment #{$booking->reference}",
                        $request->user()
                    );
                } catch (\Throwable $e) {
                    Log::warning('Loyalty stamp award failed: ' . $e->getMessage());
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Booking #{$booking->reference} updated to " . ucfirst($booking->status),
            'status' => $booking->status,
            'data' => $this->formatBookingItem($booking),
        ]);
    }

    /**
     * Record in-salon / desk payment for booking balance
     */
    public function recordPayment(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|string|in:cash,pos_card,card,momo',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($id, $validated): void {
            if (DB::getDriverName() === 'sqlite') {
                DB::table('bookings')->where('id', $id)->update(['id' => DB::raw('id')]);
            }

            $booking = Booking::query()
                ->with(['services', 'payments'])
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            $baseServicePrice = (float) $booking->services->sum(
                fn ($service) => (float) ($service->pivot?->price_at_booking ?? $service->price)
            );
            $totalPrice = ($booking->is_home_service ? $baseServicePrice * 2 : $baseServicePrice)
                + (float) ($booking->pickup_fee ?? 0);
            $totalPaid = (float) $booking->payments->where('status', 'paid')->sum('amount');
            $balance = max(0, $totalPrice - $totalPaid);

            if ((float) $validated['amount'] > $balance + 0.009) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment cannot be greater than the remaining booking balance.',
                ]);
            }

            Payment::create([
                'booking_id' => $booking->id,
                'amount' => $validated['amount'],
                'method' => $validated['method'] === 'pos_card' ? 'card' : $validated['method'],
                'status' => 'paid',
                'reference' => 'DESK-'.strtoupper(\Illuminate\Support\Str::random(12)),
                'paid_at' => now(),
            ]);

            $booking->is_fully_paid = $totalPrice > 0 && $totalPaid + (float) $validated['amount'] >= $totalPrice - 0.009;
            $booking->save();
        }, 3);

        $booking = Booking::with(['services', 'provider', 'customer.user', 'payments', 'pickupLocation'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully.',
            'data' => $this->formatBookingItem($booking),
        ]);
    }

    /**
     * Reschedule appointment or reassign provider
     */
    public function updateBooking(Request $request, $id)
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'time' => ['nullable', 'date_format:H:i'],
            'provider_id' => ['nullable', 'integer', Rule::exists('providers', 'id')],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($id, $validated): void {
            $booking = Booking::query()
                ->with('services')
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            $date = $validated['date'] ?? Carbon::parse($booking->date)->toDateString();
            $time = $validated['time'] ?? substr((string) $booking->time, 0, 5);
            $providerId = (int) ($validated['provider_id'] ?? $booking->provider_id);
            $scheduleChanged = $date !== Carbon::parse($booking->date)->toDateString()
                || $time !== substr((string) $booking->time, 0, 5)
                || $providerId !== (int) $booking->provider_id;

            if ($scheduleChanged) {
                if (!in_array($booking->status, ['pending', 'accepted', 'confirmed'], true)) {
                    throw ValidationException::withMessages(['booking' => 'Only upcoming appointments can be rescheduled.']);
                }

                if (Carbon::parse($date)->lt(Carbon::today(config('app.timezone')))) {
                    throw ValidationException::withMessages(['date' => 'Rescheduled appointments must be today or a future date.']);
                }

                if (DB::getDriverName() === 'sqlite') {
                    DB::table('providers')->where('id', $providerId)->update(['id' => DB::raw('id')]);
                }

                $provider = Provider::query()
                    ->whereKey($providerId)
                    ->where('active', true)
                    ->lockForUpdate()
                    ->first();
                if (!$provider) {
                    throw ValidationException::withMessages(['provider_id' => 'Choose an active provider.']);
                }

                $serviceIds = $booking->services->pluck('id')->map(fn ($serviceId) => (int) $serviceId)->all();
                $scheduleGuard = app(BookingScheduleGuard::class);
                $scheduleGuard->assertProviderOffersServices($provider->id, $serviceIds);
                $duration = max(15, (int) $booking->services->sum(
                    fn ($service) => $service->pivot?->duration_minutes_at_booking ?? $service->duration_minutes ?? 60
                ));
                $scheduleGuard->assertAvailable($provider, $date, $time, $duration, (int) $booking->id);

                $booking->provider_id = $provider->id;
                $booking->date = $date;
                $booking->time = $time;
                $booking->reminder_sent = false;
                $booking->reminder_delivery_status = null;
                $booking->reminder_failed_at = null;
            }

            if (array_key_exists('notes', $validated)) {
                $booking->notes = $validated['notes'];
            }
            $booking->save();
        }, 3);

        $booking = Booking::with(['services', 'provider', 'customer.user', 'payments', 'pickupLocation'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully.',
            'data' => $this->formatBookingItem($booking),
        ]);
    }

    /**
     * Download Booking Receipt PDF
     */
    public function downloadReceiptPdf($id)
    {
        $booking = Booking::with(['service.category', 'services.category', 'provider', 'customer.user', 'payments', 'pickupLocation'])->find($id);

        if (!$booking) {
            abort(404, 'Booking receipt not found');
        }

        $logoPath = public_path('storage/logo.png');
        if (!file_exists($logoPath)) {
            $logoPath = public_path('logo.png');
        }
        $logo = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $pdf = Pdf::loadHTML(PdfTemplates::receipt($booking, $logo))->setPaper('a4');

        $ref = $booking->reference ?: 'INB-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
        return $pdf->download("isaiah-receipt-{$ref}.pdf");
    }

    /**
     * Revenue Reports API
     */
    public function reports(Request $request)
    {
        $from = $request->query('from') ?? Carbon::now()->startOfMonth()->toDateString();
        $to = $request->query('to') ?? Carbon::now()->endOfMonth()->toDateString();
        $providerId = $request->query('provider_id');
        $paymentMethod = $request->query('payment_method');

        // Query bookings in date range with valid status (accepted or completed)
        $query = Booking::with(['services', 'payments', 'customer.user', 'provider', 'pickupLocation'])
            ->whereBetween('date', [$from, $to])
            ->whereIn('status', ['accepted', 'in_progress', 'completed']);

        if ($providerId && $providerId !== 'all') {
            $query->where('provider_id', $providerId);
        }

        if ($paymentMethod && $paymentMethod !== 'all') {
            $query->whereHas('payments', function ($pq) use ($paymentMethod) {
                $pq->where('status', 'paid')->where('method', $paymentMethod);
            });
        }

        $bookings = $query->orderBy('date', 'desc')->orderBy('time', 'desc')->get();

        // Financial KPIs
        $totalRevenue = 0;
        $momoTotal = 0;
        $cardTotal = 0;
        $cashTotal = 0;
        $totalValuation = 0;

        foreach ($bookings as $b) {
            $basePrice = (float) $b->services->sum('price');
            $val = $b->is_home_service ? ($basePrice * 2) : $basePrice;
            if ($b->pickup_fee) $val += (float) $b->pickup_fee;
            $totalValuation += $val;

            foreach ($b->payments as $p) {
                if ($p->status === 'paid') {
                    $amt = (float) $p->amount;
                    $totalRevenue += $amt;
                    $m = strtolower($p->method ?? '');
                    if ($m === 'momo') $momoTotal += $amt;
                    elseif ($m === 'card' || $m === 'pos_card') $cardTotal += $amt;
                    elseif ($m === 'cash') $cashTotal += $amt;
                }
            }
        }

        $totalBookingsCount = $bookings->count();
        $avgBookingValue = $totalBookingsCount > 0 ? round($totalRevenue / $totalBookingsCount) : 0;
        $taxAmount = round($totalRevenue * 0.05, 2);
        $netRevenue = $totalRevenue - $taxAmount;
        $outstandingBalance = max(0, $totalValuation - $totalRevenue);

        // Daily chart points across date range
        $startDate = Carbon::parse($from);
        $endDate = Carbon::parse($to);
        $diffDays = $startDate->diffInDays($endDate);
        $dailyChart = [];

        if ($diffDays <= 90) {
            $curr = $startDate->copy();
            while ($curr->lte($endDate)) {
                $dateStr = $curr->toDateString();
                $dayBookings = $bookings->filter(fn($b) => $b->date === $dateStr);
                $dayRev = $dayBookings->reduce(fn($c, $b) => $c + $b->payments->where('status', 'paid')->sum('amount'), 0);
                $dailyChart[] = [
                    'date' => $curr->format('M d'),
                    'full_date' => $dateStr,
                    'day_name' => $curr->format('D'),
                    'revenue' => (float) $dayRev,
                    'count' => $dayBookings->count(),
                ];
                $curr->addDay();
            }
        }

        // Top Services Breakdown
        $serviceStats = [];
        foreach ($bookings as $b) {
            foreach ($b->services as $s) {
                if (!isset($serviceStats[$s->id])) {
                    $serviceStats[$s->id] = [
                        'id' => $s->id,
                        'name' => $s->name,
                        'count' => 0,
                        'revenue' => 0,
                    ];
                }
                $serviceStats[$s->id]['count']++;
                $serviceStats[$s->id]['revenue'] += (float) $s->price;
            }
        }
        usort($serviceStats, fn($a, $b) => $b['revenue'] <=> $a['revenue']);
        $topServices = array_slice($serviceStats, 0, 8);

        // Stylist / Provider Performance Breakdown
        $providerStats = [];
        foreach ($bookings as $b) {
            $pId = $b->provider_id ?: 0;
            $pName = $b->provider?->name ?: 'Salon Specialist';
            if (!isset($providerStats[$pId])) {
                $providerStats[$pId] = [
                    'id' => $pId,
                    'name' => $pName,
                    'count' => 0,
                    'revenue' => 0,
                ];
            }
            $providerStats[$pId]['count']++;
            $providerStats[$pId]['revenue'] += (float) $b->payments->where('status', 'paid')->sum('amount');
        }
        usort($providerStats, fn($a, $b) => $b['revenue'] <=> $a['revenue']);

        // Location / Service Type Split
        $inSalonCount = 0;
        $inSalonRevenue = 0;
        $homeServiceCount = 0;
        $homeServiceRevenue = 0;
        $pickupCount = 0;
        $pickupRevenue = 0;

        foreach ($bookings as $b) {
            $bPaid = (float) $b->payments->where('status', 'paid')->sum('amount');
            if ($b->is_home_service) {
                $homeServiceCount++;
                $homeServiceRevenue += $bPaid;
            } elseif ($b->pickup_location_id) {
                $pickupCount++;
                $pickupRevenue += $bPaid;
            } else {
                $inSalonCount++;
                $inSalonRevenue += $bPaid;
            }
        }

        $locationSplit = [
            'in_salon' => [
                'count' => $inSalonCount,
                'revenue' => $inSalonRevenue,
                'percentage' => $totalRevenue > 0 ? round(($inSalonRevenue / $totalRevenue) * 100, 1) : 0,
            ],
            'home_service' => [
                'count' => $homeServiceCount,
                'revenue' => $homeServiceRevenue,
                'percentage' => $totalRevenue > 0 ? round(($homeServiceRevenue / $totalRevenue) * 100, 1) : 0,
            ],
            'pickup' => [
                'count' => $pickupCount,
                'revenue' => $pickupRevenue,
                'percentage' => $totalRevenue > 0 ? round(($pickupRevenue / $totalRevenue) * 100, 1) : 0,
            ],
        ];

        // Category Breakdown
        $categoryStats = [];
        $allCategoriesRevenue = 0;
        foreach ($bookings as $b) {
            foreach ($b->services as $s) {
                $catId = $s->category_id ?: 0;
                $catName = $s->category?->name ?: 'Signature Nails';
                if (!isset($categoryStats[$catId])) {
                    $categoryStats[$catId] = [
                        'id' => $catId,
                        'name' => $catName,
                        'count' => 0,
                        'revenue' => 0,
                    ];
                }
                $categoryStats[$catId]['count']++;
                $categoryStats[$catId]['revenue'] += (float) $s->price;
                $allCategoriesRevenue += (float) $s->price;
            }
        }
        foreach ($categoryStats as &$cat) {
            $cat['percentage'] = $allCategoriesRevenue > 0 ? round(($cat['revenue'] / $allCategoriesRevenue) * 100, 1) : 0;
        }
        unset($cat);
        usort($categoryStats, fn($a, $b) => $b['revenue'] <=> $a['revenue']);

        // Previous Equivalent Period Comparison
        $fromCarbon = Carbon::parse($from);
        $toCarbon = Carbon::parse($to);
        $diffInDays = max(1, $fromCarbon->diffInDays($toCarbon) + 1);
        $prevFrom = $fromCarbon->copy()->subDays($diffInDays)->toDateString();
        $prevTo = $fromCarbon->copy()->subDay()->toDateString();

        $prevBookings = Booking::with(['services', 'payments'])
            ->whereBetween('date', [$prevFrom, $prevTo])
            ->whereIn('status', ['accepted', 'in_progress', 'completed'])
            ->get();

        $prevRevenue = (float) $prevBookings->reduce(fn($c, $b) => $c + $b->payments->where('status', 'paid')->sum('amount'), 0);
        $prevCount = $prevBookings->count();
        $prevAvg = $prevCount > 0 ? round($prevRevenue / $prevCount) : 0;

        $revenueGrowth = $prevRevenue > 0 ? round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100, 1) : null;
        $bookingsGrowth = $prevCount > 0 ? round((($totalBookingsCount - $prevCount) / $prevCount) * 100, 1) : null;
        $avgGrowth = $prevAvg > 0 ? round((($avgBookingValue - $prevAvg) / $prevAvg) * 100, 1) : null;

        $comparison = [
            'prev_from' => $prevFrom,
            'prev_to' => $prevTo,
            'prev_revenue' => $prevRevenue,
            'prev_count' => $prevCount,
            'prev_avg' => $prevAvg,
            'revenue_growth' => $revenueGrowth,
            'bookings_growth' => $bookingsGrowth,
            'avg_growth' => $avgGrowth,
        ];

        // Monthly Target Progress (5,000,000 RWF standard goal)
        $monthlyTarget = 5000000;
        $targetProgress = min(100, round(($totalRevenue / $monthlyTarget) * 100, 1));

        // Formatted booking records
        $formattedBookings = $bookings->map(fn($b) => $this->formatBookingItem($b));

        return response()->json([
            'success' => true,
            'from' => $from,
            'to' => $to,
            'from_formatted' => Carbon::parse($from)->format('M j, Y'),
            'to_formatted' => Carbon::parse($to)->format('M j, Y'),
            'kpis' => [
                'total_revenue' => $totalRevenue,
                'total_bookings' => $totalBookingsCount,
                'avg_booking_value' => $avgBookingValue,
                'momo_total' => $momoTotal,
                'card_total' => $cardTotal,
                'cash_total' => $cashTotal,
                'tax_amount' => $taxAmount,
                'net_revenue' => $netRevenue,
                'total_valuation' => $totalValuation,
                'outstanding_balance' => $outstandingBalance,
                'monthly_target' => $monthlyTarget,
                'target_progress' => $targetProgress,
            ],
            'comparison' => $comparison,
            'location_split' => $locationSplit,
            'category_stats' => array_values($categoryStats),
            'daily_chart' => $dailyChart,
            'top_services' => $topServices,
            'stylist_performance' => array_values($providerStats),
            'bookings' => $formattedBookings,
            'providers' => Provider::where('active', true)->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    /**
     * Download Revenue Report PDF
     */
    public function reportsPdf(Request $request)
    {
        $from = $request->query('from') ?? Carbon::now()->startOfMonth()->toDateString();
        $to = $request->query('to') ?? Carbon::now()->endOfMonth()->toDateString();
        $providerId = $request->query('provider_id');

        $query = Booking::with(['services', 'payments', 'customer.user', 'provider'])
            ->whereBetween('date', [$from, $to])
            ->whereIn('status', ['accepted', 'in_progress', 'completed']);

        if ($providerId && $providerId !== 'all') {
            $query->where('provider_id', $providerId);
        }

        $bookings = $query->orderBy('date')->get();

        $total = $bookings->reduce(function ($carry, $booking) {
            return $carry + $booking->payments->where('status', 'paid')->sum('amount');
        }, 0);

        $pdf = Pdf::loadHTML(PdfTemplates::revenueReport($bookings, $total, $from, $to));

        return $pdf->download("revenue_report_{$from}_to_{$to}.pdf");
    }

    /**
     * Provider & Staff Performance API
     */
    public function performance(Request $request)
    {
        $range = $request->query('range', '30days');
        $selectedProviderId = $request->query('provider_id');

        // Date range filter
        $now = Carbon::now();
        if ($range === '7days') {
            $startDate = $now->copy()->subDays(6)->startOfDay();
            $endDate = $now->copy()->endOfDay();
        } elseif ($range === 'this_month') {
            $startDate = $now->copy()->startOfMonth()->startOfDay();
            $endDate = $now->copy()->endOfMonth()->endOfDay();
        } elseif ($range === 'all_time') {
            $startDate = Carbon::create(2020, 1, 1);
            $endDate = $now->copy()->endOfDay();
        } else { // default 30days
            $startDate = $now->copy()->subDays(29)->startOfDay();
            $endDate = $now->copy()->endOfDay();
        }

        $allProviders = Provider::with(['user'])->get();

        $providerStats = $allProviders->map(function ($p) use ($startDate, $endDate, $range) {
            $bookingQuery = Booking::where('provider_id', $p->id);
            if ($range !== 'all_time') {
                $bookingQuery->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
            }

            $totalBookings = (clone $bookingQuery)->count();
            $completedBookings = (clone $bookingQuery)->where('status', 'completed')->count();
            $acceptedBookings = (clone $bookingQuery)->where('status', 'accepted')->count();
            $cancelledBookings = (clone $bookingQuery)->whereIn('status', ['cancelled', 'declined'])->count();

            $validCount = max(1, $totalBookings - $cancelledBookings);
            $completionRate = $totalBookings > 0 ? round(($completedBookings / $validCount) * 100, 1) : 0;

            // Revenue collected for this provider
            $revenueQuery = DB::table('bookings')
                ->join('payments', 'bookings.id', '=', 'payments.booking_id')
                ->where('bookings.provider_id', $p->id)
                ->where('payments.status', 'paid');

            if ($range !== 'all_time') {
                $revenueQuery->whereBetween('bookings.date', [$startDate->toDateString(), $endDate->toDateString()]);
            }

            $totalRevenue = (float) $revenueQuery->sum('payments.amount');
            $avgTicket = ($completedBookings + $acceptedBookings) > 0 ? round($totalRevenue / ($completedBookings + $acceptedBookings)) : 0;

            // 7-day daily trend for this provider
            $dailyTrend = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = Carbon::now()->subDays($i)->toDateString();
                $dayRev = (float) DB::table('bookings')
                    ->join('payments', 'bookings.id', '=', 'payments.booking_id')
                    ->where('bookings.provider_id', $p->id)
                    ->whereDate('bookings.date', $d)
                    ->where('payments.status', 'paid')
                    ->sum('payments.amount');

                $dailyTrend[] = [
                    'date' => Carbon::parse($d)->format('M d'),
                    'day' => Carbon::parse($d)->format('D'),
                    'total' => $dayRev,
                ];
            }

            // Top services rendered by this provider
            $topServices = DB::table('bookings')
                ->join('booking_service', 'bookings.id', '=', 'booking_service.booking_id')
                ->join('services', 'booking_service.service_id', '=', 'services.id')
                ->where('bookings.provider_id', $p->id)
                ->whereIn('bookings.status', ['accepted', 'in_progress', 'completed'])
                ->select('services.name', DB::raw('count(booking_service.id) as count'), DB::raw('sum(services.price) as revenue'))
                ->groupBy('services.id', 'services.name')
                ->orderByDesc('count')
                ->take(5)
                ->get();

            return [
                'id' => $p->id,
                'name' => $p->name ?: ($p->user?->name ?: 'Salon Specialist'),
                'email' => $p->email ?: ($p->user?->email ?: null),
                'phone' => $p->phone ?: null,
                'photo' => $p->photo ? (str_starts_with($p->photo, 'http') ? $p->photo : "/storage/{$p->photo}") : null,
                'active' => (bool) $p->active,
                'total_bookings' => $totalBookings,
                'completed_bookings' => $completedBookings,
                'accepted_bookings' => $acceptedBookings,
                'cancelled_bookings' => $cancelledBookings,
                'completion_rate' => $completionRate,
                'total_revenue' => $totalRevenue,
                'avg_ticket' => $avgTicket,
                'daily_trend' => $dailyTrend,
                'top_services' => $topServices,
            ];
        });

        // Team summary totals
        $teamTotalRevenue = (float) $providerStats->sum('total_revenue');
        $teamTotalBookings = (int) $providerStats->sum('total_bookings');
        $teamCompletedBookings = (int) $providerStats->sum('completed_bookings');
        $teamAvgCompletionRate = $providerStats->count() > 0 ? round($providerStats->avg('completion_rate'), 1) : 0;
        $topPerformer = $providerStats->sortByDesc('total_revenue')->first();

        // If specific provider requested, also fetch recent appointments
        $singleProviderDetails = null;
        if ($selectedProviderId) {
            $matched = $providerStats->firstWhere('id', (int) $selectedProviderId);
            if ($matched) {
                $recentAppointments = Booking::with(['services', 'customer.user', 'payments'])
                    ->where('provider_id', $matched['id'])
                    ->orderByDesc('date')
                    ->orderByDesc('time')
                    ->take(10)
                    ->get()
                    ->map(fn($b) => $this->formatBookingItem($b));

                $matched['recent_appointments'] = $recentAppointments;
                $singleProviderDetails = $matched;
            }
        }

        return response()->json([
            'success' => true,
            'range' => $range,
            'summary' => [
                'total_providers' => $allProviders->count(),
                'active_providers' => $allProviders->where('active', true)->count(),
                'team_revenue' => $teamTotalRevenue,
                'team_total_bookings' => $teamTotalBookings,
                'team_completed_bookings' => $teamCompletedBookings,
                'team_avg_completion_rate' => $teamAvgCompletionRate,
                'top_performer' => $topPerformer ? [
                    'id' => $topPerformer['id'],
                    'name' => $topPerformer['name'],
                    'revenue' => $topPerformer['total_revenue'],
                    'completed' => $topPerformer['completed_bookings'],
                ] : null,
            ],
            'providers' => $providerStats,
            'selected_provider' => $singleProviderDetails,
        ]);
    }

    /**
     * Centralized Booking Formatter for consistency across overview & bookings list
     */
    private function formatBookingItem($b)
    {
        $baseServicePrice = (float) $b->services->sum(
            fn ($service) => (float) ($service->pivot?->price_at_booking ?? $service->price)
        );
        $totalPrice = $b->is_home_service ? ($baseServicePrice * 2) : $baseServicePrice;
        if ($b->pickup_fee) {
            $totalPrice += (float) $b->pickup_fee;
        }

        $totalPaid = (float) $b->payments->where('status', 'paid')->sum('amount');
        $balance = max(0, $totalPrice - $totalPaid);

        // Payment status badge computation
        $latestPayment = $b->payments->sortByDesc('created_at')->first();
        if ($totalPaid >= $totalPrice && $totalPrice > 0) {
            $paymentStatus = 'paid';
        } elseif ($totalPaid > 0) {
            $paymentStatus = 'deposit';
        } elseif ($latestPayment && $latestPayment->status === 'failed') {
            $paymentStatus = 'failed';
        } elseif ($latestPayment && $latestPayment->status === 'pending') {
            $paymentStatus = 'pending';
        } else {
            $paymentStatus = 'unpaid';
        }

        // Paid or latest payment method
        $paidPayment = $b->payments->where('status', 'paid')->first();
        $paymentMethod = $paidPayment ? $paidPayment->method : ($latestPayment ? $latestPayment->method : null);

        $bookingDate = Carbon::parse($b->date);
        $bookingTime = $b->time ? substr($b->time, 0, 5) : '10:00';
        $timeFormatted = Carbon::createFromFormat('H:i', $bookingTime)->format('g:i A');

        return [
            'id' => $b->id,
            'reference' => $b->reference ?: 'INB-' . str_pad($b->id, 5, '0', STR_PAD_LEFT),
            'customer_name' => $b->customer?->name ?: ($b->customer?->user?->name ?: 'Guest Customer'),
            'customer_email' => $b->customer?->email ?: ($b->customer?->user?->email ?: 'N/A'),
            'customer_phone' => $b->customer?->phone ?: ($b->customer?->user?->phone ?: 'N/A'),
            'date' => $b->date,
            'date_formatted' => $bookingDate->format('M j, Y'),
            'day_name' => $bookingDate->format('D'),
            'is_today' => $b->date === Carbon::today()->toDateString(),
            'time' => $bookingTime,
            'time_formatted' => $timeFormatted,
            'created_at' => $b->created_at ? $b->created_at->toISOString() : null,
            'created_at_formatted' => $b->created_at ? $b->created_at->format('M j, Y \a\t g:i A') : '',
            'status' => $b->status,
            'source' => $b->source ?: 'website',
            'is_home_service' => (bool) $b->is_home_service,
            'address' => $b->address,
            'pickup_location_id' => $b->pickup_location_id,
            'pickup_location_name' => $b->pickupLocation?->name,
            'pickup_fee' => (float) ($b->pickup_fee ?? 0),
            'pickup_address' => $b->pickup_address,
            'payment_option' => $b->payment_option,
            'deposit_amount' => (float) ($b->deposit_amount ?? 0),
            'notes' => $b->notes,
            'provider_id' => $b->provider_id,
            'provider_name' => $b->provider?->name ?: 'Salon Specialist',
            'provider_phone' => $b->provider?->phone ?: '',
            'services' => $b->services->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'price' => (float) ($s->pivot?->price_at_booking ?? $s->price),
                'duration_minutes' => (int) ($s->pivot?->duration_minutes_at_booking ?? ($s->duration_minutes ?: 60)),
            ]),
            'total_duration' => max(15, (int) $b->services->sum(
                fn ($service) => $service->pivot?->duration_minutes_at_booking ?? $service->duration_minutes ?? 60
            )),
            'base_service_price' => $baseServicePrice,
            'total_price' => $totalPrice,
            'total_paid' => $totalPaid,
            'balance' => $balance,
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentMethod,
            'payments' => $b->payments->map(fn($p) => [
                'id' => $p->id,
                'amount' => (float) $p->amount,
                'method' => $p->method,
                'status' => $p->status,
                'reference' => $p->reference,
                'paid_at' => $p->paid_at ? Carbon::parse($p->paid_at)->format('M j, g:i A') : null,
            ]),
        ];
    }

    /**
     * Admin Categories List & Summary
     */
    public function categories(Request $request)
    {
        $search = trim($request->query('search', ''));
        $sort = $request->query('sort', 'latest');

        $query = Category::withCount('services')
            ->with(['services' => function ($q) {
                $q->select('id', 'category_id', 'name', 'price', 'active')->limit(3);
            }]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        switch ($sort) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'services_desc':
                $query->orderBy('services_count', 'desc');
                break;
            case 'oldest':
                $query->orderBy('id', 'asc');
                break;
            case 'latest':
            default:
                $query->orderBy('id', 'desc');
                break;
        }

        $categories = $query->get();

        $totalCategories = Category::count();
        $totalServicesLinked = DB::table('services')->whereNotNull('category_id')->count();
        $uncategorizedServicesCount = DB::table('services')->whereNull('category_id')->count();
        
        $topCategory = Category::withCount('services')
            ->orderBy('services_count', 'desc')
            ->first();

        $formatted = $categories->map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'services_count' => (int) $cat->services_count,
                'active_services_count' => (int) $cat->services()->where('active', 'active')->count(),
                'sample_services' => $cat->services->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'price' => (float) $s->price,
                    'active' => $s->active,
                ]),
                'created_at' => $cat->created_at ? $cat->created_at->format('M j, Y') : null,
                'created_at_human' => $cat->created_at ? $cat->created_at->diffForHumans() : null,
                'updated_at' => $cat->updated_at ? $cat->updated_at->format('M j, Y') : null,
            ];
        });

        return response()->json([
            'categories' => $formatted,
            'summary' => [
                'total_categories' => $totalCategories,
                'total_services_linked' => $totalServicesLinked,
                'uncategorized_services_count' => $uncategorizedServicesCount,
                'top_category' => $topCategory ? [
                    'id' => $topCategory->id,
                    'name' => $topCategory->name,
                    'services_count' => (int) $topCategory->services_count,
                ] : null,
            ],
        ]);
    }

    /**
     * Store new category
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
        ]);

        $slug = !empty($validated['slug']) 
            ? \Illuminate\Support\Str::slug($validated['slug']) 
            : \Illuminate\Support\Str::slug($validated['name']);

        // Ensure unique slug
        $originalSlug = $slug;
        $counter = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        $category = Category::create([
            'name' => trim($validated['name']),
            'slug' => $slug,
        ]);

        return response()->json([
            'message' => "Category \"{$category->name}\" created successfully.",
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'services_count' => 0,
                'active_services_count' => 0,
                'sample_services' => [],
                'created_at' => $category->created_at->format('M j, Y'),
                'created_at_human' => 'just now',
            ],
        ], 201);
    }

    /**
     * Update category
     */
    public function updateCategory(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
        ]);

        $slug = !empty($validated['slug']) 
            ? \Illuminate\Support\Str::slug($validated['slug']) 
            : \Illuminate\Support\Str::slug($validated['name']);

        // Ensure unique slug if changed
        if ($slug !== $category->slug) {
            $originalSlug = $slug;
            $counter = 1;
            while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = "{$originalSlug}-{$counter}";
                $counter++;
            }
        }

        $category->update([
            'name' => trim($validated['name']),
            'slug' => $slug,
        ]);

        return response()->json([
            'message' => "Category \"{$category->name}\" updated successfully.",
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'services_count' => $category->services()->count(),
                'active_services_count' => $category->services()->where('active', 'active')->count(),
                'sample_services' => $category->services()->take(3)->get(['id', 'name', 'price', 'active']),
                'created_at' => $category->created_at ? $category->created_at->format('M j, Y') : null,
                'created_at_human' => $category->created_at ? $category->created_at->diffForHumans() : null,
            ],
        ]);
    }

    /**
     * Delete category safely
     */
    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
        $name = $category->name;
        
        // Detach any services safely to prevent foreign key errors
        $detachedCount = DB::table('services')
            ->where('category_id', $category->id)
            ->update(['category_id' => null]);

        $category->delete();

        return response()->json([
            'message' => "Category \"{$name}\" deleted successfully." . ($detachedCount > 0 ? " ({$detachedCount} services unassigned)." : ""),
            'detached_services_count' => $detachedCount,
        ]);
    }
}
