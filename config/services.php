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

    'sipintu' => [
        'base_url' => env('SIPINTU_URL', env('SIPINTU_BASE_URL', env('SIPINTU_API_URL', 'https://sipintu.smkn1bangsri.sch.id'))),
        'url' => rtrim(env('SIPINTU_URL', env('SIPINTU_BASE_URL', env('SIPINTU_API_URL', 'https://sipintu.smkn1bangsri.sch.id'))), '/'),
        'api_url' => rtrim(env('SIPINTU_URL', env('SIPINTU_BASE_URL', env('SIPINTU_API_URL', 'https://sipintu.smkn1bangsri.sch.id'))), '/'),
        'client_id' => env('SIPINTU_CLIENT_ID'),
        'client_secret' => env('SIPINTU_CLIENT_SECRET'),
        'redirect_uri' => env('SIPINTU_REDIRECT_URI', env('APP_URL', 'http://localhost:8000') . '/oauth/callback'),
        // Prevent mass deactivation when a valid API response is anomalously small.
        'student_sync_min_active_ratio' => (float) env('SIPINTU_STUDENT_SYNC_MIN_ACTIVE_RATIO', 0.5),
    ],

    // config/services.php
'whatsapp_bot' => [
    'url'     => env('WA_BOT_URL', 'http://127.0.0.1:3789'),
    'api_key' => env('WA_BOT_API_KEY', 'sipintu_wa_secret_key_2026'),
    'enabled' => env('WA_BOT_ENABLED', true),
],

];
