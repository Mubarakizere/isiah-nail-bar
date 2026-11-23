<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FlutterwaveService
{
    public function initiatePayment($data)
    {
        $payload = [
            'tx_ref' => $data['reference'],
            'amount' => $data['amount'],
            'currency' => 'RWF',
            'redirect_url' => route('flutterwave.callback'),
            'payment_options' => 'card,mobilemoneyrw',
            'customer' => [
                'email' => $data['email'],
                'phonenumber' => $data['phone'],
                'name' => $data['name'],
            ],
            'customizations' => [
                'title' => 'Isaiah Nail Bar',
                'description' => 'Booking Payment',
            ],
        ];

        $response = Http::withToken(config('services.flutterwave.secret_key'))
            ->post(config('services.flutterwave.base_url') . '/v3/payments', $payload);

        return $response->json();
    }
}
