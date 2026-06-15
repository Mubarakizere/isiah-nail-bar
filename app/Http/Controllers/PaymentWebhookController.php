<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\TwilioService;
use App\Mail\BookingConfirmed;
use App\Mail\ProviderNewBooking;
use App\Models\WebhookLog;

class PaymentWebhookController extends Controller
{
    public function handleWeFlexfyWebhook(Request $request)
    {
        Log::info('🔔 WeFlexfy Webhook Triggered', $request->all());

        $token = $request->input('token');
        $requestType = $request->input('requestType');

        if (!$token || !$requestType) {
            Log::warning('⚠️ Webhook missing required fields');
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        try {
            $secretKey = config('services.weflexfy.secret_key');
            
            // ✅ SECURITY: Decode JWT with proper validation
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            $payload = (array) $decoded;
            
            // ✅ SECURITY: Check JWT expiration if present
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                Log::warning('⚠️ Webhook JWT expired', ['exp' => $payload['exp']]);
                return response()->json(['error' => 'Token expired'], 401);
            }
            
            // ✅ SECURITY: Replay attack prevention (check if webhook is recent)
            if (isset($payload['iat'])) {
                $age = time() - $payload['iat'];
                if ($age > 300) { // 5 minutes tolerance
                    Log::warning('⚠️ Webhook too old', ['age_seconds' => $age]);
                    return response()->json(['error' => 'Token too old'], 401);
                }
            }
            
            // Save webhook data to DB for audit trail
            WebhookLog::create([
                'type' => "weflexfy_{$requestType}",
                'payload' => $payload,
                'status' => $payload['status'] ?? null,
            ]);
        
            Log::info("🔓 Decoded WeFlexfy JWT ({$requestType})", $payload);

            if ($requestType === 'transfer' && isset($payload['payload'])) {
                return $this->handleTransfer($payload);
            }

            if ($requestType === 'payment' && isset($payload['paymentRef'])) {
                return $this->handlePaymentRef($payload);
            }

            Log::info('⚠️ Webhook type not handled', ['type' => $requestType]);
            return response()->json(['message' => 'Webhook received'], 200);
            
        } catch (\Firebase\JWT\ExpiredException $e) {
            Log::error('❌ JWT expired: ' . $e->getMessage());
            return response()->json(['error' => 'Token expired'], 401);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            Log::error('❌ JWT signature invalid: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 401);
        } catch (\Exception $e) {
            Log::error('❌ Webhook exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            // ✅ SECURITY: Don't leak internal error details
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    protected function handleTransfer(array $payload)
    {
        $custom = (array) $payload['payload'];
        $reference = $custom['reference'] ?? null;
        $type = $custom['type'] ?? 'full';

        if (!$reference) {
            Log::warning("⚠️ Missing reference in transfer payload.");
            return response()->json(['error' => 'Missing reference'], 400);
        }

        $booking = Booking::where('reference', $reference)->first();
        if (!$booking) {
            Log::warning("⚠️ Booking not found for reference: $reference");
            return response()->json(['error' => 'Booking not found'], 404);
        }

        if ($payload['status'] === 'SUCCESS') {
            $booking->status = 'accepted';

            // ✅ Mark as fully paid for both 'full' and 'remaining' payments
            if (in_array($type, ['full', 'remaining'])) {
                $booking->is_fully_paid = true;
            }
        } elseif ($payload['status'] === 'FAILED') {
            $booking->status = 'declined';
        }

        $booking->save();

        $payment = Payment::where('booking_id', $booking->id)->latest()->first();
        if ($payment) {
            // ✅ FIX: Capture actual payment method from webhook
            $actualMethod = $this->extractPaymentMethod($payload);
            
            $payment->status = $payload['status'] === 'SUCCESS' ? 'paid' : 'failed';
            $payment->paid_at = now();
            $payment->actual_method_used = $actualMethod;
            $payment->provider_transaction_id = $payload['id'] ?? $payload['transactionId'] ?? null;
            $payment->webhook_payload = $payload; // Store full payload for audit
            $payment->save();
            
            Log::info("💳 Payment method captured", [
                'payment_id' => $payment->id,
                'user_selected' => $payment->method,
                'actually_used' => $actualMethod,
                'transaction_id' => $payment->provider_transaction_id
            ]);
        }

        if ($payload['status'] === 'SUCCESS') {
            $this->sendNotifications($booking);
        }

        Log::info("✔️ Transfer webhook processed for booking #{$booking->id}");
        return response()->json(['message' => 'Transfer processed'], 200);
    }

    protected function handlePaymentRef(array $payload)
{
    $paymentRef = $payload['paymentRef'];
    $requestToken = $payload['requestToken'] ?? null;

    Log::info("🔍 Looking for payment", [
        'paymentRef' => $paymentRef,
        'requestToken' => $requestToken
    ]);

    // Try to find payment by request_token first (most reliable)
    // Wrapped in try-catch because column may not exist in production
    $payment = null;
    if ($requestToken) {
        try {
            $payment = Payment::where('request_token', $requestToken)->first();
            if ($payment) {
                Log::info("✅ Found payment by request_token", ['payment_id' => $payment->id]);
            }
        } catch (\Exception $e) {
            Log::warning("⚠️ request_token column doesn't exist, using fallback", [
                'error' => $e->getMessage()
            ]);
        }
    }

    // Fallback: find by payment_ref if already set
    if (!$payment && $paymentRef) {
        $payment = Payment::where('payment_ref', $paymentRef)->first();
        if ($payment) {
            Log::info("✅ Found payment by payment_ref", ['payment_id' => $payment->id]);
        }
    }

    // Last resort: find most recent pending payment (risky, but better than nothing)
    if (!$payment) {
        $payment = Payment::whereNull('payment_ref')
            ->where('status', 'pending')
            ->latest()
            ->first();
        if ($payment) {
            Log::warning("⚠️ Found payment by fallback method (pending + null ref)", ['payment_id' => $payment->id]);
        }
    }

    if (!$payment) {
        Log::warning("⚠️ No payment found for paymentRef: $paymentRef, requestToken: $requestToken");
        return response()->json(['error' => 'No payment found'], 404);
    }

    // ✅ FIX: Extract actual payment method from webhook
    $actualMethod = $this->extractPaymentMethod($payload);

    // Update payment_ref and request_token (if column exists)
    $payment->payment_ref = $paymentRef;
    if ($requestToken) {
        try {
            $payment->request_token = $requestToken;
        } catch (\Exception $e) {
            Log::warning("⚠️ Cannot update request_token (column doesn't exist)");
        }
    }

    // Check for status: must be 'SUCCESS'
    $isPaid = strtolower($payload['status']) === 'success';

   if ($isPaid) {
    $payment->status = 'paid';
    $payment->paid_at = now();
    $payment->actual_method_used = $actualMethod;
    $payment->provider_transaction_id = $payload['id'] ?? $payload['transactionId'] ?? null;
    $payment->webhook_payload = $payload;
    $payment->save();
    
    Log::info("💳 Payment updated to PAID", [
        'payment_id' => $payment->id,
        'booking_id' => $payment->booking_id,
        'paymentRef' => $paymentRef,
        'requestToken' => $requestToken,
        'actually_used' => $actualMethod
    ]);

    $booking = $payment->booking;
    if ($booking) {
        $booking->is_fully_paid = true;
        $booking->status = 'accepted';
        $booking->save();
        $this->sendNotifications($booking);
        Log::info("✅ Booking #{$booking->id} (ref: {$booking->reference}) updated via payment webhook");
    }
} else {
    $payment->status = 'failed';
    $payment->paid_at = now();
    $payment->webhook_payload = $payload;
    $payment->save();

    Log::info("❌ Payment marked as FAILED", [
        'payment_id' => $payment->id,
        'paymentRef' => $paymentRef
    ]);

    $booking = $payment->booking;
    if ($booking) {
        $booking->status = 'declined';
        $booking->save();
        Log::info("❌ Booking #{$booking->id} marked as declined via payment webhook");
    }
}


    return response()->json(['message' => 'Payment ref processed'], 200);
}


    protected function sendNotifications(Booking $booking)
    {
        $user = $booking->customer->user ?? null;
        $customer = $booking->customer;
        $email = $user->email ?? $customer->email ?? null;
        $phone = $customer->phone ?? null;

        if ($phone && !str_starts_with($phone, '+')) {
            $phone = '+250' . ltrim($phone, '0');
        }

        // ✅ Send SMS
        try {
            if ($phone) {
                $msg = "Hi {$user?->name}, your payment has been received. Appointment confirmed on {$booking->date} at {$booking->time}. – Isaiah Nail Bar";
                app(TwilioService::class)->sendSms($phone, $msg);
                Log::info("📲 SMS sent to $phone");
            }
        } catch (\Exception $e) {
            Log::error("❌ SMS failed: " . $e->getMessage());
        }

        // ✅ Send Email to Customer
        try {
            if ($email) {
                Mail::to($email)->send(new BookingConfirmed($booking));
                Log::info("📧 Email sent to customer: $email");
            }
        } catch (\Exception $e) {
            Log::error("❌ Email to customer failed: " . $e->getMessage());
        }

        // ✅ Send Email to Provider (uses general emails if provider has no personal email)
        try {
            if ($booking->provider) {
                foreach ($booking->provider->getNotificationEmails() as $providerEmail) {
                    Mail::to($providerEmail)->send(new ProviderNewBooking($booking));
                    Log::info("📧 Email sent to provider: $providerEmail");
                }
            }
        } catch (\Exception $e) {
            Log::error("❌ Email to provider failed: " . $e->getMessage());
        }
    }

    /**
     * Extract the actual payment method used from webhook payload
     * WeFlexfy sends payment method in different fields depending on the webhook type
     */
    protected function extractPaymentMethod(array $payload): ?string
    {
        // Try to get method from various possible fields in the webhook
        $methodMapping = [
            'MTN' => 'momo',
            'MOMO' => 'momo',
            'AIRTEL' => 'airtel',
            'CARD' => 'card',
            'VISA' => 'card',
            'MASTERCARD' => 'card',
            'CASH' => 'cash',
        ];

        // Check payment channel
        $channel = strtoupper($payload['paymentChannel'] ?? $payload['channel'] ?? '');
        if (isset($methodMapping[$channel])) {
            return $methodMapping[$channel];
        }

        // Check payment method field
        $method = strtoupper($payload['paymentMethod'] ?? $payload['method'] ?? '');
        if (isset($methodMapping[$method])) {
            return $methodMapping[$method];
        }

        // Check transfer details if available
        if (isset($payload['transfers'][0])) {
            $transfer = $payload['transfers'][0];
            $transferMethod = strtoupper($transfer['method'] ?? $transfer['channel'] ?? '');
            if (isset($methodMapping[$transferMethod])) {
                return $methodMapping[$transferMethod];
            }
        }

        Log::warning('⚠️ Could not determine payment method from webhook', [
            'payload_keys' => array_keys($payload)
        ]);

        return null; // Return null if we can't determine the method
    }
}
