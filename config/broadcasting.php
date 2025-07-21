<?php

return [

    'default' => env('BROADCAST_DRIVER', 'null'),

    'connections' => [


        'pusher' => [
            'driver' => 'pusher',
            'key' => env('REVERB_APP_KEY', 'dummy'),
            'secret' => env('REVERB_APP_SECRET', 'dummy'),
            'app_id' => env('REVERB_APP_ID', 'dummy'),
            'options' => [
                'host' => env('REVERB_HOST', '127.0.0.1'),
                'port' => env('REVERB_PORT', 8080),
                'scheme' => env('REVERB_SCHEME', 'http'),
                'encrypted' => false,
                'useTLS' => env('REVERB_TLS', false),
                'curl_options' => [
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSL_VERIFYPEER => 0,
                ],
            ],
        ],

        'ably' => [
            'driver' => 'ably',
            'key' => env('ABLY_KEY'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];