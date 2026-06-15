<?php

use Illuminate\Support\Str;

return [

    'driver' => env('SESSION_DRIVER', 'database'),

    'lifetime' => env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

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

    'secure' => env('SESSION_SECURE_COOKIE', str_starts_with(env('APP_URL', ''), 'https')),

    'http_only' => true, // Prevent JavaScript access to cookies

    'same_site' => env('SESSION_SAME_SITE', 'lax'), // Protect from CSRF

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

];
