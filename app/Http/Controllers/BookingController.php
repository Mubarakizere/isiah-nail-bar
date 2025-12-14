<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\BlockedSlot;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Provider;
use App\Models\Category;
use App\Models\ProviderWorkingHour;
use App\Models\Payment;

use App\Services\WeFlexfyService;
use App\Services\TwilioService;

use App\Mail\BookingCreated;
use App\Mail\AdminBookingAlert;

class BookingController extends Controller
{
    /**
     * Ensure required session keys exist or abort with 403
     */
    private function ensureSessionKeysExist(array $keys): void
    {
        foreach ($keys as $key) {
            if (!session()->has($key)) {
                abort(403, 'Booking session expired.');
            }
        }
    }

    /**
     * Normalize phone number to international format
     */
    private function normalizePhoneNumber(string $phone): string
    {
        if (!str_starts_with($phone, '+')) {
            return '+250' . ltrim($phone, '0');
        }
        return $phone;
    }

    /**
     * Calculate total price for selected services
     */
    private function calculateTotalPrice(array $serviceIds): float
    {
        return Service::whereIn('id', $serviceIds)->sum('price');
    }

    /**
     * Step 1: Choose Service
     */
    public function step1(Request $request)
    {
        $categories = Category::with(['services' => function ($query) {
            $query->where('approved', 1);
        }])->get();

        $selectedServices = session('booking.service_ids', []);

        return view('booking.step1', compact('categories', 'selectedServices'));
    }

    /**
     * Step 1: Submit Selected Services
     */
    public function postStep1(Request $request)
    {
        $request->validate([
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'integer|exists:services,id',
        ]);

        session(['booking.service_ids' => $request->service_ids]);

        return redirect()->route('booking.step2');
    }

    /**
     * Step 2: Choose Provider
     */
    public function step2()
    {
        $serviceIds = session('booking.service_ids', []);

        if (empty($serviceIds)) {
            return redirect()->route('booking.step1')
                ->with('error', 'Please select at least one service.');
        }

        // Find providers who offer all selected services
        $providerIds = DB::table('provider_service')
            ->whereIn('service_id', $serviceIds)
            ->select('provider_id')
            ->groupBy('provider_id')
            ->havingRaw('COUNT(DISTINCT service_id) = ?', [count($serviceIds)])
            ->pluck('provider_id');

        $providers = Provider::whereIn('id', $providerIds)
            ->where('active', true)
            ->get();

        if ($providers->isEmpty()) {
            return back()->with('error', 'No providers offer all selected services.');
        }

        $selectedServices = Service::whereIn('id', $serviceIds)->get();

        return view('booking.step2', [
            'providers' => $providers,
            'selectedServices' => $selectedServices,
            'providerId' => session('booking.provider_id')
        ]);
    }

    /**
     * Step 2: Submit Selected Provider
     */
    public function postStep2(Request $request)
    {
        $request->validate([
            'provider_id' => 'required|exists:providers,id'
        ]);

        session(['booking.provider_id' => $request->provider_id]);

        return redirect()->route('booking.step3');
    }

    /**
     * Step 3: Choose Date and Time
     */
    public function step3(Request $request)
    {
        if (!session()->has('booking.provider_id') || !session()->has('booking.service_ids')) {
            return redirect()->route('booking.step2')
                ->with('error', 'Please select services and provider first.');
        }

        $providerId = session('booking.provider_id');
        $serviceIds = session('booking.service_ids', []);
        $services = Service::whereIn('id', $serviceIds)->get();
        $provider = Provider::findOrFail($providerId);
        $totalDuration = (int) $services->sum('duration_minutes');

        $selectedDate = $request->query('booking_date', now()->toDateString());
        $selectedDayOfWeek = Carbon::parse($selectedDate)->dayOfWeek;

        $workingHour = ProviderWorkingHour::where('provider_id', $providerId)
            ->where('day_of_week', $selectedDayOfWeek)
            ->first();

        $slotsWithStatus = $this->generateTimeSlots(
            $workingHour,
            $totalDuration,
            $providerId,
            $selectedDate
        );

        return view('booking.step3', compact(
            'selectedDate',
            'services',
            'provider',
            'slotsWithStatus'
        ));
    }

