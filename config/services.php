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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Structural Pesapal API constants only. Credentials and the active
    // test/live environment are admin-configurable at runtime via the
    // payment_gateway_settings table (see PaymentGatewaySetting model).
    'pesapal' => [
        'sandbox_base_url' => env('PESAPAL_SANDBOX_BASE_URL', 'https://cybqa.pesapal.com/pesapalv3/api'),
        'live_base_url' => env('PESAPAL_LIVE_BASE_URL', 'https://pay.pesapal.com/v3/api'),
        'timeout' => env('PESAPAL_HTTP_TIMEOUT', 30),
        'token_ttl_buffer' => 30,
    ],

];
