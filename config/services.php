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

    /*
    |--------------------------------------------------------------------------
    | Brevo (Sendinblue) Transactional Email API
    |--------------------------------------------------------------------------
    | Used for sending OTP emails via the Brevo HTTP API. The SMTP relay
    | is not used here because it requires IP whitelisting on the free plan.
    |
    */
    'brevo' => [
        'api_key'      => env('BREVO_API_KEY'),
        'sender_email' => env('BREVO_SENDER_EMAIL', 'g59kamikaze@gmail.com'),
        'sender_name'  => env('BREVO_SENDER_NAME', 'FMS Development'),
        'api_url'      => 'https://api.brevo.com/v3',
    ],

];
