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

    'okeconnect' => [
        'base_url' => env('OKECONNECT_BASE_URL', 'https://h2h.okeconnect.com'),
        'member_id' => env('OKECONNECT_MEMBER_ID'),
        'pin' => env('OKECONNECT_PIN'),
        'password' => env('OKECONNECT_PASSWORD'),
        'price_api_id' => env('OKECONNECT_PRICE_API_ID'),
    ],

    'smmpanel' => [
        'endpoint' => env('SMMPANEL_ENDPOINT'),
        'api_id' => env('SMMPANEL_API_ID'),
        'api_key' => env('SMMPANEL_API_KEY'),
    ],

    'payday' => [
        'base_url' => env('PAYDAY_BASE_URL', 'https://api.payday.my.id'),
        'key' => env('PAYDAY_API_KEY'),
    ],

    'midtrans' => [
        'server_key'    => env('MIDTRANS_SERVER_KEY'),
        'client_key'    => env('MIDTRANS_CLIENT_KEY'),
        'merchant_id'   => env('MIDTRANS_MERCHANT_ID'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'is_sanitized'  => true,
        'is_3ds'        => true,
    ],

];