    /**
     * Generate available time slots with their status
     */
    private function generateTimeSlots($workingHour, int $totalDuration, int $providerId, string $selectedDate): array
    {
        $slotsWithStatus = [];

        if (!$workingHour || $workingHour->is_day_off || $workingHour->is_holiday) {
            return $slotsWithStatus;
        }

        $start = Carbon::createFromFormat('H:i:s', $workingHour->start_time);
        $end = Carbon::createFromFormat('H:i:s', $workingHour->end_time);

        // Generate time slots
        $slots = [];
        while ($start->copy()->addMinutes($totalDuration) <= $end) {
            $slots[] = $start->format('H:i');
            $start->addMinutes($totalDuration);
        }

        // Get existing bookings for the date
        $futureBookings = Booking::with('payments')
            ->where('provider_id', $providerId)
            ->where('date', $selectedDate)
            ->get()
            ->filter(fn($booking) => $booking->payments->where('status', 'paid')->sum('amount') > 0);

        // Count bookings by hour
        $bookingCountsByHour = [];
        foreach ($futureBookings as $booking) {
            $hourKey = Carbon::parse($booking->time)->format('H:00');
            $bookingCountsByHour[$hourKey] = ($bookingCountsByHour[$hourKey] ?? 0) + 1;
        }

        // Get blocked slots
        $blockedSlots = BlockedSlot::where('provider_id', $providerId)
            ->where('date', $selectedDate)
            ->pluck('time')
            ->map(fn($t) => Carbon::parse($t)->format('H:i'))
            ->toArray();

        // Determine slot status
        foreach ($slots as $slot) {
            $slotTime = Carbon::createFromFormat('H:i', $slot);
            $hourKey = $slotTime->format('H:00');

            $status = $this->determineSlotStatus(
                $slotTime,
                $selectedDate,
                $slot,
                $blockedSlots,
                $bookingCountsByHour[$hourKey] ?? 0
            );

            $slotsWithStatus[] = [
                'time' => $slot,
                'status' => $status
            ];
        }

        return $slotsWithStatus;
    }

    /**
     * Determine the status of a time slot
     */
    private function determineSlotStatus(Carbon $slotTime, string $selectedDate, string $slot, array $blockedSlots, int $bookingCount): string
    {
        if ($slotTime->lt(now()) && $selectedDate === now()->toDateString()) {
            return 'past';
        }

        if (in_array($slot, $blockedSlots)) {
            return 'blocked';
        }

        if ($bookingCount >= 3) {
            return 'full';
        }

        return 'available';
    }

    /**
     * Step 3: Submit Date and Time
     */
    public function postStep3(Request $request)
    {
        $request->validate([
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required',
        ]);

        $chosenDate = Carbon::parse($request->booking_date)->toDateString();
        $chosenTime = Carbon::parse($request->booking_time);

        // Prevent selecting past time on same day
        if ($chosenDate === now()->toDateString() && $chosenTime->lt(now())) {
            return back()->withErrors(['booking_time' => 'You cannot select a past time today.']);
        }

        // Check if time slot is blocked
        $providerId = session('booking.provider_id');
        $isBlocked = BlockedSlot::where('provider_id', $providerId)
            ->where('date', $chosenDate)
            ->where('time', $chosenTime->format('H:i'))
            ->exists();

        if ($isBlocked) {
            return back()->withErrors(['booking_time' => 'This time slot is no longer available.']);
        }

        // Store valid date and time in session
        session([
            'booking.date' => $chosenDate,
            'booking.time' => $chosenTime->format('H:i'),
        ]);

        // Redirect to login if user not authenticated
        if (!auth()->check()) {
            return redirect()->guest(route('login'))
                ->with('warning', 'Please login to continue with your booking.');
        }

        return redirect()->route('booking.step4');
    }

