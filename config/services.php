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

    'reddit' => [
        'client_id' => env('REDDIT_CLIENT_ID'),
        'client_secret' => env('REDDIT_CLIENT_SECRET'),
        'redirect' => env('REDDIT_REDIRECT_URI'),
        'user_agent'    => env('REDDIT_USER_AGENT', 'Squire/2.0 by AKSquire2'),
        'subreddit'     => env('REDDIT_SUBREDDIT', 'AprilKnights'),
    ],

    'squire_api_token' => env('SQUIRE_API_TOKEN'),
    'squire_bot_webhook_url'    => env('SQUIRE_BOT_WEBHOOK_URL'),
    'squire_bot_webhook_secret' => env('SQUIRE_BOT_WEBHOOK_SECRET'),

];