<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    
'weflexfy' => [
    'access_key' => env('WEFLEXFY_ACCESS_KEY'),
    'secret_key' => env('WEFLEXFY_SECRET_KEY'),
    'base_url' => env('WEFLEXFY_BASE_URL', 'https://api.weflexfy.com'),
],

'flutterwave' => [
    'public_key' => env('FLW_PUBLIC_KEY'),
    'secret_key' => env('FLW_SECRET_KEY'),
    'encryption_key' => env('FLW_ENCRYPTION_KEY'),
     'secret_hash' => env('FLUTTERWAVE_SECRET_HASH'),
    'base_url' => env('FLW_BASE_URL', 'https://api.flutterwave.com'),
],
'twilio' => [
    'sid' => env('TWILIO_SID'),
    'token' => env('TWILIO_TOKEN'),
    'from' => env('TWILIO_FROM'),
],



    'twilio' => [
    'sid'   => env('TWILIO_SID'),
    'token' => env('TWILIO_TOKEN'),
    'from'  => env('TWILIO_FROM'),
],
'google_places' => [
    'key' => env('GOOGLE_PLACES_API_KEY'),
    'place_id' => env('GOOGLE_PLACE_ID'),
],



];