    /**
     * Step 4: Choose Payment Option
     */
    public function step4()
    {
        $paymentsDisabled = app()->environment('local');

        if (!session()->has('booking.date') || !session()->has('booking.time')) {
            return redirect()->route('booking.step3')
                ->with('error', 'Please choose date and time first.');
        }

        $serviceIds = session('booking.service_ids', []);
        if (empty($serviceIds)) {
            return redirect()->route('booking.step1')
                ->with('error', 'No services selected.');
        }

        $services = Service::whereIn('id', $serviceIds)->get();
        $totalPrice = $services->sum('price');
        $depositAmount = round($totalPrice * 0.4);
        $provider = Provider::findOrFail(session('booking.provider_id'));

        return view('booking.step4', compact(
            'services',
            'provider',
            'totalPrice',
            'depositAmount',
            'paymentsDisabled'
        ));
    }

    /**
     * Step 4: Submit Payment Options
     */
    public function postStep4(Request $request)
    {
        $request->validate([
            'payment_option' => 'required|in:full,deposit',
            'payment_phone' => 'required|string|min:10|max:15',
            'payment_method' => 'required|in:momo,card',
        ]);

        $serviceIds = session('booking.service_ids', []);
        $totalPrice = $this->calculateTotalPrice($serviceIds);

        session([
            'booking.payment_option' => $request->payment_option,
            'booking.deposit_amount' => $request->payment_option === 'deposit' ? round($totalPrice * 0.4) : null,
            'booking.payment_phone' => $request->payment_phone,
            'booking.payment_method' => $request->payment_method,
        ]);

        return redirect()->route('booking.step5');
    }

    /**
     * Step 5: Review and Confirm
     */
    public function step5()
    {
        $this->ensureSessionKeysExist([
            'booking.service_ids',
            'booking.provider_id',
            'booking.date',
            'booking.time',
            'booking.payment_option',
            'booking.payment_method'
        ]);

        $services = Service::whereIn('id', session('booking.service_ids'))->get();
        $totalPrice = $services->sum('price');
        $provider = Provider::findOrFail(session('booking.provider_id'));

        return view('booking.step5', [
            'services' => $services,
            'provider' => $provider,
            'totalPrice' => $totalPrice,
            'depositAmount' => round($totalPrice * 0.4),
            'paymentOption' => session('booking.payment_option') === 'deposit' ? 'Deposit' : 'Full Payment',
            'paymentMethod' => session('booking.payment_method'),
        ]);
    }

    /**
     * Step 5: Process Final Booking
     */
    public function postStep5(Request $request, TwilioService $twilio)
    {
        // Validate session data
        $requiredKeys = [
            'booking.service_ids',
            'booking.provider_id',
            'booking.date',
            'booking.time',
            'booking.payment_option',
            'booking.payment_phone',
            'booking.payment_method'
        ];

        $this->ensureSessionKeysExist($requiredKeys);

        // Prepare booking data
        $bookingData = $this->prepareBookingData();
        
        try {
            // Create booking and payment records
            $booking = $this->createBooking($bookingData);
            $this->createPayment($booking, $bookingData);
            
            // Send notification emails
            $this->sendBookingEmails($booking);
            
            // Store booking summary in session
            $this->storeBookingSummary($booking, $bookingData['services']);
            
            // Process payment
            $iframeUrl = $this->processPayment($booking, $bookingData);
            
            return redirect()->to('/booking/payment-iframe?iframe=' . urlencode($iframeUrl));
            
        } catch (\Exception $e) {
            Log::error('Booking creation failed: ' . $e->getMessage());
            
            if (isset($booking)) {
                $booking->delete();
            }
            
            return back()->with('error', 'Failed to create booking. Please try again.');
        }
    }

