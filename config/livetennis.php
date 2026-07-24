<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | API key
    |--------------------------------------------------------------------------
    | Your Live Tennis API key. Free tier: https://livetennisapi.com/subscribe/free
    */
    'key' => env('LIVE_TENNIS_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    */
    'base_url' => env('LIVE_TENNIS_API_BASE_URL', 'https://api.livetennisapi.com/api/public/v1'),

    /*
    |--------------------------------------------------------------------------
    | Auth header
    |--------------------------------------------------------------------------
    | 'bearer' sends Authorization: Bearer <key>; 'x-api-key' sends X-API-Key.
    */
    'auth_header' => env('LIVE_TENNIS_API_AUTH_HEADER', 'bearer'),

    /*
    |--------------------------------------------------------------------------
    | Transport
    |--------------------------------------------------------------------------
    */
    'timeout' => (float) env('LIVE_TENNIS_API_TIMEOUT', 30),
    'max_retries' => (int) env('LIVE_TENNIS_API_MAX_RETRIES', 2),

    /*
    |--------------------------------------------------------------------------
    | <x-tennis-scores /> defaults
    |--------------------------------------------------------------------------
    | Used when the Blade component is rendered without explicit attributes.
    */
    'scores' => [
        'status' => 'live',
        'limit' => 10,
    ],
];
