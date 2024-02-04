<?php


return [
    'default' => 'bga',
    'servers' => [
        'bga' => [
            'host' => env('MQTT1_HOST', 'http://localhost'),
            'port' => env('MQTT1_PORT', 1883),
            'username' => env('MQTT1_USER', null),
            'password' => env('MQTT1_PASS', null),
        ],
        'hivemq' => [
            'host' => env('MQTT2_HOST', 'broker.hivemq.com'),
            'port' => env('MQTT2_PORT', 1883),
            'username' => env('MQTT2_USER', null),
            'password' => env('MQTT2_PASS', null),
        ]
    ]
];