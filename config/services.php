<?php

return [

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'linkedin' => [
        'client_id' => env('LINKEDIN_CLIENT_ID'),
        'api_key' => env('LINKEDIN_KEY'),
        'client_secret' => env('LINKEDIN_SECRET'),
        'redirect' => env('LINKEDIN_REDIRECT_URI'),
    ],

];
