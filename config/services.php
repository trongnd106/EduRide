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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'register_webhook_url' => env('REGISTER_SLACK_WEBHOOK_URL'),
        'consultation_webhook_url' => env('CONSULTATION_SLACK_WEBHOOK_URL'),
        'exchange_point_webhook_url' => env('EXCHANGE_POINT_SLACK_WEBHOOK_URL'),
        'contact_webhook_url' => env('CONTACT_SLACK_WEBHOOK_URL'),
        'comment_webhook_url' => env('COMMENT_SLACK_WEBHOOK_URL'),
    ],
    'google' => [
        'client_secret' => env('GGL_CLIENT_PRIVATE_KEY'),
        'sheet_id' => env('GOOGLE_SHEET_ID'),
        'sheet_name' => env('GOOGLE_SHEET_NAME'),
    ],

];
