<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Replicate API Token
    |--------------------------------------------------------------------------
    |
    | This value is the API token for authenticating with the Replicate API.
    | You can obtain this token from your Replicate account settings.
    |
    */

    'api_token' => env('REPLICATE_API_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Replicate API Base URL
    |--------------------------------------------------------------------------
    |
    | This is the base URL for the Replicate API. You shouldn't need to change
    | this unless you're using a different API endpoint.
    |
    */

    'base_url' => env('REPLICATE_BASE_URL', 'https://api.replicate.com/v1'),

    /*
    |--------------------------------------------------------------------------
    | File Encoding Strategy
    |--------------------------------------------------------------------------
    |
    | This option determines the strategy used for encoding files when sending
    | them to the Replicate API. The default strategy should work for most cases.
    |
    */

    'file_encoding_strategy' => env('REPLICATE_FILE_ENCODING_STRATEGY', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Use File Output
    |--------------------------------------------------------------------------
    |
    | This option determines whether to use file output for certain operations.
    | Set this to true if you want to save results to files instead of
    | returning them directly.
    |
    */

    'use_file_output' => env('REPLICATE_USE_FILE_OUTPUT', false),
];