    /**
     * Prepare booking data from session
     */
    private function prepareBookingData(): array
    {
        $user = Auth::user();
        $serviceIds = session('booking.service_ids');
        $services = Service::whereIn('id', $serviceIds)->get();
        $paymentOption = session('booking.payment_option');
        $totalPrice = $services->sum('price');
        $amount = $paymentOption === 'deposit' ? session('booking.deposit_amount') : $totalPrice;
        $phone = $this->normalizePhoneNumber(session('booking.payment_phone'));
        $paymentMethod = session('booking.payment_method');

        // ✅ SECURITY: Validate payment method
        if (!in_array($paymentMethod, ['momo', 'card', 'airtel'])) {
            $paymentMethod = 'momo';
        }

        // ✅ SECURITY: Validate amount against expected total
        if ($paymentOption === 'full' && abs($amount - $totalPrice) > 0.01) {
            Log::warning('⚠️ Payment amount mismatch detected', [
                'expected' => $totalPrice,
                'received' => $amount,
                'user_id' => $user->id
            ]);
            throw new \Exception('Payment amount does not match booking total');
        }

        // ✅ SECURITY: Validate deposit amount is reasonable
        if ($paymentOption === 'deposit') {
            $minDeposit = $totalPrice * 0.2; // 20% minimum
            $maxDeposit = $totalPrice * 0.8; // 80% maximum
            
            if ($amount < $minDeposit || $amount > $maxDeposit) {
                Log::warning('⚠️ Invalid deposit amount', [
                    'amount' => $amount,
                    'total' => $totalPrice,
                    'user_id' => $user->id
                ]);
                throw new \Exception('Deposit amount must be between 20% and 80% of total');
            }
        }

        return [
            'user' => $user,
            'serviceIds' => $serviceIds,
            'services' => $services,
            'providerId' => session('booking.provider_id'),
            'paymentOption' => $paymentOption,
            'paymentMethod' => $paymentMethod,
            'totalPrice' => $totalPrice,
            'amount' => $amount,
            'phone' => $phone,
            'reference' => (string) Str::uuid(),
        ];
    }

    /**
     * Create booking record
     */
    private function createBooking(array $data): Booking
    {
        $booking = Booking::create([
            'customer_id' => $data['user']->customer_id,
            'provider_id' => $data['providerId'],
            'service_id' => null,
            'date' => session('booking.date'),
            'time' => session('booking.time'),
            'payment_option' => $data['paymentOption'],
            'deposit_amount' => $data['paymentOption'] === 'deposit' ? session('booking.deposit_amount') : null,
            'status' => 'pending',
            'reference' => $data['reference']
        ]);

        $booking->services()->attach($data['serviceIds']);

        return $booking;
    }

    /**
     * Create payment record
     */
    private function createPayment(Booking $booking, array $data): void
    {
        Payment::create([
            'booking_id' => $booking->id,
            'reference' => $data['reference'],
            'amount' => $data['amount'],
            'phone' => $data['phone'],
            'method' => $data['paymentMethod'],
            'status' => 'pending',
        ]);
    }

    /**
     * Send booking confirmation emails
     */
    private function sendBookingEmails(Booking $booking): void
    {
        try {
            Mail::to($booking->customer->user->email)->send(new BookingCreated($booking));
            Mail::to(config('mail.admin'))->send(new AdminBookingAlert($booking));
        } catch (\Exception $e) {
            Log::warning('Booking emails failed to send: ' . $e->getMessage());
        }
    }

    /**
     * Store booking summary for success page
     */
    private function storeBookingSummary(Booking $booking, $services): void
    {
        session()->put('last_booking', [
            'service_names' => $services->pluck('name')->toArray(),
            'provider_name' => $booking->provider->name ?? '',
            'date' => $booking->date,
            'time' => $booking->time,
            'payment' => ucfirst($booking->payment_option),
        ]);
        
        session()->put('last_booking_id', $booking->id);
    }

    /**
     * Process payment with WeFlexfy
     */
    private function processPayment(Booking $booking, array $data): string
    {
        $weflex = app(WeFlexfyService::class);

        $paymentPayload = [
            'amount' => (float) $data['amount'],
            'currency' => 'RWF',
            'billName' => $data['user']->name,
            'billEmail' => $data['user']->email,
            'billPhone' => $data['phone'],
            'billCountry' => 'RW',
            'webhook_url' => route('weflexfy.webhook'),
            'transfers' => [[
                'percentage' => 100,
                'recipientNumber' => '+250788421063',
                'payload' => [
                    'booking_id' => $booking->id,
                    'reference' => $data['reference'],
                    'type' => $data['paymentOption'],
                    'method' => $data['paymentMethod'],
                ]
            ]]
        ];

        Log::info('WeFlexfy Payment Payload:', $paymentPayload);
        $response = $weflex->initiatePayment($paymentPayload);
        Log::info('WeFlexfy Payment Response:', $response);

        if (!isset($response['status']) || $response['status'] !== 'success') {
            throw new \Exception('Failed to initiate payment with WeFlexfy');
        }

        return $response['data']['iframeUrl'];
    }

