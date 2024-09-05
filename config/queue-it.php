<?php

return [

    'customer_id' => env('QUEUE_IT_CUSTOMER_ID'),

    'secret_key' => env('QUEUE_IT_SECRET_KEY'),

    'api_key' => env('QUEUE_IT_API_KEY'),

    'config_file' => env('QUEUE_IT_CONFIG_FILE'),

    'config_update_url' => env('QUEUE_IT_CONFIG_UPDATE_URL', '/queue-it/config'),

    'redirect_cache_headers' => [
        'no_store' => true,
        'no_cache' => true,
        'must_revalidate' => true,
        'max_age' => 0,
    ],

];
