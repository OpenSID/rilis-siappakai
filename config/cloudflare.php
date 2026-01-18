<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cloudflare API Configuration
    |--------------------------------------------------------------------------
    |
    | base_url           : Cloudflare API endpoint
    | api_retry_attempts : Number of retry attempts for failed API calls
    | retry_delay        : Delay (ms) between retry attempts
    | api_call_delay     : Delay (ms) between API calls to avoid rate limiting
    | per_page           : Number of records per API pagination request
    |
    */

    'base_url' => env('CLOUDFLARE_API_BASE_URL', 'https://api.cloudflare.com/client/v4'),

    'api_retry_attempts' => env('CLOUDFLARE_API_RETRY_ATTEMPTS', 3),

    'retry_delay' => env('CLOUDFLARE_RETRY_DELAY', 1000),

    'api_call_delay' => env('CLOUDFLARE_API_CALL_DELAY', 1000),

    'per_page' => env('CLOUDFLARE_PER_PAGE', 50),
];
