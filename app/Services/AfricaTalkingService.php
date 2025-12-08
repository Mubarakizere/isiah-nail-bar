<?php

namespace App\Services;

use AfricasTalking\SDK\AfricasTalking;

class AfricaTalkingService
{
    protected $sms;

    public function __construct()
    {
        $username = config('services.africastalking.username');
        $apiKey = config('services.africastalking.api_key');

        $at = new AfricasTalking($username, $apiKey);
        $this->sms = $at->sms();
    }

    public function sendSms($to, $message)
    {
        try {
            return $this->sms->send([
                'to'      => $to,
                'message' => $message,
                'from'    => config('services.africastalking.from'), // optional
            ]);
        } catch (\Exception $e) {
            \Log::error('AfricaTalking SMS Error: ' . $e->getMessage());
            return false;
        }
    }
}
