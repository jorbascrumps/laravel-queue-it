<?php

return [

    'customer_id' => env('QUEUE_IT_CUSTOMER_ID'),

    'secret_key' => env('QUEUE_IT_SECRET_KEY'),

    'config_file' => env('QUEUE_IT_CONFIG_FILE'),

    'config_update_url' => env('QUEUE_IT_CONFIG_UPDATE_URL', '/queue-it/config'),

];
