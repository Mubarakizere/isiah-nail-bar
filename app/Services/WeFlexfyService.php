<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeFlexfyService
{
    protected $accessKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->accessKey = config('services.weflexfy.access_key');
        $this->baseUrl = config('services.weflexfy.base_url', 'https://api.weflexfy.com');
    }

    public function initiatePayment(array $payload): array
    {
        $endpoint = '/api/v1/payment/initiate';
        $url = $this->baseUrl . $endpoint;

        Log::info('WeFlexfy Payment Payload:', $payload);

        $response = Http::withHeaders([
            'access_key' => $this->accessKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        Log::info('WeFlexfy Payment Response:', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return [
            'status' => 'error',
            'message' => $response->body(),
        ];
    }
}
