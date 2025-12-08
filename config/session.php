<?php

use Illuminate\Support\Str;

return [

    'driver' => env('SESSION_DRIVER', 'database'),

    'lifetime' => env('SESSION_LIFETIME', 15), // Auto logout after 15 mins of inactivity

    'expire_on_close' => true, // Logout when browser or tab is closed

    'encrypt' => env('SESSION_ENCRYPT', true), // Encrypt session data

    'files' => storage_path('framework/sessions'),

    'connection' => env('SESSION_CONNECTION'),

    'table' => env('SESSION_TABLE', 'sessions'),

    'store' => env('SESSION_STORE'),

    'lottery' => [2, 100],

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug(env('APP_NAME', 'laravel'), '_') . '_session'
    ),

    'path' => env('SESSION_PATH', '/'),

    'domain' => env('SESSION_DOMAIN', null),

    'secure' => env('SESSION_SECURE_COOKIE', true), // Cookie only sent over HTTPS

    'http_only' => true, // Prevent JavaScript access to cookies

    'same_site' => env('SESSION_SAME_SITE', 'lax'), // Protect from CSRF

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

];