    /**
     * Show payment iframe
     */
    public function showPaymentIframe(Request $request)
    {
        $iframeUrl = $request->query('iframe');

        if (!$iframeUrl) {
            return redirect()->route('booking.step1')
                ->with('error', 'Missing payment link.');
        }

        return view('booking.payment-iframe', compact('iframeUrl'));
    }

    /**
     * Pay remaining amount view
     */
    public function payRemaining($id)
    {
        $booking = Booking::with(['customer.user', 'services', 'payments'])
            ->where('id', $id)
            ->where('customer_id', auth()->user()->customer_id)
            ->firstOrFail();

        $total = $booking->services->sum('price');
        $totalPaid = $booking->payments->where('status', 'paid')->sum('amount');
        $remainingAmount = $total - $totalPaid;

        if ($remainingAmount <= 0) {
            return back()->with('error', 'No balance to pay or already paid.');
        }

        return view('booking.pay-remaining', [
            'booking' => $booking,
            'remainingAmount' => $remainingAmount,
            'phone' => auth()->user()->phone ?? '',
        ]);
    }

    /**
     * Handle remaining payment submission
     */
    public function payRemainingPost(Request $request, $id)
    {
        $request->validate([
            'phone' => 'required|string|min:10',
            'payment_method' => 'required|in:momo,card',
        ]);

        $booking = Booking::with(['customer.user', 'services', 'payments'])
            ->where('id', $id)
            ->where('customer_id', auth()->user()->customer_id)
            ->firstOrFail();

        $total = $booking->services->sum('price');
        $totalPaid = $booking->payments->where('status', 'paid')->sum('amount');
        $remaining = $total - $totalPaid;

        if ($remaining <= 0) {
            return back()->with('error', 'No balance due or already paid.');
        }

        $user = $booking->customer->user ?? null;
        $method = $request->payment_method;
        $phone = $this->normalizePhoneNumber($request->phone);

        // Create payment record
        Payment::create([
            'booking_id' => $booking->id,
            'amount' => $remaining,
            'status' => 'pending',
            'method' => $method,
            'phone' => $phone,
            'reference' => $booking->reference,
        ]);

        // Process payment
        try {
            $iframeUrl = $this->processRemainingPayment($booking, $remaining, $user, $phone, $method);
            return redirect()->to('/booking/payment-iframe?iframe=' . urlencode($iframeUrl));
        } catch (\Exception $e) {
            Log::error('Remaining payment failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to initiate payment for balance.');
        }
    }

    /**
     * Process remaining payment
     */
    private function processRemainingPayment(Booking $booking, float $amount, $user, string $phone, string $method): string
    {
        $paymentPayload = [
            'amount' => $amount,
            'currency' => 'RWF',
            'billName' => $user?->name ?? 'Customer',
            'billEmail' => $user?->email ?? 'noreply@example.com',
            'billPhone' => $phone,
            'billCountry' => 'RW',
            'webhook_url' => route('weflexfy.webhook'),
            'transfers' => [[
                'percentage' => 100,
                'recipientNumber' => '+250788421063',
                'payload' => [
                    'booking_id' => $booking->id,
                    'reference' => $booking->reference,
                    'type' => 'remaining',
                    'method' => $method
                ]
            ]]
        ];

        Log::info('WeFlexfy Remaining Payment Payload:', $paymentPayload);

        $weflex = app(WeFlexfyService::class);
        $response = $weflex->initiatePayment($paymentPayload);

        Log::info('WeFlexfy Remaining Payment Response:', $response);

        if (!isset($response['status']) || $response['status'] !== 'success') {
            throw new \Exception('Failed to initiate remaining payment');
        }

        return $response['data']['iframeUrl'];
    }

