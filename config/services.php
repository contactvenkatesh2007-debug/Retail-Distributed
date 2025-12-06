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
        'token' => env('POSTMARK_TOKEN'),
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
    | Microservices Configuration
    |--------------------------------------------------------------------------
    |
    | Service endpoints for microservices architecture
    |
    */

    'microservices' => [
        'product' => [
            'base_url' => env('PRODUCT_SERVICE_URL', 'http://product-service:8001'),
            'timeout' => env('PRODUCT_SERVICE_TIMEOUT', 30),
        ],
        'order' => [
            'base_url' => env('ORDER_SERVICE_URL', 'http://order-service:8002'),
            'timeout' => env('ORDER_SERVICE_TIMEOUT', 30),
        ],
        'user' => [
            'base_url' => env('USER_SERVICE_URL', 'http://user-service:8003'),
            'timeout' => env('USER_SERVICE_TIMEOUT', 30),
        ],
        'payment' => [
            'base_url' => env('PAYMENT_SERVICE_URL', 'http://payment-service:8004'),
            'timeout' => env('PAYMENT_SERVICE_TIMEOUT', 30),
        ],
        'inventory' => [
            'base_url' => env('INVENTORY_SERVICE_URL', 'http://inventory-service:8005'),
            'timeout' => env('INVENTORY_SERVICE_TIMEOUT', 30),
        ],
        'notification' => [
            'base_url' => env('NOTIFICATION_SERVICE_URL', 'http://notification-service:8006'),
            'timeout' => env('NOTIFICATION_SERVICE_TIMEOUT', 30),
        ],
    ],

    'aws' => [
        'access_key_id' => env('AWS_ACCESS_KEY_ID'),
        'secret_access_key' => env('AWS_SECRET_ACCESS_KEY'),
        'default_region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'bucket' => env('AWS_BUCKET'),
        'ec2' => [
            'instance_id' => env('AWS_EC2_INSTANCE_ID'),
            'key_pair' => env('AWS_EC2_KEY_PAIR'),
            'security_group' => env('AWS_EC2_SECURITY_GROUP'),
        ],
    ],

];
