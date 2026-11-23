<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Models\Booking;
use App\Services\FlutterwaveService;

class PaymentController extends Controller
{
    public function initiate(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $booking = Booking::with('customer.user')->findOrFail($request->booking_id);

        $amount = $booking->payment_option === 'deposit'
            ? $booking->deposit_amount
            : $booking->service->price;

        $flutterData = [
            'reference' => $booking->reference,
            'amount' => $amount,
            'email' => $booking->customer->user->email ?? 'client@example.com',
            'phone' => $booking->customer->user->phone ?? '+250780000000',
            'name' => $booking->customer->user->name ?? 'Customer',
        ];

        $response = app(FlutterwaveService::class)->initiatePayment($flutterData);

        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect($response['data']['link']);
        }

        Log::error('Flutterwave payment initiation failed', ['response' => $response]);
        return back()->with('error', 'Flutterwave payment failed. Try again.');
    }

    public function showIframe($reference)
    {
        return redirect()->route('home')->with('info', 'This method is no longer used with Flutterwave.');
    }

    public function handleFlutterwaveWebhook(Request $request)
    {
        Log::info('[Flutterwave Webhook] Raw Payload:', $request->all());

        $signature = $request->header('verif-hash');
        $flutterwaveSecret = config('services.flutterwave.secret_hash');

        if (!$signature || $signature !== $flutterwaveSecret) {
            Log::warning('[Flutterwave Webhook] Signature mismatch');
            return response('Unauthorized', 401);
        }

        $data = $request->input('data');
        $reference = $data['tx_ref'] ?? null;
        $status = $data['status'] ?? null;

        if (!$reference || !$status) {
            return response()->json(['message' => 'Missing tx_ref or status'], 400);
        }

        $booking = Booking::with(['service', 'provider'])->where('reference', $reference)->first();

        if (!$booking) {
            Log::warning('[Flutterwave Webhook] Booking not found', ['reference' => $reference]);
            return response()->json(['message' => 'Booking not found'], 404);
        }

        if ($status === 'successful') {
            $booking->status = 'accepted';
            $booking->is_fully_paid = $booking->payment_option === 'full';
        } else {
            $booking->status = 'failed';
        }

        $booking->save();

        Log::info('[Flutterwave Webhook] Booking updated', [
            'booking_id' => $booking->id,
            'status' => $booking->status,
            'paid' => $booking->is_fully_paid,
        ]);

        return response()->json(['message' => 'Webhook processed'], 200);
    }

    public function flutterwaveCallback(Request $request)
    {
        $status = $request->query('status');
        $txRef = $request->query('tx_ref');
        $transactionId = $request->query('transaction_id');

        Log::info('Flutterwave Callback Received', [
            'status' => $status,
            'tx_ref' => $txRef,
            'transaction_id' => $transactionId,
        ]);

        $booking = Booking::with(['service', 'provider'])->where('reference', $txRef)->first();

        if ($status === 'successful' && $booking) {
            $booking->status = 'accepted';
            $booking->is_fully_paid = $booking->payment_option === 'full';
            $booking->save();

            Session::put('last_booking', [
                'service_name' => $booking->service->name ?? '-',
                'provider_name' => $booking->provider->name ?? '-',
                'date' => $booking->date,
                'time' => $booking->time,
                'payment' => $booking->payment_option === 'deposit' ? 'Deposit' : 'Full Payment'
            ]);

            Session::put('last_booking_id', $booking->id);

            return redirect()->route('booking.success')->with('success', 'Payment successful!');
        }

        return redirect()->route('booking.step4')->with('error', 'Payment was cancelled or failed.');
    }
}
