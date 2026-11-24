<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BlockedSlot;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PickupLocation;
use App\Models\Provider;
use App\Models\ProviderWorkingHour;
use App\Models\Service;
use App\Models\User;
use App\Support\ApiToken;
use App\Services\WeFlexfyService;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class BookingCheckoutApiController extends Controller
{
    private const SLOT_INTERVAL_MINUTES = 30;

    public function options(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'pickup_locations' => PickupLocation::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'name', 'fee']),
            ],
        ]);
    }

    public function availability(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'service_ids' => ['required', 'array', 'min:1', 'max:12'],
            'service_ids.*' => ['required', 'integer', 'distinct', 'exists:services,id'],
            'provider_id' => ['nullable', 'integer', 'min:0'],
        ]);

        $services = $this->approvedServices($filters['service_ids']);
        $providerIds = $this->eligibleProviderIds($services);
        $providerId = (int) ($filters['provider_id'] ?? 0);
        if ($providerId > 0) {
            $providerIds = in_array($providerId, $providerIds, true) ? [$providerId] : [];
        }

        $duration = max(15, (int) $services->sum(fn (Service $service) => $this->serviceDuration($service)));
        $slots = [];
        $date = Carbon::createFromFormat('Y-m-d', $filters['date'])->startOfDay();

        foreach ($providerIds as $candidateId) {
            $hours = ProviderWorkingHour::query()
                ->where('provider_id', $candidateId)
                ->where('day_of_week', $date->dayOfWeek)
                ->first();

            if (!$hours || $hours->is_day_off || $hours->is_holiday || !$hours->start_time || !$hours->end_time) {
                continue;
            }

            $cursor = Carbon::parse($filters['date'].' '.$hours->start_time);
            $closing = Carbon::parse($filters['date'].' '.$hours->end_time);

            while ($cursor->copy()->addMinutes($duration)->lte($closing)) {
                $time = $cursor->format('H:i');
                $start = $cursor->copy();
                $slots[$time] = ($slots[$time] ?? false)
                    || $this->providerCanTakeSlot($candidateId, $filters['date'], $start, $duration);
                $cursor->addMinutes(self::SLOT_INTERVAL_MINUTES);
            }
        }

        ksort($slots);

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $filters['date'],
                'duration_minutes' => $duration,
                'slots' => collect($slots)->map(fn (bool $available, string $time) => [
                    'time' => $time,
                    'available' => $available,
                ])->values(),
            ],
        ]);
    }

    public function checkout(Request $request): JsonResponse
    {
        $request->merge(['phone' => $this->normalizePhone((string) $request->input('phone', ''))]);
        $data = $request->validate([
            'idempotency_key' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'regex:/^\+2507[0-9]{8}$/'],
            'service_ids' => ['required', 'array', 'min:1', 'max:12'],
            'service_ids.*' => ['required', 'integer', 'distinct', 'exists:services,id'],
            'provider_id' => ['required', 'integer', 'min:0'],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
            'location_type' => ['required', 'in:salon,home'],
            'home_address' => ['nullable', 'required_if:location_type,home', 'string', 'max:1000'],
            'pickup_location_id' => ['nullable', 'integer', 'exists:pickup_locations,id'],
            'pickup_address' => ['nullable', 'string', 'max:1000'],
            'wants_pickup' => ['nullable', 'boolean'],
            'payment_option' => ['required', 'in:full,deposit'],
            'payment_method' => ['required', 'in:momo,card'],
            'special_notes' => ['nullable', 'string', 'max:2000'],
            'silent_appointment' => ['nullable', 'boolean'],
            'terms_accepted' => ['accepted'],
        ]);

        $existing = Payment::query()->where('checkout_key', $data['idempotency_key'])->with('booking')->first();
        if ($existing) {
            if (!$existing->booking) {
                return response()->json(['success' => false, 'message' => 'Booking not found.'], 404);
            }
            // The random checkout UUID is the capability for safely replaying
            // an initial guest checkout when its first HTTP response was lost.
            return $this->existingCheckoutResponse($existing);
        }

        $services = $this->approvedServices($data['service_ids']);
        $pickupLocation = null;
        if ($data['location_type'] === 'salon' && !empty($data['wants_pickup']) && (empty($data['pickup_location_id']) || empty($data['pickup_address']))) {
            throw ValidationException::withMessages(['pickup_location_id' => 'Choose a pickup location and enter the pickup address.']);
        }
        if (!empty($data['pickup_location_id'])) {
            if ($data['location_type'] !== 'salon' || empty($data['wants_pickup'])) {
                throw ValidationException::withMessages(['pickup_location_id' => 'Pickup can only be added to a salon appointment.']);
            }
            $pickupLocation = PickupLocation::query()
                ->where('is_active', true)
                ->find($data['pickup_location_id']);
            if (!$pickupLocation || empty($data['pickup_address'])) {
                throw ValidationException::withMessages(['pickup_location_id' => 'Choose an active pickup location and enter the pickup address.']);
            }
        }

        $providerIds = $this->eligibleProviderIds($services);
        if ($data['provider_id'] > 0) {
            $providerIds = in_array((int) $data['provider_id'], $providerIds, true)
                ? [(int) $data['provider_id']]
                : [];
        }

        if (!$providerIds) {
            throw ValidationException::withMessages(['provider_id' => 'No active provider offers all selected services.']);
        }

        $date = $data['date'];
        $time = $data['time'];
        $duration = max(15, (int) $services->sum(fn (Service $service) => $service->duration_minutes ?: 60));
        $serviceTotal = (float) $services->sum(fn (Service $service) => $this->servicePrice($service));
        $total = (int) round(($serviceTotal * ($data['location_type'] === 'home' ? 2 : 1)) + (float) ($pickupLocation?->fee ?? 0));
        $paymentAmount = $data['payment_option'] === 'deposit' ? (int) round($total * 0.4) : $total;
        $user = ApiToken::resolve($request->bearerToken() ?: $request->cookie('isaiah_api_token'));

        [$payment, $created] = DB::transaction(function () use (
            $data,
            $providerIds,
            $services,
            $date,
            $time,
            $duration,
            $total,
            $paymentAmount,
            $pickupLocation,
            $user
        ) {
            $providers = $this->lockProviderRows($providerIds);
            $existing = Payment::query()->where('checkout_key', $data['idempotency_key'])->lockForUpdate()->with('booking')->first();
            if ($existing) {
                return [$existing, false];
            }

            $provider = $providers->first(fn (Provider $candidate) => $this->providerCanTakeSlot(
                $candidate->id,
                $date,
                Carbon::parse($date.' '.$time),
                $duration
            ));

            if (!$provider) {
                throw ValidationException::withMessages(['time' => 'That appointment time has just been taken. Please select another time.']);
            }

            $customer = $this->findOrCreateCustomer($data, $user);
            $notes = trim((string) ($data['special_notes'] ?? ''));
            if (!empty($data['silent_appointment'])) {
                $notes = trim($notes."\nSilent appointment requested.");
            }

            $booking = Booking::create([
                'customer_id' => $customer->id,
                'provider_id' => $provider->id,
                'date' => $date,
                'time' => $time,
                'payment_option' => $data['payment_option'],
                'status' => 'pending',
                'is_fully_paid' => false,
                'notes' => $notes !== '' ? $notes : null,
                'deposit_amount' => $data['payment_option'] === 'deposit' ? $paymentAmount : 0,
                'reference' => 'INB-'.Str::upper(Str::random(18)),
                'source' => 'website',
                'is_home_service' => $data['location_type'] === 'home',
                'address' => $data['location_type'] === 'home' ? $data['home_address'] : null,
                'pickup_location_id' => $pickupLocation?->id,
                'pickup_fee' => $pickupLocation?->fee ?? 0,
                'pickup_address' => $pickupLocation ? $data['pickup_address'] : null,
            ]);
            $booking->services()->sync($services->mapWithKeys(fn (Service $service) => [
                $service->id => [
                    'price_at_booking' => $this->servicePrice($service),
                    'duration_minutes_at_booking' => $this->serviceDuration($service),
                ],
            ])->all());

            $payment = Payment::create([
                'booking_id' => $booking->id,
                'reference' => $booking->reference,
                'checkout_key' => $data['idempotency_key'],
                'amount' => $paymentAmount,
                'phone' => $data['phone'],
                'method' => $data['payment_method'],
                'status' => 'pending',
            ]);
            $this->issueBookingAccessToken($booking, $payment);

            return [$payment->load('booking.customer', 'booking.provider', 'booking.services'), true];
        }, 3);

        if (!$created) {
            return $this->existingCheckoutResponse($payment);
        }

        return $this->initiateGatewayCheckout($payment, $total, $paymentAmount);
    }

    public function retry(Request $request, string $reference): JsonResponse
    {
        $data = $request->validate([
            'idempotency_key' => ['required', 'uuid'],
            'payment_method' => ['nullable', 'in:momo,card'],
        ]);

        $booking = Booking::query()->with(['customer', 'provider', 'services', 'payments'])
            ->where('reference', $reference)
            ->firstOrFail();
        if (!$this->hasBookingAccess($request, $booking)) {
            return response()->json(['success' => false, 'message' => 'Booking access could not be verified.'], 403);
        }

        $existing = Payment::query()->where('checkout_key', $data['idempotency_key'])->with('booking')->first();
        if ($existing) {
            if ($existing->booking_id !== $booking->id) {
                return response()->json(['success' => false, 'message' => 'Checkout key is already in use.'], 409);
            }
            return $this->existingCheckoutResponse($existing);
        }

        $latest = $booking->payments->sortByDesc('id')->first();
        if (!$latest || $latest->status !== 'failed') {
            return response()->json(['success' => false, 'message' => 'This payment cannot be retried until WeFlexy confirms the previous attempt failed.'], 409);
        }

        $duration = max(15, (int) $booking->services->sum(fn (Service $service) => $this->serviceDuration($service)));
        $baseTotal = (float) $booking->services->sum(fn (Service $service) => $this->servicePrice($service));
        $total = (int) round(($baseTotal * ($booking->is_home_service ? 2 : 1)) + (float) $booking->pickup_fee);
        $paidAmount = (float) $booking->payments->where('status', 'paid')->sum('amount');
        $paymentAmount = $paidAmount > 0
            ? max(0, (int) round($total - $paidAmount))
            : ($booking->payment_option === 'deposit' ? (int) round($total * 0.4) : $total);

        [$payment, $created, $total, $paymentAmount] = DB::transaction(function () use ($booking, $data, $duration) {
            $this->lockProviderRows([$booking->provider_id]);
            $existing = Payment::query()->where('checkout_key', $data['idempotency_key'])->lockForUpdate()->with('booking')->first();
            if ($existing) {
                return [$existing, false, 0, 0];
            }

            $lockedBooking = Booking::query()->with(['customer', 'services', 'payments'])
                ->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $currentLatest = $lockedBooking->payments->sortByDesc('id')->first();
            if (!$currentLatest || $currentLatest->status !== 'failed') {
                throw ValidationException::withMessages(['reference' => 'Wait for WeFlexy to confirm the previous attempt failed before retrying.']);
            }

            $provider = Provider::query()->whereKey($lockedBooking->provider_id)->where('active', true)->lockForUpdate()->first();
            if (!$provider || !$this->providerCanTakeSlot(
                $provider->id,
                Carbon::parse($lockedBooking->date)->toDateString(),
                Carbon::parse($lockedBooking->date.' '.$lockedBooking->time),
                $duration,
                $lockedBooking->id
            )) {
                throw ValidationException::withMessages(['reference' => 'The reserved time is no longer available. Please contact the salon before paying again.']);
            }

            $baseTotal = (float) $lockedBooking->services->sum(fn (Service $service) => $this->servicePrice($service));
            $total = (int) round(($baseTotal * ($lockedBooking->is_home_service ? 2 : 1)) + (float) $lockedBooking->pickup_fee);
            $paidAmount = (float) $lockedBooking->payments->where('status', 'paid')->sum('amount');
            $paymentAmount = $paidAmount > 0
                ? max(0, (int) round($total - $paidAmount))
                : ($lockedBooking->payment_option === 'deposit' ? (int) round($total * 0.4) : $total);
            if ($paymentAmount <= 0) {
                throw ValidationException::withMessages(['reference' => 'This booking has already been paid in full.']);
            }
            $lockedBooking->status = 'pending';
            $lockedBooking->save();

            $payment = Payment::create([
                'booking_id' => $lockedBooking->id,
                'reference' => $lockedBooking->reference,
                'checkout_key' => $data['idempotency_key'],
                'amount' => $paymentAmount,
                'phone' => $lockedBooking->customer->phone,
                'method' => $data['payment_method'] ?? 'momo',
                'status' => 'pending',
            ])->load('booking.customer', 'booking.provider', 'booking.services');

            return [$payment, true, $total, $paymentAmount];
        }, 3);

        if (!$created) {
            return $this->existingCheckoutResponse($payment);
        }

        return $this->initiateGatewayCheckout($payment, $total, $paymentAmount);
    }

    public function payRemaining(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'idempotency_key' => ['required', 'uuid'],
            'payment_method' => ['required', 'in:momo,card'],
        ]);
        $customerId = $request->user()?->customer_id;
        if (!$customerId) {
            return response()->json(['success' => false, 'message' => 'Customer profile not found.'], 404);
        }

        $booking = Booking::query()->where('customer_id', $customerId)->find($id);
        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Booking not found.'], 404);
        }

        $existing = Payment::query()->where('checkout_key', $data['idempotency_key'])->with('booking')->first();
        if ($existing) {
            if ($existing->booking_id !== $booking->id) {
                return response()->json(['success' => false, 'message' => 'Checkout key is already in use.'], 409);
            }
            return $this->existingCheckoutResponse($existing);
        }

        if (in_array($booking->status, ['cancelled', 'declined'], true)) {
            return response()->json(['success' => false, 'message' => 'This booking cannot accept another payment.'], 409);
        }

        [$payment, $total, $balance, $created] = DB::transaction(function () use ($booking, $customerId, $data) {
            $lockedBooking = Booking::query()->with(['customer', 'services', 'payments'])
                ->whereKey($booking->id)->lockForUpdate()->firstOrFail();

            if ((int) $lockedBooking->customer_id !== (int) $customerId
                || in_array($lockedBooking->status, ['cancelled', 'declined'], true)) {
                throw ValidationException::withMessages(['booking' => 'This booking cannot accept another payment.']);
            }

            $existingPayment = Payment::query()
                ->where('checkout_key', $data['idempotency_key'])
                ->lockForUpdate()
                ->with('booking')
                ->first();
            if ($existingPayment) {
                if ($existingPayment->booking_id !== $lockedBooking->id) {
                    throw ValidationException::withMessages(['idempotency_key' => 'Checkout key is already in use.']);
                }
                return [$existingPayment, 0, 0, false];
            }

            $total = (int) round(
                ((float) $lockedBooking->services->sum(fn (Service $service) => $this->servicePrice($service))
                    * ($lockedBooking->is_home_service ? 2 : 1))
                + (float) $lockedBooking->pickup_fee
            );
            $paidAmount = (float) $lockedBooking->payments->where('status', 'paid')->sum('amount');
            $balance = max(0, $total - $paidAmount);
            if ($balance <= 0) {
                throw ValidationException::withMessages(['booking' => 'This booking has already been paid in full.']);
            }

            $latest = $lockedBooking->payments->sortByDesc('id')->first();
            if ($latest && $latest->status === 'pending') {
                throw ValidationException::withMessages(['booking' => 'The current payment is still being processed. Do not start another payment.']);
            }

            $payment = Payment::create([
                'booking_id' => $lockedBooking->id,
                'reference' => $lockedBooking->reference,
                'checkout_key' => $data['idempotency_key'],
                'amount' => $balance,
                'phone' => $lockedBooking->customer?->phone,
                'method' => $data['payment_method'],
                'status' => 'pending',
            ]);

            return [$payment->load('booking.customer', 'booking.provider', 'booking.services'), $total, $balance, true];
        }, 3);

        if (!$created) {
            return $this->existingCheckoutResponse($payment);
        }

        return $this->initiateGatewayCheckout($payment, $total, $balance, 'remaining');
    }

    public function status(Request $request, string $reference): JsonResponse
    {
        $booking = Booking::query()
            ->with(['customer', 'provider', 'services:id,name,price,duration_minutes', 'payments' => fn ($query) => $query->orderByDesc('id')])
            ->where('reference', $reference)
            ->first();

        if (!$booking) {
            return response()->json(['status' => 'not_found', 'message' => 'Booking reference not found.'], 404);
        }
        if (!$this->hasBookingAccess($request, $booking)) {
            return response()->json(['status' => 'unauthorized', 'message' => 'Booking access could not be verified.'], 403);
        }

        $total = (float) ($booking->services->sum(fn (Service $service) => $this->servicePrice($service)) * ($booking->is_home_service ? 2 : 1))
            + (float) $booking->pickup_fee;
        $paidAmount = (float) $booking->payments->where('status', 'paid')->sum('amount');
        $latestPayment = $booking->payments->first();
        $isFullyPaid = $total > 0 && $paidAmount >= $total;
        $paymentStatus = $latestPayment?->status ?? ($paidAmount > 0 ? 'paid' : 'pending');
        $status = match (true) {
            $isFullyPaid => 'success',
            $latestPayment?->status === 'pending' => 'pending',
            $latestPayment?->status === 'failed' => 'failed',
            $paidAmount > 0 => 'success',
            in_array($booking->status, ['declined', 'cancelled'], true) => 'failed',
            default => 'pending',
        };

        return response()->json([
            'status' => $status,
            'payment_status' => $paymentStatus,
            'payment_url' => $status === 'pending' ? $latestPayment?->payment_url : null,
            'message' => $status === 'pending' && !$latestPayment?->request_token
                ? 'WeFlexy did not confirm checkout setup. This booking remains pending; contact the salon with the reference before attempting another payment.'
                : null,
            'booking' => [
                'reference' => $booking->reference,
                'name' => $booking->customer?->name ?? 'Guest',
                'phone' => $booking->customer?->phone ?? '',
                'date' => Carbon::parse($booking->date)->toDateString(),
                'time' => substr((string) $booking->time, 0, 5),
                'locationType' => $booking->is_home_service ? 'home' : 'salon',
                'homeAddress' => $booking->address,
                'pickupAddress' => $booking->pickup_address,
                'wantsPickup' => (bool) $booking->pickup_location_id,
                'pickupFee' => (float) $booking->pickup_fee,
                'selectedProviderId' => $booking->provider_id,
                'providerName' => $booking->provider?->name ?? 'Salon team',
                'serviceIds' => $booking->services->pluck('id')->map(fn ($id) => (int) $id)->values(),
                'paymentOption' => $booking->payment_option ?? 'full',
                'paymentMethod' => $latestPayment?->actual_method_used ?? $latestPayment?->method ?? 'momo',
                'payableToday' => in_array($latestPayment?->status, ['pending', 'failed'], true)
                    ? (float) $latestPayment->amount
                    : $paidAmount,
                'remainingAtSalon' => max(0, $total - $paidAmount),
                'totalInvestment' => $total,
                'silentAppointment' => str_contains((string) $booking->notes, 'Silent appointment requested.'),
                'isPaid' => $isFullyPaid,
            ],
        ]);
    }

    private function initiateGatewayCheckout(Payment $payment, int $total, int $paymentAmount, ?string $paymentType = null): JsonResponse
    {
        $booking = $payment->booking()->with(['customer', 'provider', 'services'])->firstOrFail();
        $accessToken = $this->bookingAccessToken($booking);
        $providerInitiated = false;

        try {
            $gateway = app(WeFlexfyService::class);
            $response = $gateway->initiatePayment([
                'amount' => $paymentAmount,
                'currency' => 'RWF',
                'billName' => $booking->customer?->name,
                'billEmail' => $booking->customer?->email,
                'billPhone' => $payment->phone,
                'billCountry' => 'RW',
                'transfers' => [[
                    'percentage' => 100,
                    'recipientNumber' => $gateway->recipientNumber(),
                    'payload' => [
                        'booking_id' => $booking->id,
                        'reference' => $booking->reference,
                        'payment_id' => $payment->id,
                        'type' => $paymentType ?? $booking->payment_option ?? 'full',
                    ],
                ]],
            ]);
            $providerInitiated = true;

            $providerData = $response['data'] ?? [];
            $requestToken = $providerData['requestToken'] ?? null;
            $iframeUrl = $providerData['iframeUrl'] ?? $providerData['iframe_url'] ?? null;
            if (!is_string($requestToken) || $requestToken === '' || !is_string($iframeUrl) || $iframeUrl === '') {
                // The provider may already have created the request. Keep it
                // pending and never fire an automatic second initiation.
                throw new ConnectionException('WeFlexy returned an incomplete checkout response.');
            }

            $payment->request_token = $requestToken;
            $payment->payment_url = $iframeUrl;
            $payment->save();

            return response()->json([
                'success' => true,
                'status' => 'pending',
                'reference' => $booking->reference,
                'access_token' => $accessToken,
                'amount' => $paymentAmount,
                'total' => $total,
                'payment_url' => $iframeUrl,
            ], 201);
        } catch (ConnectionException $exception) {
            // No response is ambiguous: WeFlexy could have accepted the
            // request. Keep the slot/payment pending and let the signed
            // webhook or status endpoint settle it.
            return response()->json([
                'success' => true,
                'status' => 'pending',
                'reference' => $booking->reference,
                'access_token' => $accessToken,
                'amount' => $paymentAmount,
                'total' => $total,
                'payment_url' => null,
                'message' => 'WeFlexy did not return a checkout link in time. This booking remains pending; keep this reference and do not start another payment until the provider response is checked.',
            ], 202);
        } catch (Throwable $exception) {
            if ($providerInitiated) {
                // The provider accepted the request but local persistence may
                // have failed. Preserve the pending state for webhook recovery.
                report($exception);
                return response()->json([
                    'success' => true,
                    'status' => 'pending',
                    'reference' => $booking->reference,
                    'access_token' => $accessToken,
                    'amount' => $paymentAmount,
                    'total' => $total,
                    'payment_url' => null,
                    'message' => 'WeFlexy did not return a checkout link in time. This booking remains pending; keep this reference and do not start another payment until the provider response is checked.',
                ], 202);
            }

            $payment->status = 'failed';
            $payment->save();

            if (!$booking->payments()->where('status', 'paid')->exists()) {
                $booking->status = 'declined';
                $booking->save();
            }

            report($exception);

            return response()->json([
                'success' => false,
                'status' => 'failed',
                'reference' => $booking->reference,
                'access_token' => $accessToken,
                'message' => 'We could not start the payment. You can retry safely.',
            ], 502);
        }
    }

    private function existingCheckoutResponse(Payment $payment): JsonResponse
    {
        $payment->loadMissing('booking');
        if ($payment->status === 'failed') {
            return response()->json([
                'success' => false,
                'status' => 'failed',
                'reference' => $payment->booking?->reference,
                'access_token' => $payment->booking ? $this->bookingAccessToken($payment->booking) : null,
                'message' => 'This payment attempt failed. Start a new retry.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'status' => $payment->status === 'paid' ? 'success' : 'pending',
            'reference' => $payment->booking?->reference,
            'access_token' => $payment->booking ? $this->bookingAccessToken($payment->booking) : null,
            'amount' => (float) $payment->amount,
            'payment_url' => $payment->payment_url,
            'message' => $payment->payment_url
                ? null
                : ($payment->request_token
                    ? 'WeFlexy is confirming the checkout link. Keep this reference and check again shortly.'
                    : 'WeFlexy did not confirm checkout setup. This booking remains pending; contact the salon with the reference before attempting another payment.'),
        ], $payment->payment_url || $payment->status === 'paid' ? 200 : 202);
    }

    private function approvedServices(array $serviceIds)
    {
        $services = Service::query()
            ->whereIn('id', $serviceIds)
            ->where(fn ($query) => $query->whereNull('approved')->orWhere('approved', true))
            ->get(['id', 'name', 'price', 'duration_minutes']);

        if ($services->count() !== count($serviceIds)) {
            throw ValidationException::withMessages(['service_ids' => 'One or more selected services are no longer available.']);
        }

        return $services;
    }

    private function eligibleProviderIds($services): array
    {
        $serviceIds = $services->pluck('id')->map(fn ($id) => (int) $id)->all();
        return DB::table('provider_service')
            ->join('providers', 'providers.id', '=', 'provider_service.provider_id')
            ->select('provider_service.provider_id')
            ->where('providers.active', true)
            ->whereIn('provider_service.service_id', $serviceIds)
            ->groupBy('provider_service.provider_id')
            ->havingRaw('COUNT(DISTINCT provider_service.service_id) = ?', [count($serviceIds)])
            ->pluck('provider_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function servicePrice(Service $service): float
    {
        return (float) ($service->pivot?->price_at_booking ?? $service->price);
    }

    private function serviceDuration(Service $service): int
    {
        return max(15, (int) ($service->pivot?->duration_minutes_at_booking ?? $service->duration_minutes ?? 60));
    }

    private function issueBookingAccessToken(Booking $booking, Payment $payment): string
    {
        $token = hash_hmac(
            'sha256',
            $booking->reference.'|'.$payment->checkout_key,
            (string) config('app.key')
        );
        $booking->access_token_hash = hash('sha256', $token);
        $booking->save();

        return $token;
    }

    private function bookingAccessToken(Booking $booking): ?string
    {
        $firstCheckoutKey = $booking->payments()
            ->whereNotNull('checkout_key')
            ->orderBy('id')
            ->value('checkout_key');

        if (!$firstCheckoutKey) {
            return null;
        }

        $token = hash_hmac(
            'sha256',
            $booking->reference.'|'.$firstCheckoutKey,
            (string) config('app.key')
        );
        $tokenHash = hash('sha256', $token);

        if (!$booking->access_token_hash) {
            $booking->access_token_hash = $tokenHash;
            $booking->save();
        }

        return hash_equals((string) $booking->access_token_hash, $tokenHash) ? $token : null;
    }

    private function hasBookingAccess(Request $request, Booking $booking): bool
    {
        $token = $request->header('X-Booking-Access');
        if (!is_string($token) || $token === '' || !$booking->access_token_hash) {
            return false;
        }

        return hash_equals((string) $booking->access_token_hash, hash('sha256', $token));
    }

    private function lockProviderRows(array $providerIds)
    {
        $providerIds = array_values(array_unique(array_map('intval', $providerIds)));
        if (DB::getDriverName() === 'sqlite') {
            // SQLite has no SELECT ... FOR UPDATE. A no-op update acquires its
            // database write lock before availability is read, serializing
            // concurrent checkouts across supported production/local engines.
            DB::table('providers')->whereIn('id', $providerIds)->update(['id' => DB::raw('id')]);
        }

        return Provider::query()
            ->whereIn('id', $providerIds)
            ->where('active', true)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
    }

    private function findOrCreateCustomer(array $data, ?User $user = null): Customer
    {
        // 1. If user is authenticated and already has customer profile
        if ($user && $user->customer_id) {
            $customer = Customer::find($user->customer_id);
            if ($customer) {
                $customer->name = trim($data['name']);
                $customer->phone = $data['phone'];
                if (!empty($data['email'])) {
                    $customer->email = $data['email'];
                }
                $customer->save();
                return $customer;
            }
        }

        // 2. Lookup by email
        $customer = !empty($data['email'])
            ? Customer::query()->where('email', $data['email'])->first()
            : null;

        // 3. Lookup by phone
        if (!$customer) {
            $customer = Customer::query()->where('phone', $data['phone'])->first();
        }

        if (!$customer) {
            $customer = Customer::create([
                'name' => trim($data['name']),
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'],
            ]);
        } else {
            $customer->name = trim($data['name']);
            $customer->phone = $data['phone'];
            if (!empty($data['email'])) {
                $customer->email = $data['email'];
            }
            $customer->save();
        }

        // 4. Ensure user is linked to this customer profile
        if ($user && !$user->customer_id) {
            $user->customer_id = $customer->id;
            $user->save();
        } elseif (!$user && !empty($data['email'])) {
            $matchingUser = User::where('email', $data['email'])->first();
            if ($matchingUser && !$matchingUser->customer_id) {
                $matchingUser->customer_id = $customer->id;
                $matchingUser->save();
            }
        }

        return $customer;
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone) ?? '';
        if (str_starts_with($phone, '+250')) {
            return $phone;
        }
        if (str_starts_with($phone, '250')) {
            return '+'.$phone;
        }

        return '+250'.ltrim($phone, '0');
    }

    private function providerCanTakeSlot(int $providerId, string $date, Carbon $start, int $duration, ?int $ignoreBookingId = null): bool
    {
        $hours = ProviderWorkingHour::query()
            ->where('provider_id', $providerId)
            ->where('day_of_week', Carbon::parse($date)->dayOfWeek)
            ->first();

        if (!$hours || $hours->is_day_off || $hours->is_holiday || !$hours->start_time || !$hours->end_time) {
            return false;
        }

        $end = $start->copy()->addMinutes($duration);
        $opens = Carbon::parse($date.' '.$hours->start_time);
        $closes = Carbon::parse($date.' '.$hours->end_time);
        if ($start->lt($opens) || $end->gt($closes) || ($date === now()->toDateString() && $start->lte(now()))) {
            return false;
        }

        if ($hours->break_start && $hours->break_end) {
            $breakStart = Carbon::parse($date.' '.$hours->break_start);
            $breakEnd = Carbon::parse($date.' '.$hours->break_end);
            if ($start->lt($breakEnd) && $end->gt($breakStart)) {
                return false;
            }
        }

        $blocked = BlockedSlot::query()->where('provider_id', $providerId)->whereDate('date', $date)->get(['time']);
        foreach ($blocked as $blockedSlot) {
            $blockedStart = Carbon::parse($date.' '.$blockedSlot->time);
            $blockedEnd = $blockedStart->copy()->addMinutes(self::SLOT_INTERVAL_MINUTES);
            if ($start->lt($blockedEnd) && $end->gt($blockedStart)) {
                return false;
            }
        }

        $bookings = Booking::query()
            ->with(['services:id,duration_minutes', 'payments:id,booking_id,status,created_at'])
            ->where('provider_id', $providerId)
            ->whereDate('date', $date)
            ->whereIn('status', ['pending', 'accepted', 'confirmed', 'in_progress', 'completed'])
            ->when($ignoreBookingId, fn ($query) => $query->where('id', '!=', $ignoreBookingId))
            ->get();

        foreach ($bookings as $booking) {
            $bookingStart = Carbon::parse($date.' '.$booking->time);
            $bookingDuration = max(15, (int) $booking->services->sum(fn (Service $service) => $this->serviceDuration($service)));
            $bookingEnd = $bookingStart->copy()->addMinutes($bookingDuration);
            if ($start->lt($bookingEnd) && $end->gt($bookingStart)) {
                return false;
            }
        }

        return true;
    }

}