    /**
     * Retry payment view
     */
    public function retryPayment($id)
    {
        $booking = Booking::with(['customer.user', 'services', 'payments'])
            ->where('id', $id)
            ->where('customer_id', auth()->user()->customer_id)
            ->firstOrFail();

        $totalPaid = $booking->payments->where('status', 'success')->sum('amount');
        $totalPrice = $booking->services->sum('price');

        if ($totalPaid >= $totalPrice) {
            return back()->with('error', 'This booking is already fully paid.');
        }

        // If deposit was paid and remaining is due, redirect to pay remaining
        if ($booking->deposit_amount && $totalPaid >= $booking->deposit_amount) {
            return redirect()->route('booking.payRemaining', $booking->id);
        }

        return view('booking.retry-payment', compact('booking'));
    }

    /**
     * Handle retry payment submission
     */
    public function retryPaymentPost(Request $request, $id)
    {
        $request->validate([
            'phone' => 'required|string|min:10',
            'payment_method' => 'required|in:momo,card',
        ]);

        $booking = Booking::with(['customer.user', 'services', 'payments'])
            ->where('id', $id)
            ->where('customer_id', auth()->user()->customer_id)
            ->firstOrFail();

        $totalPaid = $booking->payments->where('status', 'success')->sum('amount');
        $totalPrice = $booking->services->sum('price');

        if ($totalPaid >= $totalPrice) {
            return back()->with('error', 'Already fully paid.');
        }

        $amount = $booking->deposit_amount ?? $totalPrice;
        $user = $booking->customer->user;
        $phone = $this->normalizePhoneNumber($request->phone);

        // Save new payment
        Payment::create([
            'booking_id' => $booking->id,
            'reference' => $booking->reference,
            'amount' => $amount,
            'phone' => $phone,
            'method' => $request->payment_method,
            'status' => 'pending',
        ]);

        try {
            $iframeUrl = $this->processRetryPayment($booking, $amount, $user, $phone, $request->payment_method);
            return redirect()->to('/booking/payment-iframe?iframe=' . urlencode($iframeUrl));
        } catch (\Exception $e) {
            Log::error('Retry payment failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to initiate retry payment.');
        }
    }

    /**
     * Process retry payment
     */
    private function processRetryPayment(Booking $booking, float $amount, $user, string $phone, string $method): string
    {
        $payload = [
            'amount' => $amount,
            'currency' => 'RWF',
            'billName' => $user->name,
            'billEmail' => $user->email,
            'billPhone' => $phone,
            'billCountry' => 'RW',
            'webhook_url' => route('weflexfy.webhook'),
            'transfers' => [[
                'percentage' => 100,
                'recipientNumber' => '+250788421063',
                'payload' => [
                    'booking_id' => $booking->id,
                    'reference' => $booking->reference,
                    'type' => $booking->payment_option ?? 'retry',
                    'method' => $method,
                ]
            ]]
        ];

        Log::info('Retry Payment Payload:', $payload);
        
        $weflex = app(WeFlexfyService::class);
        $response = $weflex->initiatePayment($payload);
        
        Log::info('Retry Payment Response:', $response);

        if (!isset($response['status']) || $response['status'] !== 'success') {
            throw new \Exception('Failed to initiate retry payment');
        }

        return $response['data']['iframeUrl'];
    }

    /**
     * View HTML receipt (inline view, authenticated)
     */
    public function receipt($id)
    {
        $booking = Booking::with(['customer.user', 'provider.user', 'service'])->findOrFail($id);

        // Protect access to customer only
        if (Auth::user()->role === 'customer' && Auth::user()->customer_id !== $booking->customer_id) {
            abort(403);
        }

        return view('booking.receipt', compact('booking'));
    }

    /**
     * Download PDF receipt with QR
     */
    public function downloadReceipt($id)
    {
        $booking = Booking::with(['service', 'provider', 'customer.user'])->findOrFail($id);
        $user = auth()->user();

        if ($user->role === 'customer' && $booking->customer_id !== $user->customer_id) {
            abort(403, 'You are not authorized to download this receipt.');
        }

        $logoPath = public_path('storage/logo.png');
        $logo = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $pdf = Pdf::loadView('booking.receipt-pdf', [
            'booking' => $booking,
            'logo' => $logo,
        ])->setPaper('a4');

        return $pdf->download("receipt_booking_{$booking->id}.pdf");
    }
}