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
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'search' => [
        'provider'     => env('SEARCH_PROVIDER', 'google'),
        'endpoint'     => env('SEARCH_API_ENDPOINT'),
        'key'          => env('SEARCH_API_KEY'),
        'key_header'   => env('SEARCH_API_KEY_HEADER', ''), // empty = no auth header
        'key_prefix'   => env('SEARCH_API_KEY_PREFIX', ''),

        'query_param'  => env('SEARCH_API_QUERY_PARAM', 'q'),
        'timeout'      => env('SEARCH_API_TIMEOUT', 5),

        // Google-specific
        'google_cx'    => env('GOOGLE_SEARCH_ENGINE_ID'),
    ],


];
