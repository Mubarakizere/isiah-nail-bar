<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmed;
use App\Mail\ProviderNewBooking;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\WebhookLog;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PaymentWebhookController extends Controller
{
    public function handleWeFlexfyWebhook(Request $request): JsonResponse
    {
        $token = $request->input('token');
        $requestType = strtolower((string) $request->input('requestType', ''));
        $secret = (string) config('services.weflexfy.secret_key', '');

        if (!is_string($token) || $token === '' || !in_array($requestType, ['payment', 'transfer'], true)) {
            return response()->json(['error' => 'Invalid webhook payload.'], 400);
        }
        if ($secret === '') {
            Log::error('WeFlexy webhook secret is not configured.');
            return response()->json(['error' => 'Webhook is not configured.'], 503);
        }

        try {
            $verified = JWT::decode($token, new Key($secret, 'HS256'));
            $decoded = json_decode(json_encode($verified, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        } catch (ExpiredException) {
            return response()->json(['error' => 'Webhook token expired.'], 401);
        } catch (Throwable $exception) {
            Log::warning('WeFlexy webhook signature verification failed.');
            return response()->json(['error' => 'Invalid webhook signature.'], 401);
        }

        $safePayload = $this->safePayload($requestType, $decoded);
        WebhookLog::create([
            'type' => 'weflexfy_'.$requestType,
            'payload' => $safePayload,
            'status' => $decoded['status'] ?? null,
        ]);

        if ($requestType === 'transfer') {
            if (empty($decoded['transferRef']) || isset($decoded['paymentMethod'])) {
                return response()->json(['error' => 'Webhook payload does not describe a transfer event.'], 400);
            }
            // A transfer callback can recover identifiers when the synchronous
            // initiation response timed out. It never settles customer payment.
            return $this->correlateTransferEvent($decoded);
        }

        // requestType is outside the JWT. Check the signed payload shape too,
        // so a signed transfer event cannot be replayed as a payment event.
        if (isset($decoded['transferRef']) || empty($decoded['paymentMethod'])) {
            return response()->json(['error' => 'Webhook payload does not describe a payment event.'], 400);
        }

        return $this->processPaymentEvent($decoded, $safePayload);
    }

    private function correlateTransferEvent(array $payload): JsonResponse
    {
        $transferPayload = $payload['payload'] ?? null;
        $paymentId = is_array($transferPayload)
            ? filter_var($transferPayload['payment_id'] ?? null, FILTER_VALIDATE_INT)
            : false;
        $requestToken = $payload['requestToken'] ?? null;
        $paymentRef = $payload['paymentRef'] ?? null;

        // Some transfer configurations omit the custom payload. Keep those
        // callbacks as audit records, but don't guess which payment they belong to.
        if (!$paymentId || !is_string($requestToken) || $requestToken === ''
            || !is_string($paymentRef) || $paymentRef === '') {
            return response()->json(['message' => 'Transfer event recorded.']);
        }

        $correlation = DB::transaction(function () use ($paymentId, $requestToken, $paymentRef, $transferPayload): string {
            $payment = Payment::query()->whereKey($paymentId)->lockForUpdate()->first();
            if (!$payment) {
                return 'not_found';
            }

            $booking = Booking::query()->whereKey($payment->booking_id)->lockForUpdate()->first();
            if (!$booking) {
                return 'mismatch';
            }

            $payloadBookingId = filter_var($transferPayload['booking_id'] ?? null, FILTER_VALIDATE_INT);
            $payloadReference = $transferPayload['reference'] ?? null;
            if (($payloadBookingId && (int) $payloadBookingId !== (int) $booking->id)
                || (is_string($payloadReference) && $payloadReference !== '' && $payloadReference !== $booking->reference)) {
                return 'mismatch';
            }

            if (($payment->request_token && $payment->request_token !== $requestToken)
                || ($payment->payment_ref && $payment->payment_ref !== $paymentRef)) {
                return 'mismatch';
            }

            $identifierCollision = Payment::query()
                ->where('id', '!=', $payment->id)
                ->where(function ($query) use ($requestToken, $paymentRef) {
                    $query->where('request_token', $requestToken)->orWhere('payment_ref', $paymentRef);
                })
                ->exists();
            if ($identifierCollision) {
                return 'mismatch';
            }

            $payment->request_token = $requestToken;
            $payment->payment_ref = $paymentRef;
            $payment->save();

            return 'correlated';
        }, 3);

        if ($correlation === 'mismatch') {
            Log::warning('WeFlexy transfer identifiers did not match the local checkout.', ['payment_id' => $paymentId]);
            return response()->json(['error' => 'Transfer correlation mismatch.'], 409);
        }
        if ($correlation === 'not_found') {
            Log::notice('WeFlexy transfer event has no matching local payment.', ['payment_id' => $paymentId]);
            return response()->json(['message' => 'Transfer event recorded; no local payment matched.']);
        }

        return response()->json(['message' => 'Transfer event recorded and checkout identifiers correlated.']);
    }

    private function processPaymentEvent(array $payload, array $safePayload): JsonResponse
    {
        $requestToken = $payload['requestToken'] ?? null;
        $paymentRef = $payload['paymentRef'] ?? null;
        $status = strtoupper((string) ($payload['status'] ?? ''));

        if (!is_string($requestToken) || $requestToken === '' || !is_string($paymentRef) || $paymentRef === '') {
            return response()->json(['error' => 'Payment correlation fields are missing.'], 400);
        }

        if (!isset($payload['amount']) || !is_numeric($payload['amount'])) {
            return response()->json(['error' => 'Payment amount is missing or invalid.'], 422);
        }

        $tokenMatches = Payment::query()->where('request_token', $requestToken)->limit(2)->get();
        $referenceMatches = Payment::query()->where('payment_ref', $paymentRef)->limit(2)->get();
        if ($tokenMatches->count() > 1 || $referenceMatches->count() > 1) {
            Log::error('WeFlexy payment identifiers are ambiguous.');
            return response()->json(['error' => 'Payment correlation is ambiguous.'], 409);
        }

        $byToken = $tokenMatches->first();
        $byReference = $referenceMatches->first();
        if ($byToken && $byReference && $byToken->id !== $byReference->id) {
            Log::warning('WeFlexy payment identifiers point to different local payments.');
            return response()->json(['error' => 'Payment correlation mismatch.'], 409);
        }

        $payment = $byToken ?? $byReference;
        if (!$payment) {
            Log::notice('WeFlexy payment event has no matching local payment.');
            return response()->json(['error' => 'Payment not found.'], 404);
        }

        if (isset($payload['amount']) && abs((float) $payload['amount'] - (float) $payment->amount) > 0.01) {
            Log::warning('WeFlexy payment amount did not match the pending payment.', ['payment_id' => $payment->id]);
            return response()->json(['error' => 'Payment amount mismatch.'], 422);
        }
        if (isset($payload['currency']) && strtoupper((string) $payload['currency']) !== 'RWF') {
            return response()->json(['error' => 'Payment currency mismatch.'], 422);
        }

        if (!in_array($status, ['SUCCESS', 'FAILED', 'DECLINED', 'CANCELLED', 'PENDING'], true)) {
            return response()->json(['error' => 'Unsupported payment status.'], 422);
        }
        if ($status === 'PENDING') {
            return response()->json(['message' => 'Payment is still pending.']);
        }

        $shouldNotify = false;
        $booking = DB::transaction(function () use ($payment, $paymentRef, $requestToken, $status, $payload, $safePayload, &$shouldNotify) {
            $lockedPayment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $booking = Booking::query()->whereKey($lockedPayment->booking_id)->lockForUpdate()->firstOrFail();

            if (($lockedPayment->request_token && $lockedPayment->request_token !== $requestToken)
                || ($lockedPayment->payment_ref && $lockedPayment->payment_ref !== $paymentRef)) {
                abort(409, 'Payment identifiers do not match the saved checkout.');
            }

            $identifierCollision = Payment::query()
                ->where('id', '!=', $lockedPayment->id)
                ->where(function ($query) use ($requestToken, $paymentRef) {
                    $query->where('request_token', $requestToken)->orWhere('payment_ref', $paymentRef);
                })
                ->exists();
            if ($identifierCollision) {
                abort(409, 'Payment identifiers are already linked to another checkout.');
            }

            // Repeated or delayed failure events can never undo a successful payment.
            if ($lockedPayment->status === 'paid') {
                return $booking->load(['customer', 'provider', 'services']);
            }

            if ($paymentRef) {
                $lockedPayment->payment_ref = $paymentRef;
            }
            if ($requestToken) {
                $lockedPayment->request_token = $requestToken;
            }
            $lockedPayment->webhook_payload = $safePayload;

            if ($status === 'SUCCESS') {
                $lockedPayment->status = 'paid';
                $lockedPayment->paid_at = $lockedPayment->paid_at ?? now();
                $lockedPayment->actual_method_used = $this->paymentMethod($payload);
                $lockedPayment->provider_transaction_id = $payload['transactionId'] ?? $payload['id'] ?? $lockedPayment->provider_transaction_id;
                $lockedPayment->save();

                $paidTotal = (float) Payment::query()->where('booking_id', $booking->id)->where('status', 'paid')->sum('amount');
                $bookingTotal = (float) $booking->services->sum(fn ($service) => (float) ($service->pivot?->price_at_booking ?? $service->price)) * ($booking->is_home_service ? 2 : 1)
                    + (float) $booking->pickup_fee;
                $booking->status = 'accepted';
                $booking->is_fully_paid = $bookingTotal > 0 && $paidTotal >= $bookingTotal;
                $booking->save();
                $shouldNotify = true;
            } else {
                $lockedPayment->status = 'failed';
                $lockedPayment->paid_at = null;
                $lockedPayment->save();

                $hasSuccessfulPayment = Payment::query()
                    ->where('booking_id', $booking->id)
                    ->where('status', 'paid')
                    ->exists();
                $latestPaymentId = Payment::query()->where('booking_id', $booking->id)->max('id');
                if (!$hasSuccessfulPayment && (int) $latestPaymentId === (int) $lockedPayment->id) {
                    $booking->status = 'declined';
                    $booking->save();
                }
            }

            return $booking->load(['customer', 'provider', 'services']);
        }, 3);

        if ($shouldNotify) {
            $this->sendNotifications($booking);
        }

        return response()->json(['message' => 'Payment event processed.']);
    }

    private function safePayload(string $type, array $payload): array
    {
        $keys = $type === 'payment'
            ? ['paymentRef', 'requestToken', 'status', 'amount', 'currency', 'paymentMethod', 'paymentChannel', 'transactionId', 'id']
            : ['transferRef', 'paymentRef', 'requestToken', 'status', 'amount', 'payload'];

        $safe = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $payload)) {
                $safe[$key] = $payload[$key];
            }
        }

        if ($type === 'transfer' && is_array($safe['payload'] ?? null)) {
            $safe['payload'] = array_intersect_key($safe['payload'], array_flip(['booking_id', 'reference', 'payment_id', 'type']));
        }

        return $safe;
    }

    private function paymentMethod(array $payload): ?string
    {
        $method = strtoupper((string) ($payload['paymentMethod'] ?? $payload['paymentChannel'] ?? ''));
        return match ($method) {
            'MTN', 'MOMO', 'MOBILE_MONEY' => 'momo',
            'AIRTEL' => 'airtel',
            'CARD', 'VISA', 'MASTERCARD' => 'card',
            default => null,
        };
    }

    private function sendNotifications(Booking $booking): void
    {
        $customer = $booking->customer;
        $user = $customer?->user;
        $email = $user?->email ?? $customer?->email;

        try {
            if ($email) {
                Mail::to($email)->send(new BookingConfirmed($booking));
            }
        } catch (Throwable $exception) {
            Log::warning('Booking confirmation email could not be sent.', ['booking_id' => $booking->id]);
        }

        try {
            if ($booking->provider) {
                foreach ($booking->provider->getNotificationEmails() as $providerEmail) {
                    Mail::to($providerEmail)->send(new ProviderNewBooking($booking));
                }
            }
        } catch (Throwable $exception) {
            Log::warning('Provider booking email could not be sent.', ['booking_id' => $booking->id]);
        }
    }
}
