<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Booking;

class WeFlexfyWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('📦 WeFlexfy Raw Webhook Received', $request->all());

        $reference = $request->input('external_reference') ?? $request->input('reference');
        $status = $request->input('status');
        $amount = $request->input('amount');

        if (!$reference) {
            return response()->json(['error' => 'Missing reference'], 400);
        }

        $booking = Booking::where('reference', $reference)->first();

        if (!$booking) {
            Log::warning('⚠️ Booking not found in webhook', ['ref' => $reference]);
            return response()->json(['error' => 'Booking not found'], 404);
        }

        if ($status === 'success' || $status === 'SUCCESS') {
            $booking->status = 'confirmed';

            // Optional: If amount covers full payment, mark as fully paid
            if ($booking->deposit_amount && $amount >= $booking->service->price) {
                $booking->is_fully_paid = true;
            }

        } else {
            $booking->status = 'failed';
        }

        $booking->save();

        Log::info("✅ Booking updated from raw webhook", [
            'booking_id' => $booking->id,
            'status'     => $booking->status,
        ]);

        return response()->json(['message' => 'Booking updated'], 200);
    }
}
