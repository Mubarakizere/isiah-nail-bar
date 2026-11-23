<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class WeFlexfyService
{
    private string $accessKey;
    private string $baseUrl;
    private string $recipientNumber;

    public function __construct()
    {
        $this->accessKey = (string) config('services.weflexfy.access_key', '');
        $this->baseUrl = rtrim((string) config('services.weflexfy.base_url', 'https://api.weflexfy.com'), '/');
        $this->recipientNumber = (string) config('services.weflexfy.recipient_number', '');
    }

    public function initiatePayment(array $payload): array
    {
        if ($this->accessKey === '' || $this->recipientNumber === '') {
            throw new RuntimeException('WeFlexy payment configuration is incomplete.');
        }

        try {
            // Leave enough time for Laravel to return a JSON pending response
            // before PHP's worker execution limit is reached.
            $response = Http::connectTimeout(3)
                ->timeout(12)
                ->withHeaders([
                    'access_key' => $this->accessKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl.'/api/v1/payment/initiate', $payload);
        } catch (ConnectionException $exception) {
            // The provider may have accepted the request before a timeout.
            // Keep its booking pending; never blindly send the charge again.
            Log::warning('WeFlexy payment initiation had no confirmed response.');
            throw $exception;
        }

        if (!$response->successful()) {
            Log::warning('WeFlexy rejected payment initiation.', ['http_status' => $response->status()]);
            if ($response->serverError()) {
                // A gateway 5xx may happen after it created the payment. Keep
                // the local attempt pending until a signed event settles it.
                throw new ConnectionException('WeFlexy payment initiation outcome is uncertain.');
            }
            throw new RuntimeException('WeFlexy could not start this payment.');
        }

        $data = $response->json();
        $providerData = is_array($data) ? ($data['data'] ?? null) : null;
        $iframeUrl = is_array($providerData)
            ? ($providerData['iframeUrl'] ?? $providerData['iframe_url'] ?? null)
            : null;

        if (!is_array($data) || ($data['status'] ?? null) !== 'success' || !is_string($iframeUrl)) {
            Log::warning('WeFlexy returned an incomplete payment initiation response.');
            throw new ConnectionException('WeFlexy returned no usable checkout URL.');
        }

        $url = parse_url($iframeUrl);
        $host = strtolower((string) ($url['host'] ?? ''));
        $isWeFlexyHost = $host === 'weflexfy.com' || str_ends_with($host, '.weflexfy.com');
        if (($url['scheme'] ?? null) !== 'https' || !$isWeFlexyHost) {
            Log::warning('WeFlexy returned a checkout URL outside its documented HTTPS domain.');
            throw new ConnectionException('WeFlexy returned an invalid checkout URL.');
        }

        return $data;
    }

    public function recipientNumber(): string
    {
        return $this->recipientNumber;
    }
}
